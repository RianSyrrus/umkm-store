<?php

use App\Livewire\Admin\StoreSettingsPage;
use App\Models\Store;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from store settings', function () {
    $this->get(route('admin.settings'))
        ->assertRedirect(route('login'));
});

test('allows admin to update store settings', function () {
    $this->actingAs(User::factory()->create());

    // Create initial store
    $store = Store::create([
        'name' => 'Toko Awal',
        'whatsapp' => '6281234567890',
        'address' => 'Alamat Awal',
        'latitude' => 0.0,
        'longitude' => 0.0,
        'base_delivery_fee' => 0,
        'delivery_fee_per_km' => 0,
    ]);

    Livewire::test(StoreSettingsPage::class)
        ->set('name', 'Dapur Rasa')
        ->set('whatsapp', '081234567890')
        ->set('address', 'Jakarta')
        ->set('latitude', -6.2000000)
        ->set('longitude', 106.8166667)
        ->set('baseDeliveryFee', 5000)
        ->set('deliveryFeePerKm', 2500)
        ->set('lowStockThreshold', 5)
        ->call('save')
        ->assertHasNoErrors();

    $freshStore = Store::first();
    expect($freshStore->name)->toBe('Dapur Rasa')
        ->and($freshStore->whatsapp)->toBe('6281234567890') // normalized!
        ->and($freshStore->max_delivery_distance_meters)->toBe(10000);
});
