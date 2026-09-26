<?php

use App\Facades\KadiApi;
use App\Jobs\ProcessVerifiedUser;
use App\Livewire\Auth\PromoCodeField;
use App\Livewire\Wallet\Index;
use App\Models\User;
use App\Promotions\PromoCode;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

const PROMO_API = 'https://api.kadi-kings.co.ke/api/v1/';

function promoRegistration(array $overrides = []): array
{
    return [
        'name' => 'NewPlayer',
        'email' => 'new@example.com',
        'phone' => '0700123456',
        'password' => 'password',
        'password_confirmation' => 'password',
        'age_confirmed' => '1',
        'terms' => '1',
        ...$overrides,
    ];
}

function fakePromoLookup(int $status = 200): void
{
    Http::fake([
        PROMO_API.'promo-codes/lookup*' => $status === 200
            ? Http::response(['success' => true, 'data' => ['code' => 'KADI20', 'expires_at' => '2026-10-31T23:59:00+03:00']])
            : Http::response(['success' => false, 'message' => 'Promo code not valid'], $status),
        PROMO_API.'referrals/lookup*' => Http::response(['success' => false], 404),
    ]);
}

/*
|--------------------------------------------------------------------------
| The field and the lookup
|--------------------------------------------------------------------------
*/

test('the sign-up form shows the promo field, and hides it when promotions are off', function () {
    $this->get('/register')->assertOk()->assertSee('data-test="promo-code-field"', false);

    config(['kadi.promotions.enabled' => false]);

    $this->get('/register')->assertOk()->assertDontSee('data-test="promo-code-field"', false);
});

test('a valid code says it is applied and shows the expiry', function () {
    fakePromoLookup();

    Livewire::test(PromoCodeField::class)
        ->set('code', ' kadi20 ')
        ->assertSet('status', 'valid')
        ->assertSee('Promo code applied')
        ->assertSee('31 Oct 2026, 23:59');

    Http::assertSent(fn (Request $request) => str_contains($request->url(), 'promo-codes/lookup') && $request['code'] === 'KADI20');
});

test('an invalid code says so but never blocks sign-up', function () {
    fakePromoLookup(404);

    Livewire::test(PromoCodeField::class)
        ->set('code', 'EXPIRED1')
        ->assertSet('status', 'invalid')
        ->assertSee("This promo code isn't valid. You can still sign up without it.");

    $this->post(route('register.store'), promoRegistration(['promo_code' => 'EXPIRED1']))
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'new@example.com')->value('signup_promo_code'))->toBe('EXPIRED1');
});

test('when KadiApi cannot be reached the field says nothing', function () {
    Http::fake([PROMO_API.'promo-codes/lookup*' => Http::response([], 503)]);

    Livewire::test(PromoCodeField::class)
        ->set('code', 'KADI20')
        ->assertSet('status', null)
        ->assertDontSee('Promo code applied')
        ->assertDontSee("isn't valid");
});

test('lookups are cached and rate limited per IP', function () {
    config(['kadi.promotions.lookups_per_minute' => 2]);
    fakePromoLookup(404);

    foreach (['CODE1', 'CODE2', 'CODE3', 'CODE1'] as $code) {
        PromoCode::lookup($code, '10.0.0.1');
    }

    Http::assertSentCount(2); // CODE3 over the limit, CODE1 served from the cache
});

test('a malformed code is a form error', function () {
    $this->post(route('register.store'), promoRegistration(['promo_code' => 'no spaces allowed']))
        ->assertSessionHasErrors('promo_code');
});

test('the sign-up form saves the code upper case, and not when promotions are off', function () {
    $this->post(route('register.store'), promoRegistration(['promo_code' => ' kadi20 ']))->assertSessionHasNoErrors();

    expect(User::where('email', 'new@example.com')->value('signup_promo_code'))->toBe('KADI20');

    auth()->logout();
    config(['kadi.promotions.enabled' => false]);

    $this->post(route('register.store'), promoRegistration(['email' => 'other@example.com', 'phone' => '0700123457', 'name' => 'OtherOne', 'promo_code' => 'KADI20']))
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'other@example.com')->value('signup_promo_code'))->toBeNull();
});

test('Google sign-ups save the code too', function () {
    $this->withSession(['google.pending' => [
        'id' => 'google-1', 'name' => 'Googler', 'email' => 'g@example.com', 'avatar' => null,
        'expires_at' => now()->addMinutes(10)->getTimestamp(),
    ]])->post(route('auth.google.complete.store'), ['age_confirmed' => '1', 'terms' => '1', 'promo_code' => 'kadi20'])
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'g@example.com')->value('signup_promo_code'))->toBe('KADI20');
});

/*
|--------------------------------------------------------------------------
| ?promo= links
|--------------------------------------------------------------------------
*/

