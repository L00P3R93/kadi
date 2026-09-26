<?php

use App\Jobs\CreatePromoJackpotWallet;
use App\Jobs\ReportCustomerVerified;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

const JP_KADI_API = 'https://api.kadi-kings.co.ke/api/v1/';
const JP_GAME_API = 'https://gameapi.kadi.online/kadi/mpesa_create_jp_wallet.php*';

beforeEach(function () {
    config(['services.kadi_api.game_api_url' => 'https://gameapi.kadi.online/kadi']);
    Notification::fake();
});

function fakeJackpotApis(?array $signupBonus, mixed $gameResponse = null): void
{
    Http::fake([
        JP_KADI_API.'customers/*/verified' => Http::response(['success' => true, 'data' => [
            'referred' => false,
            'signup_bonus' => $signupBonus,
        ]]),
        JP_GAME_API => $gameResponse ?? Http::response(['status' => 'success']),
    ]);
}

function jackpotCalls(): Closure
{
    return fn (Request $request) => str_contains($request->url(), 'mpesa_create_jp_wallet.php');
}

function promoPlayer(array $overrides = []): User
{
    return User::factory()->create(['linked_id' => 5001, 'phone' => '254712345678', 'signup_promo_code' => 'KADI20', ...$overrides]);
}

$bonus = ['id' => 7, 'promotion' => 'signup_bonus', 'promo_code' => 'KADI20', 'net_amount' => 20, 'locked_amount' => 20];

test('a promo player who gets the bonus has the BRONZE jackpot wallet created with their linked id', function () use ($bonus) {
    fakeJackpotApis($bonus);
    $user = promoPlayer();

    ReportCustomerVerified::dispatchSync($user);

    Http::assertSent(fn (Request $request) => jackpotCalls()($request)
        && $request->method() === 'GET'
        && $request['type'] === 'BRONZE'
        && (int) $request['amount'] === 20
        && (int) $request['id'] === 5001);
    expect($user->fresh()->promo_jackpot_wallet_status)->toBe(CreatePromoJackpotWallet::SENT);
});

test('no bonus (code expired, used up, promotion off): no jackpot wallet', function () {
    fakeJackpotApis(null);
    $user = promoPlayer();

    ReportCustomerVerified::dispatchSync($user);

    Http::assertNotSent(jackpotCalls());
    expect($user->fresh()->promo_jackpot_wallet_status)->toBeNull();
});

test('players who signed up without a promo code never get one', function () use ($bonus) {
    fakeJackpotApis($bonus);
    $user = promoPlayer(['signup_promo_code' => null]);

    ReportCustomerVerified::dispatchSync($user);

    Http::assertNotSent(jackpotCalls());
});

test('a failed verified report sends nothing to the game server', function () {
    Http::fake([
        JP_KADI_API.'customers/*/verified' => Http::response([], 503),
        JP_GAME_API => Http::response(['status' => 'success']),
    ]);
    $user = promoPlayer();

    expect(fn () => (new ReportCustomerVerified($user))->handle())->toThrow(Exception::class);

    Http::assertNotSent(jackpotCalls());
});

test('the wallet is only ever requested once', function () use ($bonus) {
    fakeJackpotApis($bonus);
    $user = promoPlayer();

    CreatePromoJackpotWallet::dispatchSync($user);
    CreatePromoJackpotWallet::dispatchSync($user);

    Http::assertSentCount(1);
});

test('a game-server error is recorded and never retried', function (int $status, string $recorded) use ($bonus) {
    fakeJackpotApis($bonus, Http::response(['error' => 'nope'], $status));
    $user = promoPlayer();

    CreatePromoJackpotWallet::dispatchSync($user);
    CreatePromoJackpotWallet::dispatchSync($user);

    Http::assertSentCount(1);
    expect($user->fresh()->promo_jackpot_wallet_status)->toBe($recorded);
})->with([
    '4xx: not created' => [400, CreatePromoJackpotWallet::FAILED],
    '5xx: may exist' => [500, CreatePromoJackpotWallet::UNKNOWN],
]);

test('a timeout is recorded as unknown, because the wallet may exist', function () use ($bonus) {
    fakeJackpotApis($bonus, fn () => throw new ConnectionException('timed out'));
    $user = promoPlayer();

    CreatePromoJackpotWallet::dispatchSync($user);

    expect($user->fresh()->promo_jackpot_wallet_status)->toBe(CreatePromoJackpotWallet::UNKNOWN);
});

test('the job never retries', function () {
    expect((new CreatePromoJackpotWallet(new User))->tries)->toBe(1);
});
