<?php

namespace App\Services\Admin\Vouchers;

use App\Contracts\Repositories\VoucherRepositoryInterface;

class AdminVoucherQueryService
{
    public function __construct(protected VoucherRepositoryInterface $vouchers)
    {
    }

    public function indexData(array $filters): array
    {
        $query = $this->vouchers->query()->orderBy('id', 'desc');

        if (!empty($filters['search'])) {
            $query->where('code', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return [
            'vouchers' => $query->paginate(10)->appends(request()->query()),
            'search' => $filters['search'] ?? null,
            'type' => $filters['type'] ?? null,
            'status' => $filters['status'] ?? null,
        ];
    }

    public function trashedData(): array
    {
        return [
            'vouchers' => $this->vouchers->trashedQuery()->paginate(10),
        ];
    }
}
