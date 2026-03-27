<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing users table WITHOUT dropping it (to avoid breaking existing FKs like carts.id_user -> users.id)
        Schema::table('users', function (Blueprint $table) {

            // Add Laravel-friendly columns (only if missing)
            if (!Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }

            if (!Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken()->after('password');
            }

            // Drop Sentinel-specific columns (only if present)
            if (Schema::hasColumn('users', 'permissions')) {
                $table->dropColumn('permissions');
            }

            if (Schema::hasColumn('users', 'last_login')) {
                $table->dropColumn('last_login');
            }
        });

        // Optional: populate name from first_name + last_name if those exist and name is empty.
        if (
            Schema::hasColumn('users', 'first_name') &&
            Schema::hasColumn('users', 'last_name') &&
            Schema::hasColumn('users', 'name')
        ) {
            DB::statement("
                UPDATE users
                SET name = TRIM(CONCAT(COALESCE(first_name,''),' ',COALESCE(last_name,'')))
                WHERE (name IS NULL OR name = '')
            ");
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // Restore Sentinel columns (best-effort)
            if (!Schema::hasColumn('users', 'permissions')) {
                $table->text('permissions')->nullable()->after('password');
            }

            if (!Schema::hasColumn('users', 'last_login')) {
                $table->timestamp('last_login')->nullable()->after('permissions');
            }

            // Remove Laravel columns added in up() (best-effort)
            if (Schema::hasColumn('users', 'remember_token')) {
                $table->dropColumn('remember_token');
            }

            if (Schema::hasColumn('users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }

            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
