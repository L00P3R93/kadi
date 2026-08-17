<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PaymentMethod: string implements HasColor, HasIcon, HasLabel
{
    case M_PESA = 'm-pesa';
    case COINS = 'coins';
    case MIXED = 'mixed';
    case AIRTEL_MONEY = 'airtel-money';
    case CARD = 'card';
    case OTHER = 'other';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::M_PESA => 'green',
            self::COINS => 'amber',
            self::MIXED => 'purple',
            self::AIRTEL_MONEY => 'red',
            self::CARD => 'blue',
            self::OTHER => 'gray',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::M_PESA => 'hugeicons-smart-phone-01',
            self::COINS => 'hugeicons-coins-01',
            self::MIXED => 'iconoir-merge',
            self::AIRTEL_MONEY => 'iconoir-smartphone-device',
            self::CARD => 'heroicon-o-credit-card',
            self::OTHER => 'iconoir-wallet',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::M_PESA => 'M-Pesa',
            self::COINS => 'Wallet Coins',
            self::MIXED => 'Mixed Payment',
            self::AIRTEL_MONEY => 'Airtel Money',
            self::CARD => 'Card',
            self::OTHER => 'Other',
        };
    }
}
