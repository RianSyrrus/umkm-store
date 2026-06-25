<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentNotification;
use App\Services\Inventory\InventoryReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        protected InventoryReservationService $reservationService
    ) {}

    /**
     * Handle incoming webhook notification from Midtrans.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();

        // 1. Extract required values for signature verification
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;

        if (! $orderId || ! $statusCode || ! $grossAmount || ! $signatureKey || ! $transactionId) {
            return response()->json(['message' => 'Invalid payload structure'], 400);
        }

        // 2. Verify signature
        $serverKey = config('services.midtrans.server_key');
        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if (! hash_equals($expectedSignature, $signatureKey)) {
            Log::warning('Midtrans Webhook: Invalid signature', [
                'received' => $signatureKey,
                'expected' => $expectedSignature,
            ]);

            return response()->json(['message' => 'Signature verification failed'], 403);
        }

        // 3. Idempotency Check - prevent double processing
        if (PaymentNotification::where('transaction_id', $transactionId)->exists()) {
            Log::info('Midtrans Webhook: Already processed transaction', ['transaction_id' => $transactionId]);

            return response()->json(['message' => 'Notification already processed']);
        }

        // 4. Save notification log
        PaymentNotification::create([
            'transaction_id' => $transactionId,
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'payment_type' => $payload['payment_type'] ?? null,
            'transaction_status' => $transactionStatus,
            'payload' => $payload,
        ]);

        // 5. Parse order code from gateway_order_id (format: UMK-YYYYMMDD-XXXXXX-timestamp)
        $parts = explode('-', $orderId);
        if (count($parts) < 3) {
            return response()->json(['message' => 'Invalid order ID format'], 400);
        }
        $orderCode = implode('-', array_slice($parts, 0, 3));

        // 6. Find order and update status inside a transaction
        return DB::transaction(function () use ($orderCode, $transactionStatus, $payload) {
            /** @var Order|null $order */
            $order = Order::where('order_code', $orderCode)->first();

            if (! $order) {
                Log::error('Midtrans Webhook: Order not found', ['order_code' => $orderCode]);

                return response()->json(['message' => 'Order not found'], 404);
            }

            $oldPaymentStatus = $order->payment_status;
            $oldOrderStatus = $order->order_status;

            $newPaymentStatus = $oldPaymentStatus;
            $newOrderStatus = $oldOrderStatus;
            $cancellationReason = null;

            switch ($transactionStatus) {
                case 'capture':
                    // For credit card capture, check if challenge/accept
                    $fraudStatus = $payload['fraud_status'] ?? 'accept';
                    if ($fraudStatus === 'accept') {
                        $newPaymentStatus = PaymentStatus::Paid;
                        $newOrderStatus = OrderStatus::Confirmed;
                    } else {
                        $newPaymentStatus = PaymentStatus::Failed;
                        $newOrderStatus = OrderStatus::Cancelled;
                        $cancellationReason = 'Payment challenged by fraud system';
                    }
                    break;

                case 'settlement':
                    $newPaymentStatus = PaymentStatus::Paid;
                    $newOrderStatus = OrderStatus::Confirmed;
                    break;

                case 'pending':
                    $newPaymentStatus = PaymentStatus::Pending;
                    break;

                case 'deny':
                    $newPaymentStatus = PaymentStatus::Failed;
                    $newOrderStatus = OrderStatus::Cancelled;
                    $cancellationReason = 'Payment denied by gateway';
                    break;

                case 'expire':
                    $newPaymentStatus = PaymentStatus::Expired;
                    $newOrderStatus = OrderStatus::Cancelled;
                    $cancellationReason = 'Payment expired';
                    break;

                case 'cancel':
                    $newPaymentStatus = PaymentStatus::Cancelled;
                    $newOrderStatus = OrderStatus::Cancelled;
                    $cancellationReason = 'Payment cancelled';
                    break;

                default:
                    Log::warning('Midtrans Webhook: Unhandled transaction status', ['status' => $transactionStatus]);
                    break;
            }

            // Update order and payment records if status changed
            if ($newPaymentStatus !== $oldPaymentStatus || $newOrderStatus !== $oldOrderStatus) {
                $updateData = [
                    'payment_status' => $newPaymentStatus,
                    'order_status' => $newOrderStatus,
                ];

                if ($newPaymentStatus === PaymentStatus::Paid) {
                    $updateData['paid_at'] = now();
                }

                if ($cancellationReason) {
                    $updateData['cancellation_reason'] = $cancellationReason;
                }

                $order->update($updateData);

                // Update payments table record
                Payment::where('order_id', $order->id)->update([
                    'status' => $newPaymentStatus,
                    'paid_at' => $newPaymentStatus === PaymentStatus::Paid ? now() : null,
                ]);

                // Create OrderStatusHistory audit log
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'from_status' => $oldOrderStatus,
                    'to_status' => $newOrderStatus,
                    'actor_type' => 'system',
                    'reason' => $cancellationReason ?? 'Webhook payment notification received',
                ]);

                // 7. Inventory reservation action
                if ($newPaymentStatus === PaymentStatus::Paid) {
                    $this->reservationService->commit($order);
                } elseif ($newOrderStatus === OrderStatus::Cancelled) {
                    $this->reservationService->release($order);
                }
            }

            return response()->json(['message' => 'Status updated successfully']);
        });
    }
}
