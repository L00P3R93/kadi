<?php

namespace App\Rules;

use App\Support\NameGuard;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects names on the blocklist (App\Support\NameGuard) when someone registers or changes their
 * name. It is NOT used for Google sign-ups: Google has already verified that name.
 *
 * A signed-in player who saves their profile without changing their name is never blocked, even if
 * the list grew after they joined; only a name they newly choose is checked.
 *
 * The message deliberately does not say which term matched.
 */
class AllowedName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $current = auth()->user()?->name;

        if ($current !== null && NameGuard::normalise($current) === NameGuard::normalise($value)) {
            return;
        }

        if (NameGuard::isBlocked($value)) {
            $fail('That name is not available. Please use your own name.');
        }
    }
}
