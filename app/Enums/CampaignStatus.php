<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum CampaignStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Active = 'active';
    case Paused = 'paused';
    case Rejected = 'rejected';
    case Exhausted = 'exhausted';
    case Completed = 'completed';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingReview => 'Pending Review',
            self::Active => 'Active',
            self::Paused => 'Paused',
            self::Rejected => 'Rejected',
            self::Exhausted => 'Exhausted',
            self::Completed => 'Completed',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-square',
            self::PendingReview => 'heroicon-o-clock',
            self::Active => 'heroicon-o-check-circle',
            self::Paused => 'heroicon-o-pause-circle',
            self::Rejected => 'heroicon-o-x-circle',
            self::Exhausted => 'heroicon-o-battery-0',
            self::Completed => 'heroicon-o-flag',
        };

    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::PendingReview => 'orange',
            self::Active => 'success',
            self::Paused => 'warning',
            self::Rejected, self::Exhausted => 'danger',
            self::Completed => 'primary',
        };
    }
}
