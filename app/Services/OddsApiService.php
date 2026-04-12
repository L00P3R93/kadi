<?php

namespace App\Services;

use App\Models\OddsApiQuota;
use App\Support\SportsbookMarkets;
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
                    'regions' => $this->region,
                    'markets' => 'h2h',
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

    public function getMultiMarketOdds(string $sport, array $markets): array
    {
        try {
            $response = $this->http->get("/sports/{$sport}/odds", [
                'regions' => $this->region,
                'markets' => implode(',', $markets),
                'oddsFormat' => $this->oddsFormat,
            ])->throw();

            $remaining = (int) $response->header('x-requests-remaining', 0);
            $used = (int) $response->header('x-requests-used', 0);

            if ($remaining || $used) {
                OddsApiQuota::updateOrCreate(['id' => 1], [
                    'remaining' => $remaining,
                    'used' => $used,
                    'updated_at' => now(),
                ]);
                Cache::put('odds_api.quota', compact('remaining', 'used'), 3600);
            }

            return $response->json() ?? [];
        } catch (Throwable) {
            return [];
        }
    }

    public function getEventMarkets(string $sport, string $eventId): array
    {
        $cacheKey = "odds_api.event_markets.{$sport}.{$eventId}";

        try {
            return Cache::remember($cacheKey, 3600, function () use ($sport, $eventId) {
                $response = $this->http->get("/sports/{$sport}/events/{$eventId}/markets", [
                    'regions' => $this->region,
                ])->throw();

                $remaining = (int) $response->header('x-requests-remaining', 0);
                $used = (int) $response->header('x-requests-used', 0);
                if ($remaining || $used) {
                    OddsApiQuota::updateOrCreate(['id' => 1], [
                        'remaining' => $remaining,
                        'used' => $used,
                        'updated_at' => now(),
                    ]);
                    Cache::put('odds_api.quota', compact('remaining', 'used'), 3600);
                }

                $data = $response->json();

                return collect($data['bookmakers'] ?? [])
                    ->flatMap(fn ($bm) => collect($bm['markets'] ?? [])->pluck('key'))
                    ->unique()
                    ->values()
                    ->filter(fn ($k) => ! str_ends_with($k, '_lay'))
                    ->toArray();
            });
        } catch (\Exception $e) {
            \Log::warning("getEventMarkets failed for {$eventId}: ".$e->getMessage());

            return [];
        }
    }

    public function getEventOddsByMarkets(string $sport, string $eventId, array $markets): array
    {
        if (empty($markets)) {
            return [];
        }

        $supportedMarkets = array_keys(SportsbookMarkets::$labels);
        $filteredMarkets = array_values(array_intersect($markets, $supportedMarkets));

        if (empty($filteredMarkets)) {
            return [];
        }

        $filteredMarkets = array_slice($filteredMarkets, 0, 8);
        $marketsStr = implode(',', $filteredMarkets);
        $cacheKey = "odds_api.event_odds.{$sport}.{$eventId}.".md5($marketsStr);

        try {
            return Cache::remember($cacheKey, 300, function () use ($sport, $eventId, $marketsStr) {
                $response = $this->http->get("/sports/{$sport}/events/{$eventId}/odds", [
                    'regions' => $this->region,
                    'markets' => $marketsStr,
                    'oddsFormat' => $this->oddsFormat,
                ])->throw();

                $remaining = (int) $response->header('x-requests-remaining', 0);
                $used = (int) $response->header('x-requests-used', 0);
                if ($remaining || $used) {
                    OddsApiQuota::updateOrCreate(['id' => 1], [
                        'remaining' => $remaining,
                        'used' => $used,
                        'updated_at' => now(),
                    ]);
                    Cache::put('odds_api.quota', compact('remaining', 'used'), 3600);
                }

                return $response->json() ?? [];
            });
        } catch (\Exception $e) {
            \Log::warning("getEventOddsByMarkets failed for {$eventId}: ".$e->getMessage());

            return [];
        }
    }

    public function getH2HOddsOnly(string $sport): array
    {
        $cacheKey = "odds_api.h2h_odds.{$sport}";

        try {
            return Cache::remember($cacheKey, 60, function () use ($sport) {
                $response = $this->http->get("/sports/{$sport}/odds", [
                    'regions' => $this->region,
                    'markets' => 'h2h',
                    'oddsFormat' => $this->oddsFormat,
                ])->throw();

                $remaining = (int) $response->header('x-requests-remaining', 0);
                $used = (int) $response->header('x-requests-used', 0);
                if ($remaining || $used) {
                    OddsApiQuota::updateOrCreate(['id' => 1], [
                        'remaining' => $remaining,
                        'used' => $used,
                        'updated_at' => now(),
                    ]);
                    Cache::put('odds_api.quota', compact('remaining', 'used'), 3600);
                }

                return $response->json() ?? [];
            });
        } catch (\Exception $e) {
            \Log::error("getH2HOddsOnly failed for {$sport}: ".$e->getMessage());

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
