<?php

namespace Database\Factories;

use App\Models\BusinessHour;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessHour>
 */
class BusinessHourFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'day_of_week' => $this->faker->numberBetween(0, 6),
            'open_time' => '08:00:00',
            'close_time' => '17:00:00',
            'is_closed' => false,
        ];
    }
}
