<?php

declare(strict_types=1);

namespace App\Features\Products\Models;

use App\Features\Categories\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Product
 *
 * Eloquent Model đại diện cho bảng products.
 *
 * @property int         $id
 * @property string      $code
 * @property int         $category_id
 * @property string      $name
 * @property string|null $description
 * @property string|null $instruction_preserve
 * @property string|null $image
 * @property float       $price
 * @property int         $stock_quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Category $category
 */
class Product extends Model
{
    /**
     * Tên bảng trong CSDL.
     */
    protected $table = 'products';

    /**
     * Các trường cho phép gán hàng loạt.
     */
    protected $fillable = [
        'code',
        'category_id',
        'name',
        'description',
        'instruction_preserve',
        'image',
        'price',
        'stock_quantity',
    ];

    /**
     * Ép kiểu dữ liệu cho các trường.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price'          => 'decimal:2',
            'stock_quantity' => 'integer',
            'category_id'   => 'integer',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    /**
     * Quan hệ N-1: Sản phẩm thuộc về một danh mục.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    // =========================================================================
    // QUERY SCOPES
    // =========================================================================

    /**
     * Scope: Lọc sản phẩm theo category_id.
     */
    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: Tìm kiếm sản phẩm theo tên (LIKE %keyword%).
     */
    public function scopeSearchByName(Builder $query, string $keyword): Builder
    {
        return $query->where('name', 'LIKE', '%' . $keyword . '%');
    }

    /**
     * Scope: Lọc sản phẩm theo khoảng giá.
     */
    public function scopeByPriceRange(Builder $query, ?float $min = null, ?float $max = null): Builder
    {
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }

        if ($max !== null) {
            $query->where('price', '<=', $max);
        }

        return $query;
    }

    /**
     * Scope: Chỉ lấy sản phẩm còn hàng (stock_quantity > 0).
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }
}
