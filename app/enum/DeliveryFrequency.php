<?php

namespace App\Enum;

enum DeliveryFrequency: string
{
    public function label(): string
    {
        return match ($this) {
            self::WEEKLY => 'Weekly',
            self::BIWEEKLY => 'Every 2 Weeks',
            self::MONTHLY => 'Monthly',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getDaysInterval(): int
    {
        return match ($this) {
            self::WEEKLY => 7,
            self::BIWEEKLY => 14,
            self::MONTHLY => 30,
        };
    }
    case WEEKLY = 'weekly';
    case BIWEEKLY = 'biweekly';
    case MONTHLY = 'monthly';
}
