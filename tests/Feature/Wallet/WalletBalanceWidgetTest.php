<?php

use App\Facades\KadiApi;
use App\Livewire\WalletBalance;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

function warmBalanceCache(User $user, float $balance): void
{
    Cache::put("wallet_balance_{$user->id}", $balance, now()->addMinutes(5));
}

test('renders the formatted balance from the warm cache', function () {
    $user = User::factory()->create();
    warmBalanceCache($user, 1500.0);

    $this->actingAs($user);

    Livewire::test(WalletBalance::class)
        ->assertSet('balance', 1500.0)
        ->assertSet('needsLoad', false)
        ->assertSee('1,500')
        ->assertDontSeeHtml('wire:init="loadBalance"');
});

test('cold caches defer loading via wire init', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(WalletBalance::class)
        ->assertSet('balance', null)
        ->assertSet('needsLoad', true)
        ->assertSeeHtml('wire:init="loadBalance"')
        ->assertSee('—', false);
});

test('loadBalance serves from the customer profile cache without hitting the api', function () {
    $user = User::factory()->create();
    Cache::put("kadi.customer.{$user->id}", ['balance' => 321.5], now()->addHour());

    KadiApi::shouldReceive('getCustomer')->never();

    $this->actingAs($user);

    Livewire::test(WalletBalance::class)
        ->call('loadBalance')
        ->assertSet('balance', 321.5)
        ->assertSet('needsLoad', false);

    expect(Cache::get("wallet_balance_{$user->id}"))->toBe(321.5);
});

test('refreshWallet fetches fresh data and repopulates caches', function () {
    $user = User::factory()->create(['linked_id' => 424242]);
    warmBalanceCache($user, 10.0);
    // Avoid the external kadi DB connectivity check inside doFetch.
    Cache::put("wallet_linked_{$user->id}", true, now()->addHour());

    KadiApi::shouldReceive('getCustomer')
        ->once()
        ->with(424242)
        ->andReturn(['data' => ['balance' => 77.77]]);

    $this->actingAs($user);

    Livewire::test(WalletBalance::class)
        ->call('refreshWallet')
        ->assertSet('balance', 77.77)
        ->assertHasNoErrors();

    expect(Cache::get("wallet_balance_{$user->id}"))->toBe(77.77);
});

test('refreshWallet flags an error for unlinked users', function () {
    $user = User::factory()->create(['linked_id' => null]);

    KadiApi::shouldReceive('getCustomer')->never();

    $this->actingAs($user);

    Livewire::test(WalletBalance::class)
        ->call('refreshWallet')
        ->assertSet('hasError', true)
        ->assertSee('unavailable');
});

test('the widget exposes visible loading affordances for both actions', function () {
    $user = User::factory()->create();
    warmBalanceCache($user, 500.0);

    $this->actingAs($user);

    Livewire::test(WalletBalance::class)
        ->assertSeeHtml('animate-spin')
        ->assertSeeHtml('wire:loading.class.remove="hidden"')
        ->assertSeeHtml('wire:target="loadBalance, refreshWallet"')
        ->assertSeeHtml(':disabled="cooldown"');
});
