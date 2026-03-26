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
        // Thêm cột status kiểu boolean, mặc định là 1 (Hiện)
        $table->tinyInteger('status')->default(1);
    });
    
}

public function down()
{
    Schema::table('shippings', function (Blueprint $table) {
        $table->dropColumn('status');
    });
}

    /**
     * Reverse the migrations.
     */
    
};
