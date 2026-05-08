<?php

namespace App\Livewire\Profile;

use App\Facades\KadiApi;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $idNo = '';

    public string $phoneNo = '';

    public array $kadiCustomer = [];

    public string $activeTab = 'info';

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    public $photo = null;

    public string $profilePicUrl = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->kadiCustomer = Cache::get("kadi.customer.{$user->id}", []);
        $this->name    = $user->name;
        $this->email   = $user->email;
        $this->idNo    = $this->kadiCustomer['id_no'] ?? '';
        $this->phoneNo = $this->kadiCustomer['phone_no'] ?? '';
        $this->profilePicUrl = $this->buildProfilePicUrl();
    }

    public function updateProfile(): void
    {
        $this->validate([
            'name'    => ['required', 'min:2', 'max:100'],
            'email'   => ['required', 'email', 'unique:users,email,'.auth()->id()],
            'idNo'    => ['nullable', 'string'],
            'phoneNo' => ['nullable', 'string'],
        ]);

        auth()->user()->update([
            'name'  => $this->name,
            'email' => $this->email,
        ]);

        $customerId = auth()->user()->linked_id;

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

        session()->flash('profile_success', 'Profile updated successfully.');
    }

    public function uploadProfilePic(): void
    {
        $this->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = auth()->user();

        if (! $user->linked_id) {
            $this->addError('photo', 'Account is not linked to Kadi.');

            return;
        }

        try {
            $ext      = $this->photo->getClientOriginalExtension() ?: 'jpg';
            $filename = 'profile_'.$user->linked_id.'_'.time().'.'.$ext;

            $response = KadiApi::uploadProfilePic(
                $user->linked_id,
                $this->photo->getRealPath(),
                $filename
            );

            if (isset($response['data'])) {
                Cache::put('kadi.customer.'.$user->id, $response['data'], now()->addHour());
                $this->kadiCustomer  = $response['data'];
                $this->profilePicUrl = $this->buildProfilePicUrl();
            }

            $this->reset('photo');
            session()->flash('profile_success', 'Profile picture updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Profile pic upload failed: '.$e->getMessage());
            $this->addError('photo', 'Upload failed. Please try again.');
        }
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
