<?php
namespace App\Enums;

enum PaymentMethod: int
{
    case CARD = 0;
    case BANK_TRANSFER = 1;
    case QR = 2;

    public function label(): string
    {
        return match($this) {
            self::CARD => 'カード決済',
            self::BANK_TRANSFER => '振込',
            self::QR => 'QR決済',
        };
    }
}