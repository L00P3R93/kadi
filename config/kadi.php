<?php

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
        'key_hashes' => array_values(array_filter(array_map(
            fn (string $hash) => strtolower(trim($hash)),
            explode(',', (string) env('PUSH_API_KEY_HASHES', '')),
        ), fn (string $hash) => preg_match('/^[a-f0-9]{64}$/', $hash) === 1)),

        // Optional extra layer: only these caller IPs may use the API (comma separated PUSH_API_ALLOWED_IPS).
        // Empty means any IP with a valid key. Behind a proxy/CDN, configure trusted proxies first.
        'allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('PUSH_API_ALLOWED_IPS', ''))))),

        'max_recipients' => 100,                                                       // per request
        'requests_per_minute' => (int) env('PUSH_API_REQUESTS_PER_MINUTE', 600),       // per key
        'failed_attempts_per_minute' => 20,                                            // bad keys, per caller IP
        'per_user_per_hour' => (int) env('PUSH_API_PER_USER_PER_HOUR', 30),            // pushes one player may receive

        'default_ttl' => 900,                                                          // seconds a push may wait for an offline device
        'max_ttl' => 86400,
        'allowed_urgencies' => ['very-low', 'low', 'normal'],                          // `high` is reserved for security alerts
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
