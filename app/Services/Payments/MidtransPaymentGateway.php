<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransPaymentGateway implements PaymentGateway
{
    protected string $serverKey;

    protected string $baseUrl;

    protected bool $isProduction;

    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key') ?? '';
        $this->isProduction = (bool) config('services.midtrans.is_production', false);
        $this->baseUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Create a payment transaction and return the snap token and redirect URL.
     *
     * @return array{snap_token: string, redirect_url: string}
     *
     * @throws Exception
     */
    public function createTransaction(Order $order): array
    {
        // 1. Generate unique gateway_order_id to allow retries on Midtrans side
        $attempt = now()->timestamp;
        $gatewayOrderId = "{$order->order_code}-{$attempt}";

        // Calculate payment expiry duration in minutes
        $expiryMinutes = max(5, (int) now()->diffInMinutes($order->payment_expires_at));

        // 2. Prepare payload
        $payload = [
            'transaction_details' => [
                'order_id' => $gatewayOrderId,
                'gross_amount' => (int) $order->grand_total,
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'phone' => (string) $order->whatsapp_normalized,
            ],
            'item_details' => $order->items->map(fn ($item) => [
                'id' => (string) $item->product_variant_id,
                'price' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'name' => Str::limit("{$item->product_name} - {$item->variant_name}", 50),
            ])->toArray(),
            'expiry' => [
                'unit' => 'minute',
                'duration' => $expiryMinutes,
            ],
        ];

        // Add delivery fee as an item if greater than 0
        if ($order->delivery_fee > 0) {
            $payload['item_details'][] = [
                'id' => 'delivery_fee',
                'price' => (int) $order->delivery_fee,
                'quantity' => 1,
                'name' => 'Biaya Pengiriman',
            ];
        }

        try {
            // 3. Make HTTP request with timeout, retry and basic auth
            $response = Http::withBasicAuth($this->serverKey, '')
                ->acceptJson()
                ->asJson()
                ->timeout(10)
                ->connectTimeout(5)
                ->retry(3, 100)
                ->post($this->baseUrl, $payload);

            if ($response->failed()) {
                Log::error('Midtrans API Request failed', [
                    'order_id' => $order->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('Failed to initialize payment gateway: '.($response->json('error_messages')[0] ?? 'Unknown error'));
            }

            $token = $response->json('token');
            $redirectUrl = $response->json('redirect_url');

            if (! $token || ! $redirectUrl) {
                throw new Exception('Invalid response from payment gateway.');
            }

            // 4. Create/update payment record in database
            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'gateway' => 'midtrans',
                    'gateway_order_id' => $gatewayOrderId,
                    'snap_token' => $token,
                    'redirect_url' => $redirectUrl,
                    'status' => PaymentStatus::Pending,
                    'gross_amount' => $order->grand_total,
                ]
            );

            return [
                'snap_token' => $token,
                'redirect_url' => $redirectUrl,
            ];

        } catch (Exception $e) {
            Log::error('Midtrans payment creation error', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
