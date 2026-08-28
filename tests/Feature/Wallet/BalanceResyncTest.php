<?php

use App\Livewire\WalletBalance;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

const RESYNC_API_BASE = 'https://api.kadi-kings.co.ke/api/v1/';

function resyncUser(): User
{
    return User::factory()->create(['linked_id' => 6161]);
}

function fakeResyncApi(): void
{
    Http::fake([
        RESYNC_API_BASE.'customers/*' => Http::response(['data' => ['balance' => 999.0]]),
        RESYNC_API_BASE.'customers/transactions/*' => Http::response(['transactions' => []]),
    ]);
}

test('resync prefers the wallet_balance cache without any api call', function () {
    fakeResyncApi();
    $user = resyncUser();
    Cache::put("wallet_balance_{$user->id}", 123.45, now()->addMinutes(5));

    Livewire::actingAs($user)->test(WalletBalance::class)
        ->call('resync')
        ->assertSet('balance', 123.45);

    Http::assertNothingSent();
});

test('resync falls back to the customer profile cache when no balance cache exists', function () {
    fakeResyncApi();
    $user = resyncUser();
    Cache::put("kadi.customer.{$user->id}", ['balance' => 777.5], now()->addHour());

    Livewire::actingAs($user)->test(WalletBalance::class)
        ->call('resync')
        ->assertSet('balance', 777.5);

    Http::assertNothingSent();
});

test('resync leaves balance untouched when both caches are cold', function () {
    fakeResyncApi();
    $user = resyncUser();

    Livewire::actingAs($user)->test(WalletBalance::class)
        ->call('resync')
        ->assertSet('balance', null);

    Http::assertNothingSent();
});

test('wallet-refreshed event triggers the resync listener', function () {
    fakeResyncApi();
    $user = resyncUser();
    Cache::put("wallet_balance_{$user->id}", 321.0, now()->addMinutes(5));

    Livewire::actingAs($user)->test(WalletBalance::class)
        ->dispatch('wallet-refreshed')
        ->assertSet('balance', 321.0);
});

test('a sibling instance picks up a refresh performed by another instance (guest layout sync)', function () {
    fakeResyncApi();
    $user = resyncUser();

    // Instance "A" (e.g. desktop widget) performs the real refresh.
    Livewire::actingAs($user)->test(WalletBalance::class)
        ->call('refreshWallet')
        ->assertSet('balance', 999.0);

    expect(Cache::get("wallet_balance_{$user->id}"))->toEqual(999.0);

    // Instance "B" (e.g. mobile dropdown) only re-reads cache via the event.
    Livewire::actingAs($user)->test(WalletBalance::class)
        ->dispatch('wallet-refreshed')
        ->assertSet('balance', 999.0);
});
