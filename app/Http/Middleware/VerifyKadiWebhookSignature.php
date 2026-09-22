<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates KadiApi's wallet-balance webhook.
 *
 * - Fails closed: with no secret configured every request is refused (503), so a missing setting
 *   can never leave the endpoint open.
 * - Signature = hash_hmac('sha256', "{timestamp}.{rawBody}", $secret), checked against the RAW
 *   request body (never a re-encoded/re-serialized version of it) and compared in constant time
 *   against every configured secret, without an early exit, so timing can't reveal which secret
 *   almost matched.
 * - X-Kadi-Timestamp must be within 5 minutes of now (replay protection).
 * - Any verification failure is 401, never 5xx — KadiApi retries 5xx/429 for up to ~43 minutes,
 *   and a bad signature will never succeed on retry.
 */
class VerifyKadiWebhookSignature
{
    private const MAX_SKEW_SECONDS = 300;

    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        /** @var list<string> $secrets */
        $secrets = config('kadi.wallet_webhook.secrets', []);

        if ($secrets === []) {
            return response()->json(['message' => 'The wallet webhook is not enabled.'], 503);
        }

        $timestamp = $request->header('X-Kadi-Timestamp');

        if (! is_string($timestamp) || ! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > self::MAX_SKEW_SECONDS) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $signature = (string) $request->header('X-Kadi-Signature');
        $matched = false;

        foreach ($secrets as $secret) {
            $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

            if (hash_equals($expected, $signature)) {
                $matched = true;
            }
        }

        if (! $matched) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
