<?php

namespace Database\Factories;

use App\Models\OptionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OptionGroup>
 */
class OptionGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'selection_type' => 'single',
            'is_required' => false,
            'min_selected' => 0,
            'max_selected' => 1,
        ];
    }
}
