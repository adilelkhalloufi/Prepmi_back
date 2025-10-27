<?php

namespace App\Enum;

enum OrderStatus: string
{
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::PREPARING => 'Preparing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function canTransitionTo(self $status): bool
    {
        return match ($this) {
            self::PENDING => in_array($status, [self::CONFIRMED, self::CANCELLED]),
            self::CONFIRMED => in_array($status, [self::PREPARING, self::CANCELLED]),
            self::PREPARING => in_array($status, [self::SHIPPED, self::CANCELLED]),
            self::SHIPPED => in_array($status, [self::DELIVERED]),
            self::DELIVERED => in_array($status, [self::REFUNDED]),
            self::CANCELLED => in_array($status, [self::REFUNDED]),
            self::REFUNDED => false,
        };
    }
    case PENDING = 'Pending';
    case CONFIRMED = 'Confirmed';
    case PREPARING = 'Preparing';
    case SHIPPED = 'Shipped';
    case DELIVERED = 'Delivered';
    case CANCELLED = 'Cancelled';
    case REFUNDED = 'Refunded';
}
