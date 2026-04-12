<?php

namespace App\Support;

class SportsbookMarkets
{
    public static array $labels = [
        'h2h' => 'Match Winner',
        'totals' => 'Over / Under',
        'btts' => 'Both Teams To Score',
        'draw_no_bet' => 'Draw No Bet',
        'double_chance' => 'Double Chance',
        'h2h_h1' => '1st Half Result',
        'h2h_3_way_h1' => '1st Half 3-Way',
        'totals_h1' => '1st Half Over/Under',
        'h2h_q1' => '1st Quarter Result',
        'totals_q1' => '1st Quarter Over/Under',
        'h2h_p1' => '1st Period Result',
        'totals_p1' => '1st Period Over/Under',
        'h2h_p2' => '2nd Period Result',
        'totals_p2' => '2nd Period Over/Under',
        'h2h_1st_5_innings' => '1st 5 Innings Winner',
        'totals_1st_5_innings' => '1st 5 Innings Total',
    ];

    public static array $priority = [
        'h2h', 'totals', 'btts', 'draw_no_bet', 'double_chance',
        'h2h_h1', 'h2h_3_way_h1', 'totals_h1',
        'h2h_q1', 'totals_q1',
        'h2h_p1', 'totals_p1', 'h2h_p2', 'totals_p2',
        'h2h_1st_5_innings', 'totals_1st_5_innings',
    ];

    public static function getLabel(string $key): string
    {
        return self::$labels[$key] ?? ucwords(str_replace('_', ' ', $key));
    }

    public static function sortMarkets(array $markets): array
    {
        $priority = array_flip(self::$priority);
        usort($markets, fn ($a, $b) => ($priority[$a] ?? 999) <=> ($priority[$b] ?? 999));

        return $markets;
    }
}
