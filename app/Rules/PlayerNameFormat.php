<?php

namespace App\Rules;

use App\Support\PlayerName;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 4-12 characters; letters, digits, single spaces and _ ! - only. Like AllowedName, a signed-in player who saves
 * without changing their name is not blocked, unless they were sent here to rename.
 */
class PlayerNameFormat implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if ($value === auth()->user()?->name && ! PlayerName::isForced()) {
            return;
        }

        if ($message = PlayerName::formatError($value)) {
            $fail($message);
        }
    }
}
