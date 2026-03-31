<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'first_name')) {
                $table->dropColumn('first_name');
            }

            if (Schema::hasColumn('users', 'last_name')) {
                $table->dropColumn('last_name');
            }

            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->unique()->after('email');
            }

            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('google_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'google_id')) {
                $table->dropUnique(['google_id']);
                $table->dropColumn('google_id');
            }

            if (Schema::hasColumn('users', 'avatar')) {
                $table->dropColumn('avatar');
            }

            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('remember_token');
            }

            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
        });
    }
};
