<?php

namespace Database\Factories;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\ScheduleSlot;
use App\ValueObjects\OrderCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_code' => (string) OrderCode::generate(),
            'customer_name' => $this->faker->name,
            'whatsapp_normalized' => '628123456789',
            'whatsapp_display' => '0812-3456-789',
            'payment_status' => PaymentStatus::Pending,
            'order_status' => OrderStatus::AwaitingPayment,
            'fulfillment_type' => FulfillmentType::Pickup,
            'schedule_slot_id' => ScheduleSlot::factory(),
            'scheduled_at' => now()->addDay(),
            'subtotal' => 10000,
            'delivery_fee' => 0,
            'grand_total' => 10000,
            'payment_expires_at' => now()->addMinutes(30),
        ];
    }
}
