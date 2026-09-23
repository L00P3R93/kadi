<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Turns KadiApi's recent played games (`customers/played/recent`) into the shapes the history page and
 * the dispute flow use, so neither reads raw API fields.
 *
 * - A single game can be reported when the player lost it; the complaint names its `game_wallet_id`.
 * - A tournament/jackpot is listed with its rounds. A lost round can be reported when KadiApi found the
 *   opponent; the complaint names the OPPONENT's `competition_wallet_id` (never the player's own) and,
 *   while that wallet is open, the opponent's winning transaction of that round.
 *
 * Every reportable item has a `report_key` (game:{game_wallet_id} or {kind}-round:{own transaction_id}).
 */
final class PlayedGame
{
    public const GAME = 'game';

    public const TOURNAMENT = 'tournament';

    public const JACKPOT = 'jackpot';

    /** Our kind => the list it arrives in from KadiApi. */
    public const API_LISTS = [
        self::GAME => 'single_games',
        self::TOURNAMENT => 'tournament_games',
        self::JACKPOT => 'jackpot_games',
    ];

    public const LABELS = [
        self::GAME => 'Games',
        self::TOURNAMENT => 'Tournaments',
        self::JACKPOT => 'Jackpots',
    ];

    /**
     * Every kind's list from a KadiApi response (plain list or paginated `{data: [...]}`), newest
     * first, at most $limit each.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public static function listsFromApi(array $body, int $limit): array
    {
        $lists = [];

        foreach (self::API_LISTS as $kind => $field) {
            $rows = $body[$field] ?? [];
            $rows = is_array($rows['data'] ?? null) ? $rows['data'] : $rows;

            $lists[$kind] = collect(is_array($rows) ? $rows : [])
                ->filter(fn ($row) => is_array($row))
                ->map(fn (array $row) => $kind === self::GAME ? self::game($row) : self::competition($kind, $row))
                ->sortByDesc('played_at')
                ->take($limit)
                ->values()
                ->all();
        }

        return $lists;
    }

    /**
     * Every reportable item in the lists, keyed by report key, with what a complaint needs.
     *
     * @return array<string, array{key: string, kind: string, title: string, amount: float, played_at: ?string, game_wallet_id: ?int, competition_wallet_id: ?int, transaction_ids: list<int>, game_id: ?string, competition_id: ?string, transaction_id: ?int, opponent_transaction_id: ?int}>
     */
    public static function reportables(array $lists): array
    {
        $items = [];

        foreach ($lists[self::GAME] ?? [] as $game) {
            if ($game['report_key']) {
                $items[$game['report_key']] = [
                    'key' => $game['report_key'],
                    'kind' => self::GAME,
                    'title' => $game['title'],
                    'amount' => $game['amount'],
                    'played_at' => $game['played_at'],
                    'game_wallet_id' => $game['game_wallet_id'],
                    'competition_wallet_id' => null,
                    'transaction_ids' => [],
                    'game_id' => $game['game_id'],
                    'competition_id' => null,
                    'transaction_id' => null,
                    'opponent_transaction_id' => null,
                ];
            }
        }

        foreach ([self::TOURNAMENT, self::JACKPOT] as $kind) {
            foreach ($lists[$kind] ?? [] as $competition) {
                foreach ($competition['rounds'] as $round) {
                    if (! $round['report_key']) {
                        continue;
                    }

                    $opponent = $round['opponent'];

                    $items[$round['report_key']] = [
                        'key' => $round['report_key'],
                        'kind' => $kind,
                        'title' => $competition['title'].' round'.($round['level'] !== null ? ' (level '.$round['level'].')' : ''),
                        'amount' => $round['amount'],
                        'played_at' => $round['played_at'],
                        'game_wallet_id' => null,
                        'competition_wallet_id' => $opponent['competition_wallet_id'],
                        // Open wallet: name the round's winning transaction. Closed: KadiApi disputes its payout.
                        'transaction_ids' => $opponent['open'] && $opponent['transaction_id'] ? [$opponent['transaction_id']] : [],
                        'game_id' => null,
                        'competition_id' => $competition['competition_id'],
                        'transaction_id' => $round['transaction_id'],
                        'opponent_transaction_id' => $opponent['transaction_id'],
                    ];
                }
            }
        }

        return $items;
    }

    private static function game(array $row): array
    {
        $gameWalletId = self::positiveInt($row['game_wallet_id'] ?? null);
        $result = self::result($row['state'] ?? null);

        return [
            'kind' => self::GAME,
            // Only a lost game has a winner payout to dispute.
            'report_key' => $gameWalletId && $result === 'loss' ? self::GAME.':'.$gameWalletId : null,
            'game_wallet_id' => $gameWalletId,
            'game_id' => self::stringOrNull($row['game_id'] ?? null),
            'title' => self::stringOrNull($row['game_type'] ?? null) ?? 'Game',
            'players' => self::positiveInt($row['players'] ?? null),
            'amount' => self::money($row['amount'] ?? null),
            'result' => $result,
            'played_at' => self::timestamp($row['created_at'] ?? null),
        ];
    }

    private static function competition(string $kind, array $row): array
    {
        $rounds = collect(is_array($row['games'] ?? null) ? $row['games'] : [])
            ->filter(fn ($round) => is_array($round))
            ->map(fn (array $round) => self::round($kind, $round))
            ->sortByDesc('played_at')
            ->values()
            ->all();

        return [
            'kind' => $kind,
            'competition_wallet_id' => self::positiveInt($row['competition_wallet_id'] ?? null),
            'competition_id' => self::stringOrNull($row['competition_id'] ?? null),
            'title' => $kind === self::TOURNAMENT ? 'Tournament' : 'Jackpot',
            'level' => self::intOrNull($row['level'] ?? null),
            'balance' => self::money($row['balance'] ?? null),
            'open' => (int) ($row['status'] ?? 0) === 1,
            'wins' => (int) ($row['wins'] ?? 0),
            'losses' => (int) ($row['losses'] ?? 0),
            'played_at' => self::timestamp($row['created_at'] ?? null),
            'rounds' => $rounds,
        ];
    }

    private static function round(string $kind, array $round): array
    {
        $transactionId = self::positiveInt($round['transaction_id'] ?? null);
        $result = self::result($round['payment_type'] ?? null);
        $opponent = is_array($round['opponent'] ?? null) ? $round['opponent'] : null;
        $opponentWalletId = self::positiveInt($opponent['competition_wallet_id'] ?? null);

        // The opponent's customer id is deliberately not kept: players never need it.
        $opponent = $opponentWalletId ? [
            'competition_wallet_id' => $opponentWalletId,
            'open' => (int) ($opponent['wallet_status'] ?? 0) === 1,
            'transaction_id' => self::positiveInt($opponent['transaction_id'] ?? null),
        ] : null;

        return [
            // Only a lost round against a known opponent can be reported.
            'report_key' => $transactionId && $result === 'loss' && $opponent ? $kind.'-round:'.$transactionId : null,
            'transaction_id' => $transactionId,
            'result' => $result,
            'amount' => self::money($round['amount'] ?? null),
            'level' => self::intOrNull($round['level'] ?? null),
            'played_at' => self::timestamp($round['created_at'] ?? null),
            'opponent' => $opponent,
        ];
    }

    private static function result(mixed $value): ?string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, ['win', 'loss'], true) ? $value : null;
    }

    private static function positiveInt(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    private static function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    private static function money(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return is_scalar($value) && trim((string) $value) !== '' ? trim((string) $value) : null;
    }

    private static function timestamp(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }
}
