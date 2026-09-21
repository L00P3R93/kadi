<?php

namespace App\Livewire;

use App\Concerns\ProfileValidationRules;
use App\Facades\KadiApi;
use App\Services\KadiAccountSync;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class PhoneRequired extends Component
{
    use ProfileValidationRules;

    public bool $show = false;

    /** What the phone is needed for: 'coins' (default) or 'deposit'. Drives the copy. */
    public string $purpose = 'coins';

    public string $phone = '';

    public function mount(): void
    {
        // No longer auto-opens on page load. It only opens when explicitly
        // requested (e.g. clicking a Buy Coins option) via the
        // 'open-phone-required' event, see open() below.
        $this->show = false;
    }

    /**
     * Opens the modal, but only if the user genuinely has no phone on file.
     * Dispatched from wherever a phone number is required to proceed
     * (currently: Buy Coins purchase tiles).
     */
    #[On('open-phone-required')]
    public function open(string $purpose = 'coins'): void
    {
        $user = auth()->user();

        if (empty($user->phone)) {
            $this->purpose = $purpose;
            $this->show = true;
        }
    }

    public function save(): void
    {
        $user = auth()->user();

        // A phone is set once: it is the M-Pesa payout number, so it is never overwritten here.
        if (! empty($user->phone)) {
            $this->show = false;

            return;
        }

        $this->validate(['phone' => $this->phoneRules($user->id)]);

        // 1. Local users table (the mutator stores it as 254XXXXXXXXX, no `+`)
        $user->update(['phone' => $this->phone]);

        // 2. KadiApi (normalised again by the service; it never receives a `+`)
        if ($user->linked_id) {
            try {
                KadiApi::updateCustomer($user->linked_id, ['phone_no' => $user->phone]);
                // Bust the cached profile so the profile page reflects the new phone
                Cache::forget("kadi.customer.{$user->id}");
            } catch (\Throwable $e) {
                Log::error("KadiApi phone update failed for user {$user->id}: ".$e->getMessage());
            }
        }

        // 3. Kadi database (skips numbers held by another account; never throws)
        app(KadiAccountSync::class)->syncPhone($user);

        $this->show = false;
        $this->dispatch('phone-saved');
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.phone-required');
    }
}
