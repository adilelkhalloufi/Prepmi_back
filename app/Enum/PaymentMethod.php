<?php

namespace App\Enum;

enum PaymentMethod: string
{
    public function label(): string
    {
        return match ($this) {
            self::CREDIT_CARD => 'Credit Card',
            self::DEBIT_CARD => 'Debit Card',
            self::PAYPAL => 'PayPal',
            self::STRIPE => 'Stripe',
            self::APPLE_PAY => 'Apple Pay',
            self::GOOGLE_PAY => 'Google Pay',
            self::BANK_TRANSFER => 'Bank Transfer',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';
    case APPLE_PAY = 'apple_pay';
    case GOOGLE_PAY = 'google_pay';
    case BANK_TRANSFER = 'bank_transfer';
}
