<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class BugsApiService
{
    protected PendingRequest $http;

    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.bugs_api.url');
        $this->http = Http::withHeaders([
            // 'x-api-key' => config('services.bugs_api.key'),
            'Accept' => 'application/json',
        ])->baseUrl($this->baseUrl);
    }

    /**
     * @throws RequestException|ConnectionException
     */
    public function registerUser(array $data): array
    {
        return $this->http
            ->post('users', $data)
            ->throw()
            ->json() ?? [];
    }
}
