<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop Sentinel-only tables. Keep users/roles/role_users (we repurpose roles for RBAC).
        Schema::dropIfExists('activations');
        Schema::dropIfExists('persistences');
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('throttle');
    }

    public function down(): void
    {
        // We do NOT recreate Sentinel tables in down() (intentionally).
        // If you ever need Sentinel back, re-install package and re-run its migrations.
    }
};
