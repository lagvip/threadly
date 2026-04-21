<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public const STATUS_ACTIVE = 1;
    public const STATUS_BANNED = 0;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'status',
        'ban_reason',
        'banned_at',
        'banned_by',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'deleted_at' => 'datetime',
        'banned_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_users', 'user_id', 'role_id')
            ->withTimestamps();
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'user_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function allOrders()
    {
        return $this->hasMany(Order::class, 'user_id', 'id')->withTrashed();
    }

    public function bannedBy()
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    public function roleNames()
    {
        return $this->roles->pluck('name')->toArray();
    }

    public function roleSlugs()
    {
        return $this->roles->pluck('slug')->toArray();
    }

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

    public function isBanned(): bool
    {
        return (int) $this->status === self::STATUS_BANNED;
    }
    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }
}
