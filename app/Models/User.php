<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // users <-> roles
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_users', 'user_id', 'role_id')
            ->withTimestamps();
    }

    // user -> addresses
    public function addresses()
    {
        return $this->hasMany(Address::class, 'user_id', 'id');
    }

    // kiểm tra 1 role theo slug
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    // kiểm tra nhiều role
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    // lấy danh sách tên role
    public function roleNames()
    {
        return $this->roles->pluck('name')->toArray();
    }

    // lấy danh sách slug role
    public function roleSlugs()
    {
        return $this->roles->pluck('slug')->toArray();
    }

    // viết nhanh
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole(['admin', 'manager']);
    }
}
