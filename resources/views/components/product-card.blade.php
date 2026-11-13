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

<!-- Thẻ Card chuẩn Artisanal Dashboard, nền trắng tinh tế -->
<div class="product-card" style="width: 100%; max-width: 100%; height: 100%; margin: 0; padding: 0; display: flex; flex-direction: column; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(121,42,22,0.04); border: 1px solid #eee7e2; transition: all 0.25s ease;">
    
    <a href="{{ route('products.show', $product->id) }}" class="product-card__image" style="display: block; position: relative; width: 100%; aspect-ratio: 1 / 1; overflow: hidden; background: #faf7f5;">
        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';" style="width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.35s ease;">
        @if ($discount > 0)
            <span class="badge badge--sale" style="position: absolute; top: 10px; left: 10px; background: var(--color-primary, #792a16); color: white; padding: 4px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 700; z-index: 2; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">-{{ $discount }}%</span>
        @endif
    </a>

    <div class="product-card__body" style="padding: 14px 16px 16px; display: flex; flex-direction: column; flex: 1; gap: 6px; background: #ffffff;">
        <span class="product-card__cat" style="font-size: 11px; text-transform: uppercase; color: var(--color-outline, #89726d); letter-spacing: 0.5px; font-weight: 700;">
            {{ $product->category->name ?? 'Handmade' }}
        </span>

        <h3 class="product-card__name" style="margin: 0; font-size: 15px; line-height: 1.4; font-weight: 700; flex: 1;">
            <a href="{{ route('products.show', $product->id) }}" style="color: var(--color-on-surface, #251914); text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                {{ $product->name }}
            </a>
        </h3>

        <div class="product-card__rating" style="display: flex; align-items: center; gap: 4px; font-size: 12.5px; color: var(--color-outline, #89726d);">
            <i class="bi bi-star-fill" style="color: #f59e0b; font-size: 12px;"></i>
            <strong style="color: var(--color-on-surface, #251914);">{{ number_format($product->rating ?? 5, 1) }}</strong>
            <span style="margin: 0 2px; opacity: 0.5;">·</span>
            <span>Đã bán {{ $product->sold ?? 0 }}</span>
        </div>

        <div class="product-card__price" style="display: flex; align-items: baseline; gap: 8px; margin: 4px 0 10px;">
            <span class="price" style="font-size: 17px; font-weight: 800; color: var(--color-primary, #792a16);">
                {{ number_format($product->price, 0, ',', '.') }}đ
            </span>
            @if (!empty($product->old_price) && $product->old_price > $product->price)
                <span class="price-old" style="font-size: 13px; color: #9ca3af; text-decoration: line-through;">
                    {{ number_format($product->old_price, 0, ',', '.') }}đ
                </span>
            @endif
        </div>

        <a href="{{ route('products.show', $product->id) }}" class="btn btn--primary btn--sm" style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 12px; background: var(--color-primary, #792a16); color: white; border-radius: 8px; text-decoration: none; font-size: 13.5px; font-weight: 700; margin-top: auto; transition: background 0.2s;">
            <span>Xem chi tiết</span>
            <i class="bi bi-arrow-right" style="font-size: 13px;"></i>
        </a>
    </div>
</div>