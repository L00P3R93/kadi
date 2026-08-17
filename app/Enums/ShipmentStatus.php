<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ShipmentStatus: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case READY = 'ready';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case IN_TRANSIT = 'in_transit';
    case RETURNED = 'returned';
    case FAILED = 'failed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::PROCESSING => 'info',
            self::READY => 'blue',
            self::SHIPPED => 'indigo',
            self::DELIVERED => 'purple',
            self::IN_TRANSIT => 'green',
            self::RETURNED => 'danger',
            self::FAILED => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::PENDING => 'iconoir-clock',
            self::PROCESSING => 'hugeicons-package-process',
            self::READY => 'iconoir-package',
            self::SHIPPED => 'hugeicons-truck-delivery',
            self::DELIVERED => 'heroicon-o-home-modern',
            self::IN_TRANSIT => 'iconoir-check-circle',
            self::RETURNED => 'heroicon-o-x-circle',
            self::FAILED => 'iconoir-warning-circle',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::READY => 'Ready',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::IN_TRANSIT => 'In Transit',
            self::RETURNED => 'Returned',
            self::FAILED => 'Failed',
        };
    }
}
