<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Import = 'import';
    case Sale = 'sale';
    case CancelRelease = 'cancel_release';
    case RefundRestock = 'refund_restock';
    case Adjustment = 'adjustment';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Import => 'Nhập kho',
            self::Sale => 'Bán hàng',
            self::CancelRelease => 'Hoàn tồn do hủy',
            self::RefundRestock => 'Hoàn hàng nhập lại',
            self::Adjustment => 'Điều chỉnh',
        };
    }
}
