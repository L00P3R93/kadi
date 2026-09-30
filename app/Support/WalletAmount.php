<?php

namespace App\Support;

/**
 * How a KadiApi wallet balance is shown: whole shillings, rounded down. A balance is never shown
 * higher than it is (1,499.60 is "1,499", not "1,500"), which is also what a player can withdraw.
 */
class WalletAmount
{
    public static function format(float|int|string|null $amount): string
    {
        // Round to cents first so float noise (99.99999999 for 100.00) is not floored a shilling down.
        return number_format(floor(round((float) $amount, 2)));
    }
}
