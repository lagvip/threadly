<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case WaitingForCancellation = 'waiting_for_cancellation'; // chỉ giữ để tương thích dữ liệu cũ

    public static function editableStatuses(): array
    {
        return [
            self::Pending->value,
            self::Processing->value,
            self::Shipped->value,
            self::Delivered->value,
        ];
    }

    public static function progressSteps(): array
    {
        return [
            self::Pending->value,
            self::Processing->value,
            self::Shipped->value,
            self::Delivered->value,
        ];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ xử lý',
            self::Processing => 'Đang xử lý',
            self::Shipped => 'Đang giao hàng',
            self::Delivered => 'Đã giao',
            self::Cancelled => 'Đã hủy',
            self::WaitingForCancellation => 'Chờ duyệt hủy',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Delivered,
            self::Cancelled,
        ], true);
    }

    public function isCancellationRequest(): bool
    {
        return $this === self::WaitingForCancellation;
    }

    /**
     * Chỉ kiểm tra luồng trạng thái chung.
     * Rule liên quan thanh toán (vd VNPay đã thanh toán không được hủy)
     * phải kiểm tra ở Order model / Controller.
     */
    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Pending => in_array($next, [
                self::Processing,
                self::Cancelled,
            ], true),

            self::Processing => in_array($next, [
                self::Shipped,
                self::Cancelled,
            ], true),

            self::Shipped => in_array($next, [
                self::Delivered,
            ], true),

            // Chỉ giữ để xử lý dữ liệu cũ nếu còn tồn tại
            self::WaitingForCancellation => in_array($next, [
                self::Cancelled,
                self::Pending,
                self::Processing,
                self::Shipped,
            ], true),

            self::Delivered,
            self::Cancelled => false,
        };
    }
}
