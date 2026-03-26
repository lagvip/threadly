<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class ExpirePendingVnpayOrders extends Command
{
    protected $signature = 'orders:expire-pending-vnpay';
    protected $description = 'Đánh dấu các đơn VNPay pending quá hạn thành expired';

    public function handle(): int
    {
        $count = Order::where('payment_method', 'vnpay')
        ->where('payment_status', 'pending')
        ->where('order_status', 'pending')
        ->where('created_at', '<', now()->subMinutes(15))
        ->update([
            'previous_status' => 'pending',
            'order_status'    => 'cancelled',
            'payment_status'  => 'expired',
            'cancel_reason'   => 'Quá hạn thanh toán VNPay',
        ]);

        $this->info("Đã cập nhật {$count} đơn sang expired.");

        return self::SUCCESS;
    }

}
