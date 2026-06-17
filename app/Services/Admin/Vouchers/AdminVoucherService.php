<?php

namespace App\Services\Admin\Vouchers;

use App\Contracts\Repositories\VoucherRepositoryInterface;
use App\Enums\VoucherStatus;
use App\Enums\VoucherType;
use App\Models\Voucher;
use Carbon\Carbon;
use RuntimeException;

class AdminVoucherService
{
    public function __construct(protected VoucherRepositoryInterface $vouchers) {}

    public function create(array $data): void
    {
        $this->assertBusinessRules($data);

        $this->vouchers->create(array_merge($this->payload($data), [
            'status' => VoucherStatus::Active->value,
        ]));
    }

    public function update(Voucher $voucher, array $data): void
    {
        $this->assertBusinessRules($data);

        $this->vouchers->update($voucher, $this->payload($data));
    }

    public function softDelete(Voucher $voucher): void
    {
        if ($voucher->hasAppliedOrders()) {
            throw new RuntimeException('Không thể xóa voucher đang áp dụng cho đơn hàng');
        }

        $this->vouchers->delete($voucher);
    }

    public function restore(int $id): void
    {
        $this->vouchers->restore($this->vouchers->findWithTrashed($id));
    }

    public function forceDelete(int $id): void
    {
        $this->vouchers->forceDelete($this->vouchers->findWithTrashed($id));
    }

    protected function assertBusinessRules(array $data): void
    {
        if (! empty($data['start_date']) && ! empty($data['end_date']) && Carbon::parse($data['end_date'])->lte(Carbon::parse($data['start_date']))) {
            throw new RuntimeException('Ngày kết thúc phải sau ngày bắt đầu');
        }

        if (($data['type'] ?? null) === VoucherType::Percent->value && (float) $data['value'] > 100) {
            throw new RuntimeException('Phần trăm giảm không được vượt quá 100%');
        }
    }

    protected function payload(array $data): array
    {
        return [
            'code' => $data['code'],
            'type' => $data['type'],
            'value' => $data['value'],
            'max_discount' => $data['max_discount'] ?? null,
            'min_order_value' => $data['min_order_value'] ?? null,
            'start_date' => ! empty($data['start_date'])
                ? Carbon::parse($data['start_date'])->format('Y-m-d H:i:s')
                : Carbon::now()->format('Y-m-d H:i:s'),
            'end_date' => ! empty($data['end_date'])
                ? Carbon::parse($data['end_date'])->format('Y-m-d H:i:s')
                : Carbon::now()->addYears(10)->format('Y-m-d H:i:s'),
            'quantity' => isset($data['quantity']) && $data['quantity'] !== '' ? (int) $data['quantity'] : 0,
            'max_uses_per_user' => $data['max_uses_per_user'],
            'max_uses_per_order' => $data['max_uses_per_order'],
        ];
    }
}
