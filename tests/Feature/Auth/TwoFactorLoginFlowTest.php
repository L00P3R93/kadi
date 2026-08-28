<?php

use App\Models\User;
use Pragmarx\Google2FA\Google2FA;

test('password login for a two factor user completes through the challenge', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.login'));

    $this->assertGuest();
    expect(session('login.id'))->toBe($user->id);

    $code = (new Google2FA)->getCurrentOtp(decrypt($user->fresh()->two_factor_secret));

    $sessionIdBefore = session()->getId();

    $this->post(route('two-factor.login.store'), ['code' => $code])
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
    // Session id must be regenerated on full authentication (fixation guard).
    expect(session()->getId())->not->toBe($sessionIdBefore);
});

test('an invalid challenge code keeps the user unauthenticated', function () {
    $user = User::factory()->withTwoFactor()->create();

    session(['login.id' => $user->id]);

    $this->post(route('two-factor.login.store'), ['code' => '000000'])
        ->assertRedirect();

    $this->assertGuest();
});

test('repeated invalid challenge codes hit the rate limiter', function () {
    $user = User::factory()->withTwoFactor()->create();

    session(['login.id' => $user->id]);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('two-factor.login.store'), ['code' => '000000'])->assertRedirect();
    }

    $this->post(route('two-factor.login.store'), ['code' => '000000'])
        ->assertStatus(429);
});
