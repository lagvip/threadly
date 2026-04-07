<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('reviews', 'product_variant_id')) {
                $table->unsignedBigInteger('product_variant_id')->nullable()->after('product_id');
            }

            if (! Schema::hasColumn('reviews', 'order_detail_id')) {
                $table->unsignedBigInteger('order_detail_id')->nullable()->after('order_id');
            }

            if (! Schema::hasColumn('reviews', 'product_name_snapshot')) {
                $table->string('product_name_snapshot')->nullable()->after('comment');
            }

            if (! Schema::hasColumn('reviews', 'color_snapshot')) {
                $table->string('color_snapshot')->nullable()->after('product_name_snapshot');
            }

            if (! Schema::hasColumn('reviews', 'size_snapshot')) {
                $table->string('size_snapshot')->nullable()->after('color_snapshot');
            }
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index('product_variant_id', 'reviews_product_variant_id_index');
            $table->index('order_detail_id', 'reviews_order_detail_id_index');
            $table->unique(['user_id', 'order_id', 'order_detail_id'], 'reviews_user_order_detail_unique');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique('reviews_user_order_detail_unique');
            $table->dropIndex('reviews_product_variant_id_index');
            $table->dropIndex('reviews_order_detail_id_index');

            $table->dropColumn([
                'product_variant_id',
                'order_detail_id',
                'product_name_snapshot',
                'color_snapshot',
                'size_snapshot',
            ]);
        });
    }
};
