<?php

namespace App\Services;

use App\Models\OddsApiQuota;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class OddsApiService
{
    protected PendingRequest $http;

    protected string $region;

    protected string $oddsFormat;

    public function __construct()
    {
        $config = config('services.odds_api');

        $this->region = $config['region'];
        $this->oddsFormat = $config['odds_format'];

        $this->http = Http::baseUrl($config['base_url'])
            ->withQueryParameters(['apiKey' => $config['key']]);
    }

    public function getSports(): array
    {
        try {
            return Cache::remember('odds_api.sports', 3600, function () {
                return $this->http->get('/sports')->throw()->json() ?? [];
            });
        } catch (Throwable) {
            return [];
        }
    }

    public function getEvents(string $sport): array
    {
        try {
            return Cache::remember("odds_api.events.{$sport}", 300, function () use ($sport) {
                return $this->http->get("/sports/{$sport}/events")->throw()->json() ?? [];
            });
        } catch (Throwable) {
            return [];
        }
    }

    public function getOdds(string $sport): array
    {
        try {
            return Cache::remember("odds_api.odds.{$sport}", 60, function () use ($sport) {
                $response = $this->http->get("/sports/{$sport}/odds", [
                    'regions'    => $this->region,
                    'markets'    => 'h2h',
                    'oddsFormat' => $this->oddsFormat,
                ])->throw();

                $remaining = (int) $response->header('x-requests-remaining');
                $used = (int) $response->header('x-requests-used');

                OddsApiQuota::updateOrCreate(
                    ['id' => 1],
                    ['remaining' => $remaining, 'used' => $used]
                );

                Cache::put('odds_api.quota', compact('remaining', 'used'), 3600);

                return $response->json() ?? [];
            });
        } catch (Throwable) {
            return [];
        }
    }

    public function getScores(string $sport): array
    {
        try {
            return Cache::remember("odds_api.scores.{$sport}", 30, function () use ($sport) {
                return $this->http->get("/sports/{$sport}/scores", [
                    'daysFrom' => 1,
                ])->throw()->json() ?? [];
            });
        } catch (Throwable) {
            return [];
        }
    }
}
