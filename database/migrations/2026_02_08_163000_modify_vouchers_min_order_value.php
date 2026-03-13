<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        // Increase precision so large order values fit (e.g. 11,111,111,111)
        if (!Schema::hasTable('vouchers')) {
            return;
        }

        if (!Schema::hasColumn('vouchers', 'min_order_value')) {
            DB::statement("ALTER TABLE `vouchers` ADD `min_order_value` DECIMAL(13,2) NOT NULL DEFAULT 0");
            return;
        }

        DB::statement("ALTER TABLE `vouchers` MODIFY `min_order_value` DECIMAL(13,2) NOT NULL DEFAULT 0");
    }

    public function down()
    {
        if (!Schema::hasTable('vouchers')) {
            return;
        }

        if (!Schema::hasColumn('vouchers', 'min_order_value')) {
            return;
        }

        DB::statement("ALTER TABLE `vouchers` MODIFY `min_order_value` DECIMAL(10,2) NOT NULL DEFAULT 0");
    }
};