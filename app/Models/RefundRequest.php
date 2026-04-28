<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundRequest extends Model
{
    use HasFactory;

    public const TYPE_FULL = 'full';
    public const TYPE_PARTIAL = 'partial';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

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
    ];

    protected $casts = [
        'requested_amount' => 'float',
        'approved_amount' => 'float',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
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
        return match ($this->type) {
            self::TYPE_FULL => 'Hoàn toàn bộ đơn',
            self::TYPE_PARTIAL => 'Hoàn 1 phần đơn',
            default => ucfirst((string) $this->type),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Chờ admin duyệt',
            self::STATUS_APPROVED => 'Đã hoàn tiền',
            self::STATUS_REJECTED => 'Đã từ chối',
            self::STATUS_CANCELLED => 'Đã hủy yêu cầu',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning text-dark',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'secondary',
        };
    }
}
