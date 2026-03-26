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
    Schema::table('brands', function (Blueprint $table) {
        // Thêm cột image kiểu string, cho phép null và đặt sau cột name
        $table->string('image')->nullable()->after('name');
    });
}

public function down(): void
{
    Schema::table('brands', function (Blueprint $table) {
        $table->dropColumn('image');
    });
}
};
