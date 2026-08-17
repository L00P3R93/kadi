<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case PENDING = 'pending';
    case AWAITING_PAYMENT = 'awaiting_payment';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case READY_FOR_FULFILLMENT = 'ready_for_fulfillment';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::AWAITING_PAYMENT => 'warning',
            self::PAID => 'success',
            self::PROCESSING => 'info',
            self::READY_FOR_FULFILLMENT => 'blue',
            self::SHIPPED => 'indigo',
            self::DELIVERED => 'purple',
            self::COMPLETED => 'green',
            self::CANCELLED => 'danger',
            self::FAILED => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::PENDING => 'iconoir-clock',
            self::AWAITING_PAYMENT => 'heroicon-o-credit-card',
            self::PAID => 'heroicon-o-check-circle',
            self::PROCESSING => 'hugeicons-package-process',
            self::READY_FOR_FULFILLMENT => 'iconoir-package',
            self::SHIPPED => 'hugeicons-truck-delivery',
            self::DELIVERED => 'heroicon-o-home-modern',
            self::COMPLETED => 'iconoir-check-circle',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::FAILED => 'iconoir-warning-circle',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::AWAITING_PAYMENT => 'Awaiting Payment',
            self::PAID => 'Paid',
            self::PROCESSING => 'Processing',
            self::READY_FOR_FULFILLMENT => 'Ready for Fulfillment',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::FAILED => 'Failed',
        };
    }
}
