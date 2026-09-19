<?php

namespace App\Support;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Makes a push API call safe to retry. The first call runs and its response is remembered for 24
 * hours; the same Idempotency-Key with the same request returns that response again without
 * running anything, and the same key with a different request is refused.
 *
 * Keys are scoped by `$scope` (which API key, and which endpoint) so two callers never collide.
 */
class PushApiIdempotency
{
    private const TTL_HOURS = 24;

    /**
     * @param  array<string, mixed>  $data  the validated request, used to detect a reused key
     * @param  Closure(): (array<string, mixed>|JsonResponse)  $run  performs the work and returns the response body (or its own error response, which is never remembered)
     */
    public function run(string $scope, string $key, array $data, Closure $run): JsonResponse
    {
        $cacheKey = "push-api:idempotency:{$scope}:{$key}";
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

            $result = $run();

            // A callback may answer with its own error response (for example a limit): never remember those.
            if ($result instanceof JsonResponse) {
                return $result;
            }

            Cache::put($cacheKey, ['fingerprint' => $fingerprint, 'body' => $result], now()->addHours(self::TTL_HOURS));

            return response()->json($result, 202);
        } finally {
            $lock->release();
        }
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
        if (isset($data['recipients'])) {
            $data['recipients'] = collect($data['recipients'])->map(fn ($id) => (string) $id)->unique()->sort()->values()->all();
        }

        ksort($data);

        return hash('sha256', (string) json_encode($data));
    }
}
