<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Storage::deleteDirectory('public');
        $this->call(RoleSeeder::class);
        $this->call(AdCategorySeeder::class);
        $this->call(AdPricingTierSeeder::class);
        $this->call(VerifiedUserSeeder::class);
    }
}
