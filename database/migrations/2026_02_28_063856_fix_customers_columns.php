<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) đổi full_name -> name bằng SQL CHANGE (MariaDB hỗ trợ)
        if (Schema::hasColumn('customers', 'full_name') && !Schema::hasColumn('customers', 'name')) {
            DB::statement("ALTER TABLE customers CHANGE full_name name VARCHAR(255) NOT NULL");
        }

        // 2) thêm phone
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'phone')) {
                $table->string('phone', 20)->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        // rollback: name -> full_name
        if (Schema::hasColumn('customers', 'name') && !Schema::hasColumn('customers', 'full_name')) {
            DB::statement("ALTER TABLE customers CHANGE name full_name VARCHAR(255) NOT NULL");
        }

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
