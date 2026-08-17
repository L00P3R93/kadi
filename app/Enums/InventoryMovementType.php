<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum InventoryMovementType: string implements HasColor, HasIcon, HasLabel
{
    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case RESERVATION = 'reservation';
    case RELEASE = 'release';
    case ADJUSTMENT = 'adjustment';
    case RETURN = 'return';
    case DAMAGE = 'damage';
    case RESTOCK = 'restock';
    case TRANSFER = 'transfer';
    case EXCHANGE = 'exchange';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PURCHASE => 'blue',
            self::SALE => 'success',
            self::RESERVATION => 'warning',
            self::RELEASE => 'info',
            self::ADJUSTMENT => 'orange',
            self::RETURN => 'purple',
            self::DAMAGE => 'danger',
            self::RESTOCK => 'green',
            self::TRANSFER => 'indigo',
            self::EXCHANGE => 'pink',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::PURCHASE => 'hugeicons-shopping-cart-01',
            self::SALE => 'hugeicons-shopping-bag-03',
            self::RESERVATION => 'hugeicons-bookmark-02',
            self::RELEASE => 'hugeicons-lock-unlock',
            self::ADJUSTMENT => 'hugeicons-settings-02',
            self::RETURN => 'hugeicons-arrow-turn-back',
            self::DAMAGE => 'hugeicons-package-remove',
            self::RESTOCK => 'hugeicons-package-add',
            self::TRANSFER => 'hugeicons-truck',
            self::EXCHANGE => 'hugeicons-exchange-01',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PURCHASE => 'Purchase',
            self::SALE => 'Sale',
            self::RESERVATION => 'Reservation',
            self::RELEASE => 'Release',
            self::ADJUSTMENT => 'Adjustment',
            self::RETURN => 'Return',
            self::DAMAGE => 'Damage',
            self::RESTOCK => 'Restock',
            self::TRANSFER => 'Transfer',
            self::EXCHANGE => 'Exchange',
        };
    }
}
