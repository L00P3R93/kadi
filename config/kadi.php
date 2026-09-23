<?php

// SHA-256 hashes (hex) from a comma separated env var. Anything that is not exactly 64 hex characters
// is dropped, so a typo can never become a key that half-works.
$keyHashes = fn (string $name) => array_values(array_filter(array_map(
    fn (string $hash) => strtolower(trim($hash)),
    explode(',', (string) env($name, '')),
), fn (string $hash) => preg_match('/^[a-f0-9]{64}$/', $hash) === 1));

return [

    /*
    |--------------------------------------------------------------------------
    | Legal consent
    |--------------------------------------------------------------------------
    |
    | Users must confirm they are of legal age and accept the current terms.
    | We store only the confirmation (a timestamp) and the terms version they
    | accepted — never a date of birth. Bump `terms_version` whenever the
    | Terms or Privacy Policy change materially to re-prompt users at their
    | next login.
    |
    */

    'min_age' => 18,

    'terms_version' => '2026-09-19',

    /*
    |--------------------------------------------------------------------------
    | Web Push
    |--------------------------------------------------------------------------
    |
    | Browsers hand us a push "endpoint" URL that the server later POSTs to. To
    | stop that being used to reach internal hosts, only endpoints on these push
    | services are accepted (`*` matches any prefix). Add a host here if a browser
    | vendor you need to support uses a different push service.
    |
    */

    'push_api' => [
        /*
         * SHA-256 hashes (hex) of the bearer keys the game server may use, comma separated in
         * PUSH_API_KEY_HASHES. Only hashes live in .env; generate a key and its hash with
         * `php artisan push-api:key`. List two hashes while rotating. Empty (or no valid entry)
         * means the API is disabled and every request is refused.
         */
        'key_hashes' => $keyHashes('PUSH_API_KEY_HASHES'),

        /*
         * Keys allowed to send a system-wide broadcast to EVERY registered device. Deliberately a
         * separate list from `key_hashes`: the per-player key must not be able to notify everyone.
         * Same format and rotation rules. Empty disables broadcasts.
         */
        'broadcast_key_hashes' => $keyHashes('PUSH_API_BROADCAST_KEY_HASHES'),

        // Optional extra layer: only these caller IPs may use the API (comma separated PUSH_API_ALLOWED_IPS).
        // Empty means any IP with a valid key. Behind a proxy/CDN, configure trusted proxies first.
        'allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('PUSH_API_ALLOWED_IPS', ''))))),

        'max_recipients' => 100,                                                       // per request
        'requests_per_minute' => (int) env('PUSH_API_REQUESTS_PER_MINUTE', 600),       // per key
        'failed_attempts_per_minute' => 20,                                            // bad keys, per caller IP
        'per_user_per_hour' => (int) env('PUSH_API_PER_USER_PER_HOUR', 30),            // pushes one player may receive

        'default_ttl' => 900,                                                          // seconds a push may wait for an offline device
        'max_ttl' => 86400,
        // `high` wakes a sleeping phone: without it Android (Doze) and iOS hold `normal` pushes back until the
        // device is next active, so time-sensitive alerts and announcements can arrive late. Allowed by default;
        // set PUSH_API_ALLOW_HIGH_URGENCY=false to refuse it again. Senders still default to `normal`.
        'allowed_urgencies' => array_values(array_filter(
            ['very-low', 'low', 'normal', 'high'],
            fn (string $urgency) => $urgency !== 'high' || filter_var(env('PUSH_API_ALLOW_HIGH_URGENCY', true), FILTER_VALIDATE_BOOLEAN),
        )),

        // System-wide broadcasts (announcements only: maintenance, tournaments, new versions).
        'broadcast_default_ttl' => 3600,                                               // announcements may wait longer than a turn alert
        'broadcast_per_hour' => (int) env('PUSH_API_BROADCAST_PER_HOUR', 6),           // per broadcast key
        'broadcast_per_day' => (int) env('PUSH_API_BROADCAST_PER_DAY', 4),             // across all keys and the command
        'broadcast_chunk_size' => 200,                                                 // devices per queued job

        // Optional dedicated queue name for broadcast jobs (PUSH_API_BROADCAST_QUEUE), so a big send
        // cannot delay per-player pushes. null = the default queue. A worker MUST listen on the name
        // (`queue:work redis --queue=broadcasts`) or broadcasts are never sent. Anything that is not a
        // plain queue name is ignored.
        'broadcast_queue' => preg_match('/^[A-Za-z0-9_\-]{1,64}$/', (string) env('PUSH_API_BROADCAST_QUEUE')) === 1 ? (string) env('PUSH_API_BROADCAST_QUEUE') : null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Wallet webhook (KadiApi -> this site)
    |--------------------------------------------------------------------------
    |
    | KadiApi signs every wallet-balance-changed webhook with one active secret.
    | This side accepts a LIST so both an old and a new secret work during a
    | rotation window. Raw secrets, not hashes: HMAC verification needs the
    | actual shared value, unlike the push API's bearer keys.
    |
    */

    'wallet_webhook' => [
        'secrets' => array_values(array_filter(array_map('trim', explode(',', (string) env('KADI_WALLET_WEBHOOK_SECRETS', ''))))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Game history and disputes
    |--------------------------------------------------------------------------
    |
    | Players see their latest games, tournaments and jackpots (read from KadiApi) and may dispute
    | one. See docs/game-disputes.md. KadiApi's complaints endpoint allows only 30 calls a minute
    | for the WHOLE site, so each player is capped here well below that.
    |
    */

    'game_disputes' => [
        // KadiApi path (under API_URL) for a player's latest 10 of each kind; the encrypted customer id is appended.
        'played_endpoint' => trim((string) env('KADI_PLAYED_GAMES_ENDPOINT', 'customers/played/recent'), '/'),

        'games_per_list' => 10,
        'cache_seconds' => 60,

        // Reports one player may send to KadiApi (accepted or not) per window.
        'max_attempts' => 3,
        'decay_minutes' => 10,
    ],

    'push' => [
        // Environments where any signed-in user may use the "Send test notification" button
        // (admins may use it everywhere). See App\Services\PushTestSender.
        'test_environments' => ['local', 'staging'],

        'allowed_endpoint_hosts' => [
            'fcm.googleapis.com',            // Chrome, Edge, Brave, Opera, Samsung Internet (Android)
            '*.push.services.mozilla.com',   // Firefox
            '*.notify.windows.com',          // Edge on Windows (WNS)
            '*.push.apple.com',              // Safari / iOS / iPadOS home-screen apps
        ],
    ],

];
