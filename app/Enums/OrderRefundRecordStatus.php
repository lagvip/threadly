<?php

namespace App\Enums;

enum OrderRefundRecordStatus: string
{
    case Requested = 'requested';
    case Processing = 'processing';
    case Success = 'success';
    case Failed = 'failed';
    case Rejected = 'rejected';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Đã tạo yêu cầu',
            self::Processing => 'Đang xử lý',
            self::Success => 'Thành công',
            self::Failed => 'Thất bại',
            self::Rejected => 'Bị từ chối',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Requested => 'secondary',
            self::Processing => 'warning',
            self::Success => 'success',
            self::Failed => 'danger',
            self::Rejected => 'dark',
        };
    }
}
