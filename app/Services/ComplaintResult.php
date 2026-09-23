<?php

namespace App\Services;

/**
 * Outcome of filing a complaint about a played game with KadiApi.
 *
 * - FILED           : KadiApi created the complaint; $complaint holds its `data`.
 * - REJECTED        : definitively not filed; $message says why in words a player can read, and
 *                     $errors holds any field errors (field => message) from a validation 422.
 * - ALREADY_REPORTED: the game or round is already under an open complaint (409).
 * - UNKNOWN         : timeout/connection/gateway error; the complaint may exist. Retrying with the
 *                     same Idempotency-Key is safe (KadiApi replays the original result).
 */
final class ComplaintResult
{
    public const FILED = 'filed';

    public const REJECTED = 'rejected';

    public const ALREADY_REPORTED = 'already_reported';

    public const UNKNOWN = 'unknown';

    /**
     * @param  array<string, string>  $errors
     */
    private function __construct(
        public readonly string $outcome,
        public readonly array $complaint = [],
        public readonly ?string $message = null,
        public readonly array $errors = [],
    ) {}

    public static function filed(array $complaint): self
    {
        return new self(self::FILED, $complaint);
    }

    /**
     * @param  array<string, string>  $errors
     */
    public static function rejected(string $message, array $errors = []): self
    {
        return new self(self::REJECTED, [], $message, $errors);
    }

    public static function alreadyReported(): self
    {
        return new self(self::ALREADY_REPORTED, [], 'Already reported. This is under review.');
    }

    public static function unknown(): self
    {
        return new self(self::UNKNOWN, [], 'We could not confirm your report was received. Please try again in a minute.');
    }

    public function succeeded(): bool
    {
        return $this->outcome === self::FILED;
    }

    public function isAlreadyReported(): bool
    {
        return $this->outcome === self::ALREADY_REPORTED;
    }

    public function isUnknown(): bool
    {
        return $this->outcome === self::UNKNOWN;
    }

    /** The complaint's reference for the player (`complaint_id`, a UUID). */
    public function reference(): ?string
    {
        return is_string($this->complaint['complaint_id'] ?? null) ? $this->complaint['complaint_id'] : null;
    }
}
