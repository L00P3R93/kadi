<?php

use App\Livewire\Wallet\Index;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

const WALLET_API_BASE = 'https://api.kadi-kings.co.ke/api/v1/';

function fakeWalletApi(array $overrides = []): void
{
    Http::fake([
        WALLET_API_BASE.'withdrawals/*' => $overrides['withdraw'] ?? Http::response(['status' => 'success']),
        WALLET_API_BASE.'customers/transactions/*' => Http::response(['transactions' => []]),
        WALLET_API_BASE.'customers/*' => $overrides['customer'] ?? Http::response([
            'data' => ['balance' => 4700.0, 'account_no' => 'KK-TEST'],
        ]),
    ]);
}

function withdrawScenario(float $balance = 5000): User
{
    $user = User::factory()->create(['linked_id' => 4242, 'phone' => '254712345678']);

    if ($balance > 0) {
        Cache::put("kadi.customer.{$user->id}", [
            'balance' => $balance,
            'account_no' => 'KK-TEST',
        ], now()->addHour());
    }

    return $user;
}

test('request withdraw below minimum is rejected and never reaches confirmation or api', function () {
    fakeWalletApi();
    $user = withdrawScenario();

    Livewire::actingAs($user)->test(Index::class)
        ->call('openWithdraw')
        ->set('withdrawAmount', '50')
        ->call('requestWithdraw')
        ->assertSet('confirmingWithdraw', false)
        ->assertSet('withdrawError', 'Minimum withdrawal amount is KES 100.');

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/withdrawals'));
});

test('request withdraw above balance shows insufficient error', function () {
    fakeWalletApi();
    $user = withdrawScenario(balance: 200);

    Livewire::actingAs($user)->test(Index::class)
        ->call('openWithdraw')
        ->set('withdrawAmount', '300')
        ->call('requestWithdraw')
        ->assertSet('confirmingWithdraw', false)
        ->assertSet('withdrawError', 'Insufficient balance for this withdrawal.');

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/withdrawals'));
});

test('valid amount reaches the confirmation step without calling the api', function () {
    fakeWalletApi();
    $user = withdrawScenario();

    Livewire::actingAs($user)->test(Index::class)
        ->call('openWithdraw')
        ->set('withdrawAmount', '300')
        ->call('requestWithdraw')
        ->assertSet('confirmingWithdraw', true)
        ->assertSet('showWithdrawModal', true);

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/withdrawals'));
});

test('unlinked account cannot open a withdraw request', function () {
    fakeWalletApi();
    $user = User::factory()->create(['linked_id' => null]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('openWithdraw')
        ->set('withdrawAmount', '300')
        ->call('requestWithdraw')
        ->assertSet('confirmingWithdraw', false)
        ->assertSet('withdrawError', 'Your account is not linked to a vault. Please contact support.');

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/withdrawals'));
});

test('confirmed withdrawal closes modal refreshes caches dispatches event and reloads transactions', function () {
    fakeWalletApi();
    $user = withdrawScenario();

    Livewire::actingAs($user)->test(Index::class)
        ->call('openWithdraw')
        ->set('withdrawAmount', '300')
        ->call('requestWithdraw')
        ->assertSet('confirmingWithdraw', true)
        ->call('confirmWithdraw')
        ->assertSet('showWithdrawModal', false)
        ->assertSet('confirmingWithdraw', false)
        ->assertSet('balance', 4700.0)
        ->assertDispatched('wallet-refreshed')
        ->assertSet('successMessage', fn ($value) => str_contains($value, 'Withdrawal request received'));

    // The shared customer cache was refreshed with the authoritative profile.
    expect(Cache::get("kadi.customer.{$user->id}")['balance'])->toEqual(4700.0);

    Http::assertSent(fn ($request) => str_starts_with($request->url(), WALLET_API_BASE.'withdrawals/'));
});

test('failed withdrawal returns to amount step with error and keeps modal state honest', function () {
    fakeWalletApi(['withdraw' => Http::response(['status' => 'failed'])]);
    $user = withdrawScenario();

    Livewire::actingAs($user)->test(Index::class)
        ->call('openWithdraw')
        ->set('withdrawAmount', '300')
        ->call('requestWithdraw')
        ->call('confirmWithdraw')
        ->assertSet('confirmingWithdraw', false)
        ->assertSet('showWithdrawModal', true)
        ->assertSet('withdrawError', 'Withdrawal could not be processed right now. Please try again shortly.')
        ->assertNotDispatched('wallet-refreshed');
});
