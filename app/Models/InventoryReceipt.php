<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryReceipt extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_POSTED = 'posted';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'receipt_code',
        'created_by',
        'status',
        'note',
        'posted_at',
        'posted_by',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_cost' => 'float',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryReceiptItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
