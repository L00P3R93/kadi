<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'JohnDoe',
        'email' => 'test@example.com',
        'phone' => '0700123456',
        'password' => 'password',
        'password_confirmation' => 'password',
        'age_confirmed' => '1',
        'terms' => '1',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('home', absolute: false));

    $this->assertAuthenticated();
});

test('registration never persists a recoverable password outside the users table', function () {
    $this->post(route('register.store'), [
        'name' => 'JaneDoe',
        'email' => 'jane@example.com',
        'phone' => '0700123456',
        'password' => 'super-secret-123',
        'password_confirmation' => 'super-secret-123',
        'age_confirmed' => '1',
        'terms' => '1',
    ]);

    $user = User::where('email', 'jane@example.com')->firstOrFail();

    // Audit C-1: no plaintext (or encrypted-plaintext) cache entries may exist.
    expect(Cache::has("user.plain_password.{$user->id}"))->toBeFalse();

    // Only a one-way bcrypt hash may be staged for the kadi account insert.
    $hash = Cache::get("user.kadi_password_hash.{$user->id}");
    expect($hash)->toStartWith('$2y$');
    expect($hash)->not->toBe('super-secret-123');
});

test('registration records age confirmation and terms acceptance', function () {
    $this->post(route('register.store'), [
        'name' => 'JohnDoe',
        'email' => 'consent@example.com',
        'phone' => '0700123456',
        'password' => 'password',
        'password_confirmation' => 'password',
        'age_confirmed' => '1',
        'terms' => '1',
    ]);

    $user = User::where('email', 'consent@example.com')->firstOrFail();

    expect($user->age_confirmed_at)->not->toBeNull()
        ->and($user->terms_accepted_at)->not->toBeNull()
        ->and($user->terms_version)->toBe(config('kadi.terms_version'))
        ->and($user->hasCurrentConsent())->toBeTrue();
});

test('registration is rejected without age confirmation or terms', function (array $omit) {
    $payload = [
        'name' => 'JohnDoe',
        'email' => 'nope@example.com',
        'phone' => '0700123456',
        'password' => 'password',
        'password_confirmation' => 'password',
        'age_confirmed' => '1',
        'terms' => '1',
    ];

    $response = $this->post(route('register.store'), array_diff_key($payload, array_flip($omit)));

    $response->assertSessionHasErrors($omit);
    $this->assertGuest();
    expect(User::where('email', 'nope@example.com')->exists())->toBeFalse();
})->with([
    'no age confirmation' => [['age_confirmed']],
    'no terms' => [['terms']],
    'neither' => [['age_confirmed', 'terms']],
]);
