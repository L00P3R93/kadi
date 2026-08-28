<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'phone' => ['required', 'string', 'min:9'],
            'password' => $this->passwordRules(),
        ])->validate();

        $accountNo = 'KK-'.strtoupper(uniqid());

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'account_no' => $accountNo,
            'password' => $input['password'],
        ]);

        // The linked kadi account needs a password that matches what the user
        // registered with, so the game site can verify logins via
        // password_verify(). We hand the job a one-way BCRYPT HASH — never the
        // plaintext — so no recoverable secret is stored in cache or queue
        // payloads (audit finding C-1).
        Cache::put(
            "user.kadi_password_hash.{$user->id}",
            Hash::make($input['password']),
            now()->addMinutes(30)
        );
        $user->assignRole('player');

        // $user->sendEmailVerificationNotification();

        return $user;
    }
}
