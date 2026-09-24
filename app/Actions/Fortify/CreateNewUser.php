<?php

namespace App\Actions\Fortify;

use App\Concerns\ConsentValidationRules;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Referrals\ReferralCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use ConsentValidationRules, PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $input['referral_code'] = ReferralCode::normalize($input['referral_code'] ?? null);

        Validator::make($input, [
            ...$this->profileRules(),
            'phone' => $this->phoneRules(),
            'password' => $this->passwordRules(),
            'referral_code' => ReferralCode::signupRules(),
            ...$this->consentRules(),
        ], [...$this->consentMessages(), ...ReferralCode::signupMessages()])->validate();

        $accountNo = 'KK-'.strtoupper(uniqid());

        $user = new User([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'account_no' => $accountNo,
            'password' => $input['password'],
        ]);
        $user->forceFill([
            ...User::consentAttributes(),
            // Sent as referral_code on POST customers by ProcessVerifiedUser, which runs later.
            'signup_referral_code' => config('kadi.referrals.enabled') ? $input['referral_code'] : null,
        ])->save();

        ReferralCode::forget(request());

        // The linked kadi account needs a password that matches what the user
        // registered with, so the game site can verify logins via
        // password_verify(). We hand the job a one-way BCRYPT HASH — never the
        // plaintext — so no recoverable secret is stored in cache or queue
        // payloads (audit finding C-1).
        Cache::put(
            "user.kadi_password_hash.{$user->id}",
            Hash::make($input['password']),
            now()->addHours(24)
        );
        $user->assignRole('player');

        // $user->sendEmailVerificationNotification();

        return $user;
    }
}
