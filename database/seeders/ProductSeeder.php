<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;

class ProductSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ProductCategory::query()->get();

        if ($categories->isEmpty()) {
            $this->command?->warn(
                'No product categories found. Please run ProductCategorySeeder first.'
            );

            return;
        }

        $this->command?->warn(
            PHP_EOL.'Creating products...'
        );

        $this->withProgressBar(
            50,
            function () use ($categories): void {
                Product::factory()
                    ->recycle($categories)
                    ->create();
            }
        );

        $this->command?->newLine();
        $this->command?->info('Products seeded successfully.');
    }
}
