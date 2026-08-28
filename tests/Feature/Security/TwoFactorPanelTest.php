<?php

use App\Livewire\Profile\Security\TwoFactorPanel;
use App\Models\User;
use Livewire\Livewire;
use Pragmarx\Google2FA\Google2FA;

function confirmedPanel(User $user)
{
    test()->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);

    return Livewire::test(TwoFactorPanel::class);
}

test('two factor panel shows disabled state and enable action', function () {
    $user = User::factory()->create();

    confirmedPanel($user)
        ->assertSet('twoFactorEnabled', false)
        ->assertSee(__('Enable 2FA'))
        ->assertDontSee(__('Disable 2FA'), false);
});

test('two factor panel shows enabled state with recovery codes for configured users', function () {
    $user = User::factory()->withTwoFactor()->create();

    confirmedPanel($user)
        ->assertSet('twoFactorEnabled', true)
        ->assertSee('recovery-codes');
});

test('setup generates provisioning data after confirmation', function () {
    $user = User::factory()->create();

    confirmedPanel($user)
        ->call('startTwoFactorSetup')
        ->assertSet('qrCodeSvg', fn (string $svg) => str_contains($svg, '<svg'))
        ->assertSet('manualSetupKey', fn (string $key) => filled($key));

    expect(decrypt($user->fresh()->two_factor_secret))->toBeString();
});

test('an invalid totp code is rejected during confirmation', function () {
    $user = User::factory()->create();

    confirmedPanel($user)
        ->call('startTwoFactorSetup')
        ->set('code', '000000')
        ->call('confirmTwoFactor')
        ->assertHasErrors(['code'])
        ->assertSet('twoFactorEnabled', false);

    expect($user->fresh()->two_factor_confirmed_at)->toBeNull();
});

test('a valid totp code confirms and enables two factor', function () {
    $user = User::factory()->create();

    $panel = confirmedPanel($user)
        ->call('startTwoFactorSetup');

    $secret = decrypt($user->fresh()->two_factor_secret);
    $code = (new Google2FA)->getCurrentOtp($secret);

    $panel
        ->set('code', $code)
        ->call('confirmTwoFactor')
        ->assertHasNoErrors()
        ->assertSet('twoFactorEnabled', true)
        ->assertSet('manualSetupKey', '')
        ->assertSet('qrCodeSvg', '');

    expect($user->fresh()->two_factor_confirmed_at)->not->toBeNull();
});

test('two factor can be disabled', function () {
    $user = User::factory()->withTwoFactor()->create();

    confirmedPanel($user)
        ->set('confirmingDisable', true)
        ->call('disableTwoFactor')
        ->assertSet('twoFactorEnabled', false)
        ->assertSet('confirmingDisable', false);

    expect($user->fresh()->two_factor_secret)->toBeNull();
});

test('setup actions are forbidden without a recent password confirmation', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(TwoFactorPanel::class)
        ->assertSet('accessConfirmed', false)
        ->call('startTwoFactorSetup')
        ->assertStatus(403);

    expect($user->fresh()->two_factor_secret)->toBeNull();
});
