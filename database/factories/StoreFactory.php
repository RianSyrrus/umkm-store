<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company;

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence,
            'logo_path' => null,
            'whatsapp' => '6281234567890',
            'address' => $this->faker->address,
            'latitude' => -6.2000000,
            'longitude' => 106.8166667,
            'timezone' => 'Asia/Jakarta',
            'base_delivery_fee' => 5000,
            'delivery_fee_per_km' => 2000,
            'max_delivery_distance_meters' => 10000,
            'low_stock_threshold' => 5,
        ];
    }
}
