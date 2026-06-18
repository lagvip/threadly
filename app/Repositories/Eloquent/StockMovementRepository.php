<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\StockMovementRepositoryInterface;
use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StockMovementRepository implements StockMovementRepositoryInterface
{
    protected function queryForAdmin(): Builder
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

    public function paginateForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->queryForAdmin();

        if (! empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);

            $query->where(function ($q) use ($keyword) {
                if (ctype_digit($keyword)) {
                    $q->orWhere('product_variant_id', (int) $keyword);
                }

                $q->orWhereHas('variant.product', fn ($productQuery) => $productQuery->where('name', 'like', '%'.$keyword.'%'))
                    ->orWhereHas('variant.color', fn ($colorQuery) => $colorQuery->where('name', 'like', '%'.$keyword.'%'))
                    ->orWhereHas('variant.size', fn ($sizeQuery) => $sizeQuery->where('name', 'like', '%'.$keyword.'%'));
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): StockMovement
    {
        return StockMovement::create($data);
    }
}
