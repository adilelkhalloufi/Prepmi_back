<?php

namespace App\Enum;

enum OrderStatus: string
{
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En Attente',
            self::PREPARING => 'En préparation',
            self::SHIPPED => 'Expédié',
            self::DELIVERED => 'Livré',
            self::CANCELLED => 'Annulé',
         };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

  
    case PENDING = 'Pending';
    case PREPARING = 'Preparing';
    case SHIPPED = 'Shipped';
    case DELIVERED = 'Delivered';
    case CANCELLED = 'Cancelled';
 }
