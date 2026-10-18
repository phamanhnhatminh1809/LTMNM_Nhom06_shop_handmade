@props(['product'])

@php
    $discount = 0;
    if (!empty($product->old_price) && $product->old_price > $product->price) {
        $discount = round((1 - $product->price / $product->old_price) * 100);
    }
    $imageUrl = str_starts_with($product->image ?? '', 'http') 
        ? $product->image 
        : asset(ltrim($product->image ?? '', '/'));
@endphp

<!-- Thẻ Card chuẩn, không dính BOM, không lỗi font -->
<div class="product-card" style="width: 100%; max-width: 100%; height: 100%; margin: 0; padding: 0; display: flex; flex-direction: column; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #f0f0f0;">
    
    <a href="{{ route('products.show', $product->id) }}" class="product-card__image" style="display: block; position: relative; width: 100%; aspect-ratio: 1 / 1; overflow: hidden; background: #f5f5f5;">
        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy" onerror="this.src='{{ asset('assets/images/logo.png') }}'" style="width: 100%; height: 100%; object-fit: cover; display: block;">
        @if ($discount > 0)
            <span class="badge badge--sale" style="position: absolute; top: 10px; left: 10px; background: #c0392b; color: white; padding: 4px 8px; border-radius: 20px; font-size: 12px; font-weight: 600; z-index: 2;">-{{ $discount }}%</span>
        @endif
    </a>

    <div class="product-card__body" style="padding: 14px 16px 16px; display: flex; flex-direction: column; flex: 1; gap: 6px;">
        <span class="product-card__cat" style="font-size: 11px; text-transform: uppercase; color: #8b5e3c; letter-spacing: 0.5px; font-weight: 600;">
            {{ $product->category->name ?? 'Handmade' }}
        </span>

        <h3 class="product-card__name" style="margin: 0; font-size: 15px; line-height: 1.35; font-weight: 600; flex: 1;">
            <a href="{{ route('products.show', $product->id) }}" style="color: #2c2c2c; text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                {{ $product->name }}
            </a>
        </h3>

        <div class="product-card__rating" style="font-size: 12.5px; color: #666;">
            Đánh giá: {{ $product->rating ?? 0 }} · Đã bán {{ $product->sold ?? 0 }}
        </div>

        <div class="product-card__price" style="display: flex; align-items: center; gap: 8px; margin: 4px 0 8px;">
            <span class="price" style="font-size: 17px; font-weight: 700; color: #c0392b;">
                {{ number_format($product->price, 0, ',', '.') }}đ
            </span>
            @if (!empty($product->old_price) && $product->old_price > $product->price)
                <span class="price-old" style="font-size: 13px; color: #999; text-decoration: line-through;">
                    {{ number_format($product->old_price, 0, ',', '.') }}đ
                </span>
            @endif
        </div>

        <a href="{{ route('products.show', $product->id) }}" class="btn btn--primary btn--sm" style="display: block; text-align: center; padding: 9px 12px; background: #6b3e26; color: white; border-radius: 8px; text-decoration: none; font-size: 13.5px; font-weight: 600; margin-top: auto;">
            Xem chi tiết
        </a>
    </div>
</div>