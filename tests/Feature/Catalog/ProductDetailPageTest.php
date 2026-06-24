<?php

use App\Livewire\Storefront\ProductDetailPage;
use App\Models\Category;
use App\Models\OptionGroup;
use App\Models\OptionValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Livewire\Livewire;

test('anyone can visit product detail page', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Basreng Pedas',
        'is_active' => true,
    ]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Bungkus Besar',
        'price' => 12000,
    ]);

    $this->get(route('products.show', $product->slug))
        ->assertOk()
        ->assertSee('Basreng Pedas')
        ->assertSee('Bungkus Besar');
});

test('detail page pricing updates dynamically', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variant1 = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 10000]);
    $variant2 = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 15000]);

    Livewire::test(ProductDetailPage::class, ['product' => $product])
        ->assertSet('variantId', $variant1->id)
        ->assertSet('unitPrice', 10000)
        ->assertSet('totalPrice', 10000)
        // Select variant 2
        ->set('variantId', $variant2->id)
        ->assertSet('unitPrice', 15000)
        ->assertSet('totalPrice', 15000)
        // Increase quantity
        ->call('incrementQuantity')
        ->assertSet('quantity', 2)
        ->assertSet('totalPrice', 30000);
});

test('submitting invalid configuration shows error toast', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 10000]);

    // Required option group
    $group = OptionGroup::factory()->create([
        'name' => 'Level Pedas',
        'is_required' => true,
        'min_selected' => 1,
        'max_selected' => 1,
    ]);
    OptionValue::factory()->create(['option_group_id' => $group->id]);
    $product->optionGroups()->attach($group);

    Livewire::test(ProductDetailPage::class, ['product' => $product])
        ->set('variantId', $variant->id)
        ->set('selectedOptions.'.$group->id, []) // empty selection
        ->call('submitConfiguration')
        ->assertDispatched('toast', message: "Pilihan 'Level Pedas' wajib diisi.", variant: 'error');
});

test('submitting valid configuration shows success toast', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 10000]);

    $group = OptionGroup::factory()->create([
        'name' => 'Level Pedas',
        'is_required' => true,
        'min_selected' => 1,
        'max_selected' => 1,
    ]);
    $value = OptionValue::factory()->create(['option_group_id' => $group->id, 'price_delta' => 1000]);
    $product->optionGroups()->attach($group);

    Livewire::test(ProductDetailPage::class, ['product' => $product])
        ->set('variantId', $variant->id)
        ->set('selectedOptions.'.$group->id.'.0', $value->id)
        ->call('submitConfiguration')
        ->assertDispatched('toast', message: 'Item berhasil ditambahkan ke keranjang (Demo). Total: Rp11.000', variant: 'success');
});
