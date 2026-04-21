<?php

namespace App\Services;

class OddsAggregator
{
    /**
     * All markets to request from the API in a single call.
     * These are passed as the 'markets' query parameter.
     */
    public static array $allMarkets = [
        'h2h',
        'btts',
        'totals',
        'draw_no_bet',
        'alternate_totals',
        'alternate_spreads',
        'spreads',
        'team_totals',
    ];

    /**
     * Markets to silently exclude from display (exchange lay markets).
     */
    public static array $excludeFromDisplay = [
        'h2h_lay',
        'h2h_3_way_lay',
    ];

    /**
     * Market key to display title mapping.
     */
    public static array $marketTitles = [
        'h2h' => 'Match Winner',
        'h2h_3_way' => 'Match Winner (3-Way)',
        'totals' => 'Over / Under',
        'btts' => 'Both Teams To Score',
        'draw_no_bet' => 'Draw No Bet',
        'double_chance' => 'Double Chance',
        'spreads' => 'Handicap',
        'team_totals' => 'Team Totals',
        'alternate_totals' => 'Alternate Totals',
        'alternate_spreads' => 'Alternate Handicap',

        // Half-time markets
        'h2h_h1' => '1st Half Result',
        'h2h_3_way_h1' => '1st Half 3-Way',
        'totals_h1' => '1st Half Over / Under',

        // Quarter/Period markets
        'h2h_q1' => '1st Quarter Result',
        'totals_q1' => '1st Quarter Over / Under',
        'h2h_p1' => '1st Period Result',
        'totals_p1' => '1st Period Over / Under',
        'h2h_p2' => '2nd Period Result',
        'totals_p2' => '2nd Period Over / Under',

        // Baseball specific
        'h2h_1st_5_innings' => '1st 5 Innings Winner',
        'totals_1st_5_innings' => '1st 5 Innings Total',
    ];

    /**
     * Market priority for display order (lower = first).
     */
    public static array $marketPriority = [
        'h2h' => 0,
        'h2h_3_way' => 1,
        'spreads' => 2,
        'totals' => 3,
        'btts' => 4,
        'draw_no_bet' => 5,
        'team_totals' => 6,
        'alternate_totals' => 7,
        'alternate_spreads' => 8,
    ];

    /**
     * Returns the comma-separated markets string for the API call.
     */
    public static function getMarketsParam(): string
    {
        return implode(',', self::$allMarkets);
    }

    public static function getMarketTitle(string $marketKey): string
    {
        return self::$marketTitles[$marketKey] ?? ucwords(str_replace('_', ' ', $marketKey));
    }

    /**
     * Aggregate a raw Odds API event response (containing bookmakers array)
     * into a normalised structure with the best price per outcome per market.
     *
     * @param  array  $eventData  Raw event object from the Odds API
     * @return array Normalised event with 'markets' map instead of 'bookmakers' array
     */
    public static function aggregate(array $eventData): array
    {
        $homeTeam = $eventData['home_team'] ?? '';
        $awayTeam = $eventData['away_team'] ?? '';
        $bookmakers = $eventData['bookmakers'] ?? [];
        $lastUpdate = null;

        // --- Step 1: Pool the best price per outcome per market ---
        // $pool[$marketKey][$outcomePoolKey] = [name, price, point, description, bookmaker]
        $pool = [];

        foreach ($bookmakers as $bm) {
            $bmKey = $bm['key'] ?? 'unknown';
            $bmLastUpdate = $bm['last_update'] ?? null;

            foreach ($bm['markets'] ?? [] as $market) {
                $mKey = $market['key'] ?? '';
                if (empty($mKey) || in_array($mKey, self::$excludeFromDisplay)) {
                    continue;
                }

                // Track most recent update time
                if ($market['last_update'] ?? false) {
                    $marketTime = strtotime($market['last_update']);
                    if ($lastUpdate === null || $marketTime > $lastUpdate) {
                        $lastUpdate = $marketTime;
                    }
                }

                foreach ($market['outcomes'] ?? [] as $outcome) {
                    $oName = trim($outcome['name'] ?? '');
                    $oPrice = (float) ($outcome['price'] ?? 0);
                    $oPoint = isset($outcome['point']) ? (float) $outcome['point'] : null;

                    if ($oPrice <= 1.0 || empty($oName)) {
                        continue; // skip invalid odds
                    }

                    // Build a unique pool key that distinguishes:
                    // - Same outcome name but different lines (e.g. Over 2.5 vs Over 3.5)
                    // - Same outcome name but different descriptions (player props)
                    $poolKey = $oName;
                    if ($oPoint !== null) {
                        $poolKey .= '|'.$oPoint;
                    }

                    // Keep only the BEST (highest) price for this outcome
                    if (! isset($pool[$mKey][$poolKey]) || $oPrice > $pool[$mKey][$poolKey]['price']) {
                        $pool[$mKey][$poolKey] = [
                            'name' => $oName,
                            'price' => $oPrice,
                            'point' => $oPoint,
                            'bookmaker' => $bmKey,
                        ];
                    }
                }
            }
        }

        // --- Step 2: Convert pool to sorted outcome arrays per market ---
        $markets = [];
        foreach ($pool as $mKey => $outcomeMap) {
            $outcomes = array_values($outcomeMap);
            $markets[$mKey] = [
                'title' => self::getMarketTitle($mKey),
                'outcomes' => self::sortOutcomes($mKey, $outcomes, $homeTeam, $awayTeam),
            ];
        }

        // --- Step 3: Sort markets in preferred display priority ---
        $markets = self::sortMarketsByPriority($markets);

        return [
            'id' => $eventData['id'] ?? '',
            'sport_key' => $eventData['sport_key'] ?? '',
            'sport_title' => $eventData['sport_title'] ?? '',
            'home_team' => $homeTeam,
            'away_team' => $awayTeam,
            'commence_time' => $eventData['commence_time'] ?? '',
            'last_update' => $lastUpdate ? date('c', $lastUpdate) : null,
            'markets' => $markets,
        ];
    }

