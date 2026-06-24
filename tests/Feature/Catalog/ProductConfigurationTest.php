<?php

use App\Models\Addon;
use App\Models\OptionGroup;
use App\Models\OptionValue;
use App\Models\Product;

test('loads active options and addons for a product', function () {
    $product = Product::factory()->create();
    $group = OptionGroup::factory()->create([
        'name' => 'Level Pedas',
        'is_required' => true,
        'min_selected' => 1,
        'max_selected' => 1,
    ]);
    OptionValue::factory()->for($group)->create(['name' => 'Pedas']);
    $addon = Addon::factory()->create(['name' => 'Keju', 'price' => 3000]);

    $product->optionGroups()->attach($group);
    $product->addons()->attach($addon);

    expect($product->load('optionGroups.values', 'addons')->optionGroups)->toHaveCount(1)
        ->and($product->addons)->toHaveCount(1);
});
