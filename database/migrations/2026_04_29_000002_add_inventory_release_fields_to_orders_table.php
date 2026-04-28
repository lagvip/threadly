<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'stock_released_at')) {
                $table->timestamp('stock_released_at')->nullable()->after('last_refunded_at');
            }

            if (!Schema::hasColumn('orders', 'voucher_released_at')) {
                $table->timestamp('voucher_released_at')->nullable()->after('stock_released_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'voucher_released_at')) {
                $table->dropColumn('voucher_released_at');
            }

            if (Schema::hasColumn('orders', 'stock_released_at')) {
                $table->dropColumn('stock_released_at');
            }
        });
    }
};
