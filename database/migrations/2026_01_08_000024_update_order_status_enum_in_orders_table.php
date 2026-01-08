<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB; // Thêm DB để sử dụng SQL raw statement
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thực hiện thay đổi enum cho trường `order_status` trong bảng `orders`
        DB::statement("ALTER TABLE orders CHANGE order_status order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'waiting_for_cancellation') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục lại enum cũ cho trường `order_status` trong bảng `orders`
        DB::statement("ALTER TABLE orders CHANGE order_status order_status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending'");
    }
};
