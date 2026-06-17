<?php

namespace App\Enums;

enum OrderRefundStatus: string
{
    case None = 'none';
    case Requested = 'requested';
    case PartiallyRefunded = 'partially_refunded';
    case Refunded = 'refunded';
    case Rejected = 'rejected';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::None => 'Chưa hoàn tiền',
            self::Requested => 'Đang chờ duyệt hoàn tiền',
            self::PartiallyRefunded => 'Đã hoàn một phần',
            self::Refunded => 'Đã hoàn hết giá trị sản phẩm',
            self::Rejected => 'Yêu cầu hoàn bị từ chối',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Requested => 'bg-warning text-dark',
            self::PartiallyRefunded => 'bg-info',
            self::Refunded => 'bg-danger',
            self::Rejected => 'bg-secondary',
            self::None => 'bg-light text-dark',
        };
    }
}
