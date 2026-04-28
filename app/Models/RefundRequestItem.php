<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'refund_request_id',
        'order_detail_id',
        'product_name_snapshot',
        'variant_snapshot',
        'quantity',
        'unit_amount',
        'line_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_amount' => 'float',
        'line_amount' => 'float',
    ];

    public function refundRequest()
    {
        return $this->belongsTo(RefundRequest::class, 'refund_request_id');
    }

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }
}
