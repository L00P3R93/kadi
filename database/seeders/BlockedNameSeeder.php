<?php

namespace Database\Seeders;

use App\Models\BlockedName;
use App\Support\NameGuard;
use Illuminate\Database\Seeder;

/**
 * Seeds the names players may not register or change to (see App\Support\NameGuard).
 *
 * Safe to run again at any time, including on production:
 *   php artisan db:seed --class=BlockedNameSeeder
 * It only ADDS entries that are missing. An entry an operator already has (even one they edited
 * with `blocked-names:add --contains`) is left exactly as it is. An entry an operator REMOVED will
 * come back on the next run, so remove it again or trim the list below.
 *
 * Terms are "word" entries (blocked as a whole word, so "admin" blocks "Kadi Admin" but not
 * "Sadmin") unless listed under CONTAINS, which blocks the text anywhere in a name and is reserved
 * for long, unambiguous terms. Matching ignores case, accents, spacing, punctuation and look-alike
 * digits ("4dm1n", "Admin2026").
 *
 * Review the profanity for your community and extend it with `php artisan blocked-names:add`.
 */
class BlockedNameSeeder extends Seeder
{
    /**
     * Names that would let someone pass for Kadi or its staff.
     *
     * @var list<string>
     */
    private const IMPERSONATION = [
        'admin', 'admins', 'administrator', 'sysadmin', 'superadmin', 'super admin', 'superuser', 'moderator', 'mod',
        'support', 'staff', 'official', 'operator', 'security', 'team', 'manager', 'developer', 'webmaster',
        'owner', 'founder', 'ceo', 'system', 'root',
        'kadi', 'kadionline', 'kadi online', 'kadi support', 'kadi admin', 'kadi team', 'kadi staff', 'kadi official',
        'customer care', 'customer service', 'customercare', 'helpdesk', 'help desk', 'service desk',
    ];

    /**
     * Payment and brand names that could be used to trick players.
     *
     * @var list<string>
     */
    private const BRANDS = [
        'mpesa', 'm pesa', 'safaricom', 'airtel', 'paypal', 'visa', 'mastercard', 'western union',
    ];

    /**
     * Placeholders and technical values that are not a real name.
     *
     * @var list<string>
     */
    private const PLACEHOLDERS = [
        'null', 'nil', 'undefined', 'none', 'unknown', 'anonymous', 'nobody', 'no name', 'noname',
        'test', 'tester', 'testing', 'user', 'username', 'player', 'guest', 'bot', 'robot',
        'name', 'first name', 'last name', 'full name', 'firstname', 'lastname', 'fullname',
        'asdf', 'qwerty', 'abc', 'xxx',
    ];

    /**
     * Profanity and slurs, blocked as whole words.
     *
     * @var list<string>
     */
    private const PROFANITY = [
        'shit', 'bitch', 'asshole', 'bastard', 'pussy', 'whore', 'slut', 'cunt', 'penis', 'vagina', 'porn', 'sex',
        'faggot', 'retard', 'rapist', 'nazi', 'hitler',
        // Swahili / Sheng
        'malaya', 'kuma', 'mkundu', 'shenzi', 'pumbavu',
    ];

    /**
     * Long, unambiguous terms, blocked anywhere inside a name (so "xfuckx" and "f u c k" are caught).
     *
     * @var list<string>
     */
    private const CONTAINS = [
        'fuck', 'nigger', 'nigga',
    ];

    public function run(): void
    {
        $this->seed(self::IMPERSONATION, BlockedName::WORD, 'Impersonation');
        $this->seed(self::BRANDS, BlockedName::WORD, 'Brand or payment name');
        $this->seed(self::PLACEHOLDERS, BlockedName::WORD, 'Placeholder, not a real name');
        $this->seed(self::PROFANITY, BlockedName::WORD, 'Profanity');
        $this->seed(self::CONTAINS, BlockedName::CONTAINS, 'Profanity (anywhere in a name)');

        NameGuard::flush();
    }

    /**
     * @param  list<string>  $terms
     */
    private function seed(array $terms, string $match, string $reason): void
    {
        foreach ($terms as $term) {
            // firstOrCreate: never overwrite something an operator already set.
            BlockedName::firstOrCreate(['term' => NameGuard::normalise($term)], ['match' => $match, 'reason' => $reason]);
        }
    }
}
