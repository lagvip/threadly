<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterOrderStatusEnumInOrdersTable extends Migration
{
    public function up()
    {
        // Bỏ enum cũ bằng cách đổi sang VARCHAR tạm
        DB::statement("ALTER TABLE orders MODIFY COLUMN order_status VARCHAR(50) NOT NULL");

        // (Không cần update dữ liệu vì đã là 'pending')

        // Đặt lại ENUM mới
        DB::statement("ALTER TABLE orders MODIFY COLUMN order_status ENUM('confirming', 'pending', 'processing', 'shipping', 'delivered') NOT NULL");
    }

    public function down()
    {
        // Đổi lại về VARCHAR trước
        DB::statement("ALTER TABLE orders MODIFY COLUMN order_status VARCHAR(50) NOT NULL");

        // Đổi tất cả về 'draft' hoặc giữ nguyên nếu muốn
        DB::table('orders')->whereNotIn('order_status', ['draft', 'packaging', 'completed', 'canceled', 'delivering'])
            ->update(['order_status' => 'draft']);

        // Đặt lại ENUM cũ (nếu cần)
        DB::statement("ALTER TABLE orders MODIFY COLUMN order_status ENUM('draft', 'packaging', 'completed', 'canceled', 'delivering') NOT NULL");
    }
}

