<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

test('persists product variants under a category', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();
    $variant = ProductVariant::factory()->for($product)->create([
        'price' => 18000,
        'stock_on_hand' => 10,
        'reserved_stock' => 2,
    ]);

    expect($category->products)->toHaveCount(1)
        ->and($product->variants)->toHaveCount(1)
        ->and($variant->available_stock)->toBe(8);
});
