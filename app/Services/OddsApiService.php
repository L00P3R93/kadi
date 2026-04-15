<?php

namespace App\Services;

use App\Jobs\FetchSportEventsJob;
use App\Models\OddsApiQuota;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OddsApiService
{
    /**
     * @var PendingRequest|Factory
     */
    protected PendingRequest $http;

    /**
     * @var string|mixed
     */
    protected string $region;

    /**
     * @var string|mixed
     */
    protected string $oddsFormat;

    /**
     * @var array|string[]
     */
    public static array $allMarkets = [
        'h2h', 'btts', 'totals', 'draw_no_bet', 'alternate_totals',
        'alternate_spreads', 'spreads', 'team_totals', 'h2h_3_way',
    ];

    /**
     * Only these sports will be fetched.
     */
    public static array $allowedSports = [
        'soccer_epl',
        'basketball_nba',
        'boxing',
    ];

    public function __construct()
    {
        $config = config('services.odds_api');

        $this->region = $config['region'];
        $this->oddsFormat = $config['odds_format'];

        $this->http = Http::baseUrl($config['base_url'])
            ->withQueryParameters(['apiKey' => $config['key']])
            ->timeout(30)
            ->retry(3, 1000);
    }

    /**
     * Get sports list (cached, filtered)
     */
    public function getSports(): array
    {
        return Cache::remember('odds_api.sports', 3600, function () {
            $response = $this->http->get('/sports')->throw()->json();

            return array_filter($response, fn ($s) => in_array($s['key'], self::$allowedSports) && ($s['active'] ?? false));
        });
    }

    /**
     * Get events for a specific sport (cached)
     */
    public function getSportEvents(string $sportKey): array
    {
        if (! in_array($sportKey, self::$allowedSports)) {
            Log::warning("Attempted to fetch non-allowed sport: {$sportKey}");

            return [];
        }

        return Cache::remember("odds_api.{$sportKey}.events", 300, function () use ($sportKey) {
            $httpResponse = $this->http->get("/sports/{$sportKey}/odds", [
                'regions' => $this->region,
                'markets' => 'h2h,totals',
                'oddsFormat' => $this->oddsFormat,
            ])->throw();

            $response = $httpResponse->json();

            $events = [];
            foreach ($response as $event) {
                $aggregated = OddsAggregator::aggregate($event);
                $events[$event['id']] = [
                    'id' => $event['id'],
                    'home_team' => $event['home_team'],
                    'away_team' => $event['away_team'],
                    'commence_time' => $event['commence_time'],
                    'sport_key' => $sportKey,
                    'sport_title' => $event['sport_title'] ?? '',
                    'markets' => $aggregated['markets'],
                ];
            }

            $this->trackQuota($httpResponse);

            return $events;
        });
    }

    /**
     * Get odds for a specific event (cached, uses OddsAggregator)
     */
    public function getSportEventOdds(string $sportKey, string $eventId): array
    {
        if (! in_array($sportKey, self::$allowedSports)) {
            return [];
        }

        return Cache::remember("odds_api.{$sportKey}.events.{$eventId}.odds", 60, function () use ($sportKey, $eventId) {
            $response = $this->http->get("/sports/{$sportKey}/events/{$eventId}/odds", [
                'regions' => $this->region,
                'markets' => OddsAggregator::getMarketsParam(),
                'oddsFormat' => $this->oddsFormat,
            ])->throw()->json();

            return OddsAggregator::aggregate($response);
        });
    }

    /**
     * Dispatch a job to sync all sports data in background
     */
    public function dispatchFullSync(): void
    {
        $sports = $this->getSports();
        Log::info('Starting full odds sync', [
            'allowed' => self::$allowedSports,
            'found' => array_column($sports, 'key'),
        ]);
        foreach ($sports as $sport) {
            FetchSportEventsJob::dispatch($sport['key'])->onQueue('odds-api-events');
        }
    }

    /**
     * Dispatch a job to sync a specific sport's events in background
     */
    public function dispatchSportSync(string $sportKey): void
    {
        FetchSportEventsJob::dispatch($sportKey)->onQueue('odds-api-events');
    }

    /**
     * Get sync status for monitoring
     */
    public function getSyncStatus(): array
    {
        $sports = $this->getSports();

        $status = [];
        foreach ($sports as $sport) {
            $key = $sport['key'];
            $events = Cache::get("odds_api.{$key}.events", []);
            $cachedOdds = 0;

            foreach (array_keys($events) as $eventId) {
                if (Cache::has("odds_api.{$key}.events.{$eventId}.odds")) {
                    $cachedOdds++;
                }
            }

            $status[$key] = [
                'title' => $sport['title'],
                'events_total' => count($events),
                'odds_cached' => $cachedOdds,
                'percent_complete' => count($events) > 0
                    ? round(($cachedOdds / count($events)) * 100, 1)
                    : 0,
                'events_cached_at' => Cache::get("odds_api.{$key}.events_cached_at"),
            ];
        }

        return $status;
    }

    public function getOdds(): array
    {
        $sports = $this->getSports();
        foreach ($sports as $sportKey => &$sport) {
            $sport['events'] = $this->getSportEvents($sportKey);
            foreach ($sport['events'] as $eventId => &$event) {
                $event['odds'] = $this->getSportEventOdds($sportKey, $eventId);
            }
        }

        return $sports;
    }

    private function trackQuota(Response $response): void
    {
        $remaining = (int) $response->header('x-requests-remaining', 0);
        $used = (int) $response->header('x-requests-used', 0);
        if ($remaining > 0 || $used > 0) {
            OddsApiQuota::updateOrCreate(
                ['id' => 1],
                ['remaining' => $remaining, 'used' => $used, 'updated_at' => now()]
            );
            Cache::put('odds_api.quota', compact('remaining', 'used'), 3600);
        }
    }
}
