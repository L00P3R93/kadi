<?php

namespace App\Support;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * The rules for a player name: 4 to 12 characters, ASCII letters and digits plus `_`, `!` and `-`
 * and single spaces between words (so no emojis, and no leading, trailing or double spaces), not on the blocklist (NameGuard), and changeable once a year.
 *
 * A player whose current name breaks these rules (for example after the blocklist grew) is flagged in
 * the session at login by FlagNameUpdateRequired and must pick a new name before continuing. A forced
 * rename skips the once-a-year limit and does not start it.
 *
 * The client-side check in resources/views/components/player-name-field.blade.php mirrors the format
 * rules; keep the two in step.
 */
class PlayerName
{
    public const MIN = 4;

    public const MAX = 12;

    public const SESSION_KEY = 'name.update_required';

    public const CHARS_MESSAGE = 'Use only letters, numbers, spaces and the symbols _ ! - (no emojis, and no leading, trailing or double spaces).';

    public const LENGTH_MESSAGE = 'Your name must be between '.self::MIN.' and '.self::MAX.' characters.';

    /**
     * The problem with the name's format, or null when it is fine.
     */
    public static function formatError(string $name): ?string
    {
        if (preg_match('/\A(?:[A-Za-z0-9_!\-]+(?: [A-Za-z0-9_!\-]+)*)?\z/', $name) !== 1) {
            return self::CHARS_MESSAGE;
        }

        $length = strlen($name);

        return $length < self::MIN || $length > self::MAX ? self::LENGTH_MESSAGE : null;
    }

    /**
     * A valid, unblocked name derived from an existing one ("Wanjiru Kamau" -> "Wanjiru"), used to
     * pre-fill the rename page. Empty when nothing sensible can be made of it.
     */
    public static function suggest(string $name): string
    {
        $clean = preg_replace('/[^A-Za-z0-9_! \-]+/', '', Str::ascii($name)) ?? '';
        $words = preg_split('/ +/', trim($clean), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $suggestion = '';
        foreach ($words as $word) {
            $next = $suggestion === '' ? $word : "{$suggestion} {$word}";

            if (strlen($next) > self::MAX) {
                break;
            }

            $suggestion = $next;
        }

        // The first word alone may already be too long.
        if ($suggestion === '' && $words !== []) {
            $suggestion = substr($words[0], 0, self::MAX);
        }

        if (self::formatError($suggestion) !== null || NameGuard::isBlocked($suggestion)) {
            return '';
        }

        return $suggestion;
    }

    /**
     * True when the player's stored name no longer meets the format or blocklist rules.
     */
    public static function needsUpdate(User $user): bool
    {
        return self::formatError($user->name) !== null || NameGuard::isBlocked($user->name);
    }

    /**
     * True while this session must rename before doing anything else.
     */
    public static function isForced(): bool
    {
        return app()->bound('session.store') && (bool) session()->get(self::SESSION_KEY);
    }

    /**
     * When the player may next change their name, or null when they may do so now.
     */
    public static function nextChangeAt(User $user): ?CarbonInterface
    {
        if (self::isForced() || $user->name_changed_at === null) {
            return null;
        }

        $next = $user->name_changed_at->copy()->addYear();

        return $next->isFuture() ? $next : null;
    }

    /**
     * Call before saving a user: a voluntary name change starts the one-year wait.
     */
    public static function stampChange(User $user): void
    {
        if ($user->isDirty('name') && ! self::isForced()) {
            $user->name_changed_at = now();
        }
    }

    /**
     * Call after saving a user: lifts the forced-rename flag once the name is acceptable.
     */
    public static function clearIfResolved(User $user): void
    {
        if (app()->bound('session.store') && ! self::needsUpdate($user)) {
            session()->forget(self::SESSION_KEY);
        }
    }
}
