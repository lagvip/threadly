<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            $table->string('request_id', 50)->unique();
            $table->enum('refund_type', ['full', 'partial']);
            $table->decimal('amount', 10, 2);
            $table->text('reason')->nullable();

            $table->enum('status', [
                'requested',
                'processing',
                'success',
                'failed',
                'rejected',
            ])->default('requested');

            $table->char('transaction_date', 14)->nullable();
            $table->string('transaction_no', 50)->nullable();
            $table->string('response_code', 10)->nullable();

            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();

            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_refunds');
    }
};
