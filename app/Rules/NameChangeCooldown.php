<?php

namespace App\Rules;

use App\Support\PlayerName;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A name can be changed once a year. Does nothing at registration (nobody is signed in), when the
 * name is unchanged, or when the player was forced to rename.
 */
class NameChangeCooldown implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = auth()->user();

        if (! $user || ! is_string($value) || $value === $user->name) {
            return;
        }

        if ($next = PlayerName::nextChangeAt($user)) {
            $fail('You can change your name once a year. You can change it again on '.$next->format('j F Y').'.');
        }
    }
}
