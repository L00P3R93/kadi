<?php

use App\Livewire\Profile\Security\PasskeyManager;
use App\Livewire\Profile\Security\TwoFactorPanel;
use App\Livewire\Profile\Show;
use App\Mail\SecurityAlertEmail;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Events\RecoveryCodeReplaced;
use Livewire\Livewire;
use Pragmarx\Google2FA\Google2FA;
use Spatie\Activitylog\Models\Activity;

function securityActivities(): Collection
{
    return Activity::whereLogName('security')->get();
}

test('enabling two factor writes audit entries without leaking secrets', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);

    $panel = Livewire::test(TwoFactorPanel::class)->call('startTwoFactorSetup');

    $secret = decrypt($user->fresh()->two_factor_secret);

    $panel
        ->set('code', (new Google2FA)->getCurrentOtp($secret))
        ->call('confirmTwoFactor')
        ->assertHasNoErrors();

    $labels = securityActivities()->pluck('description');

    expect($labels)->toContain('Two-factor authentication enabled');
    expect($labels)->toContain('Two-factor authentication confirmed');
    expect(securityActivities()->where('causer_id', $user->id))->not->toBeEmpty();
});

test('disabling two factor audits and queues a security email', function () {
    Mail::fake();

    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);

    Livewire::test(TwoFactorPanel::class)
        ->call('disableTwoFactor')
        ->assertSet('twoFactorEnabled', false);

    expect(securityActivities()->pluck('description'))->toContain('Two-factor authentication disabled');

    Mail::assertQueued(SecurityAlertEmail::class, function (SecurityAlertEmail $mail) use ($user) {
        return $mail->hasTo($user->email)
            && str_contains($mail->change, 'disabled');
    });
});

test('removing a passkey audits the change with its name only', function () {
    Mail::fake();

    $user = User::factory()->create();
    $passkey = $user->passkeys()->create([
        'name' => 'Old Phone',
        'credential_id' => bin2hex(random_bytes(32)),
        'credential' => ['type' => 'public-key'],
    ]);

    $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);

    Livewire::test(PasskeyManager::class)
        ->call('requestRemoval', $passkey->id)
        ->call('removePasskey');

    $entry = securityActivities()->where('description', 'Passkey removed')->first();

    expect($entry)->not->toBeNull();
    expect(data_get($entry->properties, 'passkey'))->toBeIn(['Old Phone', 'unknown']);
    expect($entry->properties->toArray())->not->toHaveKey('credential_id');

    Mail::assertQueued(SecurityAlertEmail::class);
});

test('password change from profile audits and queues a security email', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Show::class)
        ->set('currentPassword', 'password')
        ->set('newPassword', 'new-secure-password')
        ->set('newPasswordConfirmation', 'new-secure-password')
        ->call('updatePassword');

    $entry = securityActivities()->where('description', 'Password changed')->last();

    expect($entry?->causer_id)->toBe($user->id);

    Mail::assertQueued(SecurityAlertEmail::class, fn (SecurityAlertEmail $mail) => $mail->hasTo($user->email));
});

test('regenerating recovery codes is audited', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user);

    app(GenerateNewRecoveryCodes::class)($user);

    expect(securityActivities()->pluck('description'))->toContain('Recovery codes regenerated');
});

test('consumed recovery codes are audited but their values never logged', function () {
    $user = User::factory()->withTwoFactor()->create();

    $code = 'used-recovery-code-value';

    RecoveryCodeReplaced::dispatch($user, $code);

    $entry = securityActivities()->firstWhere('description', 'Recovery code consumed');

    expect($entry)->not->toBeNull();
    expect($entry->properties->toJson())->not->toContain($code);
});
