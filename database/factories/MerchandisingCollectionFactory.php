<?php

namespace Database\Factories;

use App\Enums\MerchandisingCollectionType;
use App\Models\MerchandisingCollection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MerchandisingCollection>
 */
class MerchandisingCollectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'type' => fake()->randomElement(MerchandisingCollectionType::cases()),
            'description' => fake()->paragraph(),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
            'starts_at' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'ends_at' => fake()->dateTimeBetween('+1 month', '+6 months'),
        ];
    }
}
