<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';
    protected $primaryKey = 'id';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'order_code',
        'name',
        'phone',
        'email',
        'address',
        'payment_method',
        'payment_status',
        'order_status',
        'shipping_fee',
        'discount',
        'voucher_id',
        'voucher_code',
        'total_price',
        'previous_status',
        'cancel_reason',
        'customer_note',

        // VNPay metadata
        'payment_request_date',
        'payment_expire_date',
        'payment_transaction_no',
        'payment_bank_code',
        'payment_response_code',
        'payment_transaction_status',
        'payment_pay_date',
        'paid_at',
    ];

    protected $casts = [
        'shipping_fee' => 'float',
        'discount' => 'float',
        'total_price' => 'float',
        'paid_at' => 'datetime',
    ];

    protected $appends = [
        'payment_status_label',
        'payment_status_badge',
        'order_status_label',
        'order_status_badge',
        'can_cancel',
        'can_repay',
        'cancel_action_type',
        'can_review',
        'pending_review_count',
        'has_pending_review',
    ];

    public const PAYMENT_METHOD_VNPAY = 'vnpay';
    public const PAYMENT_METHOD_COD   = 'cod';

    public const PAYMENT_UNPAID    = 'unpaid';
    public const PAYMENT_PENDING   = 'pending';
    public const PAYMENT_PAID      = 'paid';
    public const PAYMENT_FAILED    = 'failed';
    public const PAYMENT_CANCELLED = 'cancelled';
    public const PAYMENT_EXPIRED   = 'expired';

    public const STATUS_PENDING                  = 'pending';
    public const STATUS_PROCESSING               = 'processing';
    public const STATUS_SHIPPED                  = 'shipped';
    public const STATUS_DELIVERED                = 'delivered';
    public const STATUS_CANCELLED                = 'cancelled';
    public const STATUS_WAITING_FOR_CANCELLATION = 'waiting_for_cancellation';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class, 'order_id');
    }

    public function review()
    {
        return $this->hasOne(Review::class)->latestOfMany();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'order_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function refunds()
    {
        return $this->hasMany(OrderRefund::class, 'order_id');
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_UNPAID    => 'Chưa thanh toán',
            self::PAYMENT_PENDING   => 'Chờ thanh toán',
            self::PAYMENT_PAID      => 'Đã thanh toán',
            self::PAYMENT_FAILED    => 'Thất bại',
            self::PAYMENT_CANCELLED => 'Đã hủy',
            self::PAYMENT_EXPIRED   => 'Hết hạn',
            default => ucfirst((string) $this->payment_status),
        };
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_PAID      => 'success',
            self::PAYMENT_PENDING   => 'warning',
            self::PAYMENT_UNPAID    => 'secondary',
            self::PAYMENT_FAILED    => 'danger',
            self::PAYMENT_CANCELLED => 'dark',
            self::PAYMENT_EXPIRED   => 'secondary',
            default => 'secondary',
        };
    }

    public function getOrderStatusLabelAttribute(): string
    {
        return match ($this->order_status) {
            self::STATUS_PENDING                  => 'Chờ xử lý',
            self::STATUS_PROCESSING               => 'Đang xử lý',
            self::STATUS_SHIPPED                  => 'Đang giao',
            self::STATUS_DELIVERED                => 'Đã giao',
            self::STATUS_CANCELLED                => 'Đã hủy',
            self::STATUS_WAITING_FOR_CANCELLATION => 'Chờ xác nhận hủy',
            default => ucfirst((string) $this->order_status),
        };
    }

    public function getOrderStatusBadgeAttribute(): string
    {
        return match ($this->order_status) {
            self::STATUS_PENDING                  => 'warning',
            self::STATUS_PROCESSING               => 'info',
            self::STATUS_SHIPPED                  => 'primary',
            self::STATUS_DELIVERED                => 'success',
            self::STATUS_CANCELLED                => 'dark',
            self::STATUS_WAITING_FOR_CANCELLATION => 'secondary',
            default => 'secondary',
        };
    }

    public function canCustomerCancelDirectly(): bool
    {
        if ($this->order_status !== self::STATUS_PENDING) {
            return false;
        }

        if ($this->payment_method === self::PAYMENT_METHOD_COD) {
            return $this->payment_status === self::PAYMENT_UNPAID;
        }

        if ($this->payment_method === self::PAYMENT_METHOD_VNPAY) {
            return in_array($this->payment_status, [
                self::PAYMENT_UNPAID,
                self::PAYMENT_PENDING,
                self::PAYMENT_FAILED,
            ], true);
        }

        return false;
    }

    public function canCustomerRequestCancellation(): bool
    {
        return false;
    }

    public function isAutoExpiredVnpay(): bool
    {
        return $this->payment_method === self::PAYMENT_METHOD_VNPAY
            && $this->payment_status === self::PAYMENT_EXPIRED
            && in_array($this->order_status, [
                self::STATUS_PENDING,
                self::STATUS_CANCELLED,
            ], true);
    }

    public function canRepayVnpay(): bool
    {
        if ($this->payment_method !== self::PAYMENT_METHOD_VNPAY) {
            return false;
        }

        if (in_array($this->order_status, [
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_WAITING_FOR_CANCELLATION,
        ], true)) {
            return false;
        }

        if ($this->isAutoExpiredVnpay()) {
            return true;
        }

        return $this->order_status === self::STATUS_PENDING
            && in_array($this->payment_status, [
                self::PAYMENT_UNPAID,
                self::PAYMENT_PENDING,
                self::PAYMENT_FAILED,
            ], true);
    }

    public function canReviewProducts(): bool
    {
        return $this->order_status === self::STATUS_DELIVERED
            && $this->payment_status === self::PAYMENT_PAID;
    }

    public function getCanCancelAttribute(): bool
    {
        return $this->canCustomerCancelDirectly()
            || $this->canCustomerRequestCancellation();
    }

    public function getCancelActionTypeAttribute(): string
    {
        if ($this->canCustomerCancelDirectly()) {
            return 'direct';
        }

        return 'none';
    }

    public function getCanRepayAttribute(): bool
    {
        return $this->canRepayVnpay();
    }

    public function getCanReviewAttribute(): bool
    {
        return $this->canReviewProducts();
    }

    public function getPendingReviewCountAttribute(): int
    {
        $productIds = $this->relationLoaded('details')
            ? $this->details->pluck('product_id')->filter()->unique()->values()
            : $this->details()->pluck('product_id')->filter()->unique()->values();

        if ($productIds->isEmpty()) {
            return 0;
        }

        $reviewedIds = $this->relationLoaded('reviews')
            ? $this->reviews->pluck('product_id')->filter()->unique()->values()
            : $this->reviews()->pluck('product_id')->filter()->unique()->values();

        return $productIds->diff($reviewedIds)->count();
    }

    public function getHasPendingReviewAttribute(): bool
    {
        return $this->pending_review_count > 0;
    }
}
