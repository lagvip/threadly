<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        if (!Schema::hasTable('vouchers')) {
            Schema::create('vouchers', function (Blueprint $table) {
                $table->id();

                $table->string('code')->unique(); // Mã voucher

                $table->enum('type', ['percent', 'fixed']); 
                // percent = giảm theo %, fixed = trừ tiền

                $table->decimal('value', 10, 2); // % hoặc số tiền giảm

                $table->decimal('max_discount', 10, 2)->nullable(); 
                // giảm tối đa (chỉ dùng cho %)

                $table->decimal('min_order_value', 10, 2)->default(0); 
                // giá trị đơn tối thiểu

                $table->dateTime('start_date'); // ngày bắt đầu
                $table->dateTime('end_date');   // ngày kết thúc

                $table->integer('quantity'); // số lượt được dùng

                $table->enum('status', ['active', 'inactive', 'expired'])->default('active'); // active = hoạt động, inactive = tắt, expired = hết hạn

                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
};
