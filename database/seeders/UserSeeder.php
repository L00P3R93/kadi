<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $this->command->warn(PHP_EOL.'Creating Admin User...');
        $name = 'Sntaks Admin';
        $nameArr = explode(' ', $name);
        $admin = User::query()->create([
            'account_no' => 'KK-'.strtoupper(uniqid()),
            'name' => $name,
            'email' => 'sntaksolutionsltd@gmail.com',
            'phone' => $phone = '0727796831',
            'linked_id' => '502',
            'email_verified_at' => now(),
            'password' => Hash::make("{$nameArr[0]}@{$phone}"),
            'remember_token' => Str::random(10),
        ]);
        $admin->assignRole('super-admin');
        $this->command->info("✓ User {$name} created and assigned to Super Admin role.");
    }
}