test('a ?promo code is kept in the session and a 30-day cookie and pre-fills the field', function () {
    fakePromoLookup();

    $response = $this->get('/?promo=kadi20');

    $response->assertSessionHas(PromoCode::SESSION_KEY, 'KADI20');
    $cookie = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === 'kadi_promo');

    expect($cookie)->not->toBeNull()
        ->and($cookie->isHttpOnly())->toBeTrue()
        ->and($cookie->getExpiresTime())->toBeGreaterThan(now()->addDays(29)->getTimestamp());

    $this->get('/register')->assertOk()->assertSee('value="KADI20"', false)->assertSee('Promo code applied');
});

test('a malformed ?promo, a signed-in player, or promotions off are ignored', function () {
    $this->get('/?promo=<script>')->assertSessionMissing(PromoCode::SESSION_KEY);

    $this->actingAs(User::factory()->create())->get('/dashboard?promo=KADI20')->assertSessionMissing(PromoCode::SESSION_KEY);

    auth()->logout();
    config(['kadi.promotions.enabled' => false]);
    $this->get('/?promo=KADI20')->assertSessionMissing(PromoCode::SESSION_KEY);
});

test('the code from the link is stored at sign-up and the cookie is cleared', function () {
    fakePromoLookup();
    $this->get('/?promo=KADI20');

    $response = $this->post(route('register.store'), promoRegistration(['promo_code' => 'KADI20']));

    $response->assertSessionHasNoErrors()->assertSessionMissing(PromoCode::SESSION_KEY);
    $forgotten = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === 'kadi_promo');

    expect($forgotten?->getExpiresTime())->toBeLessThan(time())
        ->and(User::where('email', 'new@example.com')->value('signup_promo_code'))->toBe('KADI20');
});

/*
|--------------------------------------------------------------------------
| POST customers and promo_code_applied
|--------------------------------------------------------------------------
*/

test('POST customers carries promo_code alongside referral_code and records that it was applied', function () {
    Mail::fake();
    $user = User::factory()->phoneUnverified()->create([
        'linked_id' => null, 'phone' => '254712345678', 'signup_referral_code' => 'KADI2026', 'signup_promo_code' => 'KADI20',
    ]);

    KadiApi::shouldReceive('createCustomer')
        ->once()
        ->withArgs(fn (array $data) => $data['promo_code'] === 'KADI20' && $data['referral_code'] === 'KADI2026')
        ->andReturn(['status' => 'Success', 'customer_id' => 4242, 'promo_code_applied' => true]);
    KadiApi::shouldReceive('getCustomer')->andReturn(['data' => []]);

    (new ProcessVerifiedUser($user))->handle();

    expect($user->fresh())->linked_id->toBe(4242)->promo_code_applied->toBeTrue();
});

test('POST customers has no promo_code for players who used none', function () {
    Mail::fake();
    $user = User::factory()->phoneUnverified()->create(['linked_id' => null, 'phone' => '254712345678']);

    KadiApi::shouldReceive('createCustomer')
        ->once()
        ->withArgs(fn (array $data) => ! array_key_exists('promo_code', $data))
        ->andReturn(['status' => 'Success', 'customer_id' => 4243, 'promo_code_applied' => false]);
    KadiApi::shouldReceive('getCustomer')->andReturn(['data' => []]);

    (new ProcessVerifiedUser($user))->handle();

    expect($user->fresh()->promo_code_applied)->toBeNull();
});

test('promo_code_applied false: signup still succeeds and the wallet says the code was not applied, until dismissed', function () {
    Mail::fake();
    $user = User::factory()->phoneUnverified()->create(['linked_id' => null, 'phone' => '254712345678', 'signup_promo_code' => 'KADI20']);

    KadiApi::shouldReceive('createCustomer')->once()
        ->andReturn(['status' => 'Success', 'customer_id' => 4244, 'promo_code_applied' => false]);
    KadiApi::shouldReceive('getCustomer')->andReturn(['data' => ['balance' => 0]]);
    KadiApi::shouldReceive('getTransactions')->andReturn(['transactions' => []]);
    KadiApi::shouldReceive('getPromotions')->andReturn(['locked_amount' => 0, 'items' => []]);

    (new ProcessVerifiedUser($user))->handle();

    $user->refresh();
    expect($user->linked_id)->toBe(4244)->and($user->promo_code_applied)->toBeFalse();
    Cache::put("kadi.customer.{$user->id}", ['balance' => 0], now()->addHour());

    Livewire::actingAs($user)->test(Index::class)
        ->assertSee('data-test="promo-not-applied"', false)
        ->assertSee('KADI20')
        ->call('dismissPromoNotice')
        ->assertDontSee('data-test="promo-not-applied"', false);

    expect($user->fresh()->promo_notice_dismissed_at)->not->toBeNull();
});

test('verify-email nudges players with a pending code to verify straight away', function () {
    $user = User::factory()->unverified()->create(['signup_promo_code' => 'KADI20']);

    $this->actingAs($user)->get(route('verification.notice'))
        ->assertOk()
        ->assertSee('data-test="promo-verify-nudge"', false);

    $user->forceFill(['promo_code_applied' => false])->save();

    $this->actingAs($user->fresh())->get(route('verification.notice'))
        ->assertDontSee('data-test="promo-verify-nudge"', false);
});
