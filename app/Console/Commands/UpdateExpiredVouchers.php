<?php

namespace App\Console\Commands;

use App\Services\Admin\Vouchers\AdminVoucherService;
use Illuminate\Console\Command;

class UpdateExpiredVouchers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voucher:update-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động cập nhật trạng thái voucher hết hạn thành expired';

    /**
     * Execute the console command.
     */
    public function handle(AdminVoucherService $vouchers)
    {
        $count = $vouchers->expireEndedVouchers();

        if ($count === 0) {
            $this->info('Không có voucher hết hạn nào để cập nhật.');

            return Command::SUCCESS;
        }

        $this->info("Đã cập nhật {$count} voucher hết hạn.");

        return Command::SUCCESS;
    }
}
