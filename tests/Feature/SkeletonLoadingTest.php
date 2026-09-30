<?php

use App\Livewire\Games\History;
use App\Livewire\Referrals\Index as ReferralsIndex;
use App\Livewire\Wallet\Index as WalletIndex;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

const SKELETON_API = 'https://api.kadi-kings.co.ke/api/v1/';

test('the wallet page renders without calling KadiApi and shows skeletons until loadPage runs', function () {
    Http::fake();
    $user = User::factory()->create(['linked_id' => 5150]);

    Livewire::actingAs($user)->test(WalletIndex::class)
        ->assertSet('loaded', false)
        ->assertSeeHtml('wire:init="loadPage"')
        ->assertSeeHtml('data-test="wallet-balance-skeleton"')
        ->assertSee('Loading transactions…')
        ->assertDontSee('KES 0')
        ->assertDontSee('No transactions yet.');

    Http::assertNothingSent();
});

test('loadPage fetches the profile, transactions and locked bonus and replaces the skeletons', function () {
    Http::fake([
        SKELETON_API.'customers/transactions/*' => Http::response(['transactions' => [
            ['payment_type' => 'Deposit', 'amount' => 250, 'status' => 2, 'payment_ref' => 'REF-SKEL-1', 'created_at' => now()->toIso8601String()],
        ]]),
        SKELETON_API.'customers/*/promotions' => Http::response(['success' => true, 'data' => ['locked_amount' => 0, 'items' => []]]),
        SKELETON_API.'customers/*' => Http::response(['data' => ['balance' => 850.0, 'account_no' => 'KK-SKEL']]),
    ]);
    $user = User::factory()->create(['linked_id' => 5151]);

    Livewire::actingAs($user)->test(WalletIndex::class)
        ->call('loadPage')
        ->assertSet('loaded', true)
        ->assertSet('needsLoad', false)
        ->assertSee('KES 850')
        ->assertSee('KK-SKEL')
        ->assertSee('REF-SKEL-1')
        ->assertDontSeeHtml('data-test="wallet-balance-skeleton"');
});

test('a warm wallet cache shows the balance at once and only the transactions wait', function () {
    Http::fake();
    $user = User::factory()->create(['linked_id' => 5152]);
    Cache::put("kadi.customer.{$user->id}", ['balance' => 64.0, 'account_no' => 'KK-WARM'], now()->addHour());

    Livewire::actingAs($user)->test(WalletIndex::class)
        ->assertSee('KES 64')
        ->assertSee('KK-WARM')
        ->assertDontSeeHtml('data-test="wallet-balance-skeleton"')
        ->assertSee('Loading transactions…');

    Http::assertNothingSent();
});

test('game history shows skeleton rows before its games load', function () {
    Http::fake();

    Livewire::actingAs(User::factory()->create(['linked_id' => 5153]))->test(History::class)
        ->assertSeeHtml('data-test="skeleton"')
        ->assertSee('Loading your games…')
        ->assertDontSee('Loading your games...');

    Http::assertNothingSent();
});

test('referrals shows block skeletons before it loads', function () {
    Http::fake();

    Livewire::actingAs(User::factory()->create(['linked_id' => 5154]))->test(ReferralsIndex::class)
        ->assertSeeHtml('data-test="skeleton"')
        ->assertSee('Loading your referrals…')
        ->assertDontSee('Your invite');

    Http::assertNothingSent();
});
