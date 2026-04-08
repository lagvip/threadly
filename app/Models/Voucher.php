<?php

namespace App\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Voucher extends Model
{
    use HasFactory, SoftDeletes;

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
        'status',
        'max_uses_per_user',
        'max_uses_per_order'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'status'     => 'string'
    ];

    /**
     * Accessor: Lấy trạng thái thực tế (tính toán dựa trên ngày hết hạn)
     */
    public function getActualStatusAttribute()
    {
        $now = Carbon::now();

        // Nếu đã hết hạn
        if ($now->gt($this->end_date)) {
            return 'expired';
        }

        // Trả về status từ database
        return $this->status;
    }

    /**
     * Kiểm tra voucher có hợp lệ không
     */
    public function isValid($orderTotal)
    {
        // Bị tắt hoặc hết hạn
        if ($this->actual_status !== 'active') return false;

        // Hết lượt dùng, nhưng 0 được coi là vô hạn
        if ($this->quantity < 0) return false;

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

    /**
     * Kiểm tra xem user có thể sử dụng voucher này không (dựa trên giới hạn sử dụng)
     */
    public function canUserUse($userId, $currentUses = 0)
    {
        return $currentUses < $this->max_uses_per_user;
    }

    /**
     * Kiểm tra xem voucher có thể được sử dụng trong đơn hàng này không
     */
    public function canUseInOrder($usesInOrder = 1)
    {
        return $usesInOrder <= $this->max_uses_per_order;
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'voucher_id');
    }

    public function hasAppliedOrders(): bool
    {
        return $this->orders()
            ->where('order_status', '!=', Order::STATUS_CANCELLED)
            ->exists();
    }

    /**
     * Kiểm tra xem voucher có đang áp dụng không (không thể xóa)
     */
    public function isInUse()
    {
        return $this->hasAppliedOrders();
    }
}