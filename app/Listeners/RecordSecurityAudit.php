<?php

namespace App\Listeners;

use App\Events\PasswordChanged;
use Laravel\Fortify\Events\RecoveryCodeReplaced;
use Laravel\Fortify\Events\RecoveryCodesGenerated;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Passkeys\Events\PasskeyDeleted;
use Laravel\Passkeys\Events\PasskeyRegistered;

/**
 * Writes security-relevant credential changes to the activity log.
 *
 * Runs synchronously (not queued): laravel/passkeys' PasskeyDeleted
 * event carries an already-deleted model that cannot survive queue
 * serialization, and an audit trail should never depend on a worker
 * being up anyway.
 *
 * Never log secrets: TOTP secrets, OTP codes, recovery-code values
 * (RecoveryCodeReplaced carries one — ignored), challenges or tokens.
 */
class RecordSecurityAudit
{
    private const LABELS = [
        TwoFactorAuthenticationEnabled::class => 'Two-factor authentication enabled',
        TwoFactorAuthenticationConfirmed::class => 'Two-factor authentication confirmed',
        TwoFactorAuthenticationDisabled::class => 'Two-factor authentication disabled',
        RecoveryCodesGenerated::class => 'Recovery codes regenerated',
        RecoveryCodeReplaced::class => 'Recovery code consumed',
        PasskeyRegistered::class => 'Passkey registered',
        PasskeyDeleted::class => 'Passkey removed',
        PasswordChanged::class => 'Password changed',
    ];

    public function handle(object $event): void
    {
        $label = self::LABELS[$event::class] ?? null;

        if ($label === null || ! isset($event->user)) {
            return;
        }

        activity('security')
            ->causedBy($event->user)
            ->event($label)
            ->withProperties($this->propertiesFor($event))
            ->log($label);
    }

    /**
     * Safe, non-sensitive metadata for the audit trail.
     *
     * @return array<string, mixed>
     */
    private function propertiesFor(object $event): array
    {
        if ($event instanceof PasskeyRegistered || $event instanceof PasskeyDeleted) {
            // The passkey has already been deleted by the time queued
            // listeners run, so its model may no longer be resolvable.
            $name = rescue(fn () => $event->passkey->name, null, false);

            return ['passkey' => $name ?? 'unknown'];
        }

        return [];
    }
}
