<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum MerchandisingCollectionType: string implements HasColor, HasIcon, HasLabel
{
    case HERO = 'hero';
    case FEATURED = 'featured';
    case NEW_ARRIVALS = 'new_arrivals';
    case TRENDING = 'trending';
    case DEALS = 'deals';
    case EDITORIAL = 'editorial';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::HERO => 'primary',
            self::FEATURED => 'purple',
            self::NEW_ARRIVALS => 'green',
            self::TRENDING => 'orange',
            self::DEALS => 'danger',
            self::EDITORIAL => 'blue',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::HERO => 'hugeicons-image-02',
            self::FEATURED => 'hugeicons-star',
            self::NEW_ARRIVALS => 'hugeicons-new-releases',
            self::TRENDING => 'hugeicons-fire',
            self::DEALS => 'hugeicons-sale-tag-02',
            self::EDITORIAL => 'hugeicons-news-01',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::HERO => 'Hero Slides',
            self::FEATURED => 'Featured',
            self::NEW_ARRIVALS => 'New Arrivals',
            self::TRENDING => 'Trending',
            self::DEALS => 'Deals',
            self::EDITORIAL => 'Editorial',
        };
    }
}
