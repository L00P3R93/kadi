<?php

namespace App\Services;

use App\Facades\KadiApi;
use App\Models\GameDispute;
use App\Models\User;
use App\Support\PlayedGame;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * A player's recent games and the problems they report about them. See docs/game-disputes.md.
 *
 * Reporting:
 *   1. the item must be reportable in the player's OWN recent games, read server-side (a lost single
 *      game, or a lost tournament/jackpot round with a known opponent) whose `kadi.game_level_pending`
 *      row is still pending and before its `expires_at` (see withGameDeadlines()). Ids from the browser
 *      are never sent to KadiApi as-is;
 *   2. POST complaints to KadiApi (holds the disputed winnings in escrow). A round names the opponent's
 *      competition wallet, plus the opponent's winning transaction while that wallet is open;
 *   3. only once KadiApi accepted it: record it locally and set the player's pending
 *      `kadi.game_level_pending` rows for that game (single: game_id, round: competition_id) to `disputed`.
 *
 * Step 3's game-database write never throws: the complaint already exists, so an outage there is
 * logged rather than shown to the player as a failure.
 */
class GameDisputeService
{
    public const OTHER_REASON = 'Other';

    /** The pick-list; "Other" lets the player type their own reason. Sent to KadiApi as `reason`. */
    public const REASONS = [
        'My opponent kept playing after I disconnected',
        'The game froze or crashed',
        'The wrong result was recorded',
        'I think my opponent cheated',
        self::OTHER_REASON,
    ];

    public const UNDER_REVIEW = 'under_review';

    public const REPORTED = 'reported';

    /** How long KadiApi's list of a player's open complaints is reused (its limit is 20/min, shared with stats). */
    private const OPEN_COMPLAINTS_SECONDS = 300;

    /**
     * The player's latest games, tournaments and jackpots (see PlayedGame), cached briefly.
     *
     * @return array<string, list<array<string, mixed>>>
     *
     * @throws \Throwable when KadiApi cannot be reached
     */
    public function recentGames(User $user, bool $fresh = false): array
    {
        if (! $user->linked_id) {
            return array_fill_keys(array_keys(PlayedGame::API_LISTS), []);
        }

        $cacheKey = self::cacheKey($user);

        if ($fresh) {
            Cache::forget($cacheKey);
        }

        $lists = Cache::remember(
            $cacheKey,
            now()->addSeconds((int) config('kadi.game_disputes.cache_seconds', 15)),
            fn () => KadiApi::getPlayedGames((int) $user->linked_id),
        );

        // Not cached: a row that stops being pending (paid out, cancelled) must close reporting at once.
        return $this->withGameDeadlines($user, $lists);
    }

    /**
     * The game server decides how long a game can be disputed: while the player's
     * `kadi.game_level_pending` row for it is `pending`, until its `expires_at` (created_at + 3 minutes
     * today). Rows match on game_id: a single game's game_id, a round's competition_id (preferring
     * rows of the round's level). No pending row means the window is closed.
     *
     * If the game database cannot be read, the lists keep PlayedGame's fallback (KadiApi's created_at
     * + `report_window_minutes`), so an outage there does not block every report.
     *
     * @param  array<string, list<array<string, mixed>>>  $lists
     * @return array<string, list<array<string, mixed>>>
     */
    private function withGameDeadlines(User $user, array $lists): array
    {
        $gameIds = [];

        foreach ($lists[PlayedGame::GAME] ?? [] as $game) {
            if ($game['report_key'] && $game['game_id']) {
                $gameIds[] = $game['game_id'];
            }
        }

        foreach ([PlayedGame::TOURNAMENT, PlayedGame::JACKPOT] as $kind) {
            foreach ($lists[$kind] ?? [] as $competition) {
                if ($competition['competition_id'] && collect($competition['rounds'])->contains(fn ($round) => $round['report_key'])) {
                    $gameIds[] = $competition['competition_id'];
                }
            }
        }

        if ($gameIds === []) {
            return $lists;
        }

        try {
            $db = DB::connection('kadi');
            // Read as epoch seconds: correct whatever time zone the game database's session uses.
            $epoch = $db->getDriverName() === 'sqlite' ? "CAST(strftime('%s', expires_at) AS INTEGER)" : 'UNIX_TIMESTAMP(expires_at)';

            $rows = $db->table('game_level_pending')
                ->where('account_id', $user->linked_id)
                ->where('status', 'pending')
                ->whereIn('game_id', array_values(array_unique($gameIds)))
                ->selectRaw("game_id, level, {$epoch} as expires_ts")
                ->get();
        } catch (\Throwable $e) {
            Log::warning("Game dispute: could not read game_level_pending deadlines for user {$user->id}: ".class_basename($e));

            return $lists;
        }

        $deadline = function (?string $gameId, ?int $level) use ($rows): ?string {
            $matches = $rows->where('game_id', $gameId);

            if ($level !== null && $matches->contains(fn ($row) => (int) $row->level === $level)) {
                $matches = $matches->filter(fn ($row) => (int) $row->level === $level);
            }

            $latest = $matches->max(fn ($row) => (int) $row->expires_ts);

            return $latest ? Carbon::createFromTimestamp($latest, config('app.timezone'))->toDateTimeString() : null;
        };

        foreach ($lists[PlayedGame::GAME] ?? [] as $i => $game) {
            if ($game['report_key']) {
                $lists[PlayedGame::GAME][$i]['report_expires_at'] = $deadline($game['game_id'], null);
            }
        }

        foreach ([PlayedGame::TOURNAMENT, PlayedGame::JACKPOT] as $kind) {
            foreach ($lists[$kind] ?? [] as $c => $competition) {
                foreach ($competition['rounds'] as $r => $round) {
                    if ($round['report_key']) {
                        $lists[$kind][$c]['rounds'][$r]['report_expires_at'] = $deadline($competition['competition_id'], $round['level']);
                    }
                }
            }
        }

        return $lists;
    }

