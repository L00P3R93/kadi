<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendPushApiRequest;
use App\Models\User;
use App\Notifications\PushMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * POST /api/v1/push-notifications
 *
 * Lets a trusted server (the game server) ask Kadi to send a web push to players. Kadi owns
 * delivery: it resolves the players, skips anyone who has no registered device, applies a
 * per-player hourly cap, and queues one push per player. The response says what happened
 * to every recipient it could not deliver to.
 */
class PushApiController extends Controller
{
    private const IDEMPOTENCY_TTL_HOURS = 24;

    public function __invoke(SendPushApiRequest $request): JsonResponse
    {
        $keyId = (string) $request->attributes->get('push_api_key_id');
        $data = $request->validated();
        $idempotencyKey = $data['idempotency_key'] ?? null;
        unset($data['idempotency_key']);

        if ($idempotencyKey === null) {
            return response()->json($this->deliver($keyId, $data), 202);
        }

        // Retried requests (timeouts, network blips) must never send a second push.
        $cacheKey = "push-api:idempotency:{$keyId}:{$idempotencyKey}";
        $fingerprint = $this->fingerprint($data);

        if ($stored = Cache::get($cacheKey)) {
            return $this->replay($stored, $fingerprint);
        }

        $lock = Cache::lock($cacheKey.':lock', 30);

        if (! $lock->get()) {
            return response()->json(['message' => 'A request with this Idempotency-Key is still being processed.'], 409);
        }

        try {
            // Someone may have finished between our first check and taking the lock.
            if ($stored = Cache::get($cacheKey)) {
                return $this->replay($stored, $fingerprint);
            }

            $body = $this->deliver($keyId, $data);

            Cache::put($cacheKey, ['fingerprint' => $fingerprint, 'body' => $body], now()->addHours(self::IDEMPOTENCY_TTL_HOURS));

            return response()->json($body, 202);
        } finally {
            $lock->release();
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function deliver(string $keyId, array $data): array
    {
        $by = $data['by'] ?? 'linked_id';
        $normalize = fn (mixed $value) => $by === 'linked_id' ? (int) $value : mb_strtolower((string) $value);

        $requested = collect($data['recipients'])->map($normalize)->unique()->values();

        // Account numbers are matched without regard to case, the same on every database (MySQL is
        // case-insensitive by default, SQLite is not). Looking up the common spellings keeps the
        // unique index usable, unlike wrapping the column in lower().
        $lookup = $by === 'linked_id'
            ? $requested->all()
            : collect($data['recipients'])->flatMap(fn ($id) => [(string) $id, strtoupper((string) $id), strtolower((string) $id)])->unique()->values()->all();

        // One query for everyone, including whether they have any registered device.
        $users = User::query()
            ->whereIn($by, $lookup)
            ->withExists('pushSubscriptions as has_device')
            ->get()
            ->groupBy(fn (User $user) => $normalize($user->{$by}));

        $unknown = $requested->reject(fn ($id) => $users->has($id))->values();

        $counts = ['no_device' => 0, 'throttled' => 0, 'ambiguous' => 0];
        $eligible = new Collection;
        $perHour = (int) config('kadi.push_api.per_user_per_hour');

        foreach ($users as $group) {
            // users.linked_id is not unique in the schema. If one id ever maps to several accounts
            // we refuse to guess who is meant, rather than notify the wrong person.
            if ($group->count() > 1) {
                $counts['ambiguous']++;

                continue;
            }

            /** @var User $user */
            $user = $group->first();

            if (! $user->has_device) {
                $counts['no_device']++;

                continue;
            }

            // A misbehaving game server must not be able to spam one player.
            $limitKey = 'push-api:user:'.$user->id;

            if (RateLimiter::tooManyAttempts($limitKey, $perHour)) {
                $counts['throttled']++;

                continue;
            }

            RateLimiter::hit($limitKey, 3600);
            $eligible->push($user);
        }

        if ($eligible->isNotEmpty()) {
            Notification::send($eligible, new PushMessageNotification(
                title: $data['title'],
                body: $data['body'],
                url: $data['url'] ?? '/',
                tag: $data['tag'] ?? null,
                ttl: (int) ($data['ttl'] ?? config('kadi.push_api.default_ttl')),
                urgency: $data['urgency'] ?? 'normal',
            ));
        }

        $id = (string) Str::uuid();

        // An audit trail that holds no message text and no player identifiers.
        Log::info('Push API request', [
            'request_id' => $id,
            'key' => $keyId,
            'requested' => $requested->count(),
            'queued' => $eligible->count(),
            'unknown' => $unknown->count(),
            ...$counts,
        ]);

        return [
            'status' => 'accepted',
            'id' => $id,
            'requested' => $requested->count(),
            'queued' => $eligible->count(),
            'skipped' => [
                'unknown' => $unknown->take(100)->all(),
                ...$counts,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $stored
     */
    private function replay(array $stored, string $fingerprint): JsonResponse
    {
        if (! hash_equals($stored['fingerprint'], $fingerprint)) {
            return response()->json(['message' => 'This Idempotency-Key was already used with a different request.'], 422);
        }

        return response()->json($stored['body'], 202)->header('Idempotent-Replay', 'true');
    }

    /**
     * Stable across recipient order and duplicate ids, so an identical retry always matches.
     *
     * @param  array<string, mixed>  $data
     */
    private function fingerprint(array $data): string
    {
        $data['recipients'] = collect($data['recipients'])->map(fn ($id) => (string) $id)->unique()->sort()->values()->all();
        ksort($data);

        return hash('sha256', (string) json_encode($data));
    }
}
