<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('weight')->default(500)->after('status');
            $table->unsignedInteger('length')->default(20)->after('weight');
            $table->unsignedInteger('width')->default(20)->after('length');
            $table->unsignedInteger('height')->default(10)->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight', 'length', 'width', 'height']);
        });
    }
};
