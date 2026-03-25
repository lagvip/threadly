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
        if (Schema::hasTable('sizes') && !Schema::hasColumn('sizes', 'deleted_at')) {
            Schema::table('sizes', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('vouchers') && !Schema::hasColumn('vouchers', 'deleted_at')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sizes') && Schema::hasColumn('sizes', 'deleted_at')) {
            Schema::table('sizes', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('vouchers') && Schema::hasColumn('vouchers', 'deleted_at')) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
