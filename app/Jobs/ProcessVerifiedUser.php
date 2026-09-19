<?php

namespace App\Jobs;

use App\Facades\BugsApi;
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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProcessVerifiedUser implements ShouldBeUnique, ShouldQueue
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

        // Atomic lock: refresh and check linked_id inside a transaction with
        // row-level locking to prevent two concurrent jobs from both seeing
        // isLinked() === false and creating duplicate records.
        $alreadyLinked = DB::transaction(function () {
            $locked = User::lockForUpdate()->find($this->user->id);

            if ($locked->isLinked()) {
                return true;
            }

            return false;
        });

        if ($alreadyLinked) {
            return;
        }

        $cached = Cache::get("user.kadi_password_hash.{$this->user->id}");

        // Null is expected for Google-auth users (no local password) — the
        // kadi account insert handles it gracefully.
        $kadiPasswordHash = $cached !== null ? (string) $cached : null;

        $customerId = $this->registerWithKadiApi();

        if ($customerId === null) {
            Log::error('ProcessVerifiedUser: KadiApi registration returned null for user '.$this->user->id.'. Aborting remaining steps.');

            return;
        }

        $bugsId = $this->registerWithBugsApi();
        $this->fetchAndCacheCustomerProfile($customerId);
        $this->insertIntoKadiDatabase($kadiPasswordHash, $customerId);
        $this->sendWelcomeEmail();
    }

    /**
     * Atomically claim this user for linking by setting linked_id only if it
     * is currently null. Returns the customer_id on success, null on failure.
     */
    private function claimUserForLinking(int $customerId): bool
    {
        $updated = DB::table('users')
            ->where('id', $this->user->id)
            ->whereNull('linked_id')
            ->update(['linked_id' => $customerId]);

        if ($updated === 0) {
            Log::warning('ProcessVerifiedUser: Could not claim user '.$this->user->id.' — linked_id already set to '.($this->user->linked_id ?? 'null'));

            return false;
        }

        $this->user->refresh();

        return true;
    }

    /**
     * POST to KadiApi /customers, store the returned customer_id as linked_id, and return it.
     */
    private function registerWithKadiApi(): ?int
    {
        try {
            $userArr = array_filter([
                'google_id' => $this->user->google_id ?? $this->user->account_no,
                'account_no' => $this->user->account_no,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'id_no' => (string) $this->user->account_no,
                'phone_no' => $this->user->phone ?: null,
            ], fn ($v) => $v !== null);
            $response = KadiApi::createCustomer($userArr);

            if (isset($response['customer_id'])) {
                $customerId = (int) $response['customer_id'];

                if (! $this->claimUserForLinking($customerId)) {
                    return null;
                }

                return $customerId;
            }
        } catch (RequestException|ConnectionException $e) {
            Log::error('KadiApi registration failed for user '.$this->user->id.': '.$e->getMessage(), $userArr);
        }

        return null;
    }

    /**
     * POST to BugsApi /users, store the returned user_id as bugs_id, and return it.
     */
    private function registerWithBugsApi(): ?int
    {
        try {
            $userArr = [
                'account_no' => $this->user->account_no,
                'name' => $name = $this->user->name,
                'username' => Str::slug($name),
                'email' => $email = $this->user->email,
                'phone' => $this->user->phone ?? null,
                'password' => Hash::make(Str::lower($email)),
                'linked_id' => $this->user->linked_id,
            ];
            $response = BugsApi::registerUser($userArr);

            if (isset($response['user_id'])) {
                $this->user->update(['bugs_id' => $response['user_id']]);

                return $response['user_id'];
            }
        } catch (RequestException|ConnectionException $e) {
            Log::error('BugsApi registration failed for user '.$this->user->id.': '.$e->getMessage());
        }

        return null;
    }

    /**
     * Insert or update the account record in the kadi database.
     *
     * Uses upsert to be idempotent — if the row already exists (e.g. from a
     * previous partial run), it updates instead of failing with a duplicate
     * key error.
     *
     * `$passwordHash` is a one-way bcrypt hash of the user's registration
     * password (never plaintext). The KadiApi/game side must verify logins
     * with password_verify() against this column.
     */
    private function insertIntoKadiDatabase(?string $passwordHash, int $customerId): void
    {
        try {
            $userName = explode(' ', $this->user->name);

            DB::connection('kadi')->table('accounts')->upsert([
                [
                    'id' => $customerId,
                    'name' => $userName[0],
                    'phone' => $this->user->phone,
                    'email' => $this->user->email,
                    'password' => $passwordHash,
                    'outh' => $customerId,
                    'google_id' => $this->user->account_no,
                ],
            ], 'id', ['name', 'phone', 'email', 'password', 'outh', 'google_id']);

            Cache::forget("user.kadi_password_hash.{$this->user->id}");
        } catch (\Throwable $e) {
            Log::error('Kadi DB insert failed for user '.$this->user->id.': '.$e->getMessage());
        }
    }

    private function sendWelcomeEmail(): void
    {
        Mail::to($this->user->email)->send(new WelcomeEmail($this->user));
    }

    /**
     * Fetch and cache the full customer profile from KadiApi for 1 hour.
     */
    private function fetchAndCacheCustomerProfile(int $customerId): void
    {
        try {
            $response = KadiApi::getCustomer($customerId);

            Cache::put("kadi.customer.{$this->user->id}", $response['data'] ?? $response, now()->addHour());
        } catch (\Throwable $e) {
            Log::error('Failed to fetch KadiApi customer profile for user '.$this->user->id.': '.$e->getMessage());
        }
    }
}
