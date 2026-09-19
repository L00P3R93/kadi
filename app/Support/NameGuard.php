<?php

namespace App\Support;

use App\Models\BlockedName;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Decides whether a display name is allowed, against the editable `blocked_names` table.
 *
 * Names are compared in a normalised form so simple evasions do not work: case, accents,
 * punctuation and spacing ("A.d-M i n"), look-alike digits and symbols ("4dm1n", "@dmin") and
 * stretched letters ("adminnn") all reduce to the same thing.
 *
 * Every name is checked in TWO forms: with look-alikes read as letters ("4dm1n" is "admin"), and with
 * digits and symbols simply dropped ("Admin2026" and "Admin!" are "admin"). One form alone would let
 * either through: read as letters, "Admin1" becomes "admini".
 *
 * Two kinds of entry:
 *  - word (default): blocks the name when the term appears as whole word(s) in it, or when the
 *    whole name collapses to the term. "admin" blocks "Admin" and "Kadi Admin" but not "Sadmin".
 *  - contains: blocks any name containing the term anywhere. Powerful and blunt (names such as
 *    "Scunthorpe" trip over short terms), so use it for long, unambiguous terms only.
 */
class NameGuard
{
    private const CACHE_KEY = 'name-guard.terms';

    /** Look-alike characters people substitute for letters. */
    private const LOOKALIKES = ['0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's', '7' => 't', '@' => 'a', '$' => 's', '!' => 'i', '|' => 'i', '+' => 't'];

    /**
     * Lower-case letters only, single spaces between words, no stretched letters.
     */
    public static function normalise(string $value, bool $lookalikes = true): string
    {
        $value = Str::lower(Str::ascii($value));

        if ($lookalikes) {
            $value = strtr($value, self::LOOKALIKES);
        }

        // Anything that is not a letter separates words ("kadi_support", "kadi.support").
        $value = preg_replace('/[^a-z]+/', ' ', $value) ?? '';
        // "adminnn" -> "admin", "kaadii" -> "kadi": collapse repeated letters.
        $value = preg_replace('/([a-z])\1+/', '$1', $value) ?? '';

        return trim($value);
    }

    /**
     * True when the name is on the blocklist.
     */
    public static function isBlocked(string $name): bool
    {
        return self::matchingTerm($name) !== null;
    }

    /**
     * The blocklist entry that matches, for operators (never shown to the player, so the list
     * cannot be probed for). Null when the name is fine.
     */
    public static function matchingTerm(string $name): ?string
    {
        $forms = array_unique(array_filter([self::normalise($name), self::normalise($name, lookalikes: false)]));

        foreach ($forms as $normalised) {
            $collapsed = str_replace(' ', '', $normalised);
            $padded = " {$normalised} ";

            foreach (self::terms() as $entry) {
                $term = $entry['term'];

                if ($term === '') {
                    continue;
                }

                if ($entry['match'] === BlockedName::CONTAINS) {
                    if (str_contains($collapsed, str_replace(' ', '', $term))) {
                        return $term;
                    }

                    continue;
                }

                // Whole word(s) anywhere in the name, or the whole name spelled with odd spacing
                // ("k a d i", "customercare").
                if (str_contains($padded, " {$term} ") || $collapsed === str_replace(' ', '', $term)) {
                    return $term;
                }
            }
        }

        return null;
    }

    /**
     * @return list<array{term: string, match: string}>
     */
    public static function terms(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => BlockedName::query()
            ->get(['term', 'match'])
            // Normalised again on the way out, so a row inserted by hand (or by an older version of the
            // rules) still matches the way names are compared.
            ->map(fn (BlockedName $blocked) => ['term' => self::normalise($blocked->term), 'match' => $blocked->match])
            ->all());
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
