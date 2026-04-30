<?php
namespace App\Enums;

enum CheckoutStatus: int
{
    case PENDING = 0;
    case COMPLETED = 1;
    case EXPIRED = 2;
    case FAILED = 3;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => '進行中',
            self::COMPLETED => '決済成功',
            self::EXPIRED => '期限切れ',
            self::FAILED => '不成立',
            
        };
    }
}