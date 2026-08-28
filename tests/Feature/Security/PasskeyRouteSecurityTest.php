<?php

use App\Models\User;
use Laravel\Passkeys\Passkey;

function passkeyFor(User $user): Passkey
{
    return $user->passkeys()->create([
        'name' => 'Test Key',
        'credential_id' => bin2hex(random_bytes(32)),
        'credential' => ['type' => 'public-key'],
    ]);
}

test('passkey management routes reject guests', function () {
    $this->get(route('passkey.registration-options'))->assertRedirect(route('login'));
});

test('passkey management routes require a fresh password confirmation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('passkey.registration-options'))
        ->assertRedirect(route('password.confirm'));
});

test('confirmed users receive registration options as json', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('passkey.registration-options'))
        ->assertOk()
        ->assertJsonStructure(['options']);
});

test('passkey deletion over http enforces ownership', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $passkey = passkeyFor($other);

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->delete(route('passkey.destroy', $passkey))
        ->assertForbidden();

    expect(Passkey::query()->whereKey($passkey->id)->exists())->toBeTrue();

    $this->actingAs($other)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->delete(route('passkey.destroy', $passkey));

    expect(Passkey::query()->whereKey($passkey->id)->exists())->toBeFalse();
});
