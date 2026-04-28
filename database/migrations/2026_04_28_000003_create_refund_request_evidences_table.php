<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('refund_request_evidences')) {
            Schema::create('refund_request_evidences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('refund_request_id')->constrained('refund_requests')->cascadeOnDelete();
                $table->enum('file_type', ['image', 'video']);
                $table->string('file_path');
                $table->string('original_name')->nullable();
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_request_evidences');
    }
};
