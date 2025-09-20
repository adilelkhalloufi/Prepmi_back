<?php

namespace App\enum;

use Filament\Support\Contracts\HasLabel;

enum UserRole: int implements HasLabel
{
    public function getLabel(): ?string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::SELLER => 'Seller',
            self::VENDER => 'Vender',
            self::USER => 'User',
        };
    }
    case ADMIN = 1;
    case SELLER = 2;
    case VENDER = 3;
    case USER = 4;
}
