<?php

namespace App\Services;


use App\Models\Voucher;

class VoucherService
{
    //
    protected $voucher;
    public function __construct(Voucher $voucher)
    {
        $this->voucher = $voucher;
    }

    public function getAllVoucher()
    {
        $voucher = Voucher::all();
        return $voucher;
    }

    public function createVoucher(array $data)
    {
        return Voucher::create($data);
    }

    public function updateVoucher($data, $id)
    {
        $voucher = Voucher::findOrFail($id);
        return $voucher->update($data);
    }

    public function deleteVoucher($id)
    {
        $voucher = Voucher::find($id);
        $voucher->delete();
        return $voucher;
    }

    public function softDeleteWithStatus($id)
{
    $voucher = Voucher::findOrFail($id);
    $voucher->status = 'inactive';
    $voucher->save();
    return $voucher->delete();
}

    public function getVoucherById($id)
    {
        $voucher = Voucher::find($id);
        return $voucher;
    }

    public function getVoucherByName($name)
    {
        $voucher = Voucher::where('name', $name)->first();
        return $voucher;
    }
    public function type($type){
        if($type == 0){
            return 'Free Shipping';
        }elseif($type == 1){
            return 'Percentage';
        }elseif($type == 2){
            return 'Fixed Amount';
        }else{
            return 'Unknown';
        }
    }
    public function find($id)
    {
        return Voucher::findOrFail($id);
    }
    public function getStatus($status){
        if($status == 0){
            return 'Active';
        }elseif($status == 1){
            return 'Inactive';
        }elseif($status == 2){
            return 'Future Plan';
        }else{
            return 'Unknown';
        }
    }
    public function countCoupons(){
        return Voucher::count();
    }
    public function getCouponsByStatus($status){
        return Voucher::where('status', $status)->get();
    }
    public function getCouponsByType($type){
        return Voucher::where('type', $type)->get();
    }
    public function getCouponsByDate($start_date, $end_date){
        return Voucher::whereBetween('start_date', [$start_date, $end_date])->get();
    }

    public function getTrashedList(){
        $list = Voucher::onlyTrashed()->get();
        return $list;
    }
    public function restore($id)
{
    $voucher = Voucher::withTrashed()->findOrFail($id);
    return $voucher->restore();
}

public function forceDelete($id)
{
    $voucher = Voucher::withTrashed()->findOrFail($id);
    return $voucher->forceDelete();
}

public function bulkDelete(array $ids)
{
    return Voucher::whereIn('id', $ids)->delete(); // soft delete
}

public function bulkRestoreVoucher(array $ids)
{
    return Voucher::onlyTrashed()->whereIn('id', $ids)->restore();
}



}
