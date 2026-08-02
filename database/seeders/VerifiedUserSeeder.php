<?php

namespace Database\Seeders;

use App\Facades\KadiApi;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VerifiedUserSeeder extends Seeder
{
    /**
     * @throws \Throwable
     */
    public function run(): void
    {
        $batchSize = 50;
        $totalUsers = 500;
        $batches = ceil($totalUsers / $batchSize);

        $this->command->info("Seeding {$totalUsers} verified users in {$batches} batches...");
        $this->command->warn('This will make external API calls - may take several minutes.');

        for ($i = 0; $i < $batches; $i++) {
            $usersToCreate = min($batchSize, $totalUsers - ($i * $batchSize));

            $this->command->info("Processing batch ".($i + 1)."/{$batches} ({$usersToCreate} users)...");

            DB::beginTransaction();

            try {
                // Step 1: Create users in main database
                $users = $this->generateUsers($usersToCreate);
                DB::table('users')->insert($users);

                // Step 2: Process each user through KadiApi flow
                foreach ($users as $userData) {
                    $this->processUserThroughKadiApi($userData);
                }

                DB::commit();
                $this->command->info('Batch '.($i + 1)."/{$batches} completed ({$usersToCreate} users)");
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->command->error('Batch '.($i + 1).' failed: '.$e->getMessage());
                throw $e;
            }
        }

        $this->command->info("Successfully seeded {$totalUsers} verified users.");
    }

    private function generateUsers(int $count): array
    {
        $users = [];

        for ($i = 0; $i < $count; $i++) {
            $accountNo = 'KK-'.strtoupper(Str::random(8));
            $plainPassword = 'password';
            $phone = $this->generateKenyanPhone();

            $users[] = [
                'account_no' => $accountNo,
                'name' => $this->generateKenyanName(),
                'email' => $this->generateUniqueEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make($plainPassword),
                'phone' => $phone,
                'linked_id' => null,
                'google_id' => null,
                'avatar' => null,
                'remember_token' => Str::random(10),
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Store plain password for kadi database insertion
            Cache::put("seeder.plain_password.{$accountNo}", $plainPassword, now()->addMinutes(5));
        }

        return $users;
    }

    private function processUserThroughKadiApi(array $userData): void
    {
        $plainPassword = Cache::get("seeder.plain_password.{$userData['account_no']}");

        // Step 1: Register with KadiApi to get customer_id
        $customerId = $this->registerWithKadiApi($userData);

        if ($customerId === null) {
            Log::error('Failed to register user with KadiApi: '.$userData['email']);
            return;
        }

        // Step 2: Insert into kadi database
        $this->insertIntoKadiDatabase($userData, $plainPassword, $customerId);

        // Step 3: Update linked_id in main database
        DB::table('users')
            ->where('email', $userData['email'])
            ->update(['linked_id' => $customerId]);

        // Clean up cache
        Cache::forget("seeder.plain_password.{$userData['account_no']}");
    }

    private function registerWithKadiApi(array $userData): ?int
    {
        try {
            $userArr = array_filter([
                'google_id' => $userData['account_no'],
                'account_no' => $userData['account_no'],
                'name' => $userData['name'],
                'email' => $userData['email'],
                'id_no' => (string) $userData['account_no'],
                'phone_no' => $userData['phone'] ?: null,
            ], fn ($v) => $v !== null);

            $response = KadiApi::createCustomer($userArr);

            if (isset($response['customer_id'])) {
                return $response['customer_id'];
            }
        } catch (RequestException|ConnectionException $e) {
            Log::error('KadiApi registration failed for '.$userData['email'].': '.$e->getMessage(), $userArr);
        }

        return null;
    }

    private function insertIntoKadiDatabase(array $userData, ?string $plainPassword, int $customerId): void
    {
        try {
            $userName = explode(' ', $userData['name']);
            DB::connection('kadi')->table('accounts')->insert([
                'id' => $customerId,
                'name' => $userName[0],
                'phone' => $userData['phone'],
                'email' => $userData['email'],
                'password' => $plainPassword,
                'outh' => $customerId,
                'google_id' => $userData['account_no'],
            ]);
        } catch (\Throwable $e) {
            Log::error('Kadi DB insert failed for '.$userData['email'].': '.$e->getMessage());
        }
    }

    private function generateKenyanName(): string
    {
        $firstNames = ['John', 'Mary', 'Peter', 'Grace', 'David', 'Sarah', 'James', 'Esther', 'Michael', 'Anna', 'Paul', 'Ruth', 'Samuel', 'Naomi', 'Daniel', 'Rebecca', 'Joseph', 'Hannah', 'Thomas', 'Elizabeth'];
        $lastNames = ['Kamau', 'Ochieng', 'Wanjiku', 'Otieno', 'Njoroge', 'Akinyi', 'Mwangi', 'Njeri', 'Omondi', 'Wairimu', 'Kipchoge', 'Chebet', 'Mutua', 'Mwikali', 'Kipyegon', 'Jepchumba', 'Maina', 'Nyambura', 'Owino', 'Muthoni'];

        return $firstNames[array_rand($firstNames)].' '.$lastNames[array_rand($lastNames)];
    }

    private function generateKenyanPhone(): string
    {
        return '+2547'.rand(10000000, 99999999);
    }

    private function generateUniqueEmail(): string
    {
        static $emailCounter = 0;
        $emailCounter++;

        $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
        $domain = $domains[array_rand($domains)];

        return 'user'.$emailCounter.'_'.Str::random(5).'@'.$domain;
    }
}
