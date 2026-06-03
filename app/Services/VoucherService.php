<?php

namespace App\Services;

use App\Contracts\Repositories\VoucherRepositoryInterface;
use Exception;

class VoucherService
{
    public function __construct(protected VoucherRepositoryInterface $vouchers)
    {
    }

    public function getAllVoucher()
    {
        return $this->vouchers->query()->get();
    }

    public function createVoucher(array $data)
    {
        return $this->vouchers->create($data);
    }

    public function updateVoucher($data, $id)
    {
        $voucher = $this->vouchers->query()->findOrFail($id);

        return $voucher->update($data);
    }

    public function deleteVoucher($id)
    {
        $voucher = $this->vouchers->find((int) $id);

        if (!$voucher) {
            throw new Exception('Voucher không tồn tại');
        }

        if ($voucher->isInUse()) {
            throw new Exception('Không thể xóa voucher đang áp dụng');
        }

        $voucher->delete();

        return $voucher;
    }

    public function softDeleteWithStatus($id)
    {
        $voucher = $this->vouchers->query()->findOrFail($id);
        $voucher->status = 'inactive';
        $voucher->save();

        return $voucher->delete();
    }

    public function getVoucherById($id)
    {
        return $this->vouchers->find((int) $id);
    }

    public function getVoucherByName($name)
    {
        return $this->vouchers->query()->where('name', $name)->first();
    }

    public function type($type)
    {
        if ($type == 0) {
            return 'Free Shipping';
        }

        if ($type == 1) {
            return 'Percentage';
        }

        if ($type == 2) {
            return 'Fixed Amount';
        }

        return 'Unknown';
    }

    public function find($id)
    {
        return $this->vouchers->query()->findOrFail($id);
    }

    public function getStatus($status)
    {
        if ($status === 'active') {
            return 'Hoạt động';
        }

        if ($status === 'inactive') {
            return 'Không hoạt động';
        }

        if ($status === 'expired') {
            return 'Hết hạn';
        }

        return 'Không xác định';
    }

    public function countCoupons()
    {
        return $this->vouchers->query()->count();
    }

    public function getCouponsByStatus($status)
    {
        return $this->vouchers->query()->where('status', $status)->get();
    }

    public function getCouponsByType($type)
    {
        return $this->vouchers->query()->where('type', $type)->get();
    }

    public function getCouponsByDate($start_date, $end_date)
    {
        return $this->vouchers->query()->whereBetween('start_date', [$start_date, $end_date])->get();
    }

    public function getTrashedList()
    {
        return $this->vouchers->trashedQuery()->get();
    }

    public function restore($id)
    {
        return $this->vouchers->findWithTrashed((int) $id)->restore();
    }

    public function forceDelete($id)
    {
        return $this->vouchers->findWithTrashed((int) $id)->forceDelete();
    }

    public function bulkDelete(array $ids)
    {
        return $this->vouchers->query()->whereIn('id', $ids)->delete();
    }

    public function bulkRestoreVoucher(array $ids)
    {
        return $this->vouchers->trashedQuery()->whereIn('id', $ids)->restore();
    }

    public function getActiveVouchers()
    {
        return $this->vouchers->query()->whereIn('status', ['active', 'expired'])->get();
    }

    public function getValidVouchers()
    {
        return $this->vouchers->query()->where('status', 'active')->get();
    }

    public function getExpiredVouchers()
    {
        return $this->vouchers->query()->where('status', 'expired')->get();
    }
}
