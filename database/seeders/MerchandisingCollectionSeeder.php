<?php

namespace Database\Seeders;

use App\Models\MerchandisingCollection;
use App\Models\Product;

class MerchandisingCollectionSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->warn(PHP_EOL.'Creating merchandising collections...');

        $collections = collect();
        $this->withProgressBar(10, function () use (&$collections) {
            $collection = MerchandisingCollection::factory()->create();
            $collections->push($collection);
        });

        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command?->warn('No products found. Please run ProductSeeder first.');

            return;
        }

        $this->command?->warn(PHP_EOL.'Attaching products to collections...');

        foreach ($collections as $collection) {
            $collection->products()->attach(
                $products->random(rand(5, 15))->pluck('id')->toArray(),
                ['sort_order' => rand(0, 100)]
            );
        }

        $this->command?->info('Merchandising collections seeded successfully.');
    }
}