    /**
     * Sort outcomes within a market in a logical, user-friendly order.
     */
    private static function sortOutcomes(
        string $marketKey,
        array $outcomes,
        string $homeTeam,
        string $awayTeam
    ): array {
        return match (true) {
            // h2h family: Home | Draw | Away
            in_array($marketKey, ['h2h', 'h2h_3_way', 'draw_no_bet']) => self::sortH2H($outcomes, $homeTeam, $awayTeam),

            // totals family: group by point asc, Over before Under within each point
            in_array($marketKey, ['totals', 'alternate_totals', 'team_totals']) => self::sortTotals($outcomes),

            // spreads: home team first, then sort by point asc
            in_array($marketKey, ['spreads', 'alternate_spreads']) => self::sortSpreads($outcomes, $homeTeam, $awayTeam),

            // btts: Yes | No
            $marketKey === 'btts' => self::sortYesNo($outcomes),

            // default: best price first
            default => collect($outcomes)->sortByDesc('price')->values()->all(),
        };
    }

    private static function sortH2H(array $outcomes, string $home, string $away): array
    {
        $order = [$home => 0, 'Draw' => 1, $away => 2];
        usort($outcomes, fn ($a, $b) => ($order[$a['name']] ?? 99) <=> ($order[$b['name']] ?? 99)
        );

        return $outcomes;
    }

    /**
     * Sort totals: group by point asc, Over before Under within each point
     */
    private static function sortTotals(array $outcomes): array
    {
        usort($outcomes, function ($a, $b) {
            // Handle null points - push to end
            $aPoint = $a['point'] ?? null;
            $bPoint = $b['point'] ?? null;

            if ($aPoint === null || $bPoint === null) {
                return 0;
            }

            $pointCmp = ($aPoint) <=> ($bPoint);
            if ($pointCmp !== 0) {
                return $pointCmp;
            }

            // Over before Under
            $aOver = stripos($a['name'], 'over') !== false ? 0 : 1;
            $bOver = stripos($b['name'], 'over') !== false ? 0 : 1;

            return $aOver <=> $bOver;
        });

        return $outcomes;
    }

    /**
     * Sort spreads: Group by point value (handling negatives correctly), home team first within each point pair
     */
    private static function sortSpreads(array $outcomes, string $home, string $away): array
    {
        usort($outcomes, function ($a, $b) use ($home) {
            // Get pointa, default to 0 if null
            $aPoint = $a['point'] ?? 0;
            $bPoint = $b['point'] ?? 0;

            // First sort by point value (ascending: -2.5, -1.5 , 0, 1.5, 2.5)
            $pointCmp = ($aPoint) <=> ($bPoint);
            if ($pointCmp !== 0) {
                return $pointCmp;
            }

            // Same point: Home team first, then Away
            $aIsHome = $a['name'] === $home ? 0 : 1;
            $bIsHome = $b['name'] === $home ? 0 : 1;

            return $aIsHome <=> $bIsHome;
        });

        return $outcomes;
    }

    private static function sortYesNo(array $outcomes): array
    {
        $order = ['Yes' => 0, 'No' => 1];
        usort($outcomes, fn ($a, $b) => ($order[$a['name']] ?? 99) <=> ($order[$b['name']] ?? 99)
        );

        return $outcomes;
    }

    /**
     * Sort by price descending (best odds first)
     */
    private static function sortByPriceDesc(array $outcomes): array
    {
        usort($outcomes, fn ($a, $b) => ($b['price'] ?? 0) <=> ($a['price'] ?? 0));

        return $outcomes;
    }

    private static function sortMarketsByPriority(array $markets): array
    {
        uksort($markets, fn ($a, $b) => (self::$marketPriority[$a] ?? 999) <=> (self::$marketPriority[$b] ?? 999)
        );

        return $markets;
    }

    /**
     * Get the three h2h slots for the event list quick-row display:
     * [home, draw, away] — any may be null if unavailable.
     */
    public static function getH2HSlots(array $event): array
    {
        $home = $event['home_team'] ?? '';
        $away = $event['away_team'] ?? '';
        $outcomes = $event['markets']['h2h']['outcomes'] ?? $event['markets']['h2h_3_way']['outcomes'] ?? [];

        $slots = ['home' => null, 'draw' => null, 'away' => null];

        foreach ($outcomes as $o) {
            match ($o['name']) {
                $home => $slots['home'] = $o,
                'Draw' => $slots['draw'] = $o,
                $away => $slots['away'] = $o,
                default => null,
            };
        }

        return [
            ['label' => '1', 'key' => 'home', 'outcome' => $slots['home']],
            ['label' => 'X', 'key' => 'draw', 'outcome' => $slots['draw']],
            ['label' => '2', 'key' => 'away', 'outcome' => $slots['away']],
        ];
    }

    /**
     * Return all markets that have at least one outcome (filter empty).
     */
    public static function getPrimaryMarket(array $event): ?array
    {
        return $event['markets']['h2h'] ?? $event['markets']['h2h_3_way'] ?? null;
    }
}
