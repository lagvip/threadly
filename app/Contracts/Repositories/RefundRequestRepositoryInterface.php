<?php

namespace App\Contracts\Repositories;

use App\Models\RefundRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RefundRequestRepositoryInterface
{
    public function paginateForAdmin(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function loadForShow(RefundRequest $refundRequest): RefundRequest;

    public function countByStatus(string $status): int;

    public function create(array $data): RefundRequest;

    public function update(RefundRequest $refundRequest, array $data): bool;

    public function lockWithItems(int $id): RefundRequest;

    public function lockWithItemsAndOrderDetail(int $id): RefundRequest;

    public function lockById(int $id): RefundRequest;
}
