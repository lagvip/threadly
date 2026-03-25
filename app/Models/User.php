<?php

namespace App\Models;

use Cartalyst\Sentinel\Users\EloquentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends EloquentUser
{
    use HasFactory;

    protected $fillable = [
        'email',
        'password',
        'last_name',
        'first_name',
        'permissions',
    ];

    protected $loginNames = ['email'];
}
