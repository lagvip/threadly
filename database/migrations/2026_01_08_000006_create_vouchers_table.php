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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type');
            $table->decimal('value', 10, 2);
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->decimal('min_order_value', 10, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->integer('max_uses_per_user')->default(1);
            $table->integer('max_uses_per_order')->default(1);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('status')->default('active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
