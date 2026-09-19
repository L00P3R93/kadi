<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Keeps the game site's `kadi.accounts` table in step with a player's Kadi profile.
 *
 * The game shows only a first name, so `accounts.name` holds the first word of the full name (the
 * same rule ProcessVerifiedUser applies when the account is created).
 */
class KadiAccountSync
{
    /**
     * What goes in `accounts.name` for a full name: its first word.
     */
    public static function accountName(string $fullName): string
    {
        $words = preg_split('/\s+/', trim($fullName), -1, PREG_SPLIT_NO_EMPTY);

        return $words[0] ?? '';
    }

    /**
     * Copy the player's current name to their `kadi.accounts` row. Never throws: a profile save must
     * not fail because the game database is unreachable, so problems are logged instead.
     *
     * The row is found by the customer id (`accounts.id` = `users.linked_id`). Players who are not
     * linked yet, or whose row is missing, are found by e-mail; pass the address they had BEFORE this
     * save in $previousEmail when it may have just changed.
     */
    public function syncName(User $user, ?string $previousEmail = null): bool
    {
        $name = self::accountName($user->name);

        if ($name === '') {
            return false;
        }

        try {
            $accounts = DB::connection('kadi')->table('accounts');

            if ($user->linked_id && (clone $accounts)->where('id', $user->linked_id)->update(['name' => $name]) > 0) {
                return true;
            }

            foreach (array_unique(array_filter([$previousEmail, $user->email])) as $email) {
                if ((clone $accounts)->where('email', $email)->update(['name' => $name]) > 0) {
                    return true;
                }
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('Kadi DB name sync failed for user '.$user->id.': '.$e->getMessage());

            return false;
        }
    }
}
