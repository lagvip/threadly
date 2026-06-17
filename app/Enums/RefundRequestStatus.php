<?php

namespace App\Enums;

enum RefundRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ admin duyệt',
            self::Approved => 'Đã hoàn tiền',
            self::Rejected => 'Đã từ chối',
            self::Cancelled => 'Đã hủy yêu cầu',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Pending => 'warning text-dark',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Cancelled => 'secondary',
        };
    }
}
