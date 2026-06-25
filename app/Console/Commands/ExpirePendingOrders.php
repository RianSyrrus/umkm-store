<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Services\Inventory\InventoryReservationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpirePendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire pending orders that have passed their payment deadline and release their stock reservations.';

    public function __construct(
        protected InventoryReservationService $reservationService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredOrders = Order::query()
            ->where('payment_status', PaymentStatus::Pending)
            ->where('payment_expires_at', '<=', now())
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('No expired pending orders found.');

            return Command::SUCCESS;
        }

        $count = 0;

        foreach ($expiredOrders as $order) {
            try {
                DB::transaction(function () use ($order) {
                    // Update order statuses
                    $order->update([
                        'payment_status' => PaymentStatus::Expired,
                        'order_status' => OrderStatus::Cancelled,
                        'cancellation_reason' => 'Payment deadline expired.',
                    ]);

                    // Update payment record if exists
                    Payment::where('order_id', $order->id)->update([
                        'status' => PaymentStatus::Expired,
                    ]);

                    // Create status history log
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'from_status' => OrderStatus::AwaitingPayment,
                        'to_status' => OrderStatus::Cancelled,
                        'actor_type' => 'system',
                        'reason' => 'Payment deadline expired.',
                    ]);

                    // Release reservations
                    $this->reservationService->release($order);
                });

                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to expire order {$order->order_code}: {$e->getMessage()}");
                Log::error("Failed to expire order {$order->order_code}", [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Successfully expired {$count} pending order(s).");

        return Command::SUCCESS;
    }
}
