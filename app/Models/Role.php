<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
