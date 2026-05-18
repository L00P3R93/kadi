<?php

namespace App\Livewire;

use App\Facades\KadiApi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PhoneRequired extends Component
{
    public bool $show = false;

    #[Validate('required|string|min:9|max:20')]
    public string $phone = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->show = empty($user->phone);
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
                Log::error("KadiApi phone update failed for user {$user->id}: " . $e->getMessage());
            }
        }

        // 3. Kadi database
        try {
            DB::connection('kadi')
                ->table('accounts')
                ->where('email', $user->email)
                ->update(['phone' => $this->phone]);
        } catch (\Throwable $e) {
            Log::error("Kadi DB phone update failed for user {$user->id}: " . $e->getMessage());
        }

        $this->show = false;
    }

    public function render()
    {
        return view('livewire.phone-required');
    }
}
