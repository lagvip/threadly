<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $duplicateUsers = DB::table('carts')
                ->select('id_user')
                ->groupBy('id_user')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('id_user');

            foreach ($duplicateUsers as $userId) {
                $cartIds = DB::table('carts')
                    ->where('id_user', $userId)
                    ->orderBy('id')
                    ->pluck('id');

                $primaryCartId = (int) $cartIds->first();
                $details = DB::table('carts_details')
                    ->whereIn('id_cart', $cartIds)
                    ->select('id_variant', DB::raw('SUM(quantity) as quantity'))
                    ->groupBy('id_variant')
                    ->get();

                DB::table('carts_details')->whereIn('id_cart', $cartIds)->delete();

                foreach ($details as $detail) {
                    DB::table('carts_details')->insert([
                        'id_cart' => $primaryCartId,
                        'id_variant' => $detail->id_variant,
                        'quantity' => max((int) $detail->quantity, 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('carts')->whereIn('id', $cartIds->skip(1))->delete();
            }

            $duplicates = DB::table('carts_details')
                ->select('id_cart', 'id_variant')
                ->groupBy('id_cart', 'id_variant')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicates as $duplicate) {
                $rows = DB::table('carts_details')
                    ->where('id_cart', $duplicate->id_cart)
                    ->where('id_variant', $duplicate->id_variant)
                    ->orderBy('id')
                    ->get();

                $primary = $rows->first();
                $quantity = $rows->sum(fn ($row) => (int) $row->quantity);

                DB::table('carts_details')->where('id', $primary->id)->update([
                    'quantity' => max($quantity, 1),
                    'updated_at' => now(),
                ]);

                DB::table('carts_details')->whereIn('id', $rows->skip(1)->pluck('id'))->delete();
            }
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->unique('id_user', 'carts_id_user_unique');
        });

        Schema::table('carts_details', function (Blueprint $table) {
            $table->unique(['id_cart', 'id_variant'], 'carts_details_cart_variant_unique');
        });
    }

    public function down(): void
    {
        Schema::table('carts_details', function (Blueprint $table) {
            $table->dropUnique('carts_details_cart_variant_unique');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique('carts_id_user_unique');
        });
    }
};
