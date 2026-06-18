<?php

namespace App\Contracts\Repositories;

use App\Models\InventoryReceipt;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryReceiptRepositoryInterface
{
    public function paginateForAdmin(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function create(array $data): InventoryReceipt;

    public function update(InventoryReceipt $receipt, array $data): bool;

    public function lockById(int $id): InventoryReceipt;

    public function loadItems(InventoryReceipt $receipt): InventoryReceipt;

    public function loadForShow(InventoryReceipt $receipt): InventoryReceipt;

    public function receiptCodeExists(string $code): bool;
}
