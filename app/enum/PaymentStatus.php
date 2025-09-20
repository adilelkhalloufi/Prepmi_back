<?php

namespace App\Enum;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SUCCEEDED = 'succeeded';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::SUCCEEDED => 'Succeeded',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially Refunded',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isSuccessful(): bool
    {
        return $this === self::SUCCEEDED;
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::SUCCEEDED, self::FAILED, self::CANCELLED, self::REFUNDED]);
    }
}
