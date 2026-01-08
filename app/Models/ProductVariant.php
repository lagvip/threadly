<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];
     protected $table = 'product_variants';

    protected $fillable = [
        'id_product',
        'quantity',
        'id_color',
        'id_size',
        'price',
        'status',
        'image',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }
    public function productAlbum()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'id_color');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'id_size');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'id_variant');
    }
}
