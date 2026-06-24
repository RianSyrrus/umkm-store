<?php

use App\Livewire\Admin\InventoryPage;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from inventory page', function () {
    $this->get(route('admin.inventory'))
        ->assertRedirect(route('login'));
});

test('admin can view inventory list', function () {
    $this->actingAs(User::factory()->create());

    $variant = ProductVariant::factory()->create([
        'sku' => 'TEST-SKU-123',
        'name' => 'Ukuran Standar',
        'stock_on_hand' => 10,
    ]);

    Livewire::test(InventoryPage::class)
        ->assertSee('TEST-SKU-123')
        ->assertSee('Ukuran Standar')
        ->assertSee('10');
});

test('admin can adjust stock to increase it', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $variant = ProductVariant::factory()->create([
        'stock_on_hand' => 10,
        'reserved_stock' => 2,
    ]);

    Livewire::test(InventoryPage::class)
        ->call('openAdjustment', $variant->id)
        ->assertSet('selectedVariantId', $variant->id)
        ->set('adjustQuantity', 5)
        ->set('adjustType', 'restock')
        ->set('adjustReason', 'Barang baru datang')
        ->call('submitAdjustment')
        ->assertHasNoErrors()
        ->assertSet('showAdjustmentModal', false);

    expect($variant->fresh()->stock_on_hand)->toBe(15);

    $movement = StockMovement::where('product_variant_id', $variant->id)->first();
    expect($movement)->not->toBeNull()
        ->and($movement->quantity)->toBe(5)
        ->and($movement->type)->toBe('restock')
        ->and($movement->reason)->toBe('Barang baru datang')
        ->and($movement->user_id)->toBe($admin->id);
});

test('admin can adjust stock to decrease it', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $variant = ProductVariant::factory()->create([
        'stock_on_hand' => 10,
        'reserved_stock' => 2,
    ]);

    Livewire::test(InventoryPage::class)
        ->call('openAdjustment', $variant->id)
        ->set('adjustQuantity', -4)
        ->set('adjustType', 'waste')
        ->set('adjustReason', 'Rusak')
        ->call('submitAdjustment')
        ->assertHasNoErrors();

    expect($variant->fresh()->stock_on_hand)->toBe(6);
});

test('admin cannot adjust stock below zero', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $variant = ProductVariant::factory()->create([
        'stock_on_hand' => 5,
        'reserved_stock' => 0,
    ]);

    Livewire::test(InventoryPage::class)
        ->call('openAdjustment', $variant->id)
        ->set('adjustQuantity', -6)
        ->set('adjustType', 'waste')
        ->set('adjustReason', 'Kedaluwarsa')
        ->call('submitAdjustment')
        ->assertHasErrors(['adjustQuantity']);

    expect($variant->fresh()->stock_on_hand)->toBe(5); // unchanged
});