    /**
     * Which reportable items in $lists are already reported: `under_review` when KadiApi has an open
     * complaint covering it, `reported` when we filed one that is no longer open (or KadiApi's list
     * could not be read).
     *
     * @return array<string, string> report key => state
     */
    public function reportStates(User $user, array $lists): array
    {
        $local = $user->gameDisputes()->pluck('subject_key')->flip();
        $open = $this->openComplaints($user);
        $states = [];

        foreach (PlayedGame::reportables($lists) as $key => $item) {
            $underReview = $item['kind'] === PlayedGame::GAME
                ? isset($open['game_wallet_ids'][$item['game_wallet_id']])
                : (isset($open['transaction_ids'][$item['opponent_transaction_id']])
                    // A closed wallet's complaint disputes its payout, not the round's transaction.
                    || ($item['transaction_ids'] === [] && isset($open['competition_wallet_ids'][$item['competition_wallet_id']])));

            if ($underReview) {
                $states[$key] = self::UNDER_REVIEW;
            } elseif ($local->has($key)) {
                $states[$key] = self::REPORTED;
            }
        }

        return $states;
    }

    public function file(User $user, string $reportKey, string $reason, ?string $description = null): ComplaintResult
    {
        if (! $user->linked_id) {
            return ComplaintResult::rejected('Your account is not linked to the game yet.');
        }

        try {
            $item = PlayedGame::reportables($this->recentGames($user))[$reportKey] ?? null;
        } catch (\Throwable $e) {
            Log::warning("Game dispute: could not load played games for user {$user->id}: {$e->getMessage()}");

            return ComplaintResult::rejected('We could not load your games right now. Please try again shortly.');
        }

        if ($item === null) {
            return ComplaintResult::rejected('This cannot be reported. Only lost games and rounds can be.');
        }

        if ($user->gameDisputes()->where('subject_key', $reportKey)->exists()) {
            return ComplaintResult::alreadyReported();
        }

        if (! PlayedGame::reportWindowOpen($item['report_expires_at'])) {
            return ComplaintResult::rejected(self::windowClosedMessage());
        }

        $limiterKey = 'game-dispute:'.$user->id;

        if (RateLimiter::tooManyAttempts($limiterKey, (int) config('kadi.game_disputes.max_attempts', 3))) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($limiterKey) / 60));

            return ComplaintResult::rejected("You have sent several reports recently. Please try again in {$minutes} minute".($minutes === 1 ? '' : 's').'.');
        }

        $lock = Cache::lock("game-dispute:{$user->id}:{$reportKey}", 30);

        if (! $lock->get()) {
            return ComplaintResult::rejected('This report is already being sent.');
        }

        try {
            RateLimiter::hit($limiterKey, (int) config('kadi.game_disputes.decay_minutes', 10) * 60);

            $description = $description !== null && trim($description) !== '' ? trim($description) : null;
            $payload = self::payload($user, $item, trim($reason), $description);

            $result = KadiApi::fileComplaint($payload, self::idempotencyKey($user, $payload));

            if ($result->succeeded()) {
                $this->record($user, $item, $payload, $result->complaint);
                Cache::forget(self::openComplaintsCacheKey($user));
            }

            return $result;
        } finally {
            $lock->release();
        }
    }

    /**
     * Set this player's still-pending `kadi.game_level_pending` rows for the game to `disputed`.
     * Never throws; returns the number of rows changed.
     */
    public function markPendingDisputed(User $user, ?string $gameId): int
    {
        if (! $gameId || ! $user->linked_id) {
            return 0;
        }

        try {
            return DB::connection('kadi')->table('game_level_pending')
                ->where('game_id', $gameId)
                ->where('account_id', $user->linked_id)
                ->where('status', 'pending')
                ->update(['status' => 'disputed']);
        } catch (\Throwable $e) {
            Log::error("Game dispute: could not mark game_level_pending as disputed for user {$user->id}: {$e->getMessage()}");

            return 0;
        }
    }

    public static function windowClosedMessage(): string
    {
        return 'Games can only be reported within '.PlayedGame::reportWindowLabel().' of playing. This one can no longer be reported.';
    }

    public static function cacheKey(User $user): string
    {
        return 'kadi.played.'.$user->id;
    }

    public static function openComplaintsCacheKey(User $user): string
    {
        return 'kadi.complaints.open.'.$user->id;
    }

    /**
     * The complaint body: exactly one of game_wallet_id / competition_wallet_id.
     */
    public static function payload(User $user, array $item, string $reason, ?string $description): array
    {
        $payload = ['customer_id' => (int) $user->linked_id];

        if ($item['kind'] === PlayedGame::GAME) {
            $payload['game_wallet_id'] = $item['game_wallet_id'];
        } else {
            $payload['competition_wallet_id'] = $item['competition_wallet_id'];

            if ($item['transaction_ids'] !== []) {
                $payload['transaction_ids'] = $item['transaction_ids'];
            }
        }

        $payload['reason'] = $reason;

        if ($description !== null) {
            $payload['description'] = $description;
        }

        return $payload;
    }

    /**
     * Derived from the whole body: a double submit or a retry after a timeout sends the same key (KadiApi
     * replays the first response), and any change to the form gives a new one, so KadiApi never sees
     * a key reused with a different body. At most 64 characters, as KadiApi requires.
     */
    public static function idempotencyKey(User $user, array $payload): string
    {
        return 'dispute-'.$user->linked_id.'-'.substr(hash('sha256', json_encode($payload)), 0, 40);
    }

    /**
     * Ids covered by the player's open KadiApi complaints. Empty when KadiApi cannot be read: the page
     * then falls back to our own records.
     *
     * @return array{game_wallet_ids: array<int, true>, competition_wallet_ids: array<int, true>, transaction_ids: array<int, true>}
     */
    private function openComplaints(User $user): array
    {
        $empty = ['game_wallet_ids' => [], 'competition_wallet_ids' => [], 'transaction_ids' => []];

        if (! $user->linked_id) {
            return $empty;
        }

        try {
            return Cache::remember(self::openComplaintsCacheKey($user), self::OPEN_COMPLAINTS_SECONDS, function () use ($user, $empty) {
                $ids = $empty;

                foreach (KadiApi::getOpenComplaints((int) $user->linked_id) as $complaint) {
                    if (is_numeric($complaint['game_wallet_id'] ?? null)) {
                        $ids['game_wallet_ids'][(int) $complaint['game_wallet_id']] = true;
                    }

                    if (is_numeric($complaint['competition_wallet_id'] ?? null)) {
                        $ids['competition_wallet_ids'][(int) $complaint['competition_wallet_id']] = true;
                    }

                    foreach (is_array($complaint['disputed_transactions'] ?? null) ? $complaint['disputed_transactions'] : [] as $transaction) {
                        if (is_numeric($transaction['transaction_id'] ?? null)) {
                            $ids['transaction_ids'][(int) $transaction['transaction_id']] = true;
                        }
                    }
                }

                return $ids;
            });
        } catch (\Throwable $e) {
            Log::warning("Game dispute: could not load open complaints for user {$user->id}: {$e->getMessage()}");

            return $empty;
        }
    }

    private function record(User $user, array $item, array $payload, array $complaint): void
    {
        $dispute = GameDispute::firstOrCreate(
            ['user_id' => $user->id, 'subject_key' => $item['key']],
            [
                'kind' => $item['kind'],
                'game_wallet_id' => $item['game_wallet_id'],
                'competition_wallet_id' => $item['competition_wallet_id'],
                'transaction_id' => $item['transaction_id'],
                'opponent_transaction_id' => $item['opponent_transaction_id'],
                'game_id' => $item['game_id'],
                'competition_id' => $item['competition_id'],
                'kadi_complaint_id' => is_numeric($complaint['id'] ?? null) ? (int) $complaint['id'] : null,
                'complaint_uuid' => is_string($complaint['complaint_id'] ?? null) ? substr($complaint['complaint_id'], 0, 64) : null,
                'status' => is_string($complaint['status'] ?? null) ? substr($complaint['status'], 0, 30) : 'pending_dispute',
                'reason' => $payload['reason'],
                'description' => $payload['description'] ?? null,
            ],
        );

        // Single games match game_level_pending on game_id; competition rounds on the competition_id.
        $marked = $this->markPendingDisputed($user, $item['game_id'] ?? $item['competition_id']);

        Log::info("Game dispute filed: user {$user->id}, {$item['key']}, complaint ".($dispute->kadi_complaint_id ?? '?').", {$marked} pending row(s) marked disputed");
    }
}
