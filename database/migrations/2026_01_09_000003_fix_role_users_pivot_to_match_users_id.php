<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Your dump shows users.id is BIGINT, but role_users.user_id was INT.
        // This migration aligns pivot types so you can keep role-based admin without Sentinel.

        if (Schema::hasTable('role_users')) {

            // Convert pivot columns to BIGINT UNSIGNED if they exist.
            if (Schema::hasColumn('role_users', 'user_id')) {
                DB::statement("ALTER TABLE `role_users` MODIFY `user_id` BIGINT(20) UNSIGNED NOT NULL");
            }
            if (Schema::hasColumn('role_users', 'role_id')) {
                DB::statement("ALTER TABLE `role_users` MODIFY `role_id` BIGINT(20) UNSIGNED NOT NULL");
            }

            // Add indexes (safe if not existing)
            // Using raw SQL with IF NOT EXISTS isn't available on older MySQL, so we try best-effort.
            try {
                DB::statement("ALTER TABLE `role_users` ADD INDEX `role_users_user_id_index` (`user_id`)");
            } catch (\Throwable $e) {}
            try {
                DB::statement("ALTER TABLE `role_users` ADD INDEX `role_users_role_id_index` (`role_id`)");
            } catch (\Throwable $e) {}
        }

        // Optional: Align roles.id to BIGINT UNSIGNED (only if roles exists).
        // This is best-effort and may be skipped if roles.id is already compatible.
        if (Schema::hasTable('roles')) {
            try {
                DB::statement("ALTER TABLE `roles` MODIFY `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT");
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        // Best-effort rollback: set pivot columns back to INT
        if (Schema::hasTable('role_users')) {
            try { DB::statement("ALTER TABLE `role_users` MODIFY `user_id` INT(11) NOT NULL"); } catch (\Throwable $e) {}
            try { DB::statement("ALTER TABLE `role_users` MODIFY `role_id` INT(11) NOT NULL"); } catch (\Throwable $e) {}
            // indexes left as-is
        }
        if (Schema::hasTable('roles')) {
            // Not reverting roles.id (unsafe). If you need, handle manually.
        }
    }
};
