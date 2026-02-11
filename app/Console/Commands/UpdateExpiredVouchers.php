<?php

namespace App\Console\Commands;

use App\Models\Voucher;
use Carbon\Carbon;
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
    public function handle()
    {
        $now = Carbon::now();

        // Tìm tất cả voucher có ngày kết thúc < hiện tại và status = 'active'
        $expiredVouchers = Voucher::where('end_date', '<', $now)
            ->where('status', 'active')
            ->get();

        if ($expiredVouchers->isEmpty()) {
            $this->info('Không có voucher hết hạn nào để cập nhật.');
            return Command::SUCCESS;
        }

        // Cập nhật status thành 'expired'
        $count = Voucher::where('end_date', '<', $now)
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        $this->info("Đã cập nhật {$count} voucher hết hạn thành trạng thái 'expired'");

        return Command::SUCCESS;
    }
}
