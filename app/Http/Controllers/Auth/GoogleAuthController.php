<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Google authentication failed. Please try again.']);
        }

        // Case a: existing user matched by google_id
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            if (is_null($user->email_verified_at)) {
                $user->update(['email_verified_at' => now()]);
            }

            Auth::login($user, remember: true);

            return redirect()->intended(route('dashboard'));
        }

        // Case b: existing user matched by email — link the account
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $updateData = [
                'google_id' => $googleUser->getId(),
                'avatar'    => $googleUser->getAvatar(),
            ];

            if (is_null($user->email_verified_at)) {
                $updateData['email_verified_at'] = now();
            }

            $user->update($updateData);

            Auth::login($user, remember: true);

            return redirect()->intended(route('dashboard'));
        }

        // Case c: no user found — create a new account
        $user = User::create([
            'name'              => $googleUser->getName(),
            'email'             => $googleUser->getEmail(),
            'google_id'         => $googleUser->getId(),
            'avatar'            => $googleUser->getAvatar(),
            'password'          => Hash::make(Str::random(32)),
            'account_no'        => 'KK-' . strtoupper(uniqid()),
            'email_verified_at' => now(),
        ]);

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}
