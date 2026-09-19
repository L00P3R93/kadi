<?php

namespace App\Push;

use RuntimeException;

/**
 * Too many system-wide broadcasts recently. Carries how long to wait, for a Retry-After header.
 */
class BroadcastLimitExceeded extends RuntimeException
{
    public function __construct(string $message, public readonly int $retryAfter)
    {
        parent::__construct($message);
    }
}
