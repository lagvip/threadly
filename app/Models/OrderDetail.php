<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_details'; // Bảng tương ứng
    protected $primaryKey = 'id';

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'product_name',
        'quantity',
        'unit_price',
        'total',
    ];

    /**
     * Quan hệ: OrderDetail thuộc về Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Quan hệ: OrderDetail thuộc về Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Quan hệ: OrderDetail thuộc về ProductVariant
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
