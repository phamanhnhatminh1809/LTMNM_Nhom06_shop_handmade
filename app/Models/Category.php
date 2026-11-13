<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Lấy mã class Bootstrap Icon chuẩn hóa cho danh mục.
     */
    public function getDisplayIconAttribute(): string
    {
        $rawIcon = trim((string) $this->icon);

        if (!empty($rawIcon) && str_starts_with($rawIcon, 'bi-')) {
            return $rawIcon;
        }

        $iconMap = [
            '👜' => 'bi-bag-heart',
            '🔑' => 'bi-key',
            '💍' => 'bi-gem',
            '🧸' => 'bi-heart',
            '🧣' => 'bi-snow',
            '🌸' => 'bi-flower1',
            '🏺' => 'bi-cup-hot',
            '🕯️' => 'bi-fire',
            '💐' => 'bi-flower2',
            '🌿' => 'bi-tree',
            '🎨' => 'bi-palette',
            'tui-xach'     => 'bi-bag-heart',
            'moc-khoa'     => 'bi-key',
            'trang-suc'    => 'bi-gem',
            'gau-bong'     => 'bi-heart',
            'khan-len'     => 'bi-snow',
            'khan-len-non' => 'bi-snow',
            'hoa-handmade' => 'bi-flower1',
            'nen-thom'     => 'bi-fire',
            'gom-su'       => 'bi-cup-hot',
        ];

        return $iconMap[$rawIcon] ?? $iconMap[$this->slug] ?? 'bi-tag';
    }

    protected static function booted(): void
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('global_categories'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('global_categories'));
    }
}
