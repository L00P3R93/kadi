<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PaymentEventType: string implements HasColor, HasIcon, HasLabel
{
    case CREATED = 'created';
    case INITIATED = 'initiated';
    case CALLBACK_RECEIVED = 'callback_received';
    case SUCCESSFUL = 'successful';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CREATED => 'gray',
            self::INITIATED => 'info',
            self::CALLBACK_RECEIVED => 'blue',
            self::SUCCESSFUL => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'purple',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::CREATED => 'heroicon-o-document-plus',
            self::INITIATED => 'iconoir-play',
            self::CALLBACK_RECEIVED => 'hugeicons-webhook-02',
            self::SUCCESSFUL => 'heroicon-o-check-circle',
            self::FAILED => 'heroicon-o-x-circle',
            self::REFUNDED => 'iconoir-refresh-circle',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::CREATED => 'Created',
            self::INITIATED => 'Initiated',
            self::CALLBACK_RECEIVED => 'Callback Received',
            self::SUCCESSFUL => 'Successful',
            self::FAILED => 'Failed',
            self::REFUNDED => 'Refunded',
        };
    }
}
