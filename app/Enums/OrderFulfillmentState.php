<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum OrderFulfillmentState: string implements HasColor, HasIcon, HasLabel
{
    case NOT_APPLICABLE = 'not_applicable';
    case UNFULFILLED = 'unfulfilled';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case DIGITAL_DELIVERED = 'digital_delivered';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NOT_APPLICABLE => 'gray',
            self::UNFULFILLED => 'warning',
            self::PROCESSING => 'info',
            self::SHIPPED => 'blue',
            self::DELIVERED => 'success',
            self::DIGITAL_DELIVERED => 'purple',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::NOT_APPLICABLE => 'iconoir-minus-circle',
            self::UNFULFILLED => 'heroicon-o-clock',
            self::PROCESSING => 'hugeicons-package-process',
            self::SHIPPED => 'hugeicons-truck-delivery',
            self::DELIVERED => 'heroicon-o-check-badge',
            self::DIGITAL_DELIVERED => 'iconoir-cloud-download',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::NOT_APPLICABLE => 'Not Applicable',
            self::UNFULFILLED => 'Unfulfilled',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::DIGITAL_DELIVERED => 'Digital Delivered',
        };
    }
}
