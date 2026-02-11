<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    use HasFactory;

    protected $table = 'shippings';
    
    protected $fillable = [
    'provider_name',
    'price_inner', // <--- Mới
    'price_outer', // <--- Mới
    'status',
    ];
}
