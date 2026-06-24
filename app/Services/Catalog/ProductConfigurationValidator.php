<?php

namespace App\Services\Catalog;

use App\Data\ValidatedProductConfiguration;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class ProductConfigurationValidator
{
    /**
     * Validate the product configuration.
     *
     * @throws \InvalidArgumentException
     */
    public function validate(Product $product, array $input): ValidatedProductConfiguration
    {
        $quantity = (int) ($input['quantity'] ?? 1);
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Jumlah item minimal adalah 1.');
        }

        // 1. Validate Variant
        $variantId = $input['variant_id'] ?? null;
        if (! $variantId) {
            throw new \InvalidArgumentException('Varian produk wajib dipilih.');
        }

        /** @var ProductVariant|null $variant */
        $variant = $product->variants()
            ->where('id', $variantId)
            ->where('is_active', true)
            ->first();

        if (! $variant) {
            throw new \InvalidArgumentException('Varian produk tidak valid.');
        }

        $basePrice = $variant->price;
        $optionPriceDelta = 0;
        $addonPriceSum = 0;

        $selectedOptionValues = new Collection;
        $selectedAddons = new Collection;

        // 2. Validate Option Groups
        $inputOptions = $input['options'] ?? []; // option_group_id => array of option_value_id

        // Eager load option groups and their values
        $product->loadMissing('optionGroups.values');

        foreach ($product->optionGroups as $group) {
            $selectedIds = $inputOptions[$group->id] ?? [];
            if (! is_array($selectedIds)) {
                $selectedIds = $selectedIds ? [$selectedIds] : [];
            }

            // Remove empty/null values
            $selectedIds = array_filter($selectedIds);

            $selectedCount = count($selectedIds);

            // Validation: Required
            if ($group->is_required && $selectedCount === 0) {
                throw new \InvalidArgumentException("Pilihan '{$group->name}' wajib diisi.");
            }

            // Validation: Min selected
            if ($selectedCount < $group->min_selected) {
                throw new \InvalidArgumentException("Pilihan '{$group->name}' minimal harus memilih {$group->min_selected} opsi.");
            }

            // Validation: Max selected
            if ($selectedCount > $group->max_selected) {
                throw new \InvalidArgumentException("Pilihan '{$group->name}' maksimal hanya boleh memilih {$group->max_selected} opsi.");
            }

            // Validation: Ensure values belong to group
            foreach ($selectedIds as $valueId) {
                $value = $group->values->where('id', $valueId)->where('is_active', true)->first();
                if (! $value) {
                    throw new \InvalidArgumentException('Opsi pilihan tidak valid.');
                }
                $selectedOptionValues->push($value);
                $optionPriceDelta += $value->price_delta;
            }
        }

        // 3. Validate Addons
        $inputAddons = $input['addons'] ?? [];
        if (! is_array($inputAddons)) {
            $inputAddons = $inputAddons ? [$inputAddons] : [];
        }
        $inputAddons = array_filter($inputAddons);

        if (! empty($inputAddons)) {
            $product->loadMissing('addons');

            foreach ($inputAddons as $addonId) {
                $addon = $product->addons->where('id', $addonId)->where('is_active', true)->first();
                if (! $addon) {
                    throw new \InvalidArgumentException('Add-on tidak valid.');
                }
                $selectedAddons->push($addon);
                $addonPriceSum += $addon->price;
            }
        }

        // 4. Calculate pricing
        $unitPrice = (int) max(0, $basePrice + $optionPriceDelta + $addonPriceSum);
        $totalPrice = $unitPrice * $quantity;

        return new ValidatedProductConfiguration(
            variant: $variant,
            options: $selectedOptionValues,
            addons: $selectedAddons,
            quantity: $quantity,
            unitPrice: $unitPrice,
            totalPrice: $totalPrice
        );
    }
}
