<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ghn_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('order_code', 50)->nullable()->index();
            $table->string('client_order_code', 100)->nullable()->index();
            $table->string('type', 100)->nullable();
            $table->string('status', 100)->nullable();
            $table->json('payload');
            $table->boolean('processed')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ghn_webhook_logs');
    }
};
