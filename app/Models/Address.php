<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'recipient_name',
        'phone_number',
        'province',
        'district',
        'ward',
        'detailed_address',
        'ghn_province_id',
        'ghn_district_id',
        'ghn_ward_code',
        'address_type',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->detailed_address,
            $this->ward,
            $this->district,
            $this->province,
        ]));
    }
}
