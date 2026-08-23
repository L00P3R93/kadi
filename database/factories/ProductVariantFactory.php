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
            'sku' => fake()->unique()->bothify('VAR-????-####'),
            'name' => fake()->words(2, true),
            'attributes' => fake()->randomElements([
                ['color' => fake()->colorName(), 'size' => fake()->randomElement(['S', 'M', 'L', 'XL'])],
                ['weight' => fake()->randomFloat(1, 0.1, 10), 'material' => fake()->word()],
                ['style' => fake()->word(), 'finish' => fake()->word()],
            ]),
            'money_price' => fake()->randomFloat(2, 10, 1000),
            'coin_price' => fake()->numberBetween(100, 10000),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'reserved_quantity' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
