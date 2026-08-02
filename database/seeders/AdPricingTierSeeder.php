<?php

namespace Database\Seeders;

use App\Models\AdPricingTier;
use Illuminate\Database\Seeder;

class AdPricingTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->warn(PHP_EOL.'Seeding Ad Pricing Tiers...');
        $tiers = [
            ['duration_seconds' => 10, 'base_cost' => 0.50],
            ['duration_seconds' => 20, 'base_cost' => 1.00],
            ['duration_seconds' => 30, 'base_cost' => 2.00],
        ];

        foreach ($tiers as $tier) {
            AdPricingTier::query()->updateOrCreate($tier);
        }

        $this->command?->info('Ad pricing tiers seeded successfully.');
    }
}
