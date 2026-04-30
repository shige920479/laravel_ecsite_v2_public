<?php
namespace App\Enums;

enum ShippingStatus: string
{
    case UNSHIPPED = 'unshipped';
    case PREPARING = 'preparing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELED = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::UNSHIPPED => '未発送',
            self::PREPARING => '発送準備',
            self::SHIPPED => '発送済み',
            self::DELIVERED => '配送完了',
            self::CANCELED => 'キャンセル'
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::UNSHIPPED => 'bg-green-100 text-green-700',
            self::PREPARING => 'bg-yellow-100 text-yellow-700',
            self::SHIPPED => 'bg-blue-100 text-blue-600',
            self::DELIVERED => 'bg-gray-100 text-gray-500',
            self::CANCELED => 'bg-red-100 text-red-500',
        };
    }
}
