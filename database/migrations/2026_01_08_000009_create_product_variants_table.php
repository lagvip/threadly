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
          Schema::create('product_variants', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('id_product');
    $table->unsignedInteger('quantity')->default(0);
    $table->unsignedBigInteger('id_color');
    $table->unsignedBigInteger('id_size');
    $table->decimal('price', 10, 2)->default(0);
    $table->string('status')->default('active');
    $table->timestamps();
    $table->softDeletes();

    // Các ràng buộc khóa ngoại
    $table->foreign('id_product')->references('id')->on('products')->onDelete('cascade');
    $table->foreign('id_color')->references('id')->on('colors')->onDelete('cascade');
    $table->foreign('id_size')->references('id')->on('sizes')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
