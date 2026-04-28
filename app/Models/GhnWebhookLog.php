<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GhnWebhookLog extends Model
{
    protected $fillable = [
        'order_code',
        'client_order_code',
        'type',
        'status',
        'payload',
        'processed',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean',
    ];
}
