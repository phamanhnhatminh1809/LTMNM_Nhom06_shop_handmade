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
        'address',
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

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Kiểm tra tài khoản là Quản trị viên (toàn quyền).
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === Role::ADMIN;
    }

    /**
     * Kiểm tra tài khoản là Nhân viên kho.
     */
    public function isWarehouseStaff(): bool
    {
        return $this->role && $this->role->name === Role::WAREHOUSE_STAFF;
    }

    /**
     * Kiểm tra tài khoản là Nhân viên bán hàng.
     */
    public function isSalesStaff(): bool
    {
        return $this->role && $this->role->name === Role::SALES_STAFF;
    }

    /**
     * Kiểm tra tài khoản có quyền vào trang Quản trị (Admin hoặc Nhân viên).
     */
    public function isStaffOrAdmin(): bool
    {
        return $this->role && in_array($this->role->name, [
            Role::ADMIN,
            Role::WAREHOUSE_STAFF,
            Role::SALES_STAFF,
        ], true);
    }

    /**
     * Kiểm tra quyền quản lý Kho (Sản phẩm & Loại sản phẩm).
     */
    public function canManageWarehouse(): bool
    {
        return $this->isAdmin() || $this->isWarehouseStaff();
    }

    /**
     * Kiểm tra quyền quản lý Bán hàng (Đơn hàng & Đánh giá).
     */
    public function canManageSales(): bool
    {
        return $this->isAdmin() || $this->isSalesStaff();
    }

    /**
     * Kiểm tra tài khoản có một trong các role truyền vào hay không.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->role && in_array($this->role->name, $roles, true);
    }

    /**
     * Scope: chỉ lấy tài khoản khách hàng (role_id = 2).
     */
    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('role_id', 2);
    }

    /**
     * Scope: chỉ lấy tài khoản nội bộ (Admin, Nhân viên kho, Nhân viên bán hàng).
     */
    public function scopeStaff(Builder $query): Builder
    {
        return $query->whereHas('role', function (Builder $q) {
            $q->whereIn('name', [Role::ADMIN, Role::WAREHOUSE_STAFF, Role::SALES_STAFF]);
        });
    }

    /**
     * Scope: tìm kiếm theo tên, email, số điện thoại hoặc username.
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
