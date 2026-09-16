<?php

declare(strict_types=1);

namespace App\Features\Categories\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Category
 *
 * Eloquent Model đại diện cho bảng categories.
 *
 * @property int    $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Category extends Model
{
    /**
     * Tên bảng trong CSDL.
     */
    protected $table = 'categories';

    /**
     * Các trường cho phép gán hàng loạt.
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Quan hệ 1-N: Một danh mục có nhiều sản phẩm.
     */
    public function products(): HasMany
    {
        return $this->hasMany(
            \App\Features\Products\Models\Product::class,
            'category_id',
            'id'
        );
    }
}
