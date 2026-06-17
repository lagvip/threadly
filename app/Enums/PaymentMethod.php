<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Vnpay = 'vnpay';
    case Cod = 'cod';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Vnpay => 'VNPay',
            self::Cod => 'Thanh toán khi nhận hàng',
        };
    }
}
