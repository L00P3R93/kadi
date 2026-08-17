<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RedemptionTransactionDirection: string implements HasColor, HasIcon, HasLabel
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DEBIT => 'danger',
            self::CREDIT => 'success',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::DEBIT => 'iconoir-log-out',
            self::CREDIT => 'iconoir-log-in',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::DEBIT => 'Debit',
            self::CREDIT => 'Credit',
        };
    }
}
