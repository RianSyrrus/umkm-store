<?php

use App\Livewire\Admin\CategoryIndex;
use App\Livewire\Admin\ProductForm;
use App\Livewire\Admin\ProductIndex;
use App\Models\Addon;
use App\Models\Category;
use App\Models\OptionGroup;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from catalog pages', function () {
    $this->get(route('admin.categories'))->assertRedirect(route('login'));
    $this->get(route('admin.products'))->assertRedirect(route('login'));
    $this->get(route('admin.products.create'))->assertRedirect(route('login'));
});

test('admin can manage categories', function () {
    $this->actingAs(User::factory()->create());

    // 1. Create a category
    Livewire::test(CategoryIndex::class)
        ->set('name', 'Makanan Utama')
        ->set('description', 'Kategori untuk makanan utama')
        ->set('is_active', true)
        ->set('sort_order', 1)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('name', '')
        ->assertSet('description', '');

    $category = Category::where('slug', 'makanan-utama')->first();
    expect($category)->not->toBeNull()
        ->and($category->name)->toBe('Makanan Utama')
        ->and($category->description)->toBe('Kategori untuk makanan utama')
        ->and($category->sort_order)->toBe(1);

    // 2. Edit the category
    Livewire::test(CategoryIndex::class)
        ->call('editCategory', $category->id)
        ->assertSet('editingCategoryId', $category->id)
        ->assertSet('name', 'Makanan Utama')
        ->set('name', 'Makanan Utama Edit')
        ->call('save')
        ->assertHasNoErrors();

    expect($category->fresh()->name)->toBe('Makanan Utama Edit');

    // 3. Delete the category
    Livewire::test(CategoryIndex::class)
        ->call('deleteCategory', $category->id);

    expect($category->fresh()->deleted_at)->not->toBeNull();
});

test('admin can list products', function () {
    $this->actingAs(User::factory()->create());

    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Nasi Goreng Spesial',
    ]);

    Livewire::test(ProductIndex::class)
        ->assertSee('Nasi Goreng Spesial');
});

test('admin can create product with variants, options, and addons', function () {
    $this->actingAs(User::factory()->create());

    $category = Category::factory()->create();
    $addon = Addon::factory()->create(['name' => 'Kerupuk', 'price' => 2000]);
    $optionGroup = OptionGroup::factory()->create(['name' => 'Level Pedas']);

    // Call the form to create a new product
    Livewire::test(ProductForm::class)
        ->set('categoryId', $category->id)
        ->set('name', 'Mie Goreng Aceh')
        ->set('description', 'Mie khas Aceh')
        ->set('saleMode', 'ready_stock')
        ->set('isActive', true)
        ->set('isFeatured', true)
        // Add a variant row
        ->set('variants', [
            [
                'name' => 'Porsi Biasa',
                'sku' => 'MIE-ACEH-REG',
                'price' => 15000,
                'stock_on_hand' => 50,
            ],
            [
                'name' => 'Porsi Jumbo',
                'sku' => 'MIE-ACEH-JUMBO',
                'price' => 20000,
                'stock_on_hand' => 30,
            ],
        ])
        // Select options & addons
        ->set('selectedOptionGroups', [$optionGroup->id])
        ->set('selectedAddons', [$addon->id])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.products'));

    $product = Product::where('slug', 'mie-goreng-aceh')->first();
    expect($product)->not->toBeNull()
        ->and($product->category_id)->toBe($category->id)
        ->and($product->variants)->toHaveCount(2)
        ->and($product->optionGroups)->toHaveCount(1)
        ->and($product->addons)->toHaveCount(1);

    expect($product->variants[0]->name)->toBe('Porsi Biasa')
        ->and($product->variants[0]->sku)->toBe('MIE-ACEH-REG')
        ->and($product->variants[0]->price)->toBe(15000)
        ->and($product->variants[0]->stock_on_hand)->toBe(50);
});

test('admin can edit product', function () {
    $this->actingAs(User::factory()->create());

    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Sate Ayam',
    ]);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => '10 Tusuk',
        'sku' => 'SATE-10',
        'price' => 20000,
        'stock_on_hand' => 100,
    ]);

    Livewire::test(ProductForm::class, ['product' => $product])
        ->assertSet('name', 'Sate Ayam')
        ->set('name', 'Sate Madura')
        ->set('variants', [
            [
                'id' => $variant->id,
                'name' => '10 Tusuk Edit',
                'sku' => 'SATE-10-EDIT',
                'price' => 22000,
                'stock_on_hand' => 90,
            ],
        ])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.products'));

    expect($product->fresh()->name)->toBe('Sate Madura');
    expect($variant->fresh()->name)->toBe('10 Tusuk Edit');
    expect($variant->fresh()->price)->toBe(22000);
});

test('admin can delete product', function () {
    $this->actingAs(User::factory()->create());

    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Kopi Tubruk',
    ]);

    Livewire::test(ProductIndex::class)
        ->call('deleteProduct', $product->id)
        ->assertHasNoErrors();

    expect($product->fresh()->deleted_at)->not->toBeNull();
});
