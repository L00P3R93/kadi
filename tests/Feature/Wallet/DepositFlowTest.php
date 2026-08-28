<?php

use App\Livewire\Wallet\Index;
use App\Models\User;
use Illuminate\Support\Facades\Http;

const DEPOSIT_API_BASE = 'https://api.kadi-kings.co.ke/api/v1/';

function depositFlowUser(): User
{
    return User::factory()->create(['linked_id' => 5151, 'phone' => '254712345678']);
}

function fakeDepositApi(array $overrides = []): void
{
    Http::fake([
        DEPOSIT_API_BASE.'deposits/*' => $overrides['deposit'] ?? Http::response(['status' => 'success']),
        DEPOSIT_API_BASE.'customers/transactions/*' => Http::response(['transactions' => []]),
    ]);
}

test('request deposit below minimum is rejected and stays on the amount step', function () {
    fakeDepositApi();
    $user = depositFlowUser();

    Livewire::actingAs($user)->test(Index::class)
        ->call('openDeposit')
        ->set('depositAmount', '5')
        ->call('requestDeposit')
        ->assertSet('confirmingDeposit', false)
        ->assertSet('depositError', 'Minimum deposit amount is KES 10.');

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/deposits'));
});

test('valid deposit amount reaches the confirmation step without calling the api', function () {
    fakeDepositApi();
    $user = depositFlowUser();

    Livewire::actingAs($user)->test(Index::class)
        ->call('openDeposit')
        ->set('depositAmount', '500')
        ->call('requestDeposit')
        ->assertSet('confirmingDeposit', true)
        ->assertSet('showDepositModal', true);

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/deposits'));
});

test('quick amounts populate the field via set and pass validation', function () {
    fakeDepositApi();
    $user = depositFlowUser();

    Livewire::actingAs($user)->test(Index::class)
        ->call('openDeposit')
        ->set('depositAmount', '5000')
        ->call('requestDeposit')
        ->assertSet('confirmingDeposit', true)
        ->assertSet('depositError', null);
});

test('confirmed deposit sends stk push closes modal and does not dispatch wallet-refreshed', function () {
    fakeDepositApi();
    $user = depositFlowUser();

    Livewire::actingAs($user)->test(Index::class)
        ->call('openDeposit')
        ->set('depositAmount', '500')
        ->call('requestDeposit')
        ->call('confirmDeposit')
        ->assertSet('showDepositModal', false)
        ->assertSet('confirmingDeposit', false)
        ->assertSet('depositAmount', '')
        ->assertNotDispatched('wallet-refreshed')
        ->assertSet('successMessage', fn ($value) => str_contains($value, 'Confirm the M-Pesa prompt'));

    // NB: can't compare the encrypted customer id directly — encryptOpenSSL
    // uses a random IV, so the recorded ciphertext differs per call.
    Http::assertSent(function ($request) {
        return str_starts_with($request->url(), DEPOSIT_API_BASE.'deposits/')
            && $request['amount'] === '500';
    });
});

test('failed deposit returns to the amount step with an inline error', function () {
    fakeDepositApi(['deposit' => Http::response(['status' => 'failed'])]);
    $user = depositFlowUser();

    Livewire::actingAs($user)->test(Index::class)
        ->call('openDeposit')
        ->set('depositAmount', '500')
        ->call('requestDeposit')
        ->call('confirmDeposit')
        ->assertSet('confirmingDeposit', false)
        ->assertSet('showDepositModal', true)
        ->assertSet('depositError', 'Deposit could not be initiated right now. Please try again shortly.');
});
