<?php

namespace App\Livewire\Profile\Security;

use App\Livewire\Profile\Security\Concerns\ConfirmsPasswords;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Laravel\Passkeys\Actions\DeletePasskey;
use Laravel\Passkeys\Passkey;
use Livewire\Component;

class PasskeyManager extends Component
{
    use ConfirmsPasswords;

    /** @var array<int, array<string, mixed>> */
    public array $passkeys = [];

    public bool $confirmingRemoval = false;

    public ?int $pendingRemovalId = null;

    public ?int $editingId = null;

    public string $name = '';

    public string $statusMessage = '';

    public function mount(): void
    {
        $this->initPasswordConfirmation();
        $this->loadPasskeys();
    }

    /**
     * Refresh the authenticated user's registered passkeys.
     */
    public function loadPasskeys(): void
    {
        $this->passkeys = Auth::user()
            ->passkeys()
            ->orderBy('created_at')
            ->get()
            ->map(fn (Passkey $passkey) => [
                'id' => $passkey->id,
                'name' => $passkey->name,
                'authenticator' => $passkey->authenticator,
                'added' => $passkey->created_at?->format('j M Y'),
                'last_used' => $passkey->last_used_at?->diffForHumans() ?? __('Never'),
            ])
            ->all();
    }

    /**
     * Begin renaming a passkey.
     */
    public function startRenaming(int $passkeyId): void
    {
        $passkey = $this->findOwnedPasskey($passkeyId);

        if (! $passkey) {
            return;
        }

        $this->editingId = $passkey->id;
        $this->name = $passkey->name;
        $this->resetErrorBag('name');
    }

    /**
     * Persist a friendly-name change for one of the user's passkeys.
     */
    public function renamePasskey(): void
    {
        $this->enforceFreshConfirmation();

        if (is_null($this->editingId)) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $passkey = $this->findOwnedPasskey($this->editingId);

        if (! $passkey) {
            $this->cancelRenaming();

            return;
        }

        $passkey->update(['name' => $validated['name']]);

        $this->cancelRenaming();
        $this->loadPasskeys();

        $this->statusMessage = __('Passkey renamed.');
    }

    /**
     * Discard an in-progress rename.
     */
    public function cancelRenaming(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->resetErrorBag('name');
    }

    /**
     * Ask for confirmation before removing a passkey.
     */
    public function requestRemoval(int $passkeyId): void
    {
        $this->enforceFreshConfirmation();

        if (! $this->findOwnedPasskey($passkeyId)) {
            return;
        }

        $this->pendingRemovalId = $passkeyId;
        $this->confirmingRemoval = true;
    }

    /**
     * Remove the pending passkey after confirmation.
     */
    public function removePasskey(DeletePasskey $deletePasskey): void
    {
        $this->enforceFreshConfirmation();

        $passkey = $this->findOwnedPasskey((int) $this->pendingRemovalId);

        if ($passkey) {
            $deletePasskey(Auth::user(), $passkey);

            $this->statusMessage = __('Passkey removed.');
        }

        $this->confirmingRemoval = false;
        $this->pendingRemovalId = null;
        $this->loadPasskeys();
    }

    /**
     * Close the removal dialog without deleting anything.
     */
    public function cancelRemoval(): void
    {
        $this->confirmingRemoval = false;
        $this->pendingRemovalId = null;
    }

    /**
     * Abort mutations unless the password was confirmed recently.
     */
    private function enforceFreshConfirmation(): void
    {
        abort_unless($this->recentlyConfirmed(), 403);
    }

    /**
     * Find a passkey belonging to the authenticated user only.
     */
    private function findOwnedPasskey(?int $passkeyId): ?Passkey
    {
        if (is_null($passkeyId)) {
            return null;
        }

        return Auth::user()->passkeys()->whereKey($passkeyId)->first();
    }

    public function render(): Factory|View
    {
        return view('livewire.profile.security.passkey-manager');
    }
}
