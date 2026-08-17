<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum CartStatus: string implements HasColor, HasIcon, HasLabel
{
    case ACTIVE = 'active';
    case CONVERTED = 'converted';
    case ABANDONED = 'abandoned';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ACTIVE => 'primary',
            self::CONVERTED => 'success',
            self::ABANDONED => 'gray',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::ACTIVE => 'iconoir-cart',
            self::CONVERTED => 'heroicon-o-check-circle',
            self::ABANDONED => 'hugeicons-shopping-cart-remove-01',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::CONVERTED => 'Converted',
            self::ABANDONED => 'Abandoned',
        };
    }
}
