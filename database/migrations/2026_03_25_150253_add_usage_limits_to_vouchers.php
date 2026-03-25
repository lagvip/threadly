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
        if (Schema::hasTable('vouchers')) {
            Schema::table('vouchers', function (Blueprint $table) {
                if (!Schema::hasColumn('vouchers', 'max_uses_per_user')) {
                    $table->integer('max_uses_per_user')->default(1)->after('quantity');
                }
                if (!Schema::hasColumn('vouchers', 'max_uses_per_order')) {
                    $table->integer('max_uses_per_order')->default(1)->after('max_uses_per_user');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('vouchers')) {
            Schema::table('vouchers', function (Blueprint $table) {
                if (Schema::hasColumn('vouchers', 'max_uses_per_user')) {
                    $table->dropColumn('max_uses_per_user');
                }
                if (Schema::hasColumn('vouchers', 'max_uses_per_order')) {
                    $table->dropColumn('max_uses_per_order');
                }
            });
        }
    }
};
