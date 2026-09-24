<?php

namespace App\Services;

/**
 * Outcome of PUT customers/{id}/referral-code.
 *
 * - SAVED  : 200, data holds code, link and qr_code.
 * - TAKEN  : 409, another player has the code; generate a new one and try again.
 * - INVALID: 422, the code, link or QR code was refused (a bug on our side, do not retry).
 * - ERROR  : anything else (timeout, 5xx, 404 customer).
 */
final class ReferralCodeResult
{
    public const SAVED = 'saved';

    public const TAKEN = 'taken';

    public const INVALID = 'invalid';

    public const ERROR = 'error';

    /** @param  array<string, mixed>  $data */
    private function __construct(
        public readonly string $outcome,
        public readonly array $data = [],
    ) {}

    /** @param  array<string, mixed>  $data */
    public static function saved(array $data): self
    {
        return new self(self::SAVED, $data);
    }

    public static function taken(): self
    {
        return new self(self::TAKEN);
    }

    public static function invalid(): self
    {
        return new self(self::INVALID);
    }

    public static function error(): self
    {
        return new self(self::ERROR);
    }
}
