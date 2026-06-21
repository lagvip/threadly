<?php

use App\Enums\OrderPaymentStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('stock_deducted_at')->nullable()->after('stock_released_at');
            $table->timestamp('payment_reconciliation_required_at')->nullable()->after('paid_at');
            $table->string('payment_reconciliation_note', 500)->nullable()->after('payment_reconciliation_required_at');
        });

        DB::table('orders')
            ->where('payment_method', PaymentMethod::Cod->value)
            ->update(['stock_deducted_at' => DB::raw('COALESCE(created_at, NOW())')]);

        DB::table('orders')
            ->where('payment_method', PaymentMethod::Vnpay->value)
            ->where('payment_status', OrderPaymentStatus::Paid->value)
            ->update(['stock_deducted_at' => DB::raw('COALESCE(paid_at, created_at, NOW())')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'stock_deducted_at',
                'payment_reconciliation_required_at',
                'payment_reconciliation_note',
            ]);
        });
    }
};
