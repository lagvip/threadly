<?php

namespace App\Models;

use App\Enums\RefundRequestStatus;
use App\Enums\RefundRequestType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'type',
        'requested_amount',
        'approved_amount',
        'reason',
        'status',
        'admin_id',
        'admin_note',
        'approved_at',
        'rejected_at',
        'restocked_at',
        'restocked_by',
        'restock_note',
    ];

    protected $casts = [
        'requested_amount' => 'float',
        'approved_amount' => 'float',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'restocked_at' => 'datetime',
    ];

    protected $appends = [
        'type_label',
        'status_label',
        'status_badge',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function restockedBy()
    {
        return $this->belongsTo(User::class, 'restocked_by');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function evidences()
    {
        return $this->hasMany(RefundRequestEvidence::class, 'refund_request_id');
    }

    public function items()
    {
        return $this->hasMany(RefundRequestItem::class, 'refund_request_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'refund_request_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return RefundRequestType::tryFrom((string) $this->type)?->label()
            ?? ucfirst((string) $this->type);
    }

    public function getStatusLabelAttribute(): string
    {
        return RefundRequestStatus::tryFrom((string) $this->status)?->label()
            ?? ucfirst((string) $this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return RefundRequestStatus::tryFrom((string) $this->status)?->badge()
            ?? 'secondary';
    }
}
