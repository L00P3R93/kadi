<?php

return [

    /*
    |--------------------------------------------------------------------------
    | KadiApi Shared ID-Encryption Key
    |--------------------------------------------------------------------------
    |
    | Used by encryptOpenSSL()/decryptOpenSSL() to encrypt customer IDs on
    | URLs sent to the KadiApi service (deposits, withdrawals, transactions).
    |
    | REQUIRED in every non-local environment — the application refuses to
    | boot without it. Generate with:
    |
    |   php -r "echo 'base64:'.base64_encode(random_bytes(32));"
    |
    | This key is shared with the KadiApi service and must be rotated there
    | simultaneously. It must NEVER have a committed fallback value.
    |
    */

    'key' => env('OPENSSL_KEY'),
    'method' => env('OPENSSL_METHOD', 'AES-256-CBC'), // default fallback

    /*
    | Authenticated cipher used for the "v2." wire format.
    */
    'cipher' => 'aes-256-gcm',
];
