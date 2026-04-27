<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_address_id')->nullable()->after('customer_note');
            $table->unsignedInteger('ghn_to_province_id')->nullable()->after('shipping_address_id');
            $table->unsignedInteger('ghn_to_district_id')->nullable()->after('ghn_to_province_id');
            $table->string('ghn_to_ward_code', 50)->nullable()->after('ghn_to_district_id');

            $table->string('ghn_order_code', 50)->nullable()->after('order_code')->index();
            $table->string('ghn_client_order_code', 100)->nullable()->after('ghn_order_code')->index();
            $table->string('ghn_status', 100)->nullable()->after('ghn_client_order_code');
            $table->string('ghn_status_name', 255)->nullable()->after('ghn_status');
            $table->unsignedInteger('ghn_service_id')->nullable()->after('ghn_status_name');
            $table->unsignedTinyInteger('ghn_service_type_id')->nullable()->after('ghn_service_id');
            $table->timestamp('ghn_expected_delivery_time')->nullable()->after('ghn_service_type_id');
            $table->json('ghn_raw_response')->nullable()->after('ghn_expected_delivery_time');
            $table->timestamp('ghn_synced_at')->nullable()->after('ghn_raw_response');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_address_id',
                'ghn_to_province_id',
                'ghn_to_district_id',
                'ghn_to_ward_code',
                'ghn_order_code',
                'ghn_client_order_code',
                'ghn_status',
                'ghn_status_name',
                'ghn_service_id',
                'ghn_service_type_id',
                'ghn_expected_delivery_time',
                'ghn_raw_response',
                'ghn_synced_at',
            ]);
        });
    }
};
