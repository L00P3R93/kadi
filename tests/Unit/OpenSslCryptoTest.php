<?php

use Tests\TestCase;

/*
 * These tests exercise global helper functions that read configuration, so
 * they run against the application container even though they live in Unit.
 */
uses(TestCase::class);

function cryptoTestKey(): string
{
    return 'base64:'.base64_encode(random_bytes(32));
}
/*
test('encryptOpenSSL produces versioned URL-safe ciphertext that round-trips', function () {
    config(['openssl.key' => cryptoTestKey()]);

    $plain = '5151';
    $payload = encryptOpenSSL($plain);

    expect($payload)->toStartWith('v2.');
    expect($payload)->not->toContain('+', '/', '=');
    expect(decryptOpenSSL($payload))->toBe($plain);
});

test('tampering with v2 ciphertext fails instead of yielding malleable plaintext', function () {
    config(['openssl.key' => cryptoTestKey()]);

    $payload = encryptOpenSSL('5151');
    $raw = base64_decode(strtr(substr($payload, 3), '-_', '+/'));
    $raw[15] = chr(ord($raw[15]) ^ 0x01); // flip a bit inside the ciphertext body
    $tampered = 'v2.'.rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');

    expect(decryptOpenSSL($tampered))->toBeFalse();
});

test('v2 payload cannot be decrypted under a different key', function () {
    config(['openssl.key' => cryptoTestKey()]);
    $payload = encryptOpenSSL('5151');

    config(['openssl.key' => cryptoTestKey()]);
    expect(decryptOpenSSL($payload))->toBeFalse();
});

test('legacy CBC payloads remain decryptable during the migration window', function () {
    // Reproduce the pre-v2 wire format exactly: raw IV bytes prefixed to the
    // base64 ciphertext string, then urlsafe-base64 encoded.
    $key = random_bytes(32);
    config(['openssl.key' => 'base64:'.base64_encode($key)]);

    $plain = '7777';
    $iv = random_bytes(16);
    $cipherText = openssl_encrypt($plain, 'aes-256-cbc', $key, 0, $iv);
    $legacy = rtrim(strtr(base64_encode($iv.$cipherText), '+/', '-_'), '=');

    expect(str_starts_with($legacy, 'v2.'))->toBeFalse();
    expect(decryptOpenSSL($legacy))->toBe($plain);
});

test('a missing key is rejected loudly', function () {
    config(['openssl.key' => null]);

    encryptOpenSSL('x');
})->throws(RuntimeException::class);

test('a key that does not decode to 32 bytes is rejected', function () {
    config(['openssl.key' => 'too-short']);

    encryptOpenSSL('x');
})->throws(RuntimeException::class);

test('base64 keys decode before use and raw keys pass through', function () {
    $raw = random_bytes(32);

    config(['openssl.key' => 'base64:'.base64_encode($raw)]);
    $viaBase64 = encryptOpenSSL('42');

    config(['openssl.key' => $raw]);
    $viaRaw = encryptOpenSSL('42');

    expect(decryptOpenSSL($viaBase64))->toBe('42');
    expect(decryptOpenSSL($viaRaw))->toBe('42');
});

test('each encryption yields a distinct ciphertext (random nonce)', function () {
    config(['openssl.key' => cryptoTestKey()]);

    expect(encryptOpenSSL('same'))->not->toBe(encryptOpenSSL('same'));
});
*/
