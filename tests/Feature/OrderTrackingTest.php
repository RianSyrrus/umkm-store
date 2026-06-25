<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Storefront\OrderTrackingPage;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('renders order tracking search form', function () {
    Livewire::test(OrderTrackingPage::class)
        ->assertStatus(200)
        ->assertSee('Lacak Status Pesanan')
        ->assertSee('Kode Pesanan')
        ->assertSee('Nomor WhatsApp');
});

test('shows error when order not found', function () {
    Livewire::test(OrderTrackingPage::class)
        ->set('searchCode', 'UMK-20260625-NONEXIST')
        ->set('searchPhone', '08123456789')
        ->call('track')
        ->assertSee('Pesanan tidak ditemukan');
});

test('tracks existing order details successfully', function () {
    $order = Order::factory()->create([
        'order_code' => 'UMK-20260625-ABCDEF',
        'whatsapp_normalized' => '628123456789',
        'whatsapp_display' => '0812-3456-789',
        'customer_name' => 'John Doe',
        'payment_status' => PaymentStatus::Pending,
        'order_status' => OrderStatus::AwaitingPayment,
    ]);

    Payment::factory()->create([
        'order_id' => $order->id,
        'status' => PaymentStatus::Pending,
        'snap_token' => 'snap-token-xyz',
    ]);

    Livewire::test(OrderTrackingPage::class)
        ->set('searchCode', 'UMK-20260625-ABCDEF')
        ->set('searchPhone', '0812-3456-789')
        ->call('track')
        ->assertSet('order.id', $order->id)
        ->assertSee('John Doe')
        ->assertSee('Menunggu Pembayaran')
        ->assertSee('Bayar Sekarang');
});
