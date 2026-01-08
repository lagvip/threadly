vvvvvv<?php

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name',250)->nullable()->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('id_brand');
            $table->unsignedBigInteger('id_category');
            $table->string('image_primary', 255)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            
              $table->foreign('id_brand')->references('id')->on('brands')->onDelete('cascade');
            $table->foreign('id_category')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
