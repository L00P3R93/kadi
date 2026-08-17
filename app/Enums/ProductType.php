<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProductType: string implements HasColor, HasIcon, HasLabel
{
    case PHYSICAL_PRODUCT = 'physical_product';
    case DIGITAL_PRODUCT = 'digital_product';
    case VOUCHER = 'voucher';
    case GIFT_CARD = 'gift_card';
    case REWARD = 'reward';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PHYSICAL_PRODUCT => 'blue',
            self::DIGITAL_PRODUCT => 'purple',
            self::VOUCHER => 'orange',
            self::GIFT_CARD => 'pink',
            self::REWARD => 'amber',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::PHYSICAL_PRODUCT => 'hugeicons-package',
            self::DIGITAL_PRODUCT => 'hugeicons-file-02',
            self::VOUCHER => 'hugeicons-ticket-01',
            self::GIFT_CARD => 'hugeicons-gift',
            self::REWARD => 'hugeicons-medal-01',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::PHYSICAL_PRODUCT => 'Physical Product',
            self::DIGITAL_PRODUCT => 'Digital Product',
            self::VOUCHER => 'Voucher',
            self::GIFT_CARD => 'Gift Card',
            self::REWARD => 'Reward',
        };
    }
}
