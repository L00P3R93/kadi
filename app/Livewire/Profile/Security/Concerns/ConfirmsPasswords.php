<?php

namespace App\Livewire\Profile\Security\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Locked;

/**
 * Gates sensitive security operations behind a recent password
 * confirmation, mirroring Fortify's `password.confirm` semantics
 * (same session key and timeout) so package routes such as passkey
 * registration succeed once access has been confirmed here.
 */
trait ConfirmsPasswords
{
    public string $confirmPassword = '';

    #[Locked]
    public bool $accessConfirmed = false;

    public function initPasswordConfirmation(): void
    {
        $this->accessConfirmed = $this->recentlyConfirmed();
    }

    public function recentlyConfirmed(): bool
    {
        $confirmedAt = session('auth.password_confirmed_at');

        if (! is_numeric($confirmedAt)) {
            return false;
        }

        return (time() - (int) $confirmedAt) < config('auth.password_timeout', 10800);
    }

    public function confirmAccess(): void
    {
        $this->validate([
            'confirmPassword' => ['required', 'string'],
        ]);

        if (! Hash::check($this->confirmPassword, Auth::user()->password)) {
            $this->addError('confirmPassword', __('The provided password is incorrect.'));

            return;
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->confirmPassword = '';
        $this->accessConfirmed = true;
        $this->resetErrorBag('confirmPassword');
    }
}
