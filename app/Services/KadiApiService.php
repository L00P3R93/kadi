<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KadiApiService
{
    protected PendingRequest $http;

    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.kadi_api.url');
        $this->http = Http::withHeaders([
            'x-api-key' => config('services.kadi_api.key'),
            'Accept' => 'application/json',
        ])->baseUrl($this->baseUrl);
    }

    /**
     * Clone the shared client and attach an Idempotency-Key header.
     *
     * withHeaders() mutates and returns the same PendingRequest instance, so
     * calling it directly on $this->http would leak every prior key onto
     * subsequent requests. Cloning first keeps $this->http untouched.
     */
    protected function withIdempotencyKey(?string $idempotencyKey = null): PendingRequest
    {
        return (clone $this->http)->withHeaders([
            'Idempotency-Key' => $idempotencyKey ?? Str::uuid()->toString(),
        ]);
    }

    /**
     * Make a GET request
     *
     * @throws RequestException|ConnectionException
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->http->get($endpoint, $query)
            ->throw()
            ->json('data') ?? [];
    }

    /**
     * Make a POST request
     *
     * @throws RequestException|ConnectionException
     */
    public function post(string $endpoint, array $data = [], string $bodyType = 'json', ?string $idempotencyKey = null): array
    {
        $request = $this->withIdempotencyKey($idempotencyKey);

        $response = $bodyType === 'form'
            ? $request->asForm()->post($endpoint, $data)
            : $request->post($endpoint, $data);

        return $response->throw()->json('data') ?? [];
    }

    /**
     * Make a PUT request
     *
     * @throws RequestException|ConnectionException
     */
    public function put(string $endpoint, array $data = [], string $bodyType = 'json', ?string $idempotencyKey = null): array
    {
        $request = $this->withIdempotencyKey($idempotencyKey);

        $response = $bodyType === 'form'
            ? $request->asForm()->put($endpoint, $data)
            : $request->put($endpoint, $data);

        return $response->throw()->json('data') ?? [];
    }

    /**
     * Get player statistics for a given date, cached for 1 hour.
     *
     * @throws RequestException|ConnectionException
     */
    public function getPlayerStats(int $linkedId, string $date): array
    {
        $cacheKey = "kadiapi_stats_{$linkedId}_{$date}";

        return Cache::remember($cacheKey, now()->addHour(), function () use ($linkedId, $date) {
            return $this->post('stats/customers/played', [
                'customer_id' => $linkedId,
                'start_date' => $date.' 00:00:00',
                'end_date' => $date.' 23:59:59',
            ]);
        });
    }

    /**
     * Register a new customer in KadiApi.
     *
     * @throws RequestException|ConnectionException
     */
    public function createCustomer(array $data): array
    {
        // Deterministic key: retries for the same customer reuse the same key
        // (so KadiApi returns the original result instead of 409), while
        // different customers always get distinct keys.
        $key = 'customer-create-'.($data['account_no'] ?? $data['google_id'] ?? Str::uuid()->toString());

        return $this->withIdempotencyKey($key)
            ->post('customers', $data)
            ->throw()
            ->json() ?? [];
    }

    /**
     * Fetch transactions for a customer, optionally filtered by type.
     *
     * @throws RequestException|ConnectionException
     */
    public function getTransactions(int $customerId, string $type = 'all', ?string $idempotencyKey = null): array
    {
        return $this->withIdempotencyKey($idempotencyKey)
            ->post('customers/transactions/'.encryptOpenSSL($customerId), [
                'payment_type' => $type,
            ])->throw()->json() ?? [];
    }

    /**
     * Update a customer profile in KadiApi.
     *
     * @throws RequestException|ConnectionException
     */
    public function updateCustomer(int $customerId, array $data, ?string $idempotencyKey = null): array
    {
        return $this->withIdempotencyKey($idempotencyKey)
            ->put('customers/'.encryptOpenSSL($customerId), $data)
            ->throw()
            ->json() ?? [];
    }

    /**
     * Fetch a customer profile by ID.
     *
     * @throws RequestException|ConnectionException
     */
    public function getCustomer(int $customerId): array
    {
        return $this->http->get('customers/'.encryptOpenSSL($customerId))
            ->throw()
            ->json() ?? [];
    }

    /**
     * Upload a profile picture for a customer.
     * Stores the image at kadi/images/{accounts_id}/{filename} on the API server.
     *
     * @throws RequestException|ConnectionException
     */
    public function uploadProfilePic(int $customerId, string $filePath, string $filename, ?string $idempotencyKey = null): array
    {
        return $this->withIdempotencyKey($idempotencyKey)
            ->attach('pic', fopen($filePath, 'r'), $filename)
            ->post('customers/'.encryptOpenSSL($customerId).'/pic')
            ->throw()
            ->json() ?? [];
    }

    /**
     * Make a DELETE request
     *
     * @throws RequestException|ConnectionException
     */
    public function delete(string $endpoint, ?string $idempotencyKey = null): array
    {
        return $this->withIdempotencyKey($idempotencyKey)
            ->delete($endpoint)
            ->throw()
            ->json('data') ?? [];
    }

    public function stkDeposit(User $user, int $amount, ?string $idempotencyKey = null): bool
    {
        try {
            $response = $this->withIdempotencyKey($idempotencyKey)
                ->post('deposits/'.encryptOpenSSL($user->linked_id), [
                    'amount' => (string) $amount,
                ])
                ->throw()
                ->json() ?? [];

            Log::info("StkPush Deposit Response for user {$user->id}: {$response['status']}");

            return $response['status'] === 'success';
        } catch (\Throwable $e) {
            Log::error("StkPush Deposit Error for user {$user->id}: {$e->getMessage()}");

            return false;
        }
    }

    public function stkLoad(User $user, array $options, ?string $idempotencyKey = null): bool
    {
        try {
            $response = $this->withIdempotencyKey($idempotencyKey)
                ->post('load/'.encryptOpenSSL($user->linked_id), [
                    'amount' => (string) $options['price'],
                    'type' => $options['type'],
                ])
                ->throw()
                ->json() ?? [];

            Log::info("StkPush Deposit Response for user {$user->id}: {$response['status']}");

            return $response['status'] === 'success';
        } catch (\Throwable $e) {
            Log::error("StkPush Load Error for user {$user->id}: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Request an M-Pesa withdrawal (B2C payout) for a customer.
     *
     * Mirrors stkDeposit()/stkLoad(): try/catch, log response, return bool.
     *
     * Endpoint : POST withdrawals/{encrypted_linked_id}  (confirm against staging)
     * Payload  : ['amount' => string]
     * Response : ['status' => 'success'|'failed', ...]   (confirm against staging)
     */
    public function withdraw(User $user, float $amount, ?string $idempotencyKey = null): bool
    {
        try {
            $response = $this->withIdempotencyKey($idempotencyKey)
                ->post('withdrawals/'.encryptOpenSSL($user->linked_id), [
                    'amount' => (string) $amount,
                ])
                ->throw()
                ->json() ?? [];

            Log::info("StkPush Withdrawal Response for user {$user->id}: ".($response['status'] ?? 'unknown'));

            return ($response['status'] ?? '') === 'success';
        } catch (\Throwable $e) {
            Log::error("StkPush Withdrawal Error for user {$user->id}: {$e->getMessage()}");

            return false;
        }
    }
}
