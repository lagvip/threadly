<?php

namespace App\Enums;

enum VoucherType: string
{
    case Percent = 'percent';
    case Fixed = 'fixed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Percent => 'Giảm theo phần trăm',
            self::Fixed => 'Giảm số tiền cố định',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Percent => 'info',
            self::Fixed => 'warning text-dark',
        };
    }

    public function unit(): string
    {
        return match ($this) {
            self::Percent => '%',
            self::Fixed => '₫',
        };
    }
}
