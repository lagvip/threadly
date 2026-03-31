<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE orders
            MODIFY order_code VARCHAR(50) NOT NULL
        ");

        DB::statement("
            ALTER TABLE orders
            MODIFY payment_status ENUM('unpaid','pending','paid','failed','cancelled','expired')
            NOT NULL DEFAULT 'unpaid'
        ");
    }

    public function down(): void
    {
        
        DB::statement("
            UPDATE orders
            SET payment_status = 'failed'
            WHERE payment_status IN ('pending', 'cancelled', 'expired')
        ");

        DB::statement("
            ALTER TABLE orders
            MODIFY payment_status ENUM('unpaid','paid','failed')
            NOT NULL DEFAULT 'unpaid'
        ");

        DB::statement("
            ALTER TABLE orders
            MODIFY order_code VARCHAR(20) NOT NULL
        ");
    }
};
