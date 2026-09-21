<?php

use App\Models\User;
use App\Services\KadiApiService;
use App\Services\WithdrawResult;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

const WITHDRAW_BASE = 'https://api.kadi-kings.co.ke/api/v1/';

function withdrawTestUser(): User
{
    return User::factory()->create(['linked_id' => 777]);
}

test('withdraw succeeds, posting to the singular withdraw endpoint with the amount and key', function () {
    Http::fake([
        WITHDRAW_BASE.'withdraw/*' => Http::response(['status' => 'success', 'ledger_entry_id' => 'LE-000123'], 201),
    ]);

    $result = app(KadiApiService::class)->withdraw(withdrawTestUser(), 250.0, 'key-1');

    expect($result->succeeded())->toBeTrue()
        ->and($result->ledgerEntryId)->toBe('LE-000123');

    Http::assertSent(function ($request) {
        return str_starts_with($request->url(), WITHDRAW_BASE.'withdraw/')
            && ! str_contains($request->url(), 'withdrawals')
            && $request['amount'] == 250
            && $request->header('Idempotency-Key') === ['key-1'];
    });
});

test('withdraw maps definitive API errors to rejected results', function (int $status, array $body, string $contains) {
    Http::fake([WITHDRAW_BASE.'withdraw/*' => Http::response($body, $status)]);

    $result = app(KadiApiService::class)->withdraw(withdrawTestUser(), 100);

    expect($result->outcome)->toBe(WithdrawResult::REJECTED)
        ->and($result->message)->toContain($contains);
})->with([
    'insufficient balance' => [400, ['status' => 'Insufficient wallet balance for withdrawal'], 'Insufficient balance'],
    'no phone' => [400, ['status' => 'Customer phone number not found'], 'phone number'],
    'not found' => [404, ['status' => 'Wallet not found'], 'wallet'],
    'validation' => [422, ['status' => ['amount' => ['min']]], 'not valid'],
    'rate limited' => [429, ['message' => 'Too Many Attempts.'], 'wait a minute'],
    'b2c failed' => [500, ['status' => 'M-Pesa B2C failed'], 'not charged'],
]);

test('withdraw reports unknown on a gateway error', function () {
    Http::fake([WITHDRAW_BASE.'withdraw/*' => Http::response('Bad gateway', 502)]);

    expect(app(KadiApiService::class)->withdraw(withdrawTestUser(), 100)->isUnknown())->toBeTrue();
});

test('withdraw reports unknown on a network timeout', function () {
    Http::fake(function () {
        throw new ConnectionException('Connection timed out');
    });

    expect(app(KadiApiService::class)->withdraw(withdrawTestUser(), 100)->isUnknown())->toBeTrue();
});
