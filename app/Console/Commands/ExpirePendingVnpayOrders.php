<?php

namespace App\Console\Commands;

use App\Services\Checkout\ExpirePendingVnpayOrdersService;
use Illuminate\Console\Command;

class ExpirePendingVnpayOrders extends Command
{
    protected $signature = 'orders:expire-pending-vnpay';

    protected $description = 'Đánh dấu các đơn VNPay pending quá hạn thành expired';

    public function handle(ExpirePendingVnpayOrdersService $expiration): int
    {
        $count = $expiration->execute();

        $this->info("Đã cập nhật {$count} đơn sang expired.");

        return self::SUCCESS;
    }
}
