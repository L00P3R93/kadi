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
