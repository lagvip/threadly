<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('addresses', 'district')) {
                $table->string('district')->nullable()->after('province');
            }

            if (!Schema::hasColumn('addresses', 'ghn_province_id')) {
                $table->unsignedInteger('ghn_province_id')->nullable()->after('detailed_address');
            }

            if (!Schema::hasColumn('addresses', 'ghn_district_id')) {
                $table->unsignedInteger('ghn_district_id')->nullable()->after('ghn_province_id');
            }

            if (!Schema::hasColumn('addresses', 'ghn_ward_code')) {
                $table->string('ghn_ward_code', 50)->nullable()->after('ghn_district_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            foreach (['district', 'ghn_province_id', 'ghn_district_id', 'ghn_ward_code'] as $col) {
                if (Schema::hasColumn('addresses', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
