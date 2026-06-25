<?php

use App\Exceptions\OutOfStockException;
use App\Exceptions\ScheduleSlotFullException;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\ScheduleSlot;
use App\Models\StockMovement;
use App\Services\Inventory\InventoryReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new InventoryReservationService;
});

test('reserves stock and schedule slot successfully', function () {
    $slot = ScheduleSlot::factory()->create([
        'quota' => 5,
        'reserved_count' => 1,
        'is_active' => true,
        'order_deadline' => now()->addHour(),
    ]);

    $variant = ProductVariant::factory()->create([
        'stock_on_hand' => 10,
        'reserved_stock' => 2,
    ]);

    $order = Order::factory()->create([
        'schedule_slot_id' => $slot->id,
    ]);

    $item = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
    ]);

    // Load relation
    $order->load('items');

    $this->service->reserve($order);

    // Assertions
    $slot->refresh();
    $variant->refresh();

    expect($slot->reserved_count)->toBe(2);
    expect($variant->reserved_stock)->toBe(5);

    $reservation = InventoryReservation::first();
    expect($reservation)->not->toBeNull();
    expect($reservation->order_id)->toBe($order->id);
    expect($reservation->product_variant_id)->toBe($variant->id);
    expect($reservation->quantity)->toBe(3);
    expect($reservation->status)->toBe('active');
});

test('throws out of stock exception when stock is insufficient', function () {
    $slot = ScheduleSlot::factory()->create([
        'quota' => 5,
        'reserved_count' => 0,
        'is_active' => true,
        'order_deadline' => now()->addHour(),
    ]);

    $variant = ProductVariant::factory()->create([
        'stock_on_hand' => 5,
        'reserved_stock' => 3, // available is 2
    ]);

    $order = Order::factory()->create([
        'schedule_slot_id' => $slot->id,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3, // needs 3, but only 2 available
    ]);

    $order->load('items');

    expect(fn () => $this->service->reserve($order))
        ->toThrow(OutOfStockException::class);

    // Verify database remains unchanged due to rollback
    $slot->refresh();
    $variant->refresh();
    expect($slot->reserved_count)->toBe(0);
    expect($variant->reserved_stock)->toBe(3);
    expect(InventoryReservation::count())->toBe(0);
});

test('throws schedule slot full exception when slot quota is reached', function () {
    $slot = ScheduleSlot::factory()->create([
        'quota' => 2,
        'reserved_count' => 2, // fully booked
        'is_active' => true,
        'order_deadline' => now()->addHour(),
    ]);

    $variant = ProductVariant::factory()->create([
        'stock_on_hand' => 10,
        'reserved_stock' => 0,
    ]);

    $order = Order::factory()->create([
        'schedule_slot_id' => $slot->id,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
    ]);

    $order->load('items');

    expect(fn () => $this->service->reserve($order))
        ->toThrow(ScheduleSlotFullException::class);

    $slot->refresh();
    $variant->refresh();
    expect($slot->reserved_count)->toBe(2);
    expect($variant->reserved_stock)->toBe(0);
});

test('commits reservations and reduces stock on hand', function () {
    $variant = ProductVariant::factory()->create([
        'stock_on_hand' => 10,
        'reserved_stock' => 3,
    ]);

    $order = Order::factory()->create();

    $reservation = InventoryReservation::create([
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
        'status' => 'active',
        'expires_at' => now()->addMinutes(30),
    ]);

    $this->service->commit($order);

    $reservation->refresh();
    $variant->refresh();

    expect($reservation->status)->toBe('committed');
    expect($reservation->committed_at)->not->toBeNull();
    expect($variant->stock_on_hand)->toBe(7); // 10 - 3
    expect($variant->reserved_stock)->toBe(0); // 3 - 3

    // Check stock movement log
    $movement = StockMovement::first();
    expect($movement)->not->toBeNull();
    expect($movement->product_variant_id)->toBe($variant->id);
    expect($movement->quantity)->toBe(-3);
    expect($movement->type)->toBe('sale');
});

test('releases reservations and restores reserved stock', function () {
    $slot = ScheduleSlot::factory()->create([
        'quota' => 5,
        'reserved_count' => 2,
    ]);

    $variant = ProductVariant::factory()->create([
        'stock_on_hand' => 10,
        'reserved_stock' => 4,
    ]);

    $order = Order::factory()->create([
        'schedule_slot_id' => $slot->id,
    ]);

    $reservation = InventoryReservation::create([
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
        'status' => 'active',
        'expires_at' => now()->addMinutes(30),
    ]);

    $this->service->release($order);

    $reservation->refresh();
    $variant->refresh();
    $slot->refresh();

    expect($reservation->status)->toBe('released');
    expect($reservation->released_at)->not->toBeNull();
    expect($variant->reserved_stock)->toBe(1); // 4 - 3
    expect($variant->stock_on_hand)->toBe(10); // remains unchanged
    expect($slot->reserved_count)->toBe(1); // 2 - 1
});
