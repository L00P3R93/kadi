<?php

use App\Actions\Security\RevokeOtherSessions;
use App\Livewire\Profile\Security\TwoFactorPanel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

test('passkey endpoints are rate limited', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    foreach (range(1, 5) as $attempt) {
        $this->get(route('passkey.registration-options'))->assertOk();
    }

    $this->get(route('passkey.registration-options'))->assertStatus(429);
});

test('other database sessions are revoked but the current one kept', function () {
    config(['session.driver' => 'database']);

    $user = User::factory()->create();

    // Attach a real (array-driver) session to the request; its generated
    // id stands in for "the session we are currently using".
    $session = Session::driver('array');
    request()->setLaravelSession($session);
    $currentId = $session->getId();

    DB::table('sessions')->insert([
        ['id' => $currentId, 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'ua', 'payload' => '', 'last_activity' => time()],
        ['id' => str_repeat('o', 40), 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'ua', 'payload' => '', 'last_activity' => time()],
    ]);

    app(RevokeOtherSessions::class)($user);

    expect(DB::table('sessions')->where('id', $currentId)->exists())->toBeTrue();
    expect(DB::table('sessions')->where('id', str_repeat('o', 40))->exists())->toBeFalse();
});

test('without a resolvable current session every session is revoked', function () {
    config(['session.driver' => 'database']);

    $user = User::factory()->create();

    DB::table('sessions')->insert([
        ['id' => 'device-a', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'ua', 'payload' => '', 'last_activity' => time()],
        ['id' => 'device-b', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'ua', 'payload' => '', 'last_activity' => time()],
    ]);

    app(RevokeOtherSessions::class)($user);

    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);
});

test('revocation no-ops gracefully for non-database session drivers', function () {
    config(['session.driver' => 'array']);

    $user = User::factory()->create();

    app(RevokeOtherSessions::class)($user);

    expect(true)->toBeTrue();
});

test('a consumed recovery code cannot be reused', function () {
    $user = User::factory()->withTwoFactor()->create();

    // First login: consume the only available recovery code.
    session(['login.id' => $user->id]);

    $this->post(route('two-factor.login.store'), [
        'recovery_code' => 'recovery-code-1',
    ])->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
    expect(decrypt($user->fresh()->two_factor_recovery_codes))->not->toContain('recovery-code-1');

    // Second login attempt replaying the same consumed code must fail.
    $this->post(route('logout'));

    session(['login.id' => $user->id]);

    $this->post(route('two-factor.login.store'), [
        'recovery_code' => 'recovery-code-1',
    ]);

    $this->assertGuest();
});

test('an abandoned unconfirmed two factor setup is wiped on panel mount', function () {
    $user = User::factory()->create([
        'two_factor_secret' => encrypt('abandoned'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code-1'])),
        'two_factor_confirmed_at' => null,
    ]);

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    Livewire::test(TwoFactorPanel::class)
        ->assertSet('twoFactorEnabled', false);

    expect($user->fresh()->two_factor_secret)->toBeNull();
    expect($user->fresh()->two_factor_recovery_codes)->toBeNull();
});
