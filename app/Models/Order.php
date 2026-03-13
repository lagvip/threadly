<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
     use SoftDeletes;

    protected $dates = ['deleted_at'];
    use HasFactory;

    protected $table = 'orders';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'order_code',
        'name',
        'phone',
        'email',
        'address',
        'payment_method',
        'payment_status',
        'order_status',
        'shipping_fee',
        'discount',
        'total_price',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }
    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class, 'order_id');
    }
    public function review()
{
    return $this->hasOne(Review::class);
}
}
