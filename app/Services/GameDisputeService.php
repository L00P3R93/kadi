<?php

namespace App\Services;

use App\Facades\KadiApi;
use App\Models\GameDispute;
use App\Models\User;
use App\Support\PlayedGame;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * A player's recent games and the problems they report about them. See docs/game-disputes.md.
 *
 * Reporting:
 *   1. the item must be reportable in the player's OWN recent games, read server-side (a lost single
 *      game, or a lost tournament/jackpot round with a known opponent). Ids from the browser are never
 *      sent to KadiApi as-is;
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

        return Cache::remember(
            $cacheKey,
            now()->addSeconds((int) config('kadi.game_disputes.cache_seconds', 60)),
            fn () => KadiApi::getPlayedGames((int) $user->linked_id),
        );
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
