<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Role extends Model
{
    public const ADMIN = 'admin';
    public const CUSTOMER = 'customer';
    public const WAREHOUSE_STAFF = 'warehouse_staff';
    public const SALES_STAFF = 'sales_staff';

    protected $fillable = ['id', 'name'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Trả về bảng ánh xạ [role_id => role_name] có cache để kiểm tra phân quyền không cần query lặp.
     */
    public static function cachedMap(): array
    {
        return Cache::remember('global_roles_by_id', 3600, function () {
            return static::pluck('name', 'id')->all();
        });
    }

    /**
     * Nhãn tiếng Việt của từng quyền trong hệ thống.
     */
    public static function roleLabels(): array
    {
        return [
            self::ADMIN           => 'Quản trị viên',
            self::WAREHOUSE_STAFF => 'Nhân viên kho',
            self::SALES_STAFF     => 'Nhân viên bán hàng',
            self::CUSTOMER        => 'Khách hàng',
        ];
    }

    public function getLabelAttribute(): string
    {
        return self::roleLabels()[$this->name] ?? $this->name;
    }

    protected static function booted(): void
    {
        $clearRoleCaches = static function (): void {
            Cache::forget('global_roles_by_id');
            Cache::forget('default_roles_seeded');
        };

        static::saved($clearRoleCaches);
        static::deleted($clearRoleCaches);
    }
}
