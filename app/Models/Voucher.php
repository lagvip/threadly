<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Voucher extends Model
{
    use HasFactory;

    protected $table = 'vouchers';

    protected $fillable = [
        'code',
        'type',
        'value',
        'max_discount',
        'min_order_value',
        'start_date',
        'end_date',
        'quantity',
        'status'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'status'     => 'boolean'
    ];

    /**
     * Kiểm tra voucher có hợp lệ không
     */
    public function isValid($orderTotal)
    {
        // Bị tắt
        if (!$this->status) return false;

        // Hết lượt dùng
        if ($this->quantity <= 0) return false;

        $now = Carbon::now();

        // Chưa tới ngày hoặc đã hết hạn
        if ($now->lt($this->start_date) || $now->gt($this->end_date)) {
            return false;
        }

        // Đơn hàng chưa đủ điều kiện
        if ($orderTotal < $this->min_order_value) {
            return false;
        }

        return true;
    }

    /**
     * Tính số tiền được giảm
     */
    public function getDiscount($orderTotal)
    {
        // Giảm theo %
        if ($this->type === 'percent') {
            $discount = $orderTotal * ($this->value / 100);

            // Giới hạn giảm tối đa
            if (!is_null($this->max_discount)) {
                $discount = min($discount, $this->max_discount);
            }

            return round($discount, 2);
        }

        // Giảm trừ tiền trực tiếp
        if ($this->type === 'fixed') {
            return min($this->value, $orderTotal);
        }

        return 0;
    }

    /**
     * Giảm số lượt sử dụng sau khi áp dụng
     */
    public function decreaseQuantity()
    {
        if ($this->quantity > 0) {
            $this->quantity -= 1;
            $this->save();
        }
    }
}
