<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->char('payment_request_date', 14)->nullable()->after('customer_note');
            $table->char('payment_expire_date', 14)->nullable()->after('payment_request_date');
            $table->string('payment_transaction_no', 50)->nullable()->after('payment_expire_date');
            $table->string('payment_bank_code', 50)->nullable()->after('payment_transaction_no');
            $table->string('payment_response_code', 10)->nullable()->after('payment_bank_code');
            $table->string('payment_transaction_status', 10)->nullable()->after('payment_response_code');
            $table->char('payment_pay_date', 14)->nullable()->after('payment_transaction_status');
            $table->timestamp('paid_at')->nullable()->after('payment_pay_date');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_request_date',
                'payment_expire_date',
                'payment_transaction_no',
                'payment_bank_code',
                'payment_response_code',
                'payment_transaction_status',
                'payment_pay_date',
                'paid_at',
            ]);
        });
    }
};
