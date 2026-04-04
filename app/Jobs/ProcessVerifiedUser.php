<?php

namespace App\Jobs;

use App\Facades\KadiApi;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessVerifiedUser implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public function __construct(public User $user) {}

    public function uniqueId(): string
    {
        return (string) $this->user->id;
    }

    public function handle(): void
    {
        $this->user->refresh();

        if ($this->user->isLinked()) {
            return;
        }

        $plainPassword = Cache::get("user.plain_password.{$this->user->id}");

        $customerId = $this->registerWithKadiApi();
        $this->fetchAndCacheCustomerProfile($customerId);
        $this->insertIntoKadiDatabase($plainPassword, $customerId);
        $this->sendWelcomeEmail($plainPassword);
    }

    /**
     * POST to KadiApi /customers, store the returned customer_id as linked_id, and return it.
     */
    private function registerWithKadiApi(): ?int
    {
        try {
            $userArr = [
                'google_id' => $this->user->account_no,
                'account_no' => $this->user->account_no,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'id_no' => (string) $this->user->account_no,
                'phone_no' => $this->user->phone,
            ];
            $response = KadiApi::createCustomer($userArr);

            if (isset($response['customer_id'])) {
                $this->user->update(['linked_id' => $response['customer_id']]);

                return $response['customer_id'];
            }
        } catch (RequestException|ConnectionException $e) {
            Log::error('KadiApi registration failed for user '.$this->user->id.': '.$e->getMessage(), $userArr);
        }

        return null;
    }

    /**
     * Insert a new account record into the kadi database.
     */
    private function insertIntoKadiDatabase(?string $plainPassword, ?int $customerId): void
    {
        try {
            DB::connection('kadi')->table('accounts')->insert([
                'name' => $this->user->name,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
                'password' => $plainPassword,
                'outh' => $customerId,
                'google_id' => $this->user->account_no,
            ]);
            Cache::forget("user.plain_password.{$this->user->id}");
        } catch (\Throwable $e) {
            Log::error('Kadi DB insert failed for user '.$this->user->id.': '.$e->getMessage());
        }
    }

    private function sendWelcomeEmail(?string $plainPassword): void
    {
        Mail::to($this->user->email)->send(new WelcomeEmail($this->user, $plainPassword));
    }

    /**
     * Fetch and cache the full customer profile from KadiApi for 1 hour.
     */
    private function fetchAndCacheCustomerProfile(?int $customerId): void
    {
        if ($customerId === null) {
            return;
        }

        try {
            $response = KadiApi::getCustomer($customerId);

            Cache::put("kadi.customer.{$this->user->id}", $response['data'] ?? $response, now()->addHour());
        } catch (\Throwable $e) {
            Log::error('Failed to fetch KadiApi customer profile for user '.$this->user->id.': '.$e->getMessage());
        }
    }
}
