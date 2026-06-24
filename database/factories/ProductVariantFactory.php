<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => 'Regular',
            'sku' => $this->faker->unique()->slug(3),
            'price' => $this->faker->numberBetween(10, 50) * 1000,
            'stock_on_hand' => 10,
            'reserved_stock' => 0,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
