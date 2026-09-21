<?php

namespace App\Rules;

use App\Models\User;
use App\Support\PhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A valid Kenyan mobile number that no other player has. The value is normalised first, so
 * "+254 712 345 678", "0712345678" and "254712345678" are the same number.
 */
class KenyanPhone implements ValidationRule
{
    public function __construct(private readonly ?int $ignoreUserId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $phone = PhoneNumber::normalize(is_string($value) ? $value : null);

        if ($phone === null || ! PhoneNumber::isValid($phone)) {
            $fail('Enter a valid Kenyan mobile number, e.g. 0712 345 678.');

            return;
        }

        $taken = User::query()
            ->where('phone', $phone)
            ->when($this->ignoreUserId, fn ($q) => $q->where('id', '!=', $this->ignoreUserId))
            ->exists();

        if ($taken) {
            $fail('This phone number is already registered to another account.');
        }
    }
}
