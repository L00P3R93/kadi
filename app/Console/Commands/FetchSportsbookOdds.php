<?php

namespace App\Console\Commands;

use App\Services\OddsApiService;
use Illuminate\Console\Command;

class FetchSportsbookOdds extends Command
{
    protected $signature = 'sportsbook:fetch-odds';

    protected $description = 'Fetch and cache odds from The Odds API for guest sportsbook display';

    /**
     * Only fetch these three sport group categories.
     * Keys match the 'group' field in the Odds API sports response.
     */
    private array $allowedGroups = [
        'Soccer' => ['display_name' => 'Football',   'priority' => 1],
        'Basketball' => ['display_name' => 'Basketball', 'priority' => 2],
        'Boxing' => ['display_name' => 'Boxing',     'priority' => 3],
    ];

    public function handle(): void
    {
        $startTime = microtime(true);
        $service = app(OddsApiService::class);

        $this->info('');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   ANGEL PALACE — Sportsbook Cache Fetch  ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info('Started: '.now()->format('Y-m-d H:i:s').' EAT');
        $this->info('Region:  uk');
        $this->info('Markets: h2h,spreads,totals');
        $this->info('Sports:  Football (Soccer), Basketball, Boxing');
        $this->info('');

        // ─── STEP 1: Get active sports filtered to our 3 groups ───────────
        $this->line('► Fetching sports list...');
        $allSports = $service->getSports(); // free endpoint

        $activeSports = collect($allSports)
            ->filter(fn ($s) => $s['active'] && isset($this->allowedGroups[$s['group']]))
            ->sortBy(fn ($s) => $this->allowedGroups[$s['group']]['priority'])
            ->values()
            ->toArray();

        $this->info('  Active sports in scope: '.count($activeSports));
        foreach ($activeSports as $s) {
            $displayGroup = $this->allowedGroups[$s['group']]['display_name'];
            $this->line("  [{$displayGroup}] {$s['title']} ({$s['key']})");
        }

        // ─── STEP 2: For each sport, call getSportEvents() (1 call per spot, total credits = 1 region x 3 markets = 3 credits) ───────────
        $sportData = [];
        $totalEvents = 0;
        $skippedSports = 0;

        foreach ($activeSports as $sport) {
            $sportKey = $sport['key'];
            $sportGroup = $sport['group'];
            $groupConfig = $this->allowedGroups[$sportGroup];
            $displayGroup = $groupConfig['display_name'];

            $this->info('');
            $this->line("►  [{$displayGroup}] {$displayGroup}");

            try {
                $eventsKeyed = $service->getSportEvents($sportKey);
            } catch (\Exception $e) {
                $this->warn("  Failed to fetch {$sportKey}: ".$e->getMessage());
                $skippedSports++;

                continue;
            }

            if (empty($eventsKeyed)) {
                $this->line("  No upcoming events found for {$sportKey}. Skipping.");

                continue;
            }

            $mergedEvents = array_values($eventsKeyed);

            foreach ($mergedEvents as $event) {
                $marketsFound = count($event['markets']) ?? [];
                $this->line("  ✓ {$event['home_team']} vs {$event['away_team']} - {$marketsFound} markets");
            }

            $totalEvents += count($mergedEvents);

            $sportData[] = [
                'sport_key' => $sportKey,
                'sport_title' => $sport['title'],
                'sport_group' => $sportGroup,
                'display_group' => $displayGroup,
                'display_name' => $sport['title'],
                'priority' => $groupConfig['priority'],
                'fetched_at' => now()->toIso8601String(),
                'events' => $mergedEvents,
            ];
        }

        // ─── STEP 3: Sort by priority
        usort($sportData, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        // ─── STEP 4: Build the payload
        $payload = [
            'generated_at' => now()->toIso8601String(),
            'expires_at' => now()->addHours(2)->toIso8601String(),
            'meta' => [
                'total_sports' => count($sportData),
                'total_events' => $totalEvents,
                'region' => 'uk',
                'markets' => ['h2h', 'spreads', 'totals'],
            ],
            'sports' => $sportData,
        ];

        // ─── STEP 5: Write the JSON file
        $path = storage_path('app/sportsbook/cache.json');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $elapsed = round(microtime(true) - $startTime, 1);
        $fileSize = round(filesize($path) / 1024, 1);

        $this->info('');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║              Cache Written ✓             ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->line('  Sports:      '.count($sportData));
        $this->line("  Events:      {$totalEvents}");
        $this->line("  File size:   {$fileSize} KB");
        $this->line("  Duration:    {$elapsed}s");
        $this->line('  Expires:     '.now()->addHours(2)->setTimezone('Africa/Nairobi')->format('Y-m-d H:i').' EAT');
        $this->line("  Path:        {$path}");
    }
}
