<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up()
    {
        // Increase precision so large order values fit (e.g. 11,111,111,111)
        DB::statement("ALTER TABLE `vouchers` MODIFY `min_order_value` DECIMAL(13,2) NOT NULL DEFAULT 0");
    }

    public function down()
    {
        DB::statement("ALTER TABLE `vouchers` MODIFY `min_order_value` DECIMAL(10,2) NOT NULL DEFAULT 0");
    }
};