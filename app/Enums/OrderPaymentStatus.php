<?php

namespace App\Enums;

enum OrderPaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Chưa thanh toán',
            self::Pending => 'Chờ thanh toán',
            self::Paid => 'Đã thanh toán',
            self::Failed => 'Thất bại',
            self::Cancelled => 'Đã hủy',
            self::Expired => 'Hết hạn',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Pending => 'warning',
            self::Unpaid => 'secondary',
            self::Failed => 'danger',
            self::Cancelled => 'dark',
            self::Expired => 'secondary',
        };
    }

    public function badgeClass(): string
    {
        return 'bg-'.$this->badge();
    }
}
