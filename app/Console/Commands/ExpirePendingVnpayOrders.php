<?php

namespace App\Console\Commands;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use Illuminate\Console\Command;

class ExpirePendingVnpayOrders extends Command
{
    protected $signature = 'orders:expire-pending-vnpay';

    protected $description = 'Đánh dấu các đơn VNPay pending quá hạn thành expired';

    public function handle(): int
    {
        $count = Order::where('payment_method', PaymentMethod::Vnpay->value)
            ->where('payment_status', OrderPaymentStatus::Pending->value)
            ->where('order_status', OrderStatus::Pending->value)
            ->where('created_at', '<', now()->subMinutes(15))
            ->update([
                'previous_status' => OrderStatus::Pending->value,
                'order_status' => OrderStatus::Cancelled->value,
                'payment_status' => OrderPaymentStatus::Expired->value,
                'cancel_reason' => 'Quá hạn thanh toán VNPay',
            ]);

        $this->info("Đã cập nhật {$count} đơn sang expired.");

        return self::SUCCESS;
    }
}
