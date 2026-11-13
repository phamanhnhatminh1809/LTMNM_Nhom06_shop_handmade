@props(['product'])

@php
    $imageUrl = str_starts_with($product->image ?? '', 'http')
        ? $product->image
        : asset(ltrim($product->image ?? '', '/'));
    $hasReviews = $product->hasReviews();
    $isInStock = (int) ($product->stock_quantity ?? 0) > 0;
@endphp

<!-- Thẻ Card chuẩn Artisanal Dashboard, nền trắng tinh tế -->
<div class="product-card" style="width: 100%; max-width: 100%; height: 100%; margin: 0; padding: 0; display: flex; flex-direction: column; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(121,42,22,0.04); border: 1px solid #eee7e2; transition: all 0.25s ease;">

    <div class="product-card__media" style="position: relative; width: 100%; aspect-ratio: 1 / 1; overflow: hidden; background: #faf7f5;">
        <a href="{{ route('products.show', $product->id) }}" class="product-card__image" style="display: block; width: 100%; height: 100%;">
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';" style="width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.35s ease;">
        </a>

        @if(!$isInStock)
            <span class="product-card__stock-badge" style="position: absolute; top: 10px; left: 10px; background: rgba(31, 41, 55, 0.86); color: #ffffff; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; z-index: 2;">
                Hết hàng
            </span>
        @endif

        <button type="button"
                class="product-card__quick-cart"
                data-quick-add-cart
                data-product-id="{{ $product->id }}"
                data-product-name="{{ $product->name }}"
                aria-label="{{ $isInStock ? 'Thêm nhanh ' . $product->name . ' vào giỏ hàng' : 'Sản phẩm đã hết hàng' }}"
                title="{{ $isInStock ? 'Thêm nhanh vào giỏ hàng' : 'Sản phẩm hiện đã hết hàng' }}"
                {{ $isInStock ? '' : 'disabled' }}>
            <i class="bi {{ $isInStock ? 'bi-cart-plus' : 'bi-bag-x' }}"></i>
        </button>
    </div>

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
            @if($hasReviews)
                <i class="bi bi-star-fill" style="color: #f59e0b; font-size: 12px;"></i>
                <strong style="color: var(--color-on-surface, #251914);">{{ number_format($product->rating, 1) }}</strong>
            @else
                <i class="bi bi-star" style="color: #cbd5e1; font-size: 12px;"></i>
                <span>Chưa có đánh giá</span>
            @endif
            <span style="margin: 0 2px; opacity: 0.5;">·</span>
            <span>Đã bán {{ $product->sold ?? 0 }}</span>
        </div>

        <div class="product-card__price" style="display: flex; align-items: baseline; gap: 8px; margin: 4px 0 10px;">
            <span class="price" style="font-size: 17px; font-weight: 800; color: var(--color-primary, #792a16);">
                {{ number_format($product->price, 0, ',', '.') }}đ
            </span>
        </div>

        <a href="{{ route('products.show', $product->id) }}" class="btn btn--primary btn--sm" style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 12px; background: var(--color-primary, #792a16); color: white; border-radius: 8px; text-decoration: none; font-size: 13.5px; font-weight: 700; margin-top: auto; transition: background 0.2s;">
            <span>Xem chi tiết</span>
            <i class="bi bi-arrow-right" style="font-size: 13px;"></i>
        </a>
    </div>
</div>
