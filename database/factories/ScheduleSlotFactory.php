<?php

namespace Database\Factories;

use App\Models\ScheduleSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleSlot>
 */
class ScheduleSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'quota' => 10,
            'reserved_count' => 0,
            'order_deadline' => now()->addDay()->startOfDay()->subHours(2),
            'is_active' => true,
        ];
    }
}
