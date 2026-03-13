<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum UserRole: int implements HasLabel
{
    public function getLabel(): ?string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::CUISINIER => 'Cuisinier',
            self::LIVREUR => 'Livreur',
            self::CLIENT => 'Client',
        };
    }
    case ADMIN = 1;
    case CUISINIER = 2;
    case LIVREUR = 3;
    case CLIENT = 4;
}
