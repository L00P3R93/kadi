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

    /**
     * Copy the player's phone (already 254XXXXXXXXX) to their `kadi.accounts` row. Never throws.
     *
     * `accounts.phone` has no unique index we control, so uniqueness is enforced here: if another
     * account already holds the number, the write is skipped and logged.
     */
    public function syncPhone(User $user): bool
    {
        if (! $user->phone) {
            return false;
        }

        try {
            $accounts = DB::connection('kadi')->table('accounts');

            $takenElsewhere = (clone $accounts)
                ->where('phone', $user->phone)
                ->where('email', '!=', $user->email)
                ->when($user->linked_id, fn ($q) => $q->where('id', '!=', $user->linked_id))
                ->exists();

            if ($takenElsewhere) {
                Log::warning('Kadi DB phone sync skipped for user '.$user->id.': number already on another account');

                return false;
            }

            if ($user->linked_id && (clone $accounts)->where('id', $user->linked_id)->update(['phone' => $user->phone]) > 0) {
                return true;
            }

            return (clone $accounts)->where('email', $user->email)->update(['phone' => $user->phone]) > 0;
        } catch (\Throwable $e) {
            Log::error('Kadi DB phone sync failed for user '.$user->id.': '.$e->getMessage());

            return false;
        }
    }
}
