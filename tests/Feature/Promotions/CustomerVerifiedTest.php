<?php

use App\Jobs\ProcessVerifiedUser;
use App\Jobs\ReportCustomerVerified;
use App\Jobs\ReportReferralVerified;
use App\Models\User;
use App\Notifications\SignupBonusGranted;
use App\Support\PhoneOtp;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

const VERIFIED_API = 'https://api.kadi-kings.co.ke/api/v1/';

beforeEach(function () {
    config(['services.textsms.api_key' => 'test-key', 'services.textsms.partner_id' => '1']);
});

function signupBonus(array $overrides = []): array
{
    return [
        'id' => 7, 'promotion' => 'signup_bonus', 'promo_code' => 'KADI20',
        'gross_amount' => 21.05, 'excise_amount' => 1.05, 'net_amount' => 20,
        'wagered' => 0, 'locked_amount' => 20, 'unlocked' => false,
        'status' => 'granted', 'granted_at' => '2026-10-02T10:15:00+03:00',
        ...$overrides,
    ];
}

function fakeVerifiedApi(int $status = 200, ?array $body = null): void
{
    Http::fake([
        VERIFIED_API.'customers/*/referral/verified' => Http::response(['success' => true], 200),
        VERIFIED_API.'customers/*/verified' => Http::response($body ?? ['success' => true, 'data' => [
            'verified_at' => '2026-10-02T10:15:00+03:00', 'referred' => false, 'signup_bonus' => null,
        ]], $status),
        VERIFIED_API.'customers' => Http::response(['status' => 'Success', 'customer_id' => 5002], 201),
        'sms.textsms.co.ke/*' => Http::response(['responses' => [['response-code' => 200]]]),
        '*' => Http::response(['data' => []]),
    ]);
}

function verifiedCalls(): Closure
{
    return fn (Request $request) => str_ends_with($request->url(), '/verified') && ! str_ends_with($request->url(), '/referral/verified');
}

function referralVerifiedCalls(): Closure
{
    return fn (Request $request) => str_ends_with($request->url(), '/referral/verified');
}

/** Send a code and read it back from the SMS that was "sent". */
function confirmPhone(User $user): string
{
    app(PhoneOtp::class)->send($user);
    $sms = Http::recorded(fn (Request $request) => str_contains($request->url(), 'textsms'))->last()[0];
    preg_match('/\d{6}/', $sms['message'], $match);

    return app(PhoneOtp::class)->verify($user->fresh(), $match[0]);
}

test('not reported after the email alone', function () {
    fakeVerifiedApi();
    Mail::fake();
    $user = User::factory()->phoneUnverified()->create(['linked_id' => null, 'phone' => '254712345678', 'signup_referral_code' => 'KADI2026']);

    (new ProcessVerifiedUser($user))->handle();

    Http::assertNotSent(verifiedCalls());
});

test('not reported after the phone alone (email unverified)', function () {
    fakeVerifiedApi();
    $user = User::factory()->unverified()->phoneUnverified()->create(['linked_id' => 5001, 'phone' => '254712345678', 'signup_referral_code' => 'KADI2026']);

    expect(confirmPhone($user))->toBe(PhoneOtp::VERIFIED);

    Http::assertNotSent(verifiedCalls());
});

test('reported once both are verified, referred or not, and never with /referral/verified', function (?string $referralCode) {
    fakeVerifiedApi();
    $user = User::factory()->phoneUnverified()->create(['linked_id' => 5001, 'phone' => '254712345678', 'signup_referral_code' => $referralCode]);

    confirmPhone($user);

    Http::assertSentCount(2); // the SMS and one verified report
    Http::assertSent(fn (Request $request) => verifiedCalls()($request) && $request->method() === 'POST');
    Http::assertNotSent(referralVerifiedCalls());
    expect($user->fresh()->verified_reported_at)->not->toBeNull();
})->with(['referred' => 'KADI2026', 'not referred' => null]);

test('reported even with referrals switched off', function () {
    config(['kadi.referrals.enabled' => false]);
    fakeVerifiedApi();
    $user = User::factory()->phoneUnverified()->create(['linked_id' => 5001, 'phone' => '254712345678']);

    confirmPhone($user);

    Http::assertSent(verifiedCalls());
});

test('reported once both are verified: phone first, then the account is linked', function () {
    fakeVerifiedApi();
    Mail::fake();
    $user = User::factory()->create(['linked_id' => null, 'phone' => '254712345678']);

    (new ProcessVerifiedUser($user))->handle();

    Http::assertSent(verifiedCalls());
    Http::assertNotSent(referralVerifiedCalls());
    expect($user->fresh()->verified_reported_at)->not->toBeNull();
});

test('a granted signup bonus is announced and the wallet caches are dropped', function () {
    fakeVerifiedApi(200, ['success' => true, 'data' => ['referred' => false, 'signup_bonus' => signupBonus()]]);
    Notification::fake();
    $user = User::factory()->create(['linked_id' => 5001, 'phone' => '254712345678', 'signup_promo_code' => 'KADI20']);
    Cache::put("wallet_balance_{$user->id}", 0, 60);
    Cache::put("kadi.promotions.{$user->id}", ['locked_amount' => 0, 'items' => []], 60);

    ReportCustomerVerified::dispatchSync($user);

    Notification::assertSentTo($user, SignupBonusGranted::class, fn ($n) => $n->amount === '20'
        && str_contains($n->toArray($user)['change'], 'KES 20'));
    expect(Cache::has("wallet_balance_{$user->id}"))->toBeFalse()
        ->and(Cache::has("kadi.promotions.{$user->id}"))->toBeFalse();
});

test('signup_bonus null is not an error and announces nothing', function () {
    fakeVerifiedApi();
    Notification::fake();
    $user = User::factory()->create(['linked_id' => 5001, 'phone' => '254712345678', 'signup_promo_code' => 'KADI20']);

    ReportCustomerVerified::dispatchSync($user);

    Notification::assertNothingSent();
    expect($user->fresh()->verified_reported_at)->not->toBeNull();
});

test('a player already reported is not reported again', function () {
    fakeVerifiedApi();
    $user = User::factory()->create(['linked_id' => 5001, 'phone' => '254712345678']);
    $user->forceFill(['verified_reported_at' => now()])->save();

    ReportCustomerVerified::dispatchSync($user);

    Http::assertNothingSent();
});

test('jobs queued before the deploy (ReportReferralVerified) use the new endpoint', function () {
    fakeVerifiedApi();
    $user = User::factory()->create(['linked_id' => 5001, 'phone' => '254712345678', 'signup_referral_code' => 'KADI2026']);

    ReportReferralVerified::dispatchSync($user);

    Http::assertSent(verifiedCalls());
    Http::assertNotSent(referralVerifiedCalls());
});

test('a 5xx is thrown so the queue retries it, and nothing is stamped', function () {
    fakeVerifiedApi(503, []);
    $user = User::factory()->create(['linked_id' => 5001, 'phone' => '254712345678']);

    expect(fn () => (new ReportCustomerVerified($user))->handle())->toThrow(RequestException::class);
    expect($user->fresh()->verified_reported_at)->toBeNull();
});

test('a 404 is not retried', function () {
    fakeVerifiedApi(404, ['success' => false]);
    $user = User::factory()->create(['linked_id' => 5001, 'phone' => '254712345678']);

    (new ReportCustomerVerified($user))->handle();

    expect($user->fresh()->verified_reported_at)->toBeNull();
});
