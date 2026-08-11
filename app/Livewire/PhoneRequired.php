<?php

namespace App\Livewire;

use App\Facades\KadiApi;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PhoneRequired extends Component
{
    public bool $show = false;

    #[Validate('required|string|min:9|max:20')]
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
    public function open(): void
    {
        $user = auth()->user();

        if (empty($user->phone)) {
            $this->show = true;
        }
    }

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();

        // 1. Local users table
        $user->update(['phone' => $this->phone]);

        // 2. KadiApi
        if ($user->linked_id) {
            try {
                KadiApi::updateCustomer($user->linked_id, ['phone_no' => $this->phone]);
                // Bust the cached profile so the profile page reflects the new phone
                Cache::forget("kadi.customer.{$user->id}");
            } catch (\Throwable $e) {
                Log::error("KadiApi phone update failed for user {$user->id}: ".$e->getMessage());
            }
        }

        // 3. Kadi database
        try {
            DB::connection('kadi')
                ->table('accounts')
                ->where('email', $user->email)
                ->update(['phone' => $this->phone]);
        } catch (\Throwable $e) {
            Log::error("Kadi DB phone update failed for user {$user->id}: ".$e->getMessage());
        }

        $this->show = false;
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.phone-required');
    }
}
