<?php

use App\Livewire\Storefront\CartPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Cart\CartService;
use Livewire\Livewire;

test('anyone can visit cart page', function () {
    $this->get(route('home.cart'))
        ->assertOk()
        ->assertSee('Keranjang Belanja');
});

test('cart page displays items and updates quantity', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Kopi Tubruk']);
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

    Livewire::test(CartPage::class)
        ->assertSee('Kopi Tubruk')
        ->assertSee('Rp20.000')
        ->call('increment', $itemId)
        ->assertSet('total', 30000)
        ->call('decrement', $itemId)
        ->assertSet('total', 20000)
        ->call('removeItem', $itemId)
        ->assertSee('Keranjang Anda Kosong');
});
