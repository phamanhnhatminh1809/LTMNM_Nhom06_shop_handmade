<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    protected $fillable = [
        'code',
        'category_id',
        'name',
        'slug',
        'description',
        'instruction_preserve',
        'image',
        'gallery',
        'price',
        'old_price',
        'stock_quantity',
        'min_stock_threshold',
        'rating',
        'sold',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'is_featured' => 'boolean',
            'price' => 'float',
            'old_price' => 'float',
            'rating' => 'float',
            'stock_quantity' => 'integer',
            'min_stock_threshold' => 'integer',
            'sold' => 'integer',
        ];
    }

    public function effectiveMinStockThreshold(): int
    {
        return max(0, (int) ($this->attributes['min_stock_threshold'] ?? $this->min_stock_threshold ?? 5));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (empty($keyword)) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('code', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%");
        });
    }

    public function scopeFilterCategory(Builder $query, ?string $categorySlug): Builder
    {
        if (empty($categorySlug)) {
            return $query;
        }

        return $query->whereHas('category', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    public function hasReviews(): bool
    {
        if (array_key_exists('reviews_count', $this->attributes)) {
            return (int) $this->attributes['reviews_count'] > 0;
        }

        return $this->reviews()->exists();
    }

    /**
     * Trả về điểm đánh giá trung bình đã lưu sẵn trên bảng products (tránh N+1 query).
     */
    public function getRatingAttribute($value): float
    {
        $rating = $this->attributes['reviews_avg_rating'] ?? $value;

        if ($rating !== null && (float) $rating > 0) {
            return round((float) $rating, 1);
        }

        return 5.0;
    }

    /**
     * Cập nhật lại cột rating trong DB từ reviews.
     */
    public function recalculateRating(): void
    {
        $avg = (float) $this->reviews()->avg('rating');
        $this->attributes['rating'] = $avg > 0 ? round($avg, 1) : 5.0;
        $this->save();
    }

    protected static function booted(): void
    {
        $clearCaches = static function (): void {
            Cache::forget('admin_dashboard_stats');
            Cache::forget('admin_review_filter_products');
            Cache::forget('ai_chat_catalog_context');
        };

        static::saved($clearCaches);
        static::deleted($clearCaches);
    }
}