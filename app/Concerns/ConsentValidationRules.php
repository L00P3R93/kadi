<?php

namespace App\Concerns;

trait ConsentValidationRules
{
    /**
     * Rules for the 18+ confirmation and terms acceptance checkboxes.
     *
     * @return array<string, array<int, string>>
     */
    protected function consentRules(): array
    {
        return [
            'age_confirmed' => ['accepted'],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function consentMessages(): array
    {
        $age = config('kadi.min_age');

        return [
            'age_confirmed.accepted' => "You must be {$age} or older to use Kadi.",
            'terms.accepted' => 'You must accept the Terms & Conditions and Privacy Policy.',
        ];
    }
}
