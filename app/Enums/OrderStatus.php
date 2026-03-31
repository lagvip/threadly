<?php

namespace App\Enums;

enum OrderStatus: string
{
    // Progress steps
    case Pending    = 'pending';    // Đơn hàng đã xác nhận
    case Processing = 'processing'; // Đơn hàng đang xử lý
    case Shipped    = 'shipped';   // Đơn hàng đang vận chuyển
    case Delivered  = 'delivered'; // Đơn hàng đã giao
    case Cancelled  = 'cancelled'; // Đơn hàng đã hủy
    case WaitingForCancellation = 'waiting_for_cancellation'; // Trạng thái yêu cầu hủy

    /**
     * Những trạng thái admin được phép chỉnh (ngoại trừ delivered, cancelled, returned)
     */
    public static function editableStatuses(): array
    {
        return [
            self::Pending->value,
            self::Processing->value,
            self::Shipped->value,
            self::Delivered->value,
            self::Cancelled->value,  // Đã hủy
            self::WaitingForCancellation->value, // Thêm Waiting_for_cancellation
        ];
    }

    /**
     * 5 bước chính cho progress bar
     */
    public static function progressSteps(): array
    {
        return [
            self::Pending->value,
            self::Processing->value,
            self::Shipped->value,
            self::Delivered->value,
            self::WaitingForCancellation->value, // Thêm trạng thái yêu cầu hủy vào đây
        ];
    }

    /**
     * Tất cả giá trị enum (dùng validate chung)
     */
    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }

    /**
     * Gắn nhãn cho từng trạng thái đơn hàng
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'Đã xác nhận',
            self::Processing => 'Đang xử lý',
            self::Shipped   => 'Đang vận chuyển',
            self::Delivered  => 'Đã giao hàng',
            self::Cancelled  => 'Đã hủy',
            self::WaitingForCancellation => 'Xin huỷ đơn hàng', // Nhãn cho trạng thái "Waiting_for_cancellation"
        };
    }
}
