<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Models\Product;
use App\Models\ProductCategory;
use Database\Seeders\LocalImages;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_category_id' => ProductCategory::factory(),
            'sku' => fake()->unique()->bothify('SKU-????-####'),
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'specifications' => fake()->randomElements([
                ['color' => fake()->colorName(), 'size' => fake()->randomElement(['S', 'M', 'L', 'XL'])],
                ['weight' => fake()->randomFloat(1, 0.1, 10), 'material' => fake()->word()],
                ['brand' => fake()->company(), 'model' => fake()->word()],
            ]),
            'product_type' => fake()->randomElement(ProductType::cases()),
            'status' => fake()->randomElement(ProductStatus::cases()),
            'money_price' => fake()->randomFloat(2, 10, 1000),
            'coin_price' => fake()->numberBetween(100, 10000),
            'original_money_price' => fake()->randomFloat(2, 10, 1000),
            'original_coin_price' => fake()->numberBetween(100, 10000),
            'currency' => 'USD',
            'stock_quantity' => fake()->numberBetween(0, 100),
            'reserved_quantity' => fake()->numberBetween(0, 10),
            'low_stock_threshold' => fake()->numberBetween(5, 20),
            'is_featured' => fake()->boolean(20),
            'is_new' => fake()->boolean(30),
            'is_popular' => fake()->boolean(20),
            'is_trending' => fake()->boolean(15),
            'is_promotional' => fake()->boolean(25),
            'estimated_value' => fake()->randomFloat(2, 10, 1000),
            'requires_shipping' => fake()->boolean(80),
            'is_redeemable_with_coins' => fake()->boolean(70),
            'is_purchasable_with_money' => fake()->boolean(80),
            'metadata' => fake()->randomElements([
                ['tags' => fake()->words(3), 'season' => fake()->randomElement(['spring', 'summer', 'fall', 'winter'])],
                ['age_group' => fake()->randomElement(['adult', 'teen', 'child']), 'gender' => fake()->randomElement(['male', 'female', 'unisex'])],
            ]),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product): void {
            $this->attachCoverImage($product);
            $this->attachGalleryImages($product);
        });
    }

    protected function attachCoverImage(Product $product): void
    {
        try {
            $coverImage = LocalImages::getRandomFile(LocalImages::SIZE_1280x720);

            $product
                ->addMedia($coverImage)
                ->preservingOriginal()
                ->toMediaCollection('cover');
        } catch (\Throwable $e) {
            // Silently fail if image attachment fails - product is still created
        }
    }

    protected function attachGalleryImages(Product $product): void
    {
        $galleryCount = fake()->numberBetween(2, 5);

        for ($i = 0; $i < $galleryCount; $i++) {
            try {
                $galleryImage = LocalImages::getRandomFile();

                $product
                    ->addMedia($galleryImage)
                    ->preservingOriginal()
                    ->toMediaCollection('gallery');
            } catch (\Throwable $e) {
                // Silently fail if image attachment fails - product is still created
            }
        }
    }
}
