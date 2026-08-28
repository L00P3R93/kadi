<?php

use Laravel\Passkeys\Passkey;
use Laravel\Passkeys\Support\Aaguids;

test('an unknown aaguid yields no authenticator label', function () {
    $passkey = new Passkey([
        'credential' => ['aaguid' => Aaguids::unknown()],
    ]);

    expect($passkey->authenticator)->toBeNull();
});

test('a missing aaguid yields no authenticator label', function () {
    $passkey = new Passkey(['credential' => ['type' => 'public-key']]);

    expect($passkey->authenticator)->toBeNull();
});
