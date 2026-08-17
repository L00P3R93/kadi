<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PromotionType: string implements HasColor, HasIcon, HasLabel
{
    case PERCENTAGE_DISCOUNT = 'percentage_discount';
    case FIXED_DISCOUNT = 'fixed_discount';
    case COIN_DISCOUNT = 'coin_discount';
    case SPECIAL_COIN_PRICE = 'special_coin_price';
    case BUY_X_GET_Y = 'buy_x_get_y';
    case FREE_SHIPPING = 'free_shipping';

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::PERCENTAGE_DISCOUNT => 'heroicon-o-percent-badge',
            self::FIXED_DISCOUNT => 'iconoir-dollar-circle',
            self::COIN_DISCOUNT => 'hugeicons-coins-01',
            self::SPECIAL_COIN_PRICE => 'heroicon-o-tag',
            self::BUY_X_GET_Y => 'iconoir-gift',
            self::FREE_SHIPPING => 'hugeicons-truck-delivery',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PERCENTAGE_DISCOUNT => 'Percentage Discount',
            self::FIXED_DISCOUNT => 'Fixed Discount',
            self::COIN_DISCOUNT => 'Coin Discount',
            self::SPECIAL_COIN_PRICE => 'Special Coin Price',
            self::BUY_X_GET_Y => 'Buy X, Get Y',
            self::FREE_SHIPPING => 'Free Shipping',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PERCENTAGE_DISCOUNT => 'orange',
            self::FIXED_DISCOUNT => 'blue',
            self::COIN_DISCOUNT => 'amber',
            self::SPECIAL_COIN_PRICE => 'purple',
            self::BUY_X_GET_Y => 'pink',
            self::FREE_SHIPPING => 'green',
        };
    }
}
