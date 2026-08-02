<?php

namespace App\Enums;

use App\Interfaces\HasColor;
use App\Interfaces\HasIcon;
use App\Interfaces\HasLabel;

enum CampaignStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Active = 'active';
    case Paused = 'paused';
    case Rejected = 'rejected';
    case Exhausted = 'exhausted';
    case Completed = 'completed';

    public function label(): string
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

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'border-gray-700 bg-gray-900/30 text-gray-400',
            self::PendingReview => 'border-orange-700 bg-orange-900/30 text-orange-400',
            self::Active => 'border-green-800 bg-green-950/30 text-green-400',
            self::Paused => 'border-yellow-700 bg-yellow-900/30 text-yellow-400',
            self::Rejected, self::Exhausted => 'border-red-800 bg-red-950/30 text-red-400',
            self::Completed => 'border-blue-800 bg-blue-950/30 text-blue-400',
        };
    }

    public function icon(): string
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
}
