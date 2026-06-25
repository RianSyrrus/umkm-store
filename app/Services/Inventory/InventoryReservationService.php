<?php

namespace App\Services\Inventory;

use App\Exceptions\OutOfStockException;
use App\Exceptions\ScheduleSlotFullException;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ScheduleSlot;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryReservationService
{
    /**
     * Reserve stock for order items and slot capacity for the order.
     * Uses database row locking (lockForUpdate) to prevent race conditions.
     *
     * @throws OutOfStockException
     * @throws ScheduleSlotFullException
     */
    public function reserve(Order $order, int $expiryMinutes = 30): void
    {
        DB::transaction(function () use ($order, $expiryMinutes) {
            // 1. Lock and check ScheduleSlot capacity if the order is associated with a slot
            if ($order->schedule_slot_id) {
                /** @var ScheduleSlot|null $slot */
                $slot = ScheduleSlot::where('id', $order->schedule_slot_id)
                    ->lockForUpdate()
                    ->first();

                if (! $slot) {
                    throw new ScheduleSlotFullException('Schedule slot not found.');
                }

                if (! $slot->is_active || now()->gte($slot->order_deadline)) {
                    throw new ScheduleSlotFullException('The selected schedule slot is no longer available.');
                }

                if ($slot->reserved_count >= $slot->quota) {
                    throw new ScheduleSlotFullException('The selected schedule slot is fully booked.');
                }

                // Increment reserved count
                $slot->increment('reserved_count');
            }

            // 2. Lock and check ProductVariant stock for each item in the order
            foreach ($order->items as $item) {
                if (! $item->product_variant_id) {
                    continue;
                }

                /** @var ProductVariant|null $variant */
                $variant = ProductVariant::where('id', $item->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if (! $variant) {
                    throw new OutOfStockException('Product variant not found.');
                }

                $availableStock = $variant->stock_on_hand - $variant->reserved_stock;
                if ($availableStock < $item->quantity) {
                    throw new OutOfStockException("Product '{$item->product_name}' ({$item->variant_name}) is out of stock. Only {$availableStock} left.");
                }

                // Create inventory reservation record
                InventoryReservation::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $item->quantity,
                    'status' => 'active',
                    'expires_at' => now()->addMinutes($expiryMinutes),
                ]);

                // Increment reserved stock
                $variant->increment('reserved_stock', $item->quantity);
            }
        });
    }

    /**
     * Commit the active reservations when payment succeeds.
     * Reduces physical stock on hand.
     */
    public function commit(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $reservations = InventoryReservation::where('order_id', $order->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get();

            foreach ($reservations as $reservation) {
                /** @var ProductVariant|null $variant */
                $variant = ProductVariant::where('id', $reservation->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if ($variant) {
                    // Reduce reserved stock and stock on hand
                    $variant->decrement('reserved_stock', $reservation->quantity);
                    $variant->decrement('stock_on_hand', $reservation->quantity);

                    // Log stock movement
                    StockMovement::create([
                        'product_variant_id' => $variant->id,
                        'quantity' => -$reservation->quantity,
                        'type' => 'sale',
                        'reason' => "Order {$order->order_code} payment completed.",
                    ]);
                }

                $reservation->update([
                    'status' => 'committed',
                    'committed_at' => now(),
                ]);
            }
        });
    }

    /**
     * Release/cancel the active reservations when order is cancelled or expired.
     */
    public function release(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // 1. Release ScheduleSlot reservation if applicable
            if ($order->schedule_slot_id) {
                /** @var ScheduleSlot|null $slot */
                $slot = ScheduleSlot::where('id', $order->schedule_slot_id)
                    ->lockForUpdate()
                    ->first();

                if ($slot && $slot->reserved_count > 0) {
                    $slot->decrement('reserved_count');
                }
            }

            // 2. Release ProductVariant reserved stock
            $reservations = InventoryReservation::where('order_id', $order->id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->get();

            foreach ($reservations as $reservation) {
                /** @var ProductVariant|null $variant */
                $variant = ProductVariant::where('id', $reservation->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if ($variant) {
                    $variant->decrement('reserved_stock', $reservation->quantity);
                }

                $reservation->update([
                    'status' => 'released',
                    'released_at' => now(),
                ]);
            }
        });
    }
}
