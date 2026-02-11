<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            // Thêm cột price, kiểu số, mặc định là 0, đặt sau cột provider_name
            $table->decimal('price', 15, 0)->default(0)->after('provider_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            // Xóa cột price nếu chạy rollback (quay lại)
            $table->dropColumn('price');
        });
    }
};
