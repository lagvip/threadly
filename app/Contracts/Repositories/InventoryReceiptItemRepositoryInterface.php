<?php

namespace App\Contracts\Repositories;

use App\Models\InventoryReceiptItem;

interface InventoryReceiptItemRepositoryInterface
{
    public function create(array $data): InventoryReceiptItem;

    public function update(InventoryReceiptItem $item, array $data): bool;
}
