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
                <img id="mainImage" src="{{ $mainImgUrl }}" alt="{{ $product->name }}" onerror="this.src='{{ asset('assets/images/logo.png') }}'">
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

            <div class="product-info__rating" style="display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--color-on-surface-variant); margin-bottom: 12px;">
                <x-star-rating :rating="$product->rating" size="16" />
                <span>{{ $product->rating }}/5 · Đã bán {{ $product->sold }} · Còn {{ $product->stock_quantity }} sản phẩm</span>
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
                    <strong>Hướng dẫn bảo quản:</strong> {{ $product->instruction_preserve }}
                </div>
            @endif

            @if(session('success'))
                <div style="background: #ecfdf5; border-left: 4px solid #10b981; color: #065f46; padding: 12px 16px; border-radius: 6px; font-size: 14px; margin-bottom: 18px;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 12px 16px; border-radius: 6px; font-size: 14px; margin-bottom: 18px;">
                    {{ session('error') }}
                </div>
            @endif

            @if($product->stock_quantity > 0)
                <form action="{{ route('cart.store') }}" method="POST" style="margin-bottom: 20px;">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <div style="display: flex; gap: 14px; align-items: center; margin-bottom: 16px; flex-wrap: wrap;">
                        <span style="font-size: 14px; font-weight: 600; color: var(--color-on-surface);">Số lượng:</span>
                        <div class="qty" style="display: inline-flex; border: 1px solid #d1d5db; border-radius: 6px; overflow: hidden;">
                            <button type="button"
                                    onclick="const input = this.nextElementSibling; if(parseInt(input.value) > 1) input.value = parseInt(input.value) - 1;"
                                    style="border: none; background: #f9fafb; padding: 8px 14px; cursor: pointer; font-weight: 700;">−</button>
                            <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock_quantity }}"
                                   style="width: 50px; text-align: center; border: none; font-weight: 600; font-size: 14px;">
                            <button type="button"
                                    onclick="const input = this.previousElementSibling; if(parseInt(input.value) < {{ $product->stock_quantity }}) input.value = parseInt(input.value) + 1;"
                                    style="border: none; background: #f9fafb; padding: 8px 14px; cursor: pointer; font-weight: 700;">+</button>
                        </div>
                        <span style="font-size: 13px; color: var(--color-on-surface-variant);">(Còn {{ $product->stock_quantity }} sản phẩm)</span>
                    </div>

                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <button type="submit" class="btn btn--primary btn--lg"
                                style="flex: 1; min-width: 180px; padding: 12px 20px; font-weight: 700;">
                            🛒 Thêm vào giỏ
                        </button>
                        <button type="submit" name="buy_now" value="1" class="btn btn--outline btn--lg"
                                style="flex: 1; min-width: 180px; padding: 12px 20px; font-weight: 700; border-color: var(--color-primary); color: var(--color-primary);">
                            ⚡ Mua ngay
                        </button>
                    </div>
                </form>
            @else
                <div style="background: #f3f4f6; color: #6b7280; padding: 14px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 20px;">
                    Sản phẩm hiện đang tạm hết hàng
                </div>
            @endif

            <ul class="product-info__meta" style="list-style: none; padding: 0; margin: 0; font-size: 13px; color: var(--color-on-surface-variant); display: flex; flex-direction: column; gap: 8px;">
                <li>✅ Cam kết sản phẩm làm thủ công 100%</li>
                <li>🚚 Giao hàng toàn quốc, thanh toán khi nhận hàng (COD)</li>
                <li>🔄 Đổi trả trong 7 ngày nếu lỗi từ shop</li>
            </ul>
        </div>
    </div>
</section>

{{-- Phần Đánh giá của khách hàng --}}
<section class="section" style="padding-top: 0;">
    <div class="container">
        <div style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 32px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid var(--color-outline-variant, #f0eae1); padding-bottom: 16px;">
                <div>
                    <h2 style="font-size: 20px; font-weight: 800; color: var(--color-on-surface); margin: 0 0 6px;">
                        Đánh giá từ khách hàng
                    </h2>
                    <p style="margin: 0; font-size: 13px; color: var(--color-on-surface-variant);">
                        Ý kiến thực tế từ những khách hàng đã mua và trải nghiệm sản phẩm
                    </p>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <x-star-rating :rating="$product->rating" size="22" />
                    <span style="font-size: 20px; font-weight: 800; color: var(--color-primary, #e76f51);">
                        {{ $product->rating }}
                    </span>
                    <span style="font-size: 14px; color: var(--color-on-surface-variant);">
                        / 5 ({{ $product->reviews->count() }} lượt đánh giá)
                    </span>
                </div>
            </div>

            @if($product->reviews->isEmpty())
                <div style="text-align: center; padding: 36px 20px; color: var(--color-on-surface-variant); font-size: 14px;">
                    Chưa có đánh giá nào cho sản phẩm này. Khách hàng đã mua hàng có thể để lại đánh giá sau khi hoàn thành đơn hàng.
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 18px;">
                    @foreach($product->reviews as $rev)
                        <div style="padding-bottom: 18px; border-bottom: 1px solid var(--color-outline-variant, #f0eae1);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">
                                        {{ mb_strtoupper(mb_substr($rev->user->full_name ?? 'K', 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong style="font-size: 14px; color: var(--color-on-surface); display: block;">
                                            {{ $rev->user->full_name ?? 'Khách hàng' }}
                                        </strong>
                                        <span style="font-size: 12px; color: var(--color-on-surface-variant);">
                                            {{ $rev->created_at->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>

                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700; background: #fef3c7; color: #d97706;">
                                    <x-star-rating :rating="$rev->rating" size="12" />
                                    {{ $rev->rating }}/5
                                </span>
                            </div>

                            @if(!empty($rev->comment))
                                <p style="margin: 0; font-size: 14px; color: var(--color-on-surface); line-height: 1.6; padding-left: 44px;">
                                    {{ $rev->comment }}
                                </p>
                            @else
                                <p style="margin: 0; font-size: 13px; color: var(--color-on-surface-variant); font-style: italic; padding-left: 44px;">
                                    (Khách hàng không để lại nhận xét chữ)
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
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