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
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'phone' => '0700123456',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('home', absolute: false));

    $this->assertAuthenticated();
});

test('registration never persists a recoverable password outside the users table', function () {
    $this->post(route('register.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '0700123456',
        'password' => 'super-secret-123',
        'password_confirmation' => 'super-secret-123',
    ]);

    $user = User::where('email', 'jane@example.com')->firstOrFail();

    // Audit C-1: no plaintext (or encrypted-plaintext) cache entries may exist.
    expect(Cache::has("user.plain_password.{$user->id}"))->toBeFalse();

    // Only a one-way bcrypt hash may be staged for the kadi account insert.
    $hash = Cache::get("user.kadi_password_hash.{$user->id}");
    expect($hash)->toStartWith('$2y$');
    expect($hash)->not->toBe('super-secret-123');
});
