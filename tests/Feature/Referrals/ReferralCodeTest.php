<?php

use App\Models\User;
use App\Referrals\ReferralCode;
use App\Referrals\ReferralCodes;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

const REF_CODE_API = 'https://api.kadi-kings.co.ke/api/v1/';

function referralCodePuts(): Closure
{
    return fn (Request $request) => $request->method() === 'PUT' && str_ends_with($request->url(), '/referral-code');
}

test('generated codes are 8 characters without the look-alikes 0 O 1 I L', function () {
    foreach (range(1, 200) as $i) {
        expect(ReferralCode::generate())->toMatch('/^[23456789ABCDEFGHJKMNPQRSTUVWXYZ]{8}$/');
    }
});

test('an existing code in KadiApi is used and nothing is created', function () {
    Http::fake([
        REF_CODE_API.'customers/*/referral-code' => Http::response(['success' => true, 'data' => [
            'code' => 'KADI2026', 'link' => 'https://kadi.test/register?ref=KADI2026', 'qr_code' => 'data:image/png;base64,AAAA',
        ]]),
    ]);
    $user = User::factory()->create(['linked_id' => 42]);

    $share = app(ReferralCodes::class)->ensure($user);

    expect($share['code'])->toBe('KADI2026')
        ->and($share['qr_code'])->toBe('data:image/png;base64,AAAA');
    Http::assertNotSent(referralCodePuts());
});

test('a player without a code gets one with a register link and an SVG QR code', function () {
    Http::fake([
        REF_CODE_API.'customers/*/referral-code' => fn (Request $request) => $request->method() === 'GET'
            ? Http::response(['success' => false, 'message' => 'Customer has no referral code'], 404)
            : Http::response(['success' => true, 'data' => ['code' => $request['code'], 'link' => $request['link'], 'qr_code' => $request['qr_code']]]),
    ]);
    $user = User::factory()->create(['linked_id' => 42]);

    $share = app(ReferralCodes::class)->ensure($user);

    Http::assertSent(function (Request $request) {
        if ($request->method() !== 'PUT') {
            return false;
        }

        $svg = base64_decode(substr($request['qr_code'], strlen('data:image/svg+xml;base64,')));

        return preg_match('/^[23456789ABCDEFGHJKMNPQRSTUVWXYZ]{8}$/', $request['code']) === 1
            && $request['link'] === route('register', ['ref' => $request['code']])
            && str_starts_with($request['qr_code'], 'data:image/svg+xml;base64,')
            && strlen($request['qr_code']) < 500_000
            && str_contains($svg, '<svg');
    });

    expect($share['link'])->toContain('/register?ref='.$share['code']);
});

test('a code taken by another player (409) is replaced by a new one', function () {
    $attempts = 0;
    Http::fake([
        REF_CODE_API.'customers/*/referral-code' => function (Request $request) use (&$attempts) {
            if ($request->method() === 'GET') {
                return Http::response([], 404);
            }

            return ++$attempts < 3
                ? Http::response(['success' => false, 'message' => 'Referral code is already taken'], 409)
                : Http::response(['success' => true, 'data' => ['code' => $request['code'], 'link' => $request['link'], 'qr_code' => $request['qr_code']]]);
        },
    ]);
    $user = User::factory()->create(['linked_id' => 42]);

    $share = app(ReferralCodes::class)->ensure($user);

    expect($share)->not->toBeNull()->and($attempts)->toBe(3);

    $codes = Http::recorded(fn (Request $request) => $request->method() === 'PUT')->map(fn ($pair) => $pair[0]['code']);
    expect($codes->unique())->toHaveCount(3);
});

test('it gives up after 5 taken codes', function () {
    Http::fake([
        REF_CODE_API.'customers/*/referral-code' => fn (Request $request) => $request->method() === 'GET'
            ? Http::response([], 404)
            : Http::response(['success' => false], 409),
    ]);
    $user = User::factory()->create(['linked_id' => 42]);

    expect(app(ReferralCodes::class)->ensure($user))->toBeNull();
    Http::assertSentCount(1 + 5);
});

test('a 422 stops at once instead of retrying', function () {
    Http::fake([
        REF_CODE_API.'customers/*/referral-code' => fn (Request $request) => $request->method() === 'GET'
            ? Http::response([], 404)
            : Http::response(['errors' => ['code' => ['The code must be 4 to 20 letters or digits.']]], 422),
    ]);
    $user = User::factory()->create(['linked_id' => 42]);

    expect(app(ReferralCodes::class)->ensure($user))->toBeNull();
    Http::assertSentCount(2);
});

test('the code is cached, so a second visit does not call KadiApi', function () {
    Http::fake([
        REF_CODE_API.'customers/*/referral-code' => Http::response(['data' => ['code' => 'KADI2026', 'link' => null, 'qr_code' => null]]),
    ]);
    $user = User::factory()->create(['linked_id' => 42]);

    app(ReferralCodes::class)->ensure($user);
    $second = app(ReferralCodes::class)->ensure($user);

    Http::assertSentCount(1);
    // A missing link or QR code is rebuilt locally.
    expect($second['link'])->toBe(route('register', ['ref' => 'KADI2026']))
        ->and($second['qr_code'])->toStartWith('data:image/svg+xml;base64,')
        ->and(Cache::has(ReferralCodes::cacheKey($user)))->toBeTrue();
});

test('a player not yet linked to KadiApi gets no code and no call', function () {
    Http::fake();
    $user = User::factory()->create(['linked_id' => null]);

    expect(app(ReferralCodes::class)->ensure($user))->toBeNull();
    Http::assertNothingSent();
});
