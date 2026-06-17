<?php

namespace App\Services\Admin\Vouchers;

use App\Contracts\Repositories\VoucherRepositoryInterface;
use App\Enums\VoucherStatus;
use App\Enums\VoucherType;

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
            'voucherTypeOptions' => $this->typeOptions(),
            'voucherStatusOptions' => $this->statusOptions(),
        ];
    }

    public function trashedData(): array
    {
        return [
            'vouchers' => $this->vouchers->trashedPaginatedForAdmin(),
            'voucherTypeOptions' => $this->typeOptions(),
        ];
    }

    public function formData(): array
    {
        return [
            'voucherTypeOptions' => $this->typeOptions(),
            'percentVoucherType' => VoucherType::Percent->value,
        ];
    }

    protected function typeOptions(): array
    {
        return collect(VoucherType::cases())
            ->mapWithKeys(fn (VoucherType $type) => [
                $type->value => [
                    'label' => $type->label(),
                    'badge' => $type->badge(),
                    'unit' => $type->unit(),
                ],
            ])
            ->all();
    }

    protected function statusOptions(): array
    {
        return collect(VoucherStatus::cases())
            ->mapWithKeys(fn (VoucherStatus $status) => [
                $status->value => [
                    'label' => $status->label(),
                    'badge' => $status->badge(),
                ],
            ])
            ->all();
    }
}
