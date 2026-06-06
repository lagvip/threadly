<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\InventoryReceiptRepositoryInterface;
use App\Models\InventoryReceipt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class InventoryReceiptRepository implements InventoryReceiptRepositoryInterface
{
    public function queryForAdmin(): Builder
    {
        return InventoryReceipt::query()
            ->with('creator')
            ->withCount('items')
            ->withSum('items as total_quantity', 'quantity')
            ->addSelect([
                'total_cost' => DB::table('inventory_receipt_items')
                    ->selectRaw('COALESCE(SUM(quantity * COALESCE(unit_cost, 0)), 0)')
                    ->whereColumn('inventory_receipt_items.inventory_receipt_id', 'inventory_receipts.id'),
            ]);
    }

    public function create(array $data): InventoryReceipt
    {
        return InventoryReceipt::create($data);
    }

    public function update(InventoryReceipt $receipt, array $data): bool
    {
        return $receipt->update($data);
    }

    public function lockById(int $id): InventoryReceipt
    {
        return InventoryReceipt::whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function loadItems(InventoryReceipt $receipt): InventoryReceipt
    {
        return $receipt->load('items');
    }

    public function loadForShow(InventoryReceipt $receipt): InventoryReceipt
    {
        return $receipt->load([
            'creator',
            'postedBy',
            'cancelledBy',
            'items.variant.product',
            'items.variant.color',
            'items.variant.size',
        ]);
    }

    public function receiptCodeExists(string $code): bool
    {
        return InventoryReceipt::where('receipt_code', $code)->exists();
    }
}
