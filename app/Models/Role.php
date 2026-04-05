<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'slug',
        'name',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_users', 'role_id', 'user_id')
            ->withTimestamps();
    }

    // Lấy cả user đã soft delete để check xóa role cho chặt
    public function usersWithTrashed()
    {
        return $this->belongsToMany(User::class, 'role_users', 'role_id', 'user_id')
            ->withTimestamps()
            ->withTrashed();
    }
}
