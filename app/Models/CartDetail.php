<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartDetail extends Model
{
    protected $table = 'carts_details';

    protected $fillable = [
        'id_cart',
        'id_variant',
        'quantity',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'id_cart');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'id_variant');
    }
}
