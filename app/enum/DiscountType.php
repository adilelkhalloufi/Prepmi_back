<?php

namespace App\Enum;

enum DiscountType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED_AMOUNT = 'fixed_amount';
    case FREE_SHIPPING = 'free_shipping';
    case BUY_ONE_GET_ONE = 'buy_one_get_one';

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Percentage Discount',
            self::FIXED_AMOUNT => 'Fixed Amount Discount',
            self::FREE_SHIPPING => 'Free Shipping',
            self::BUY_ONE_GET_ONE => 'Buy One Get One',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function requiresValue(): bool
    {
        return in_array($this, [self::PERCENTAGE, self::FIXED_AMOUNT]);
    }
}
