<?php

namespace App\Services\Admin\Vouchers;

use App\Contracts\Repositories\VoucherRepositoryInterface;

class AdminVoucherQueryService
{
    public function __construct(protected VoucherRepositoryInterface $vouchers) {}

    public function indexData(array $filters): array
    {
        return [
            'vouchers' => $this->vouchers->paginatedForAdmin($filters)->appends($filters),
            'search' => $filters['search'] ?? null,
            'type' => $filters['type'] ?? null,
            'status' => $filters['status'] ?? null,
        ];
    }

    public function trashedData(): array
    {
        return [
            'vouchers' => $this->vouchers->trashedPaginatedForAdmin(),
        ];
    }
}
