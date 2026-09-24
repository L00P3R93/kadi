<?php

use App\Jobs\ProcessVerifiedUser;
use App\Jobs\ReportReferralVerified;
use App\Models\User;
use App\Support\PhoneOtp;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

const REF_VERIFY_API = 'https://api.kadi-kings.co.ke/api/v1/';

beforeEach(function () {
    config(['services.textsms.api_key' => 'test-key', 'services.textsms.partner_id' => '1']);
});

function fakeVerifiedApi(int $status = 200, array $body = ['success' => true, 'referred' => true]): void
{
    Http::fake([
        REF_VERIFY_API.'customers/*/referral/verified' => Http::response($body, $status),
        REF_VERIFY_API.'customers' => Http::response(['customer_id' => 5002], 201),
        'sms.textsms.co.ke/*' => Http::response(['responses' => [['response-code' => 200]]]),
        '*' => Http::response(['data' => []]),
    ]);
}

function verifiedCalls(): Closure
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

test('reported once both are verified: email first, then phone', function () {
    fakeVerifiedApi();
    $user = User::factory()->phoneUnverified()->create(['linked_id' => 5001, 'phone' => '254712345678', 'signup_referral_code' => 'KADI2026']);

    confirmPhone($user);

    Http::assertSentCount(2); // the SMS and one verified report
    Http::assertSent(fn (Request $request) => verifiedCalls()($request) && $request->method() === 'POST');
    expect($user->fresh()->referral_verified_reported_at)->not->toBeNull();
});

test('reported once both are verified: phone first, then the account is linked', function () {
    fakeVerifiedApi();
    Mail::fake();
    // Phone confirmed, e-mail verified, not linked yet: the report waits for ProcessVerifiedUser.
    $user = User::factory()->create(['linked_id' => null, 'phone' => '254712345678', 'signup_referral_code' => 'KADI2026']);

    (new ProcessVerifiedUser($user))->handle();

    Http::assertSent(verifiedCalls());
    expect($user->fresh()->referral_verified_reported_at)->not->toBeNull();
});

test('players who signed up without a code are never reported', function () {
    fakeVerifiedApi();
    $user = User::factory()->phoneUnverified()->create(['linked_id' => 5001, 'phone' => '254712345678']);

    confirmPhone($user);

    Http::assertNotSent(verifiedCalls());
});

test('referred: false is a normal answer', function () {
    fakeVerifiedApi(200, ['success' => true, 'referred' => false]);
    $user = User::factory()->create(['linked_id' => 5001, 'phone' => '254712345678', 'signup_referral_code' => 'AGENT-7']);

    ReportReferralVerified::dispatchSync($user);

    expect($user->fresh()->referral_verified_reported_at)->not->toBeNull();
});

test('a player already reported is not reported again', function () {
    fakeVerifiedApi();
    $user = User::factory()->create([
        'linked_id' => 5001, 'phone' => '254712345678', 'signup_referral_code' => 'KADI2026',
    ]);
    $user->forceFill(['referral_verified_reported_at' => now()])->save();

    ReportReferralVerified::dispatchSync($user);

    Http::assertNothingSent();
});

test('a 5xx is thrown so the queue retries it, and nothing is stamped', function () {
    fakeVerifiedApi(503, []);
    $user = User::factory()->create(['linked_id' => 5001, 'phone' => '254712345678', 'signup_referral_code' => 'KADI2026']);

    expect(fn () => (new ReportReferralVerified($user))->handle())->toThrow(RequestException::class);
    expect($user->fresh()->referral_verified_reported_at)->toBeNull();
});

test('a 404 is not retried', function () {
    fakeVerifiedApi(404, ['success' => false]);
    $user = User::factory()->create(['linked_id' => 5001, 'phone' => '254712345678', 'signup_referral_code' => 'KADI2026']);

    (new ReportReferralVerified($user))->handle();

    expect($user->fresh()->referral_verified_reported_at)->toBeNull();
});
