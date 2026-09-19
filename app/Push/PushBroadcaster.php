<?php

namespace App\Push;

use App\Jobs\SendPushBroadcast;
use App\Models\PushBroadcast;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use NotificationChannels\WebPush\PushSubscription;

/**
 * Creates, limits and cancels system-wide broadcasts. Used by both the API and `push:broadcast`,
 * so the frequency caps apply the same way to every entry point.
 */
class PushBroadcaster
{
    /**
     * How many devices a broadcast would reach right now. Sends nothing.
     */
    public function audienceSize(): int
    {
        return PushSubscription::query()->count();
    }

    /**
     * @param  array{title: string, body: string, url?: ?string, tag?: ?string, urgency?: ?string, ttl?: ?int}  $data
     *
     * @throws BroadcastLimitExceeded when a frequency cap is reached (unless $enforceLimits is false)
     */
    public function create(array $data, string $source, ?string $keyId = null, bool $enforceLimits = true): PushBroadcast
    {
        // Serialise creation so two simultaneous requests cannot both slip under a cap.
        $lock = Cache::lock('push-broadcast:create', 10);
        $lock->block(5);

        try {
            if ($enforceLimits) {
                $this->assertWithinLimits($keyId);
            }

            $broadcast = PushBroadcast::create([
                'source' => $source,
                'key_id' => $keyId,
                'title' => $data['title'],
                'body' => $data['body'],
                'url' => $data['url'] ?? '/',
                'tag' => $data['tag'] ?? null,
                'urgency' => $data['urgency'] ?? 'normal',
                'ttl' => (int) ($data['ttl'] ?? config('kadi.push_api.broadcast_default_ttl')),
                'status' => PushBroadcast::QUEUED,
            ]);
        } finally {
            $lock->release();
        }

        // Counts and ids only: the text is in the table, and nothing here identifies a player.
        Log::info('Push broadcast requested', ['broadcast' => $broadcast->id, 'source' => $source, 'key' => $keyId]);

        SendPushBroadcast::dispatch($broadcast->id)->onQueue(config('kadi.push_api.broadcast_queue'));

        return $broadcast;
    }

    /**
     * Stops a broadcast that has not finished. Chunks already delivered cannot be recalled.
     */
    public function cancel(PushBroadcast $broadcast): bool
    {
        return (bool) PushBroadcast::whereKey($broadcast->id)
            ->whereIn('status', [PushBroadcast::QUEUED, PushBroadcast::SENDING])
            ->update(['status' => PushBroadcast::CANCELLED, 'finished_at' => now()]);
    }

    private function assertWithinLimits(?string $keyId): void
    {
        $perDay = (int) config('kadi.push_api.broadcast_per_day');
        $perHour = (int) config('kadi.push_api.broadcast_per_hour');

        $day = PushBroadcast::where('created_at', '>=', now()->subDay())->orderBy('created_at')->pluck('created_at');

        if ($perDay > 0 && $day->count() >= $perDay) {
            throw new BroadcastLimitExceeded(
                "At most {$perDay} system-wide broadcasts are allowed per 24 hours.",
                $this->secondsUntilFree($day->all(), 86400),
            );
        }

        if ($keyId !== null && $perHour > 0) {
            $hour = PushBroadcast::where('key_id', $keyId)->where('created_at', '>=', now()->subHour())->orderBy('created_at')->pluck('created_at');

            if ($hour->count() >= $perHour) {
                throw new BroadcastLimitExceeded(
                    "At most {$perHour} broadcasts per hour are allowed for this key.",
                    $this->secondsUntilFree($hour->all(), 3600),
                );
            }
        }
    }

    /**
     * Seconds until the oldest counted broadcast leaves the window.
     *
     * @param  array<int, Carbon>  $times  oldest first
     */
    private function secondsUntilFree(array $times, int $window): int
    {
        $oldest = $times[0] ?? now();

        return max(1, (int) ceil($window - $oldest->diffInSeconds(now(), true)));
    }
}
