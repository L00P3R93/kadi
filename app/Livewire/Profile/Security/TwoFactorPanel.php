<?php

namespace App\Livewire\Profile\Security;

use App\Actions\Security\RevokeOtherSessions;
use App\Livewire\Profile\Security\Concerns\ConfirmsPasswords;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class TwoFactorPanel extends Component
{
    use ConfirmsPasswords;

    public bool $canManageTwoFactor = false;

    public bool $twoFactorEnabled = false;

    public bool $requiresConfirmation = false;

    public bool $confirmingDisable = false;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showVerificationStep = false;

    public bool $setupComplete = false;

    public string $code = '';

    public string $statusMessage = '';

    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $this->initPasswordConfirmation();

        $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();

        if (! $this->canManageTwoFactor) {
            return;
        }

        if (Fortify::confirmsTwoFactorAuthentication() && is_null(Auth::user()->two_factor_confirmed_at)) {
            $disableTwoFactorAuthentication(Auth::user());
        }

        $this->twoFactorEnabled = Auth::user()->hasEnabledTwoFactorAuthentication();
        $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
    }

    /**
     * Begin 2FA setup: generate a secret and load QR / manual key data.
     */
    #[On('start-two-factor-setup')]
    public function startTwoFactorSetup(): void
    {
        $this->enforceFreshConfirmation();

        app(EnableTwoFactorAuthentication::class)(Auth::user());

        $this->loadSetupData();
    }

    /**
     * Move to the verification step (or finish immediately when
     * confirmation of a valid TOTP code is not required).
     */
    public function showVerificationIfNecessary(): void
    {
        $this->enforceFreshConfirmation();

        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;
            $this->resetErrorBag();

            return;
        }

        $this->finishSetup();
    }

    /**
     * Verify the entered TOTP code and enable 2FA.
     */
    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->enforceFreshConfirmation();

        $this->validate(['code' => ['required', 'string', 'size:6']]);

        try {
            $confirmTwoFactorAuthentication(Auth::user(), $this->code);
        } catch (ValidationException) {
            $this->addError('code', __('The provided two factor authentication code was invalid.'));

            return;
        }

        $this->setupComplete = true;
        $this->finishSetup();
    }

    /**
     * Return from the verification step to the QR code step.
     */
    public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');
        $this->resetErrorBag();
    }

    /**
     * Close the setup modal and discard transient secret material.
     */
    public function closeSetup(): void
    {
        $this->reset('code', 'manualSetupKey', 'qrCodeSvg', 'showVerificationStep', 'setupComplete');
        $this->resetErrorBag();
    }

    /**
     * Disable 2FA after explicit confirmation.
     */
    public function disableTwoFactor(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $this->enforceFreshConfirmation();

        $user = Auth::user();

        $disableTwoFactorAuthentication($user);

        app(RevokeOtherSessions::class)($user);

        $this->twoFactorEnabled = false;
        $this->confirmingDisable = false;
        $this->statusMessage = __('Two-factor authentication has been disabled.');
    }

    #[On('two-factor-enabled')]
    public function onTwoFactorEnabled(): void
    {
        $this->twoFactorEnabled = true;
    }

    /**
     * Load provisioning data for the current setup attempt.
     */
    private function loadSetupData(): void
    {
        $user = Auth::user()?->fresh();

        try {
            if (! $user || ! $user->two_factor_secret) {
                throw new Exception('Two-factor setup secret is not available.');
            }

            $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', __('Failed to fetch setup data.'));

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }

    private function finishSetup(): void
    {
        $this->closeSetup();
        $this->twoFactorEnabled = true;
        $this->statusMessage = __('Two-factor authentication is now enabled.');
    }

    private function enforceFreshConfirmation(): void
    {
        abort_unless($this->recentlyConfirmed(), 403);
    }

    public function render(): Factory|View
    {
        return view('livewire.profile.security.two-factor-panel');
    }
}
