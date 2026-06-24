<?php

use App\Models\Addon;
use App\Models\Category;
use App\Models\OptionGroup;
use App\Models\OptionValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Catalog\ProductConfigurationValidator;

test('validates and calculates correct prices for a valid product configuration', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    // Create variant
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 15000,
        'is_active' => true,
    ]);

    // Create required option group (Level Pedas: 1-1)
    $groupA = OptionGroup::factory()->create([
        'name' => 'Level Pedas',
        'is_required' => true,
        'min_selected' => 1,
        'max_selected' => 1,
    ]);
    $optVal1 = OptionValue::factory()->create(['option_group_id' => $groupA->id, 'price_delta' => 0, 'is_active' => true]);
    $optVal2 = OptionValue::factory()->create(['option_group_id' => $groupA->id, 'price_delta' => 1000, 'is_active' => true]);
    $product->optionGroups()->attach($groupA);

    // Create addon
    $addon = Addon::factory()->create(['price' => 2000, 'is_active' => true]);
    $product->addons()->attach($addon);

    $validator = new ProductConfigurationValidator;

    // Valid configuration input
    $input = [
        'variant_id' => $variant->id,
        'options' => [
            $groupA->id => [$optVal2->id], // +1000
        ],
        'addons' => [
            $addon->id, // +2000
        ],
        'quantity' => 2,
    ];

    $validated = $validator->validate($product, $input);

    expect($validated->unitPrice)->toBe(18000) // 15000 + 1000 + 2000
        ->and($validated->totalPrice)->toBe(36000) // 18000 * 2
        ->and($validated->variant->id)->toBe($variant->id)
        ->and($validated->options->pluck('id'))->toContain($optVal2->id)
        ->and($validated->addons->pluck('id'))->toContain($addon->id);
});

test('throws error if variant is missing or invalid', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $validator = new ProductConfigurationValidator;

    expect(fn () => $validator->validate($product, ['variant_id' => 999]))
        ->toThrow(InvalidArgumentException::class, 'Varian produk tidak valid.');
});

test('throws error if required option is missing', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 10000]);

    $group = OptionGroup::factory()->create([
        'name' => 'Rasa',
        'is_required' => true,
        'min_selected' => 1,
        'max_selected' => 1,
    ]);
    $product->optionGroups()->attach($group);

    $validator = new ProductConfigurationValidator;

    expect(fn () => $validator->validate($product, [
        'variant_id' => $variant->id,
        'options' => [],
    ]))->toThrow(InvalidArgumentException::class, "Pilihan 'Rasa' wajib diisi.");
});

test('throws error if options selected exceed max allowed limit', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 10000]);

    $group = OptionGroup::factory()->create([
        'name' => 'Topping Gratis',
        'is_required' => false,
        'min_selected' => 0,
        'max_selected' => 1,
    ]);
    $val1 = OptionValue::factory()->create(['option_group_id' => $group->id]);
    $val2 = OptionValue::factory()->create(['option_group_id' => $group->id]);
    $product->optionGroups()->attach($group);

    $validator = new ProductConfigurationValidator;

    expect(fn () => $validator->validate($product, [
        'variant_id' => $variant->id,
        'options' => [
            $group->id => [$val1->id, $val2->id],
        ],
    ]))->toThrow(InvalidArgumentException::class, "Pilihan 'Topping Gratis' maksimal hanya boleh memilih 1 opsi.");
});
