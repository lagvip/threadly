<?php

namespace App\Models;

use App\Enums\GhnOrderStatus;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderRefundStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\RefundRequestStatus;
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
        'payment_method_label',
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
        return OrderPaymentStatus::tryFrom((string) $this->payment_status)?->label()
            ?? ucfirst((string) $this->payment_status);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return PaymentMethod::tryFrom((string) $this->payment_method)?->label()
            ?? strtoupper((string) $this->payment_method);
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        return OrderPaymentStatus::tryFrom((string) $this->payment_status)?->badge()
            ?? 'secondary';
    }

    public function getOrderStatusLabelAttribute(): string
    {
        return OrderStatus::tryFrom((string) $this->order_status)?->label()
            ?? ucfirst((string) $this->order_status);
    }

    public function getOrderStatusBadgeAttribute(): string
    {
        return OrderStatus::tryFrom((string) $this->order_status)?->badge()
            ?? 'secondary';
    }

    public function getGhnStatusGroupAttribute(): string
    {
        return GhnOrderStatus::groupFor((string) $this->ghn_status, (bool) $this->ghn_order_code);
    }

    public function getGhnStatusGroupBadgeAttribute(): string
    {
        return GhnOrderStatus::badgeFor((string) $this->ghn_status);
    }

    public function canCustomerCancelDirectly(): bool
    {
        if ($this->order_status !== OrderStatus::Pending->value) {
            return false;
        }

        if ($this->payment_method === PaymentMethod::Cod->value) {
            return $this->payment_status === OrderPaymentStatus::Unpaid->value;
        }

        if ($this->payment_method === PaymentMethod::Vnpay->value) {
            return in_array($this->payment_status, [
                OrderPaymentStatus::Unpaid->value,
                OrderPaymentStatus::Pending->value,
                OrderPaymentStatus::Failed->value,
            ], true);
        }

        return false;
    }

    public function canCancelPaidVnpayBeforeProcessing(): bool
    {
        if ($this->payment_method !== PaymentMethod::Vnpay->value) {
            return false;
        }

        if ($this->payment_status !== OrderPaymentStatus::Paid->value) {
            return false;
        }

        if ($this->order_status !== OrderStatus::Pending->value) {
            return false;
        }

        if (! empty($this->ghn_order_code)) {
            return false;
        }

        if ($this->refundable_amount <= 0) {
            return false;
        }

        if (($this->refund_status ?? OrderRefundStatus::None->value) === OrderRefundStatus::Refunded->value) {
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
        return $this->payment_method === PaymentMethod::Vnpay->value
            && $this->payment_status === OrderPaymentStatus::Expired->value
            && in_array($this->order_status, [
                OrderStatus::Pending->value,
                OrderStatus::Cancelled->value,
            ], true);
    }

    public function canRepayVnpay(): bool
    {
        if ($this->payment_method !== PaymentMethod::Vnpay->value) {
            return false;
        }

        if (in_array($this->order_status, [
            OrderStatus::Shipped->value,
            OrderStatus::Delivered->value,
            OrderStatus::WaitingForCancellation->value,
        ], true)) {
            return false;
        }

        if ($this->isAutoExpiredVnpay()) {
            return true;
        }

        return $this->order_status === OrderStatus::Pending->value
            && in_array($this->payment_status, [
                OrderPaymentStatus::Unpaid->value,
                OrderPaymentStatus::Pending->value,
                OrderPaymentStatus::Failed->value,
            ], true);
    }

    public function canCustomerConfirmReceived(): bool
    {
        if (! empty($this->customer_confirmed_at)) {
            return false;
        }

        if ($this->order_status !== OrderStatus::Delivered->value) {
            return false;
        }

        if ($this->payment_status !== OrderPaymentStatus::Paid->value) {
            return false;
        }

        if ($this->ghn_order_code && $this->ghn_status !== GhnOrderStatus::Delivered->value) {
            return false;
        }

        return true;
    }

    public function canReviewProducts(): bool
    {
        return $this->order_status === OrderStatus::Delivered->value
            && $this->payment_status === OrderPaymentStatus::Paid->value
            && ! empty($this->customer_confirmed_at);
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
            ->where('status', RefundRequestStatus::Pending->value)
            ->latestOfMany();
    }

    public function getRefundStatusLabelAttribute(): string
    {
        return OrderRefundStatus::tryFrom((string) ($this->refund_status ?: OrderRefundStatus::None->value))?->label()
            ?? ucfirst((string) $this->refund_status);
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
            return $this->refundRequests->contains('status', RefundRequestStatus::Pending->value);
        }

        return $this->refundRequests()
            ->where('status', RefundRequestStatus::Pending->value)
            ->exists();
    }

    public function canRequestRefund(): bool
    {
        // Demo refund: cho phép cả VNPay và COD, nhưng đều hoàn vào ví nội bộ.
        // Không gọi API hoàn tiền thật của VNPay/GHN và không hoàn phí vận chuyển.
        if (! in_array($this->payment_method, [
            PaymentMethod::Vnpay->value,
            PaymentMethod::Cod->value,
        ], true)) {
            return false;
        }

        // COD chỉ được hoàn khi đã giao thành công và đã thu tiền.
        // VNPay cũng chỉ được hoàn khi callback thanh toán đã thành công.
        if ($this->payment_status !== OrderPaymentStatus::Paid->value) {
            return false;
        }

        if ($this->order_status !== OrderStatus::Delivered->value) {
            return false;
        }

        // Nếu đơn có vận đơn GHN thì phải chờ GHN báo delivered.
        if ($this->ghn_order_code && $this->ghn_status !== GhnOrderStatus::Delivered->value) {
            return false;
        }

        if ($this->refundable_amount <= 0) {
            return false;
        }

        if (($this->refund_status ?? OrderRefundStatus::None->value) === OrderRefundStatus::Refunded->value) {
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
