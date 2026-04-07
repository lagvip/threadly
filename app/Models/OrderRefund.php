<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRefund extends Model
{
    use HasFactory;

    protected $table = 'order_refunds';

    protected $fillable = [
        'order_id',
        'request_id',
        'refund_type',
        'amount',
        'reason',
        'status',
        'transaction_date',
        'transaction_no',
        'response_code',
        'request_payload',
        'response_payload',
        'requested_by',
        'approved_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public const TYPE_FULL = 'full';
    public const TYPE_PARTIAL = 'partial';

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REJECTED = 'rejected';

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getRefundTypeLabelAttribute(): string
    {
        return match ($this->refund_type) {
            self::TYPE_FULL => 'Hoàn toàn phần',
            self::TYPE_PARTIAL => 'Hoàn một phần',
            default => ucfirst((string) $this->refund_type),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_REQUESTED => 'Đã tạo yêu cầu',
            self::STATUS_PROCESSING => 'Đang xử lý',
            self::STATUS_SUCCESS => 'Thành công',
            self::STATUS_FAILED => 'Thất bại',
            self::STATUS_REJECTED => 'Bị từ chối',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_REQUESTED => 'secondary',
            self::STATUS_PROCESSING => 'warning',
            self::STATUS_SUCCESS => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_REJECTED => 'dark',
            default => 'secondary',
        };
    }
}
