<?php

namespace App\Contracts\Repositories;

use App\Models\RefundRequest;
use Illuminate\Database\Eloquent\Builder;

interface RefundRequestRepositoryInterface
{
    public function adminIndexQuery(): Builder;

    public function loadForShow(RefundRequest $refundRequest): RefundRequest;

    public function countByStatus(string $status): int;

    public function create(array $data): RefundRequest;

    public function update(RefundRequest $refundRequest, array $data): bool;

    public function lockWithItems(int $id): RefundRequest;

    public function lockWithItemsAndOrderDetail(int $id): RefundRequest;

    public function lockById(int $id): RefundRequest;
}
