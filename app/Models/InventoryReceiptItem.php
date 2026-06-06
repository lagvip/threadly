<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReceiptItem extends Model
{
    protected $fillable = [
        'inventory_receipt_id',
        'product_variant_id',
        'quantity',
        'unit_cost',
        'stock_before',
        'stock_after',
        'note',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'float',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(InventoryReceipt::class, 'inventory_receipt_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
