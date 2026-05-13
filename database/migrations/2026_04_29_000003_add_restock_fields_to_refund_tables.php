<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refund_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('refund_requests', 'restocked_at')) {
                $table->timestamp('restocked_at')->nullable()->after('rejected_at');
            }

            if (!Schema::hasColumn('refund_requests', 'restocked_by')) {
                $table->unsignedBigInteger('restocked_by')->nullable()->after('restocked_at');
            }

            if (!Schema::hasColumn('refund_requests', 'restock_note')) {
                $table->text('restock_note')->nullable()->after('restocked_by');
            }
        });

        Schema::table('refund_request_items', function (Blueprint $table) {
            if (!Schema::hasColumn('refund_request_items', 'restocked_quantity')) {
                $table->unsignedInteger('restocked_quantity')->default(0)->after('quantity');
            }

            if (!Schema::hasColumn('refund_request_items', 'restocked_at')) {
                $table->timestamp('restocked_at')->nullable()->after('restocked_quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('refund_request_items', function (Blueprint $table) {
            if (Schema::hasColumn('refund_request_items', 'restocked_at')) {
                $table->dropColumn('restocked_at');
            }

            if (Schema::hasColumn('refund_request_items', 'restocked_quantity')) {
                $table->dropColumn('restocked_quantity');
            }
        });

        Schema::table('refund_requests', function (Blueprint $table) {
            if (Schema::hasColumn('refund_requests', 'restock_note')) {
                $table->dropColumn('restock_note');
            }

            if (Schema::hasColumn('refund_requests', 'restocked_by')) {
                $table->dropColumn('restocked_by');
            }

            if (Schema::hasColumn('refund_requests', 'restocked_at')) {
                $table->dropColumn('restocked_at');
            }
        });
    }
};
