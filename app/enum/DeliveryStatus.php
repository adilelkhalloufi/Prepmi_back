<?php

namespace App\Enum;

enum DeliveryStatus: string
{
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ASSIGNED => 'Assigned to Courier',
            self::PICKED_UP => 'Picked Up',
            self::IN_TRANSIT => 'In Transit',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Delivered',
            self::FAILED_DELIVERY => 'Failed Delivery',
            self::RETURNED => 'Returned',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isCompleted(): bool
    {
        return in_array($this, [self::DELIVERED, self::RETURNED]);
    }
    case PENDING = 'pending';
    case ASSIGNED = 'assigned';
    case PICKED_UP = 'picked_up';
    case IN_TRANSIT = 'in_transit';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case FAILED_DELIVERY = 'failed_delivery';
    case RETURNED = 'returned';
}
