<?php

namespace App\Models;

use App\Enums\OrderRefundRecordStatus;
use App\Enums\OrderRefundRecordType;
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
        return OrderRefundRecordType::tryFrom((string) $this->refund_type)?->label()
            ?? ucfirst((string) $this->refund_type);
    }

    public function getStatusLabelAttribute(): string
    {
        return OrderRefundRecordStatus::tryFrom((string) $this->status)?->label()
            ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return OrderRefundRecordStatus::tryFrom((string) $this->status)?->badge()
            ?? 'secondary';
    }
}
