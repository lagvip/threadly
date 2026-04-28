<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'refund_status')) {
                $table->string('refund_status')->default('none')->after('customer_confirmed_at');
            }

            if (!Schema::hasColumn('orders', 'refunded_amount')) {
                $table->decimal('refunded_amount', 15, 2)->default(0)->after('refund_status');
            }

            if (!Schema::hasColumn('orders', 'last_refund_requested_at')) {
                $table->timestamp('last_refund_requested_at')->nullable()->after('refunded_amount');
            }

            if (!Schema::hasColumn('orders', 'last_refunded_at')) {
                $table->timestamp('last_refunded_at')->nullable()->after('last_refund_requested_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['last_refunded_at', 'last_refund_requested_at', 'refunded_amount', 'refund_status'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
