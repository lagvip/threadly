<?php

namespace App\Enums;

enum RefundRequestType: string
{
    case Full = 'full';
    case Partial = 'partial';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Full => 'Hoàn toàn bộ tiền hàng',
            self::Partial => 'Hoàn một phần tiền hàng',
        };
    }
}
