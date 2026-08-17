<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PromotionStatus: string implements HasColor, HasIcon, HasLabel
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case EXPIRED = 'expired';
    case ARCHIVED = 'archived';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SCHEDULED => 'blue',
            self::ACTIVE => 'success',
            self::PAUSED => 'warning',
            self::EXPIRED => 'danger',
            self::ARCHIVED => 'secondary',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-document',
            self::SCHEDULED => 'iconoir-calendar',
            self::ACTIVE => 'heroicon-o-play-circle',
            self::PAUSED => 'hugeicons-pause-circle',
            self::EXPIRED => 'iconoir-clock-outline',
            self::ARCHIVED => 'hugeicons-archive-02',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SCHEDULED => 'Scheduled',
            self::ACTIVE => 'Active',
            self::PAUSED => 'Paused',
            self::EXPIRED => 'Expired',
            self::ARCHIVED => 'Archived',
        };
    }
}
