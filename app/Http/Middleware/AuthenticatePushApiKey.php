<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates the game server (or any other trusted service) calling the push API.
 *
 * - Fails closed: with no valid key configured every request is refused (503), so a missing
 *   or empty setting can never open the endpoint.
 * - Only SHA-256 hashes of keys are configured; the presented key is hashed and compared in
 *   constant time against every configured hash (two while rotating), without an early exit.
 * - Repeated bad keys from one IP are throttled (429). A valid key is never affected by that.
 * - Always answers in JSON, even if the client sent no Accept header.
 *
 * On success it stores a short, non-secret key identifier in the request attributes
 * (`push_api_key_id`) for rate limiting, idempotency scoping and audit logs.
 *
 * Route parameter `broadcast` (`push.api:broadcast`) checks the separate broadcast key list, so the
 * per-player key cannot send system-wide announcements. Without it the normal key list is used.
 */
class AuthenticatePushApiKey
{
    public function handle(Request $request, Closure $next, string $scope = 'messages'): Response
    {
        $request->headers->set('Accept', 'application/json');

        /** @var list<string> $hashes */
        $hashes = config($scope === 'broadcast' ? 'kadi.push_api.broadcast_key_hashes' : 'kadi.push_api.key_hashes', []);

        if ($hashes === []) {
            return response()->json(['message' => $scope === 'broadcast' ? 'Push broadcasts are not enabled.' : 'The push API is not enabled.'], 503);
        }

        if (! $this->ipAllowed($request)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $keyId = $this->matchKey((string) $request->bearerToken(), $hashes);

        if ($keyId !== null) {
            $request->attributes->set('push_api_key_id', $keyId);

            // A valid key is never blocked by other callers' failures. Behind a proxy or CDN many
            // callers can share one IP, so locking out by IP must not be able to lock out the real server.
            return $next($request);
        }

        // Only failures are throttled. This slows guessing and log spam; real keys are 256-bit.
        $failedKey = 'push-api-failed:'.$request->ip();

        if (RateLimiter::tooManyAttempts($failedKey, (int) config('kadi.push_api.failed_attempts_per_minute', 20))) {
            return response()->json(['message' => 'Too many failed attempts.'], 429)
                ->header('Retry-After', (string) RateLimiter::availableIn($failedKey));
        }

        RateLimiter::hit($failedKey, 60);

        return response()->json(['message' => 'Unauthenticated.'], 401)
            ->header('WWW-Authenticate', 'Bearer');
    }

    /**
     * @param  list<string>  $hashes
     */
    private function matchKey(string $token, array $hashes): ?string
    {
        // Real keys are long (see `push-api:key`); refuse junk without hashing it.
        if (strlen($token) < 32 || strlen($token) > 256) {
            return null;
        }

        $candidate = hash('sha256', $token);
        $matched = null;

        foreach ($hashes as $hash) {
            if (hash_equals($hash, $candidate)) {
                $matched = substr($hash, 0, 8); // first 8 hex chars: enough to tell keys apart, useless to attackers
            }
        }

        return $matched;
    }

    private function ipAllowed(Request $request): bool
    {
        /** @var list<string> $allowed */
        $allowed = config('kadi.push_api.allowed_ips', []);

        if ($allowed === []) {
            return true;
        }

        $caller = @inet_pton((string) $request->ip());

        foreach ($allowed as $ip) {
            $candidate = @inet_pton($ip);

            // Compare the packed binary form so "::1" and "0:0:0:0:0:0:0:1" are the same address.
            if ($caller !== false && $candidate !== false && hash_equals($candidate, $caller)) {
                return true;
            }
        }

        return false;
    }
}
