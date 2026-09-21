<?php

namespace App\Services;

/**
 * Outcome of a KadiApi withdrawal request.
 *
 * - SUCCESS : B2C accepted; wallet debited.
 * - REJECTED: definitively not paid out (any debit was reversed); safe to retry.
 * - UNKNOWN : timeout/connection/gateway error; the wallet may have been
 *             debited, so the user must check history before retrying.
 */
final class WithdrawResult
{
    public const SUCCESS = 'success';

    public const REJECTED = 'rejected';

    public const UNKNOWN = 'unknown';

    private function __construct(
        public readonly string $outcome,
        public readonly ?string $ledgerEntryId = null,
        public readonly ?string $message = null,
    ) {}

    public static function success(?string $ledgerEntryId = null): self
    {
        return new self(self::SUCCESS, $ledgerEntryId);
    }

    public static function rejected(string $message): self
    {
        return new self(self::REJECTED, null, $message);
    }

    public static function unknown(): self
    {
        return new self(self::UNKNOWN);
    }

    public function succeeded(): bool
    {
        return $this->outcome === self::SUCCESS;
    }

    public function isUnknown(): bool
    {
        return $this->outcome === self::UNKNOWN;
    }
}
