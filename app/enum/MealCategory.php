<?php

namespace App\enum;

enum MealCategory: int
{
    case MENU = 0;
    case BREAKFAST = 1;
    case DRINKS = 2;

    /**
     * Get the label for the category
     */
    public function label(): string
    {
        return match ($this) {
            self::MENU => 'Menu',
            self::BREAKFAST => 'Breakfast',
            self::DRINKS => 'Drinks',
        };
    }

    /**
     * Get all categories as array
     */
    public static function toArray(): array
    {
        return [
            self::MENU->value => self::MENU->label(),
            self::BREAKFAST->value => self::BREAKFAST->label(),
            self::DRINKS->value => self::DRINKS->label(),
        ];
    }

    /**
     * Get category from value
     */
    public static function fromValue(int $value): ?self
    {
        return match ($value) {
            1 => self::MENU,
            2 => self::BREAKFAST,
            3 => self::DRINKS,
            default => null,
        };
    }
}
