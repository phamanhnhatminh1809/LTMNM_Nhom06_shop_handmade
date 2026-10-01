<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'username',
        'email',
        'password',
        'full_name',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'admin';
    }

    /**
     * Scope: chỉ lấy tài khoản khách hàng (role_id = 2).
     */
    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('role_id', 2);
    }

    /**
     * Scope: tìm kiếm theo tên, email hoặc số điện thoại.
     */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (empty($keyword)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('full_name', 'like', "%{$keyword}%")
              ->orWhere('email', 'like', "%{$keyword}%")
              ->orWhere('phone', 'like', "%{$keyword}%")
              ->orWhere('username', 'like', "%{$keyword}%");
        });
    }

    /**
     * Scope: lọc theo trạng thái hoạt động.
     * $status: 'active' | 'locked' | '' (tất cả)
     */
    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        return match ($status) {
            'active' => $query->where('is_active', true),
            'locked' => $query->where('is_active', false),
            default  => $query,
        };
    }
}
