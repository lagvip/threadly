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
        'max_uses_per_order',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'status'     => 'string',
    ];

    /**
     * Accessor: Lấy trạng thái thực tế dựa trên ngày hết hạn.
     */
    public function getActualStatusAttribute()
    {
        $now = Carbon::now();

        if ($this->end_date && $now->gt($this->end_date)) {
            return 'expired';
        }

        return $this->status;
    }

    /**
     * Kiểm tra voucher có hợp lệ không.
     *
     * $orderTotal   = tổng tiền hàng trước giảm giá.
     * $currentUses  = số lần user hiện tại đã dùng voucher này.
     * $usesInOrder  = số lần voucher được áp dụng trong đơn hiện tại, thường là 1.
     */
    public function isValid($orderTotal, int $currentUses = 0, int $usesInOrder = 1): bool
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

        if (!is_null($this->quantity) && $this->quantity <= 0) {
            return false;
        }

        $now = Carbon::now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        if (!is_null($this->min_order_value) && $orderTotal < $this->min_order_value) {
            return false;
        }

        if (!$this->canUserUse(null, $currentUses)) {
            return false;
        }

        if (!$this->canUseInOrder($usesInOrder)) {
            return false;
        }

        return true;
    }

    /**
     * Tính số tiền được giảm.
     */
    public function getDiscount($orderTotal)
    {
        if ($this->type === 'percent') {
            $discount = $orderTotal * ($this->value / 100);

            if (!is_null($this->max_discount)) {
                $discount = min($discount, $this->max_discount);
            }

            return round($discount, 2);
        }

        if ($this->type === 'fixed') {
            return min($this->value, $orderTotal);
        }

        return 0;
    }

    /**
     * Giảm số lượt sử dụng sau khi áp dụng.
     */
    public function decreaseQuantity()
    {
        if ($this->quantity > 0) {
            $this->quantity -= 1;
            $this->save();
        }
    }

    /**
     * Kiểm tra user còn lượt dùng voucher không.
     */
    public function canUserUse($userId = null, int $currentUses = 0): bool
    {
        if (is_null($this->max_uses_per_user)) {
            return true;
        }

        return $currentUses < (int) $this->max_uses_per_user;
    }

    /**
     * Kiểm tra voucher có thể dùng trong đơn hiện tại không.
     */
    public function canUseInOrder(int $usesInOrder = 1): bool
    {
        if (is_null($this->max_uses_per_order)) {
            return true;
        }

        return $usesInOrder <= (int) $this->max_uses_per_order;
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
     * Kiểm tra voucher có đang áp dụng không.
     */
    public function isInUse()
    {
        $now = Carbon::now();

        return $this->actual_status === 'active'
            && $this->quantity > 0
            && (!$this->start_date || !$now->lt($this->start_date))
            && (!$this->end_date || !$now->gt($this->end_date));
    }
}
