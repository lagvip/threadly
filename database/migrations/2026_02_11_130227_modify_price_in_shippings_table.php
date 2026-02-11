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
        Schema::table('shippings', function (Blueprint $table) {
            // Xóa cột giá cũ
            $table->dropColumn('price');

            // Thêm 2 cột giá mới
            $table->decimal('price_inner', 12, 0)->default(0)->after('provider_name')->comment('Giá nội thành');
            $table->decimal('price_outer', 12, 0)->default(0)->after('price_inner')->comment('Giá ngoại thành');
        });
    }

    public function down()
    {
        Schema::table('shippings', function (Blueprint $table) {
            $table->dropColumn(['price_inner', 'price_outer']);
            $table->decimal('price', 12, 0)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    
};
