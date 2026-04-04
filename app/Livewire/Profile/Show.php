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

    public function mount(): void
    {
        $user = auth()->user();
        $this->kadiCustomer = Cache::get("kadi.customer.{$user->id}", []);
        $this->name   = $user->name;
        $this->email  = $user->email;
        $this->idNo   = $this->kadiCustomer['id_no'] ?? '';
        $this->phoneNo = $this->kadiCustomer['phone_no'] ?? '';
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

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.profile.show')
            ->layout('layouts.app');
    }
}
