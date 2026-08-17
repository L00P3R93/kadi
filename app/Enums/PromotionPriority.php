<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PromotionPriority: int implements HasColor, HasIcon, HasLabel
{
    case Low = 1;
    case Medium = 2;
    case High = 3;

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Low => 'iconoir-priority-down',
            self::Medium => 'iconoir-priority-medium',
            self::High => 'iconoir-priority-up',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Low => 'danger',
            self::Medium => 'warning',
            self::High => 'indigo',
        };
    }
}
