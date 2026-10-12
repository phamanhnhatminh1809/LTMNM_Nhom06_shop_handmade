@props(['product'])
@php
    $discount = 0;
    if (!empty($product->old_price) && $product->old_price > $product->price) {
        $discount = round((1 - $product->price / $product->old_price) * 100);
    }
    $imageUrl = str_starts_with($product->image, 'http') 
        ? $product->image 
        : asset(ltrim($product->image, '/'));
@endphp
<div class="product-card">
    <a href="{{ route('products.show', $product->id) }}" class="product-card__image">
        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy" onerror="this.src='{{ asset('images/logo.png') }}'">
        @if ($discount > 0)
            <span class="badge badge--sale">-{{ $discount }}%</span>
        @endif
    </a>
    <div class="product-card__body">
        <span class="product-card__cat">{{ $product->category->name ?? 'Handmade' }}</span>
        <h3 class="product-card__name">
            <a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a>
        </h3>
        <div class="product-card__rating">
            Đánh giá: {{ $product->rating }} · Đã bán {{ $product->sold }}
        </div>
        <div class="product-card__price">
            <span class="price">{{ number_format($product->price, 0, ',', '.') }}đ</span>
            @if (!empty($product->old_price))
                <span class="price-old">{{ number_format($product->old_price, 0, ',', '.') }}đ</span>
            @endif
        </div>
        <a href="{{ route('products.show', $product->id) }}" class="btn btn--primary btn--sm">Xem chi tiết</a>
    </div>
</div>
