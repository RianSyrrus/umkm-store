<?php

namespace Database\Factories;

use App\Models\OptionGroup;
use App\Models\OptionValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OptionValue>
 */
class OptionValueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'option_group_id' => OptionGroup::factory(),
            'name' => $this->faker->word,
            'price_delta' => 0,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
