<?php

namespace App\Models;

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
        'value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'quantity' => 'integer',
        'max_uses_per_user' => 'integer',
        'max_uses_per_order' => 'integer',
        'status' => 'string',
    ];

    /**
     * Quan hệ: 1 voucher có thể được dùng trong nhiều order
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'voucher_id');
    }

    /**
     * Accessor: trạng thái thực tế của voucher
     * - expired: đã quá hạn
     * - inactive: admin tắt hoặc chưa tới ngày bắt đầu
     * - active: dùng được
     */
    public function getActualStatusAttribute()
    {
        $now = Carbon::now();

        if ($this->status !== 'active') {
            return $this->status;
        }

        if ($now->lt($this->start_date)) {
            return 'inactive';
        }

        if ($now->gt($this->end_date)) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Kiểm tra voucher có hợp lệ không
     *
     * @param float $orderTotal Tổng tiền hàng trước khi trừ voucher
     * @param int $currentUses Số lần user hiện tại đã dùng voucher này
     * @param int $usesInOrder Số lần voucher được áp trong 1 đơn
     */
    public function isValid($orderTotal, $currentUses = 0, $usesInOrder = 1)
    {
        $now = Carbon::now();

        // Voucher không ở trạng thái dùng được
        if ($this->actual_status !== 'active') {
            return false;
        }

        // Hết số lượng
        if ($this->quantity <= 0) {
            return false;
        }

        // Ngoài thời gian áp dụng
        if ($now->lt($this->start_date) || $now->gt($this->end_date)) {
            return false;
        }

        // Không đạt giá trị đơn hàng tối thiểu
        if ((float) $orderTotal < (float) $this->min_order_value) {
            return false;
        }

        // Vượt giới hạn theo user
        if (!$this->canUserUse($currentUses)) {
            return false;
        }

        // Vượt giới hạn trong 1 đơn
        if (!$this->canUseInOrder($usesInOrder)) {
            return false;
        }

        return true;
    }

    /**
     * Tính số tiền được giảm
     */
    public function getDiscount($orderTotal)
    {
        $orderTotal = (float) $orderTotal;

        if ($orderTotal <= 0) {
            return 0;
        }

        // Giảm theo phần trăm
        if ($this->type === 'percent') {
            $discount = $orderTotal * ((float) $this->value / 100);

            if (!is_null($this->max_discount)) {
                $discount = min($discount, (float) $this->max_discount);
            }

            return round($discount, 2);
        }

        // Giảm tiền cố định
        if ($this->type === 'fixed') {
            return min((float) $this->value, $orderTotal);
        }

        return 0;
    }

    /**
     * Giảm số lượng voucher sau khi chốt đơn
     */
    public function decreaseQuantity($amount = 1)
    {
        $amount = max(1, (int) $amount);

        if ($this->quantity >= $amount) {
            $this->decrement('quantity', $amount);
            $this->refresh();
        }

        return $this;
    }

    /**
     * Kiểm tra user có còn lượt dùng voucher không
     */
    public function canUserUse($currentUses = 0)
    {
        if (is_null($this->max_uses_per_user) || $this->max_uses_per_user <= 0) {
            return true;
        }

        return (int) $currentUses < (int) $this->max_uses_per_user;
    }

    /**
     * Kiểm tra voucher có vượt giới hạn trong 1 đơn không
     */
    public function canUseInOrder($usesInOrder = 1)
    {
        if (is_null($this->max_uses_per_order) || $this->max_uses_per_order <= 0) {
            return true;
        }

        return (int) $usesInOrder <= (int) $this->max_uses_per_order;
    }
}
