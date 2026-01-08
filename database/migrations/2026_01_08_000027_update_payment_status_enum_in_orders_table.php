<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Thay đổi ENUM cho payment_status để thêm 'failed'
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_status', ['unpaid', 'paid', 'failed'])->default('unpaid')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Quay lại ENUM cũ nếu cần
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid')->change();
        });
    }
};
