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

        // GHN shipping metadata
        'shipping_address_id',
        'ghn_to_province_id',
        'ghn_to_district_id',
        'ghn_to_ward_code',
        'ghn_order_code',
        'ghn_client_order_code',
        'ghn_status',
        'ghn_status_name',
        'ghn_service_id',
        'ghn_service_type_id',
        'ghn_expected_delivery_time',
        'ghn_raw_response',
        'ghn_synced_at',

        // VNPay / payment metadata
        'payment_request_date',
        'payment_expire_date',
        'payment_transaction_no',
        'payment_bank_code',
        'payment_response_code',
        'payment_transaction_status',
        'payment_pay_date',
        'paid_at',

        // Customer confirmation
        'customer_confirmed_at',

        // Demo refund wallet metadata
        'refund_status',
        'refunded_amount',
        'last_refund_requested_at',
        'last_refunded_at',
    ];

    protected $casts = [
        'shipping_fee' => 'float',
        'discount' => 'float',
        'total_price' => 'float',
        'paid_at' => 'datetime',
        'customer_confirmed_at' => 'datetime',
        'refunded_amount' => 'float',
        'last_refund_requested_at' => 'datetime',
        'last_refunded_at' => 'datetime',
        'ghn_expected_delivery_time' => 'datetime',
        'ghn_raw_response' => 'array',
        'ghn_synced_at' => 'datetime',
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
        'ghn_status_group',
        'ghn_status_group_badge',
        'can_confirm_received',
        'refund_status_label',
        'refundable_product_subtotal',
        'refundable_product_amount',
        'refundable_amount',
        'net_paid_amount',
        'can_request_refund',
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

    public const REFUND_NONE = 'none';
    public const REFUND_REQUESTED = 'requested';
    public const REFUND_PARTIALLY_REFUNDED = 'partially_refunded';
    public const REFUND_REFUNDED = 'refunded';
    public const REFUND_REJECTED = 'rejected';

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
        return $this->hasMany(RefundRequest::class, 'order_id');
    }

    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class, 'order_id');
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

    public function getGhnStatusGroupAttribute(): string
    {
        return match ((string) $this->ghn_status) {
            'ready_to_pick',
            'picking',
            'money_collect_picking' => 'Chờ bàn giao',

            'picked',
            'storing',
            'transporting',
            'sorting',
            'delivering',
            'money_collect_delivering' => 'Đã bàn giao - Đang giao',

            'delivery_fail' => 'Chờ xác nhận giao lại',

            'waiting_to_return',
            'return',
            'return_transporting',
            'return_sorting',
            'returning',
            'return_fail',
            'returned' => 'Đã bàn giao - đang hoàn hàng',

            'delivered' => 'Hoàn tất',

            'cancel' => 'Đơn hủy',

            'exception',
            'damage',
            'lost' => 'Hàng thất lạc - hư hỏng',

            default => $this->ghn_order_code ? 'Không xác định' : 'Chưa gửi GHN',
        };
    }

    public function getGhnStatusGroupBadgeAttribute(): string
    {
        return match ((string) $this->ghn_status) {
            'ready_to_pick',
            'picking',
            'money_collect_picking' => 'bg-primary',

            'picked',
            'storing',
            'transporting',
            'sorting',
            'delivering',
            'money_collect_delivering' => 'bg-info',

            'delivery_fail' => 'bg-warning text-dark',

            'waiting_to_return',
            'return',
            'return_transporting',
            'return_sorting',
            'returning',
            'return_fail',
            'returned' => 'bg-secondary',

            'delivered' => 'bg-success',

            'cancel' => 'bg-danger',

            'exception',
            'damage',
            'lost' => 'bg-dark',

            default => 'bg-light text-dark',
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

    public function canCancelPaidVnpayBeforeProcessing(): bool
    {
        if ($this->payment_method !== self::PAYMENT_METHOD_VNPAY) {
            return false;
        }

        if ($this->payment_status !== self::PAYMENT_PAID) {
            return false;
        }

        if ($this->order_status !== self::STATUS_PENDING) {
            return false;
        }

        if (!empty($this->ghn_order_code)) {
            return false;
        }

        if ($this->refundable_amount <= 0) {
            return false;
        }

        if (($this->refund_status ?? self::REFUND_NONE) === self::REFUND_REFUNDED) {
            return false;
        }

        if ($this->hasPendingRefundRequest()) {
            return false;
        }

        return true;
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

    public function canCustomerConfirmReceived(): bool
    {
        if (!empty($this->customer_confirmed_at)) {
            return false;
        }

        if ($this->order_status !== self::STATUS_DELIVERED) {
            return false;
        }

        if ($this->payment_status !== self::PAYMENT_PAID) {
            return false;
        }

        if ($this->ghn_order_code && $this->ghn_status !== 'delivered') {
            return false;
        }

        return true;
    }

    public function canReviewProducts(): bool
    {
        return $this->order_status === self::STATUS_DELIVERED
            && $this->payment_status === self::PAYMENT_PAID
            && !empty($this->customer_confirmed_at);
    }

    public function getCanCancelAttribute(): bool
    {
        return $this->canCustomerCancelDirectly()
            || $this->canCancelPaidVnpayBeforeProcessing()
            || $this->canCustomerRequestCancellation();
    }

    public function getCancelActionTypeAttribute(): string
    {
        if ($this->canCustomerCancelDirectly()) {
            return 'direct';
        }

        if ($this->canCancelPaidVnpayBeforeProcessing()) {
            return 'paid_vnpay_refund';
        }

        return 'none';
    }

    public function getCanRepayAttribute(): bool
    {
        return $this->canRepayVnpay();
    }

    public function getCanConfirmReceivedAttribute(): bool
    {
        return $this->canCustomerConfirmReceived();
    }

    public function getCanReviewAttribute(): bool
    {
        return $this->canReviewProducts();
    }

    public function getPendingReviewCountAttribute(): int
    {
        $detailIds = $this->relationLoaded('details')
            ? $this->details->pluck('id')->filter()->values()
            : $this->details()->pluck('id')->filter()->values();

        if ($detailIds->isEmpty()) {
            return 0;
        }

        $reviewedDetailIds = $this->relationLoaded('reviews')
            ? $this->reviews->pluck('order_detail_id')->filter()->values()
            : $this->reviews()->pluck('order_detail_id')->filter()->values();

        return $detailIds->diff($reviewedDetailIds)->count();
    }

    public function getHasPendingReviewAttribute(): bool
    {
        return $this->pending_review_count > 0;
    }

    public function pendingRefundRequest()
    {
        return $this->hasOne(RefundRequest::class, 'order_id')
            ->where('status', RefundRequest::STATUS_PENDING)
            ->latestOfMany();
    }

    public function getRefundStatusLabelAttribute(): string
    {
        return match ($this->refund_status ?: self::REFUND_NONE) {
            self::REFUND_NONE => 'Chưa hoàn tiền',
            self::REFUND_REQUESTED => 'Đang chờ duyệt hoàn tiền',
            self::REFUND_PARTIALLY_REFUNDED => 'Đã hoàn một phần',
            self::REFUND_REFUNDED => 'Đã hoàn hết giá trị sản phẩm',
            self::REFUND_REJECTED => 'Yêu cầu hoàn bị từ chối',
            default => ucfirst((string) $this->refund_status),
        };
    }

    /**
     * Tổng giá trị sản phẩm gốc trong đơn, không bao gồm phí vận chuyển.
     */
    public function getRefundableProductSubtotalAttribute(): float
    {
        $subtotal = $this->relationLoaded('details')
            ? $this->details->sum(fn ($detail) => (float) $detail->total)
            : (float) $this->details()->sum('total');

        return max((float) $subtotal, 0);
    }

    /**
     * Tổng giá trị sản phẩm còn có thể dùng làm cơ sở hoàn tiền.
     * Không hoàn phí vận chuyển. Nếu có voucher, trừ discount khỏi phần sản phẩm.
     */
    public function getRefundableProductAmountAttribute(): float
    {
        $productSubtotal = (float) $this->refundable_product_subtotal;
        $discount = (float) ($this->discount ?? 0);

        return max($productSubtotal - $discount, 0);
    }

    /**
     * Số tiền sản phẩm còn có thể hoàn.
     * Tuyệt đối không tính phí vận chuyển vào số tiền còn hoàn.
     */
    public function getRefundableAmountAttribute(): float
    {
        $refundedAmount = (float) ($this->refunded_amount ?? 0);

        return max((float) $this->refundable_product_amount - $refundedAmount, 0);
    }

    /**
     * Số tiền thực thu sau hoàn: tổng khách đã thanh toán ban đầu - tiền đã hoàn.
     * Giá trị này vẫn có thể còn phí vận chuyển nếu shop không hoàn phí ship.
     */
    public function getNetPaidAmountAttribute(): float
    {
        return max((float) $this->total_price - (float) ($this->refunded_amount ?? 0), 0);
    }

    public function hasPendingRefundRequest(): bool
    {
        if ($this->relationLoaded('refundRequests')) {
            return $this->refundRequests->contains('status', RefundRequest::STATUS_PENDING);
        }

        return $this->refundRequests()
            ->where('status', RefundRequest::STATUS_PENDING)
            ->exists();
    }

    public function canRequestRefund(): bool
    {
        // Demo refund: cho phép cả VNPay và COD, nhưng đều hoàn vào ví nội bộ.
        // Không gọi API hoàn tiền thật của VNPay/GHN và không hoàn phí vận chuyển.
        if (!in_array($this->payment_method, [
            self::PAYMENT_METHOD_VNPAY,
            self::PAYMENT_METHOD_COD,
        ], true)) {
            return false;
        }

        // COD chỉ được hoàn khi đã giao thành công và đã thu tiền.
        // VNPay cũng chỉ được hoàn khi callback thanh toán đã thành công.
        if ($this->payment_status !== self::PAYMENT_PAID) {
            return false;
        }

        if ($this->order_status !== self::STATUS_DELIVERED) {
            return false;
        }

        // Nếu đơn có vận đơn GHN thì phải chờ GHN báo delivered.
        if ($this->ghn_order_code && $this->ghn_status !== 'delivered') {
            return false;
        }

        if ($this->refundable_amount <= 0) {
            return false;
        }

        if (($this->refund_status ?? self::REFUND_NONE) === self::REFUND_REFUNDED) {
            return false;
        }

        if ($this->hasPendingRefundRequest()) {
            return false;
        }

        return true;
    }

    public function getCanRequestRefundAttribute(): bool
    {
        return $this->canRequestRefund();
    }

}
