<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('refund_request_items')) {
            Schema::create('refund_request_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('refund_request_id')->constrained('refund_requests')->cascadeOnDelete();
                $table->foreignId('order_detail_id')->nullable()->constrained('order_details')->nullOnDelete();
                $table->string('product_name_snapshot');
                $table->string('variant_snapshot')->nullable();
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('unit_amount', 15, 2)->default(0);
                $table->decimal('line_amount', 15, 2)->default(0);
                $table->timestamps();

                $table->index(['refund_request_id', 'order_detail_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_request_items');
    }
};
