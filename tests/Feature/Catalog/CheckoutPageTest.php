<?php

use App\Livewire\Storefront\CheckoutPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ScheduleSlot;
use App\Models\Store;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    // Fake Midtrans Snap API
    Http::fake([
        'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
            'token' => 'mock-snap-token-123',
            'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/pay?token=mock-snap-token-123',
        ], 201),
    ]);

    // Ensure we have a store record
    $this->store = Store::factory()->create([
        'latitude' => -6.9174639, // Bandung
        'longitude' => 107.6191228,
        'base_delivery_fee' => 5000,
        'delivery_fee_per_km' => 2000,
        'max_delivery_distance_meters' => 10000,
    ]);

    // Setup Cart
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

    // Create Schedule Slot
    $this->slot = ScheduleSlot::factory()->create([
        'date' => now()->addDay()->format('Y-m-d'),
        'order_deadline' => now()->addDay(),
        'is_active' => true,
    ]);
});

test('checkout page displays cart items and subtotal', function () {
    Livewire::test(CheckoutPage::class)
        ->assertSee('20.000') // Subtotal for 2 items @ 10,000
        ->assertSet('subtotal', 20000);
});

test('validation fails when fields are missing', function () {
    Livewire::test(CheckoutPage::class)
        ->call('submit')
        ->assertHasErrors(['customerName', 'customerWhatsapp', 'scheduleSlotId']);
});

test('pickup checkout succeeds with valid data', function () {
    Livewire::test(CheckoutPage::class)
        ->set('customerName', 'Rian')
        ->set('customerWhatsapp', '08123456789')
        ->set('fulfillmentMethod', 'pickup')
        ->set('scheduleSlotId', $this->slot->id)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect()
        ->assertDispatched('toast', message: 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.', variant: 'success');

    // Assert database state
    $this->assertDatabaseHas('orders', [
        'customer_name' => 'Rian',
        'whatsapp_normalized' => '628123456789',
        'fulfillment_type' => 'pickup',
    ]);
});

test('delivery checkout calculates fee and succeeds within 10km limit', function () {
    // Coordinate approx 2 km away (around -6.927, 107.629)
    Livewire::test(CheckoutPage::class)
        ->set('customerName', 'Rian')
        ->set('customerWhatsapp', '08123456789')
        ->set('fulfillmentMethod', 'delivery')
        ->set('scheduleSlotId', $this->slot->id)
        ->set('deliveryAddress', 'Jl. Braga No. 10')
        ->call('setCoordinates', -6.9274639, 107.6291228)
        ->assertHasNoErrors('distance')
        ->assertSet('deliveryFee', 9000) // ceil(approx 1.58 km) = 2 km * 2000 + 5000 = 9000
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect();
});

test('delivery checkout fails when distance exceeds 10km limit', function () {
    // Coordinate approx 15 km away (Jakarta center)
    Livewire::test(CheckoutPage::class)
        ->set('customerName', 'Rian')
        ->set('customerWhatsapp', '08123456789')
        ->set('fulfillmentMethod', 'delivery')
        ->set('scheduleSlotId', $this->slot->id)
        ->set('deliveryAddress', 'Monas Jakarta')
        ->call('setCoordinates', -6.2088, 106.8456)
        ->assertHasErrors('distance')
        ->call('submit')
        ->assertHasErrors(['distance']);
});
