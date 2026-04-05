<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('ban_reason')->nullable()->after('status');
            $table->timestamp('banned_at')->nullable()->after('ban_reason');
            $table->unsignedBigInteger('banned_by')->nullable()->after('banned_at');

            $table->foreign('banned_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['banned_by']);
            $table->dropColumn(['ban_reason', 'banned_at', 'banned_by']);
        });
    }
};
