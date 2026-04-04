<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
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
            'phone'    => ['required', 'string', 'min:9'],
            'password' => $this->passwordRules(),
        ])->validate();

        $accountNo = 'KK-' . strtoupper(uniqid());

        $user = User::create([
            'name'       => $input['name'],
            'email'      => $input['email'],
            'phone'      => $input['phone'],
            'account_no' => $accountNo,
            'password'   => $input['password'],
        ]);

        Cache::put("user.plain_password.{$user->id}", $input['password'], now()->addHours(24));

        //$user->sendEmailVerificationNotification();

        return $user;
    }
}
