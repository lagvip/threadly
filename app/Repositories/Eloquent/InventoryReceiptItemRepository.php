<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\InventoryReceiptItemRepositoryInterface;
use App\Models\InventoryReceiptItem;

class InventoryReceiptItemRepository implements InventoryReceiptItemRepositoryInterface
{
    public function create(array $data): InventoryReceiptItem
    {
        return InventoryReceiptItem::create($data);
    }

    public function update(InventoryReceiptItem $item, array $data): bool
    {
        return $item->update($data);
    }
}
