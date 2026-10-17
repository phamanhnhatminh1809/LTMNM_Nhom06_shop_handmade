<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_item_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Scope: lọc đánh giá theo sản phẩm cụ thể.
     */
    public function scopeFilterProduct(Builder $query, ?int $productId): Builder
    {
        if (empty($productId)) {
            return $query;
        }

        return $query->where('product_id', $productId);
    }

    /**
     * Scope: lọc đánh giá theo số sao (1..5).
     */
    public function scopeFilterRating(Builder $query, ?int $rating): Builder
    {
        if (empty($rating)) {
            return $query;
        }

        return $query->where('rating', $rating);
    }

    /**
     * Scope: tìm kiếm theo nội dung bình luận, tên/email khách hàng hoặc tên/mã sản phẩm.
     */
    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if (empty($keyword)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('comment', 'like', "%{$keyword}%")
              ->orWhereHas('user', function (Builder $uq) use ($keyword) {
                  $uq->where('full_name', 'like', "%{$keyword}%")
                     ->orWhere('email', 'like', "%{$keyword}%")
                     ->orWhere('username', 'like', "%{$keyword}%");
              })
              ->orWhereHas('product', function (Builder $pq) use ($keyword) {
                  $pq->where('name', 'like', "%{$keyword}%")
                     ->orWhere('code', 'like', "%{$keyword}%");
              });
        });
    }
}
