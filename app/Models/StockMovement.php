<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const TYPE_IMPORT = 'import';
    public const TYPE_SALE = 'sale';
    public const TYPE_CANCEL_RELEASE = 'cancel_release';
    public const TYPE_REFUND_RESTOCK = 'refund_restock';
    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'product_variant_id',
        'type',
        'quantity_change',
        'stock_before',
        'stock_after',
        'unit_cost',
        'reference_type',
        'reference_id',
        'created_by',
        'note',
    ];

    protected $casts = [
        'quantity_change' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'unit_cost' => 'float',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
