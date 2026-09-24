<?php

namespace App\Services;

/**
 * Outcome of a referral wallet withdrawal (POST customers/{id}/referral-wallet/withdraw).
 *
 * - PROCESSING: 201, Safaricom accepted it; the M-Pesa message follows.
 * - PENDING   : 202, a timeout or a gateway error; the outcome is unknown and the money may still
 *               be paid. Never retried automatically; a manual retry reuses the same key.
 * - RESTORED  : 502, Safaricom rejected it and the referral wallet was credited back.
 * - REJECTED  : 400/403/422/429/503/other, nothing was paid.
 */
final class ReferralWithdrawResult
{
    public const PROCESSING = 'processing';

    public const PENDING = 'pending';

    public const RESTORED = 'restored';

    public const REJECTED = 'rejected';

    private function __construct(
        public readonly string $outcome,
        public readonly string $message,
    ) {}

    public static function processing(): self
    {
        return new self(self::PROCESSING, "Payout sent, you'll receive an M-Pesa message shortly.");
    }

    public static function pending(): self
    {
        return new self(self::PENDING, 'Processing, check back later.');
    }

    public static function restored(): self
    {
        return new self(self::RESTORED, 'Payout failed, your balance was restored.');
    }

    public static function rejected(string $message): self
    {
        return new self(self::REJECTED, $message);
    }

    /** The outcome is known, so the next attempt must use a new Idempotency-Key. */
    public function isFinal(): bool
    {
        return $this->outcome !== self::PENDING;
    }
}
