<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Guards the push endpoint URLs clients register.
 *
 * The server later POSTs to whatever endpoint is stored, so accepting arbitrary
 * URLs would let an authenticated user point us at an internal host (blind SSRF).
 * Only HTTPS on the default port, hosted by a known push service, is accepted.
 * The allowed hosts are configurable in config/kadi.php (push.allowed_endpoint_hosts).
 */
class PushEndpoint
{
    public static function isAllowed(mixed $url): bool
    {
        if (! is_string($url)) {
            return false;
        }

        $parts = parse_url($url);

        if ($parts === false || ($parts['scheme'] ?? null) !== 'https') {
            return false;
        }

        // No credentials, and only the default HTTPS port.
        if (isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        if (isset($parts['port']) && $parts['port'] !== 443) {
            return false;
        }

        $host = strtolower(rtrim($parts['host'] ?? '', '.'));

        if ($host === '') {
            return false;
        }

        /** @var list<string> $allowed */
        $allowed = config('kadi.push.allowed_endpoint_hosts', []);

        return Str::is($allowed, $host);
    }
}
