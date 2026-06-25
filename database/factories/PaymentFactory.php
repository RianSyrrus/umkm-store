<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'gateway' => 'midtrans',
            'gateway_order_id' => $this->faker->uuid,
            'snap_token' => $this->faker->uuid,
            'redirect_url' => $this->faker->url,
            'status' => PaymentStatus::Pending,
            'gross_amount' => 10000,
        ];
    }
}
