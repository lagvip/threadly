<?php

namespace App\Contracts\Repositories;

use App\Models\InventoryReceipt;
use Illuminate\Database\Eloquent\Builder;

interface InventoryReceiptRepositoryInterface
{
    public function queryForAdmin(): Builder;

    public function create(array $data): InventoryReceipt;

    public function update(InventoryReceipt $receipt, array $data): bool;

    public function lockById(int $id): InventoryReceipt;

    public function loadItems(InventoryReceipt $receipt): InventoryReceipt;

    public function loadForShow(InventoryReceipt $receipt): InventoryReceipt;

    public function receiptCodeExists(string $code): bool;
}
