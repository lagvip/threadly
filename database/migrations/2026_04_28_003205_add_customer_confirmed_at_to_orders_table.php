<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'customer_confirmed_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('customer_confirmed_at')->nullable()->after('paid_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'customer_confirmed_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('customer_confirmed_at');
            });
        }
    }
};
