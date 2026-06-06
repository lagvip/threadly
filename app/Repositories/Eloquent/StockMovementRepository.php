<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\StockMovementRepositoryInterface;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StockMovementRepository implements StockMovementRepositoryInterface
{
    public function queryForAdmin(): Builder
    {
        return StockMovement::query()
            ->addSelect([
                'stock_movements.*',
                'receipt_unit_cost' => DB::table('inventory_receipt_items')
                    ->select('unit_cost')
                    ->whereColumn('inventory_receipt_items.inventory_receipt_id', 'stock_movements.reference_id')
                    ->whereColumn('inventory_receipt_items.product_variant_id', 'stock_movements.product_variant_id')
                    ->limit(1),
            ])
            ->with(['variant.product', 'variant.color', 'variant.size', 'creator'])
            ->latest('id');
    }

    public function create(array $data): StockMovement
    {
        return StockMovement::create($data);
    }
}
