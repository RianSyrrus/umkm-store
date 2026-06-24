<?php

use App\Livewire\Storefront\CatalogPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Livewire\Livewire;

test('anyone can visit catalog page', function () {
    $store = Store::factory()->create([
        'name' => 'Toko Cemilan',
        'address' => 'Bandung',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Toko Cemilan')
        ->assertSee('Bandung');
});

test('catalog displays active products only', function () {
    $category = Category::factory()->create();
    $activeProduct = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Basreng Pedas',
        'is_active' => true,
    ]);
    $inactiveProduct = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Basreng Biasa',
        'is_active' => false,
    ]);

    Livewire::test(CatalogPage::class)
        ->assertSee('Basreng Pedas')
        ->assertDontSee('Basreng Biasa');
});

test('catalog can filter by category', function () {
    $categoryA = Category::factory()->create(['name' => 'Cemilan']);
    $categoryB = Category::factory()->create(['name' => 'Minuman']);

    $productA = Product::factory()->create([
        'category_id' => $categoryA->id,
        'name' => 'Keripik Kaca',
        'is_active' => true,
    ]);
    $productB = Product::factory()->create([
        'category_id' => $categoryB->id,
        'name' => 'Es Teh Manis',
        'is_active' => true,
    ]);

    Livewire::test(CatalogPage::class)
        ->set('selectedCategoryId', $categoryA->id)
        ->assertSee('Keripik Kaca')
        ->assertDontSee('Es Teh Manis');
});

test('catalog can filter by sale mode', function () {
    $category = Category::factory()->create();

    $readyProduct = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Sosis Bakar',
        'sale_mode' => 'ready_stock',
        'is_active' => true,
    ]);
    $poProduct = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Tumpeng Mini',
        'sale_mode' => 'preorder',
        'is_active' => true,
    ]);

    Livewire::test(CatalogPage::class)
        ->set('saleModeFilter', 'ready_stock')
        ->assertSee('Sosis Bakar')
        ->assertDontSee('Tumpeng Mini');
});
