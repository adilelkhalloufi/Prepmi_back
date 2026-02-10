<?php

namespace App\Enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SlotType: string implements HasColor, HasLabel
{
    case MEMBERSHIP = 'membership';
    case NORMAL = 'normal';
    case BOTH = 'both';

    public function getLabel(): string
    {
        return match ($this) {
            self::MEMBERSHIP => 'Membership Only',
            self::NORMAL => 'Normal Users',
            self::BOTH => 'All Users',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::MEMBERSHIP => 'success',
            self::NORMAL => 'info',
            self::BOTH => 'warning',
        };
    }
}
