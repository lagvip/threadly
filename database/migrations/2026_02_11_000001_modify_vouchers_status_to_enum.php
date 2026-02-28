<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up()
    {
        // Kiểm tra xem bảng và cột status có tồn tại không
        if (Schema::hasTable('vouchers') && Schema::hasColumn('vouchers', 'status')) {
            // Thay đổi cột status từ boolean sang enum
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('vouchers', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive', 'expired'])->default('active')->after('quantity');
            });

            // Sau khi thay đổi cấu trúc, chuyển đổi dữ liệu cũ
            // Cập nhật tất cả bản ghi thành 'active' (mặc định)
            DB::table('vouchers')->update(['status' => 'active']);
        }
    }

    public function down()
    {
        if (Schema::hasTable('vouchers') && Schema::hasColumn('vouchers', 'status')) {
            // Thay đổi cột status từ enum sang boolean
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('vouchers', function (Blueprint $table) {
                $table->boolean('status')->default(1)->after('quantity');
            });
        }
    }
};