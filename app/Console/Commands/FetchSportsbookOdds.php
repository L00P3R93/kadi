<?php

namespace App\Console\Commands;

use App\Services\OddsApiService;
use Illuminate\Console\Command;

class FetchSportsbookOdds extends Command
{
    protected $signature = 'sportsbook:fetch-odds';

    protected $description = 'Fetch and cache odds from The Odds API for guest sportsbook display';

    public function handle(): void
    {
        $service = app(OddsApiService::class);

        $this->info('Fetching active sports...');
        $sports = $service->getSports();
        $activeSports = collect($sports)->where('active', true)->values()->toArray();

        $sportData = [];
        $totalCreditsEstimate = 0;

        foreach ($activeSports as $sport) {
            $sportKey = $sport['key'];
            $this->line("  Processing: {$sport['title']} ({$sportKey})");

            // 1. Fetch events — FREE
            $events = $service->getEvents($sportKey);
            if (empty($events)) {
                $this->line('    No events, skipping.');

                continue;
            }

            // 2. Fetch H2H odds ONLY — costs 1 credit per sport
            $h2hOdds = $service->getH2HOddsOnly($sportKey);
            $totalCreditsEstimate++;

            // 3. Merge h2h odds into events (keep only first bookmaker, h2h market only)
            $oddsMap = collect($h2hOdds)->keyBy('id');
            $mergedEvents = collect($events)->map(function ($event) use ($oddsMap) {
                $eventOdds = $oddsMap->get($event['id'], []);
                $h2hBookmakers = collect($eventOdds['bookmakers'] ?? [])
                    ->map(function ($bm) {
                        $h2hMarkets = collect($bm['markets'] ?? [])
                            ->filter(fn ($m) => $m['key'] === 'h2h')
                            ->values()
                            ->toArray();

                        return $h2hMarkets ? array_merge($bm, ['markets' => $h2hMarkets]) : null;
                    })
                    ->filter()
                    ->take(1)
                    ->values()
                    ->toArray();

                return array_merge($event, ['bookmakers' => $h2hBookmakers]);
            })->toArray();

            $sportData[] = [
                'sport_key' => $sportKey,
                'sport_title' => $sport['title'],
                'sport_group' => $sport['group'],
                'fetched_at' => now()->toIso8601String(),
                'events' => $mergedEvents,
            ];

            usleep(200000); // 0.2s between sports to avoid rate limiting
        }

        $path = storage_path('app/sportsbook/cache.json');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'expires_at' => now()->addHours(2)->toIso8601String(),
            'sports' => $sportData,
        ];

        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info('');
        $this->info('✓ Cache written: '.count($sportData).' sports');
        $this->info("✓ Estimated credits used: ~{$totalCreditsEstimate}");
        $this->info('✓ File size: '.round(filesize($path) / 1024, 1).' KB');
        $this->info("✓ Path: {$path}");
    }
}
