<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

test('play form posts to the play route', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee(route('play'), false);
});

test('the app account no takes precedence over the cached kadi mirror', function () {
    $user = User::factory()->create(['account_no' => 'app-google-123']);
    Cache::put("kadi.customer.{$user->id}", [
        'balance' => 500,
        'account_no' => 'kadi-mirror-999',
    ], now()->addHour());

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('value="app-google-123"', false)
        ->assertDontSee('kadi-mirror-999', false);
});

test('the cached kadi mirror account no is used as a fallback', function () {
    $user = User::factory()->create(['account_no' => 'kadi-mirror-999']);
    Cache::put("kadi.customer.{$user->id}", [
        'balance' => 500,
        'account_no' => 'kadi-mirror-999',
    ], now()->addHour());

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('value="kadi-mirror-999"', false);
});

test('balance card prefers the fresher wallet balance cache over the stale profile mirror', function () {
    $user = User::factory()->create();

    // Header widget source (fresh, 5-min TTL) vs profile mirror (stale, 1h TTL).
    Cache::put("wallet_balance_{$user->id}", 777.0, now()->addMinutes(5));
    Cache::put("kadi.customer.{$user->id}", ['balance' => 111.0], now()->addHour());

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('KES 777', false);
});

test('external play forms open in a new tab', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $html = $this->get(route('dashboard'))->getContent();

    $playForms = substr_count($html, 'target="_blank"');
    expect($playForms)->toBeGreaterThanOrEqual(2);
});
