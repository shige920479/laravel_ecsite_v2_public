<?php
namespace App\Enums;

enum OrderStatus: int
{
    case UNPAID = 0;
    case PAID = 1;
    case CANCELLED = 2;
    case IN_REFUND = 3;
    case REFUNDED = 4;
    
    public function label(): string
    {
        return match($this) {
            self::UNPAID => '未払い',
            self::PAID => '支払済み',
            self::CANCELLED => 'キャンセル',
            self::IN_REFUND => '返金処理中',
            self::REFUNDED => '返金済み',
        };
    }
}