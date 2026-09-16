<?php

declare(strict_types=1);

namespace App\Features\Products\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ProductResource
 *
 * API Resource chuẩn hóa dữ liệu đầu ra của Product.
 * Trả về toàn bộ trường + thông tin category (Eager Loaded).
 *
 * @mixin \App\Features\Products\Models\Product
 */
class ProductResource extends JsonResource
{
    /**
     * Chuyển đổi resource thành mảng dữ liệu.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'code'                 => $this->code,
            'category_id'          => $this->category_id,
            'category'             => $this->whenLoaded('category', function () {
                return [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),
            'name'                 => $this->name,
            'description'          => $this->description,
            'instruction_preserve' => $this->instruction_preserve,
            'image'                => $this->image ? asset($this->image) : null,
            'price'                => $this->price,
            'stock_quantity'       => $this->stock_quantity,
            'created_at'           => $this->created_at?->toISOString(),
            'updated_at'           => $this->updated_at?->toISOString(),
        ];
    }
}
