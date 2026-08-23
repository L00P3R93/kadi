<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;

class ProductVariantSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->warn(PHP_EOL.'Creating product variants...');

        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command?->warn('No products found. Please run ProductSeeder first.');

            return;
        }

        $this->withProgressBar(100, function () use ($products) {
            return ProductVariant::factory()
                ->recycle($products)
                ->create();
        });

        $this->command?->info('Product variants seeded successfully.');
    }
}
