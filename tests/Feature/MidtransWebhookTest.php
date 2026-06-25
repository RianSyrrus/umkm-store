<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\PaymentNotification;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['services.midtrans.server_key' => 'test-server-key']);
});

test('webhook fails if signature is invalid', function () {
    $response = $this->postJson(route('webhooks.midtrans'), [
        'order_id' => 'UMK-20260625-XXXXXX-123',
        'status_code' => '200',
        'gross_amount' => '10000.00',
        'signature_key' => 'wrong-signature-key',
        'transaction_id' => 'tx-123',
        'transaction_status' => 'settlement',
    ]);

    $response->assertForbidden();
    expect(PaymentNotification::count())->toBe(0);
});

test('webhook returns 404 if order is not found', function () {
    $orderId = 'UMK-20260625-NONEXIST-123';
    $statusCode = '200';
    $grossAmount = '10000.00';
    $serverKey = 'test-server-key';
    $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

    $response = $this->postJson(route('webhooks.midtrans'), [
        'order_id' => $orderId,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $signature,
        'transaction_id' => 'tx-123',
        'transaction_status' => 'settlement',
    ]);

    $response->assertNotFound();
    expect(PaymentNotification::count())->toBe(1); // Notification log is saved even if order is not found
});

test('webhook processes settlement successfully', function () {
    $order = Order::factory()->create([
        'order_code' => 'UMK-20260625-ABCDEF',
        'payment_status' => PaymentStatus::Pending,
        'order_status' => OrderStatus::AwaitingPayment,
        'grand_total' => 10000,
    ]);

    $variant = ProductVariant::factory()->create(['stock_on_hand' => 10, 'reserved_stock' => 1]);
    $reservation = InventoryReservation::create([
        'order_id' => $order->id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
        'status' => 'active',
        'expires_at' => now()->addMinutes(30),
    ]);

    $orderId = 'UMK-20260625-ABCDEF-123';
    $statusCode = '200';
    $grossAmount = '10000';
    $serverKey = 'test-server-key';
    $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

    $response = $this->postJson(route('webhooks.midtrans'), [
        'order_id' => $orderId,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $signature,
        'transaction_id' => 'tx-123',
        'transaction_status' => 'settlement',
        'payment_type' => 'bank_transfer',
    ]);

    $response->assertSuccessful();

    $order->refresh();
    $reservation->refresh();
    $variant->refresh();

    expect($order->payment_status)->toBe(PaymentStatus::Paid);
    expect($order->order_status)->toBe(OrderStatus::Confirmed);
    expect($reservation->status)->toBe('committed');
    expect($variant->stock_on_hand)->toBe(9);
});

test('webhook is idempotent', function () {
    $order = Order::factory()->create([
        'order_code' => 'UMK-20260625-ABCDEF',
        'payment_status' => PaymentStatus::Pending,
        'order_status' => OrderStatus::AwaitingPayment,
    ]);

    $orderId = 'UMK-20260625-ABCDEF-123';
    $statusCode = '200';
    $grossAmount = '10000';
    $serverKey = 'test-server-key';
    $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

    $payload = [
        'order_id' => $orderId,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'signature_key' => $signature,
        'transaction_id' => 'tx-123',
        'transaction_status' => 'settlement',
    ];

    // First call
    $this->postJson(route('webhooks.midtrans'), $payload)->assertSuccessful();

    // Second call
    $response = $this->postJson(route('webhooks.midtrans'), $payload);
    $response->assertSuccessful();

    expect(PaymentNotification::count())->toBe(1); // Only 1 record created
});
