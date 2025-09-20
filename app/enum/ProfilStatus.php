<?php

namespace App\enum;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProfilStatus: int implements HasColor, HasLabel
{
    case ACTIF = 1;
    case INACTIF = 2;
    case PENDING = 3;
    case DELETED = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIF => 'Actif',
            self::INACTIF => 'Inactif',
            self::PENDING => 'Pending',
            self::DELETED => 'Deleted',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ACTIF => 'success',
            self::INACTIF => 'gray',
            self::PENDING => 'warning',
            self::DELETED => 'danger',
        };
    }
}
