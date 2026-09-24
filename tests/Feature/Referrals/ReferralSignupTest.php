<?php

use App\Facades\KadiApi;
use App\Jobs\ProcessVerifiedUser;
use App\Models\User;
use App\Referrals\ReferralCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

const REF_SIGNUP_API = 'https://api.kadi-kings.co.ke/api/v1/';

function referralRegistration(array $overrides = []): array
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

test('a ?ref code is kept in the session and a 30-day cookie', function () {
    $response = $this->get('/?ref=kadi2026');

    $response->assertSessionHas(ReferralCode::SESSION_KEY, 'KADI2026');
    $cookie = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === 'kadi_ref');

    expect($cookie)->not->toBeNull()
        ->and($cookie->isHttpOnly())->toBeTrue()
        ->and($cookie->getExpiresTime())->toBeGreaterThan(now()->addDays(29)->getTimestamp());
});

test('a malformed ?ref is ignored', function () {
    $this->get('/?ref=<script>')->assertSessionMissing(ReferralCode::SESSION_KEY);
});

test('signed-in players are never given a referral code', function () {
    $this->actingAs(User::factory()->create())
        ->get('/dashboard?ref=KADI2026')
        ->assertSessionMissing(ReferralCode::SESSION_KEY);
});

test('the sign-up form is pre-filled and names the inviter by first name', function () {
    Http::fake([REF_SIGNUP_API.'referrals/lookup*' => Http::response(['data' => ['code' => 'KADI2026', 'name' => 'Jane Doe']])]);

    $this->get('/register?ref=KADI2026')
        ->assertOk()
        ->assertSee('value="KADI2026"', false)
        ->assertSee('Invited by Jane')
        ->assertDontSee('Doe');
});

test('an unknown code shows a neutral hint and never blocks sign-up', function () {
    Http::fake([REF_SIGNUP_API.'referrals/lookup*' => Http::response(['success' => false], 404)]);

    $this->get('/register?ref=AGENT-7')
        ->assertOk()
        ->assertDontSee('Invited by')
        ->assertSee("We couldn't find a player with this code");

    $this->post(route('register.store'), referralRegistration(['referral_code' => 'AGENT-7']))
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'new@example.com')->value('signup_referral_code'))->toBe('AGENT-7');
});

test('the code from the link survives to sign-up, is stored and the cookie is cleared', function () {
    Http::fake([REF_SIGNUP_API.'referrals/lookup*' => Http::response(['data' => ['name' => 'Jane']])]);

    $this->get('/?ref=KADI2026');

    $response = $this->post(route('register.store'), referralRegistration(['referral_code' => 'kadi2026']));

    $response->assertSessionHasNoErrors()->assertSessionMissing(ReferralCode::SESSION_KEY);
    $forgotten = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === 'kadi_ref');
    expect($forgotten?->getExpiresTime())->toBeLessThan(time())
        ->and(User::where('email', 'new@example.com')->value('signup_referral_code'))->toBe('KADI2026');
});

test('sign-up without a code stores none', function () {
    $this->post(route('register.store'), referralRegistration())->assertSessionHasNoErrors();

    expect(User::where('email', 'new@example.com')->value('signup_referral_code'))->toBeNull();
});

test('a code with invalid characters is refused on the form', function () {
    $this->post(route('register.store'), referralRegistration(['referral_code' => 'bad code!']))
        ->assertSessionHasErrors('referral_code');
});

test('google sign-up stores the code too', function () {
    Queue::fake();

    $this->withSession(['google.pending' => [
        'id' => 'google-1', 'name' => 'Googler', 'email' => 'g@example.com', 'avatar' => null,
        'expires_at' => now()->addMinutes(10)->getTimestamp(),
    ]])->post(route('auth.google.complete.store'), ['age_confirmed' => '1', 'terms' => '1', 'referral_code' => 'kadi2026'])
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'g@example.com')->value('signup_referral_code'))->toBe('KADI2026');
});

test('POST customers carries the code as referral_code', function () {
    Mail::fake();
    $user = User::factory()->create(['linked_id' => null, 'phone' => '254712345678', 'signup_referral_code' => 'KADI2026']);

    KadiApi::shouldReceive('createCustomer')
        ->once()
        ->withArgs(fn (array $data) => ($data['referral_code'] ?? null) === 'KADI2026')
        ->andReturn(['customer_id' => 4242]);
    KadiApi::shouldReceive('getCustomer')->andReturn(['data' => []]);
    KadiApi::shouldReceive('reportReferralVerified')->andReturn(['referred' => true]);

    (new ProcessVerifiedUser($user))->handle();

    expect($user->fresh()->linked_id)->toBe(4242);
});

test('POST customers has no referral_code for players who used none', function () {
    Mail::fake();
    $user = User::factory()->create(['linked_id' => null, 'phone' => '254712345678']);

    KadiApi::shouldReceive('createCustomer')
        ->once()
        ->withArgs(fn (array $data) => ! array_key_exists('referral_code', $data))
        ->andReturn(['customer_id' => 4243]);
    KadiApi::shouldReceive('getCustomer')->andReturn(['data' => []]);

    (new ProcessVerifiedUser($user))->handle();
});
