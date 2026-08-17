<?php

namespace App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ProductStatus: string implements HasColor, HasIcon, HasLabel
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case OUT_OF_STOCK = 'out_of_stock';
    case ARCHIVED = 'archived';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'success',
            self::INACTIVE => 'secondary',
            self::OUT_OF_STOCK => 'warning',
            self::ARCHIVED => 'danger',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::DRAFT => 'hugeicons-file-edit',
            self::ACTIVE => 'hugeicons-checkmark-circle-02',
            self::INACTIVE => 'hugeicons-pause-circle',
            self::OUT_OF_STOCK => 'hugeicons-package-remove',
            self::ARCHIVED => 'hugeicons-archive-02',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::OUT_OF_STOCK => 'Out of Stock',
            self::ARCHIVED => 'Archived',
        };
    }
}
