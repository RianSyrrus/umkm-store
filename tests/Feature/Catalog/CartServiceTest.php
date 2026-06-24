<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Cart\CartService;

test('adds and retrieves items from cart session', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 10000,
        'stock_on_hand' => 10,
        'is_active' => true,
    ]);

    $cartService = new CartService;
    $cartService->clear();

    $cartService->add($product, [
        'variant_id' => $variant->id,
        'options' => [],
        'addons' => [],
        'quantity' => 2,
        'notes' => 'Tolong pedas',
    ]);

    $items = $cartService->get();
    expect($items)->toHaveCount(1);

    $item = $items->first();
    expect($item['quantity'])->toBe(2)
        ->and($item['unit_price'])->toBe(10000)
        ->and($item['total_price'])->toBe(20000)
        ->and($item['notes'])->toBe('Tolong pedas')
        ->and($item['variant']->id)->toBe($variant->id);
});

test('adds duplicate items and merges their quantity', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 10000,
        'stock_on_hand' => 10,
        'is_active' => true,
    ]);

    $cartService = new CartService;
    $cartService->clear();

    // Add first time
    $cartService->add($product, [
        'variant_id' => $variant->id,
        'options' => [],
        'addons' => [],
        'quantity' => 1,
    ]);

    // Add second time (same config)
    $cartService->add($product, [
        'variant_id' => $variant->id,
        'options' => [],
        'addons' => [],
        'quantity' => 2,
    ]);

    $items = $cartService->get();
    expect($items)->toHaveCount(1)
        ->and($items->first()['quantity'])->toBe(3)
        ->and($items->first()['total_price'])->toBe(30000);
});

test('throws exception when adding quantity exceeding stock limit', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'sale_mode' => 'ready_stock']);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 10000,
        'stock_on_hand' => 5,
        'is_active' => true,
    ]);

    $cartService = new CartService;
    $cartService->clear();

    expect(fn () => $cartService->add($product, [
        'variant_id' => $variant->id,
        'options' => [],
        'addons' => [],
        'quantity' => 6,
    ]))->toThrow(InvalidArgumentException::class, 'Stok tidak mencukupi.');
});

test('can update item quantity and remove it', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 10000,
        'stock_on_hand' => 10,
        'is_active' => true,
    ]);

    $cartService = new CartService;
    $cartService->clear();

    $cartService->add($product, [
        'variant_id' => $variant->id,
        'options' => [],
        'addons' => [],
        'quantity' => 2,
    ]);

    $itemId = $cartService->get()->first()['id'];

    // Update quantity to 4
    $cartService->updateQuantity($itemId, 4);
    expect($cartService->get()->first()['quantity'])->toBe(4)
        ->and($cartService->getTotalPrice())->toBe(40000);

    // Remove item
    $cartService->remove($itemId);
    expect($cartService->get())->toBeEmpty();
});
