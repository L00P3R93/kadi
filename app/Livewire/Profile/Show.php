<?php

namespace App\Livewire\Profile;

use App\Facades\KadiApi;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Component;

class Show extends Component
{
    public string $name = '';

    public string $email = '';

    public string $idNo = '';

    public string $phoneNo = '';

    public array $kadiCustomer = [];

    public string $activeTab = 'info';

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    public string $profilePicUrl = '';

    public string $resolvedAvatarUrl = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->kadiCustomer = Cache::get("kadi.customer.{$user->id}", []);
        $this->name    = $user->name;
        $this->email   = $user->email;
        $this->idNo    = $this->kadiCustomer['id_no'] ?? '';
        $this->phoneNo = $this->kadiCustomer['phone_no'] ?? $user->phone ?? '';
        $this->profilePicUrl      = $this->buildProfilePicUrl();
        $this->resolvedAvatarUrl  = $this->resolveAvatarUrl();
    }

    public function updateProfile(): void
    {
        $this->validate([
            'name'    => ['required', 'min:2', 'max:100'],
            'email'   => ['required', 'email', 'unique:users,email,'.auth()->id()],
            'idNo'    => ['nullable', 'string'],
            'phoneNo' => ['nullable', 'string'],
        ]);

        $user = auth()->user();

        $userUpdate = ['name' => $this->name, 'email' => $this->email];
        // Only allow setting phone when it isn't already on record
        if (empty($user->phone) && $this->phoneNo !== '') {
            $userUpdate['phone'] = $this->phoneNo;
        }
        $user->update($userUpdate);

        $customerId = $user->linked_id;

        if ($customerId) {
            try {
                $response = KadiApi::updateCustomer($customerId, [
                    'name'     => $this->name,
                    'id_no'    => $this->idNo,
                    'phone_no' => $this->phoneNo,
                ]);

                if (isset($response['data'])) {
                    Cache::put('kadi.customer.'.auth()->id(), $response['data'], now()->addHour());
                    $this->kadiCustomer = $response['data'];
                }
            } catch (\Throwable $e) {
                Log::error('KadiApi profile update failed: '.$e->getMessage());
            }
        }

        // Sync phone to kadi database when setting it for the first time
        if (isset($userUpdate['phone'])) {
            try {
                DB::connection('kadi')
                    ->table('accounts')
                    ->where('email', $user->email)
                    ->update(['phone' => $this->phoneNo]);
            } catch (\Throwable $e) {
                Log::error('Kadi DB phone sync failed: '.$e->getMessage());
            }
        }

        session()->flash('profile_success', 'Profile updated successfully.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'currentPassword' => ['required'],
            'newPassword'     => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($this->currentPassword, auth()->user()->password)) {
            $this->addError('currentPassword', 'Current password is incorrect.');

            return;
        }

        auth()->user()->update([
            'password' => Hash::make($this->newPassword),
        ]);

        $this->reset('currentPassword', 'newPassword', 'newPasswordConfirmation');
        session()->flash('password_success', 'Password updated successfully.');
    }

    private function resolveAvatarUrl(): string
    {
        // 1. Kadi-uploaded profile picture (highest priority)
        $uploaded = $this->buildProfilePicUrl();
        if ($uploaded !== '') {
            return $uploaded;
        }

        $user = auth()->user();

        // 2. Google avatar (linked account)
        if ($user->avatar) {
            return $user->avatar;
        }

        // 3. Gravatar with robohash fallback
        $hash = md5(strtolower(trim($user->email)));

        return "https://www.gravatar.com/avatar/{$hash}?d=robohash&r=pg&s=200";
    }

    private function buildProfilePicUrl(): string
    {
        $pic       = $this->kadiCustomer['pic'] ?? null;
        $accountId = $this->kadiCustomer['id'] ?? auth()->user()->linked_id;

        if (! $pic || ! $accountId) {
            return '';
        }

        $base = rtrim(config('services.kadi_api.image_url'), '/');

        return "{$base}/{$accountId}/{$pic}";
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.profile.show')
            ->layout('layouts.app');
    }
}
