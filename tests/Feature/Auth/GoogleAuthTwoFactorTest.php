<?php

use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;

function fakeGoogleUser(string $id, string $email): void
{
    $googleUser = new GoogleUser;
    $googleUser->id = $id;
    $googleUser->email = $email;
    $googleUser->name = 'Test Player';
    $googleUser->avatar = 'https://example.com/avatar.png';

    Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($googleUser);
}

test('google login for a two factor user redirects to the challenge without authenticating', function () {
    Queue::fake();

    $user = User::factory()->withTwoFactor()->create([
        'google_id' => 'google-123',
    ]);

    fakeGoogleUser('google-123', $user->email);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('two-factor.login'));

    $this->assertGuest();
    expect(session('login.id'))->toBe($user->id);
});

test('google login linking by email for a two factor user also requires the challenge', function () {
    Queue::fake();

    $user = User::factory()->withTwoFactor()->create();

    fakeGoogleUser('google-456', $user->email);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('two-factor.login'));

    $this->assertGuest();
    expect(session('login.id'))->toBe($user->id);
    expect($user->fresh()->google_id)->toBe('google-456');
});

test('google login completes normally when two factor is disabled', function () {
    Queue::fake();

    $user = User::factory()->create([
        'google_id' => 'google-789',
    ]);

    fakeGoogleUser('google-789', $user->email);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

test('an unconfirmed abandoned two factor setup does not trigger the challenge', function () {
    Queue::fake();

    $user = User::factory()->create([
        'google_id' => 'google-000',
        'two_factor_secret' => encrypt('abandoned-secret'),
        'two_factor_confirmed_at' => null,
    ]);

    fakeGoogleUser('google-000', $user->email);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});
