<?php

use App\Models\User;
use App\Services\KadiApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

const WITHDRAW_BASE = 'https://api.kadi-kings.co.ke/api/v1/';

function withdrawTestUser(): User
{
    return User::factory()->create(['linked_id' => 777]);
}

test('withdraw returns true and posts encrypted id with string amount on success', function () {
    Http::fake([
        WITHDRAW_BASE.'withdrawals/*' => Http::response(['status' => 'success']),
    ]);

    $result = app(KadiApiService::class)->withdraw(withdrawTestUser(), 250.0);

    expect($result)->toBeTrue();

    Http::assertSent(function ($request) {
        return str_starts_with($request->url(), WITHDRAW_BASE.'withdrawals/')
            && $request['amount'] === '250';
    });
});

test('withdraw returns false when api reports failed status', function () {
    Http::fake([
        WITHDRAW_BASE.'withdrawals/*' => Http::response(['status' => 'failed']),
    ]);

    expect(app(KadiApiService::class)->withdraw(withdrawTestUser(), 100))->toBeFalse();
});

test('withdraw returns false on http error response', function () {
    Http::fake([
        WITHDRAW_BASE.'withdrawals/*' => Http::response(['message' => 'server error'], 500),
    ]);

    expect(app(KadiApiService::class)->withdraw(withdrawTestUser(), 100))->toBeFalse();
});

test('withdraw returns false on network exception', function () {
    Http::fake(function () {
        throw new ConnectionException('Connection timed out');
    });

    expect(app(KadiApiService::class)->withdraw(withdrawTestUser(), 100))->toBeFalse();
});
