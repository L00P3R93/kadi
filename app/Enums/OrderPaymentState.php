<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum OrderPaymentState: string implements HasColor, HasIcon, HasLabel
{
    case UNPAID = 'unpaid';
    case AWAITING_PAYMENT = 'awaiting_payment';
    case PAID = 'paid';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case FAILED = 'failed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::UNPAID => 'gray',
            self::AWAITING_PAYMENT => 'warning',
            self::PAID => 'success',
            self::REFUNDED => 'purple',
            self::PARTIALLY_REFUNDED => 'orange',
            self::FAILED => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::UNPAID => 'iconoir-wallet',
            self::AWAITING_PAYMENT => 'heroicon-o-clock',
            self::PAID => 'heroicon-o-check-circle',
            self::REFUNDED => 'hugeicons-money-send-02',
            self::PARTIALLY_REFUNDED => 'iconoir-receive-dollars',
            self::FAILED => 'heroicon-o-x-circle',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::UNPAID => 'Unpaid',
            self::AWAITING_PAYMENT => 'Awaiting Payment',
            self::PAID => 'Paid',
            self::REFUNDED => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
            self::FAILED => 'Failed',
        };
    }
}
