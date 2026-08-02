<?php

namespace Database\Seeders;

use App\Models\AdCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdCategorySeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->warn(PHP_EOL.'Creating Ad categories...');
        $categories = [
            ['key' => 'gaming',            'name' => 'Gaming',              'pricing_multiplier' => 1.00, 'requires_approval' => false],
            ['key' => 'shopping',          'name' => 'Shopping',            'pricing_multiplier' => 1.00, 'requires_approval' => false],
            ['key' => 'finance',           'name' => 'Finance',             'pricing_multiplier' => 1.40, 'requires_approval' => false],
            ['key' => 'insurance',         'name' => 'Insurance',           'pricing_multiplier' => 1.40, 'requires_approval' => false],
            ['key' => 'real_estate',       'name' => 'Real Estate',         'pricing_multiplier' => 1.30, 'requires_approval' => false],
            ['key' => 'automotive',        'name' => 'Automotive',          'pricing_multiplier' => 1.20, 'requires_approval' => false],
            ['key' => 'travel',            'name' => 'Travel',              'pricing_multiplier' => 1.20, 'requires_approval' => false],
            ['key' => 'food_restaurants',  'name' => 'Food & Restaurants',  'pricing_multiplier' => 1.00, 'requires_approval' => false],
            ['key' => 'health_fitness',    'name' => 'Health & Fitness',    'pricing_multiplier' => 1.20, 'requires_approval' => false],
            ['key' => 'education',         'name' => 'Education',           'pricing_multiplier' => 1.10, 'requires_approval' => false],
            ['key' => 'technology',        'name' => 'Technology',          'pricing_multiplier' => 1.10, 'requires_approval' => false],
            ['key' => 'entertainment',     'name' => 'Entertainment',       'pricing_multiplier' => 1.00, 'requires_approval' => false],
            ['key' => 'sports',            'name' => 'Sports',              'pricing_multiplier' => 1.10, 'requires_approval' => false],
            ['key' => 'alcohol',           'name' => 'Alcohol',             'pricing_multiplier' => 2.00, 'requires_approval' => true],
            ['key' => 'political',         'name' => 'Political',           'pricing_multiplier' => 2.50, 'requires_approval' => true],
        ];

        foreach ($categories as $category) {
            AdCategory::query()->updateOrCreate(['key' => $category['key']], $category);
        }
        $this->command?->info('Ad categories seeded successfully.');
    }
}
