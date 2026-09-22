@extends('layouts.app')

@section('title', $product->name)

@php
    $discount = 0;
    if (!empty($product->old_price) && $product->old_price > $product->price) {
        $discount = round((1 - $product->price / $product->old_price) * 100);
    }
    $mainImgUrl = str_starts_with($product->image, 'http') 
        ? $product->image 
        : asset(ltrim($product->image, '/'));
    $gallery = is_array($product->gallery) ? $product->gallery : [$product->image];
@endphp

@section('content')
<div class="breadcrumb">
    <div class="container">
        <a href="{{ route('home') }}">Trang chủ</a>
        <span>/</span>
        <a href="{{ route('products.index') }}">Sản phẩm</a>
        <span>/</span>
        @if($product->category)
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a>
            <span>/</span>
        @endif
        <span>{{ $product->name }}</span>
    </div>
</div>

<section class="section">
    <div class="container product-detail">
        <div class="product-gallery">
            <div class="gallery-main">
                <img id="mainImage" src="{{ $mainImgUrl }}" alt="{{ $product->name }}" onerror="this.src='{{ asset('images/logo.png') }}'">
                @if ($discount > 0)
                    <span class="badge badge--sale badge--lg">-{{ $discount }}%</span>
                @endif
            </div>
            <div class="gallery-thumbs">
                @foreach ($gallery as $i => $img)
                    @php
                        $thumbUrl = str_starts_with($img, 'http') ? $img : asset(ltrim($img, '/'));
                    @endphp
                    <img src="{{ $thumbUrl }}"
                         alt="thumb {{ $i }}"
                         class="thumb {{ $i === 0 ? 'active' : '' }}"
                         onclick="document.getElementById('mainImage').src = this.src; document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active')); this.classList.add('active');">
                @endforeach
            </div>
        </div>

        <div class="product-info">
            <span class="product-info__cat">{{ $product->category->name ?? 'Handmade' }}</span>
            <h1>{{ $product->name }}</h1>
            <div class="product-info__rating">
                ⭐ {{ $product->rating }} / 5 · Đã bán {{ $product->sold }} · Còn {{ $product->stock_quantity }} sản phẩm
            </div>
            <div class="product-info__price">
                <span class="price price--lg">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                @if (!empty($product->old_price))
                    <span class="price-old">{{ number_format($product->old_price, 0, ',', '.') }}đ</span>
                    <span class="discount">Tiết kiệm {{ number_format($product->old_price - $product->price, 0, ',', '.') }}đ</span>
                @endif
            </div>
            <p class="product-info__desc">{!! nl2br(e($product->description)) !!}</p>
            @if(!empty($product->instruction_preserve))
                <div style="margin-bottom: 20px; padding: 12px; background: #fff1ec; border-radius: 8px; font-size: 14px;">
                    <strong>💡 Hướng dẫn bảo quản:</strong> {{ $product->instruction_preserve }}
                </div>
            @endif
            <div class="product-info__actions">
                <div class="qty">
                    <button type="button" onclick="const input = this.nextElementSibling; if(input.value > 1) input.value--;">−</button>
                    <input type="number" value="1" min="1" max="{{ $product->stock_quantity }}">
                    <button type="button" onclick="const input = this.previousElementSibling; if(input.value < {{ $product->stock_quantity }}) input.value++;">+</button>
                </div>
                <button class="btn btn--primary btn--lg">🛒 Thêm vào giỏ</button>
                <button class="btn btn--outline btn--lg">❤️ Yêu thích</button>
            </div>
            <ul class="product-info__meta">
                <li>✅ Cam kết hàng handmade 100%</li>
                <li>🚚 Giao hàng toàn quốc</li>
                <li>🔄 Đổi trả trong 7 ngày nếu lỗi từ shop</li>
            </ul>
        </div>
    </div>
</section>

@if ($related->isNotEmpty())
<section class="section section--alt">
    <div class="container">
        <h2 class="section__title">Sản phẩm liên quan</h2>
        <div class="product-grid">
            @foreach ($related as $p)
                <x-product-card :product="$p" />
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
