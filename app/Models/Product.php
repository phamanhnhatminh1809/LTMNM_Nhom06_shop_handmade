<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
            'sold' => 'integer',
        ];
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

    /**
     * Tính rating trung bình từ reviews thực tế.
     */
    public function getRatingAttribute()
    {
        $avg = $this->reviews()->avg('rating');
        return $avg > 0 ? round($avg, 1) : ($this->attributes['rating'] ?? 5.0);
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
}