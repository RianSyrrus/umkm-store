<?php

namespace App\Actions\Inventory;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdjustStock
{
    /**
     * Adjust the stock of a product variant.
     *
     * @throws \InvalidArgumentException
     */
    public function execute(ProductVariant $variant, int $quantity, string $type, ?string $reason = null, ?User $actor = null): ProductVariant
    {
        return DB::transaction(function () use ($variant, $quantity, $type, $reason, $actor) {
            // Retrieve variant with lock to prevent race conditions
            /** @var ProductVariant $lockedVariant */
            $lockedVariant = ProductVariant::query()
                ->where('id', $variant->id)
                ->lockForUpdate()
                ->firstOrFail();

            $newStock = $lockedVariant->stock_on_hand + $quantity;

            if ($newStock < 0) {
                throw new \InvalidArgumentException("Stok tidak mencukupi untuk melakukan penyesuaian (Stok saat ini: {$lockedVariant->stock_on_hand}, Penyesuaian: {$quantity}).");
            }

            // Update stock_on_hand
            $lockedVariant->update([
                'stock_on_hand' => $newStock,
            ]);

            // Write stock movement log
            StockMovement::create([
                'product_variant_id' => $lockedVariant->id,
                'quantity' => $quantity,
                'type' => $type,
                'reason' => $reason,
                'user_id' => $actor?->id,
            ]);

            return $lockedVariant;
        });
    }
}
