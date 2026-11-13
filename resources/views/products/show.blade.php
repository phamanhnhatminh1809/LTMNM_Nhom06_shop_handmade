@extends('layouts.app')

@section('title', $product->name)

@php
    $discount = 0;
    if (!empty($product->old_price) && $product->old_price > $product->price) {
        $discount = round((1 - $product->price / $product->old_price) * 100);
    }

    // Xử lý ảnh chính an toàn
    $mainImg = $product->image ?? '';
    $mainImgUrl = (str_starts_with($mainImg, 'http') || str_starts_with($mainImg, '//'))
        ? $mainImg
        : asset(ltrim($mainImg, '/'));

    // Xử lý gallery an toàn
    $gallery = [];
    if (!empty($product->gallery)) {
        if (is_array($product->gallery)) {
            $gallery = $product->gallery;
        } elseif (is_string($product->gallery)) {
            $decoded = json_decode($product->gallery, true);
            $gallery = is_array($decoded) ? $decoded : [$product->gallery];
        }
    }
    if (empty($gallery)) {
        $gallery = [$mainImg];
    }
@endphp

@push('styles')
<style>
    .product-detail {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        align-items: start;
    }
    @media (max-width: 800px) {
        .product-detail { grid-template-columns: 1fr; gap: 24px; }
    }
    
    /* Thu nhỏ khung ảnh để ảnh không bị phóng to gây vỡ hạt */
    .gallery-main {
        width: 100%;
        max-width: 480px; 
        aspect-ratio: 1 / 1;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        position: relative;
        border: 1px solid #f0eae1;
    }
    
    .gallery-main img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }
    
    /* Căn chỉnh lại kích thước thumbnail cho khớp với ảnh chính */
    .gallery-thumbs { 
        display: flex; 
        gap: 10px; 
        margin-top: 12px; 
        flex-wrap: wrap; 
        max-width: 480px; 
    }
    
    .gallery-thumbs .thumb {
        width: 70px; height: 70px; 
        object-fit: cover;
        border-radius: 8px; 
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.2s;
    }
    
    .gallery-thumbs .thumb.active { border-color: var(--color-primary); }
    .gallery-thumbs .thumb:hover { border-color: #ccc; }
</style>
@endpush

@section('content')
<div class="breadcrumb">
    <div class="container" style="display: flex; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('home') }}">Trang chủ</a>
        <i class="bi bi-chevron-right" style="font-size: 11px; margin: 0 8px; opacity: 0.5;"></i>
        <a href="{{ route('products.index') }}">Sản phẩm</a>
        <i class="bi bi-chevron-right" style="font-size: 11px; margin: 0 8px; opacity: 0.5;"></i>
        @if($product->category)
        <a href="{{ route('products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a>
        <i class="bi bi-chevron-right" style="font-size: 11px; margin: 0 8px; opacity: 0.5;"></i>
        @endif
        <span style="color: var(--color-on-surface-variant);">{{ $product->name }}</span>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="product-detail">

            {{-- ==================== GALLERY ==================== --}}
            <div class="product-gallery" style="min-width: 0;">
                <div class="gallery-main">
                    <img id="mainImage"
                         src="{{ $mainImgUrl }}"
                         alt="{{ $product->name }}"
                         onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">

                    @if ($discount > 0)
                    <span class="badge badge--sale badge--lg" style="position: absolute; top: 12px; left: 12px; z-index: 2;">
                        -{{ $discount }}%
                    </span>
                    @endif
                </div>

                <div class="gallery-thumbs">
                    @foreach ($gallery as $i => $img)
                    @php
                        $thumb = is_string($img) ? $img : ($img['url'] ?? $img['path'] ?? '');
                        $thumbUrl = (str_starts_with($thumb, 'http') || str_starts_with($thumb, '//'))
                            ? $thumb
                            : asset(ltrim($thumb, '/'));
                    @endphp
                    <img src="{{ $thumbUrl }}"
                         alt="thumb {{ $i + 1 }}"
                         class="thumb {{ $i === 0 ? 'active' : '' }}"
                         onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';"
                         onclick="
                            document.getElementById('mainImage').src = this.src;
                            document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
                            this.classList.add('active');
                         ">
                    @endforeach
                </div>
            </div>

            {{-- ==================== THÔNG TIN ==================== --}}
            <div class="product-info" style="min-width: 0;">
                <span class="product-info__cat">{{ $product->category->name ?? 'Handmade' }}</span>
                <h1 style="font-size: 28px; font-weight: 800; margin: 8px 0 16px; color: #2c2c2c;">{{ $product->name }}</h1>

                <div class="product-info__rating" style="display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--color-on-surface-variant); margin-bottom: 20px;">
                    <x-star-rating :rating="$product->rating" size="16" />
                    <span>{{ $product->rating }}/5 · Đã bán {{ $product->sold }} · Còn {{ $product->stock_quantity }} sản phẩm</span>
                </div>

                <div class="product-info__price" style="margin-bottom: 24px; display: flex; align-items: baseline; gap: 12px; flex-wrap: wrap;">
                    <span class="price price--lg" style="font-size: 28px; font-weight: 800; color: #c0392b;">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                    @if (!empty($product->old_price) && $product->old_price > $product->price)
                    <span class="price-old" style="font-size: 16px; color: #999; text-decoration: line-through;">{{ number_format($product->old_price, 0, ',', '.') }}đ</span>
                    <span class="discount" style="background: #fef2f2; color: #c0392b; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-weight: 600;">Tiết kiệm {{ number_format($product->old_price - $product->price, 0, ',', '.') }}đ</span>
                    @endif
                </div>

                <p class="product-info__desc" style="font-size: 15px; line-height: 1.6; color: #444; margin-bottom: 24px;">{!! nl2br(e($product->description)) !!}</p>

                @if(!empty($product->instruction_preserve))
                <div style="margin-bottom: 24px; padding: 14px 16px; background: #fff1ec; border-left: 4px solid #c0392b; border-radius: 4px; font-size: 14px; color: #5c3a21;">
                    <strong>Hướng dẫn bảo quản:</strong> {{ $product->instruction_preserve }}
                </div>
                @endif

                @if(session('success'))
                <div style="background: #ecfdf5; border-left: 4px solid #10b981; color: #065f46; padding: 12px 16px; border-radius: 6px; font-size: 14px; margin-bottom: 20px;">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 12px 16px; border-radius: 6px; font-size: 14px; margin-bottom: 20px;">
                    {{ session('error') }}
                </div>
                @endif

                @if($product->stock_quantity > 0)
                <form action="{{ route('cart.store') }}" method="POST" style="margin-bottom: 24px; padding: 20px; background: #fdfaf7; border: 1px solid #f0eae1; border-radius: 12px;">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <div style="display: flex; gap: 14px; align-items: center; margin-bottom: 16px; flex-wrap: wrap;">
                        <span style="font-size: 15px; font-weight: 600; color: #333;">Số lượng:</span>
                        <div class="qty" style="display: inline-flex; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; background: #fff;">
                            <button type="button"
                                onclick="const input = this.nextElementSibling; if(parseInt(input.value) > 1) input.value = parseInt(input.value) - 1;"
                                style="border: none; background: #fff; padding: 8px 16px; cursor: pointer; font-weight: 700; font-size: 16px; color: #555; border-right: 1px solid #d1d5db;">−</button>
                            <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock_quantity }}"
                                style="width: 60px; text-align: center; border: none; font-weight: 600; font-size: 15px; outline: none;">
                            <button type="button"
                                onclick="const input = this.previousElementSibling; if(parseInt(input.value) < {{ $product->stock_quantity }}) input.value = parseInt(input.value) + 1;"
                                style="border: none; background: #fff; padding: 8px 16px; cursor: pointer; font-weight: 700; font-size: 16px; color: #555; border-left: 1px solid #d1d5db;">+</button>
                        </div>
                        <span style="font-size: 13px; color: #888;">(Còn {{ $product->stock_quantity }} sản phẩm)</span>
                    </div>

                    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                        <button type="submit" class="btn btn--primary btn--lg"
                            style="flex: 1; min-width: 160px; padding: 12px 20px; font-weight: 700; border-radius: 8px; background: var(--color-primary); color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="bi bi-cart-plus" style="font-size: 18px;"></i>
                            <span>Thêm vào giỏ</span>
                        </button>
                        <button type="submit" name="buy_now" value="1" class="btn btn--outline btn--lg"
                            style="flex: 1; min-width: 160px; padding: 12px 20px; font-weight: 700; border-radius: 8px; background: #fff; color: var(--color-primary); border: 2px solid var(--color-primary); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="bi bi-lightning-charge-fill" style="font-size: 18px;"></i>
                            <span>Mua ngay</span>
                        </button>
                    </div>
                </form>
                @else
                <div style="background: #f3f4f6; color: #6b7280; padding: 14px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-exclamation-circle"></i>
                    <span>Sản phẩm hiện đang tạm hết hàng</span>
                </div>
                @endif

                <ul class="product-info__meta" style="list-style: none; padding: 0; margin: 0; font-size: 14px; color: var(--color-on-surface-variant); display: flex; flex-direction: column; gap: 10px;">
                    <li style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-patch-check-fill" style="color: #059669; font-size: 18px;"></i>
                        <span>Cam kết sản phẩm làm thủ công 100%</span>
                    </li>
                    <li style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-truck" style="color: var(--color-primary); font-size: 18px;"></i>
                        <span>Giao hàng toàn quốc, thanh toán khi nhận hàng (COD)</span>
                    </li>
                    <li style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-arrow-repeat" style="color: var(--color-primary); font-size: 18px;"></i>
                        <span>Đổi trả trong 7 ngày nếu lỗi từ shop</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- ĐÁNH GIÁ --}}
<section class="section" style="padding-top: 0;">
    <div class="container">
        <div style="background: #fff; border-radius: 16px; padding: 32px; border: 1px solid #f0eae1; box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; border-bottom: 1px solid #f0eae1; padding-bottom: 16px;">
                <div>
                    <h2 style="font-size: 20px; font-weight: 800; margin: 0 0 6px;">Đánh giá từ khách hàng</h2>
                    <p style="margin: 0; font-size: 13px; color: #666;">Ý kiến thực tế từ những khách hàng đã mua sản phẩm</p>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <x-star-rating :rating="$product->rating" size="22" />
                    <span style="font-size: 20px; font-weight: 800; color: var(--color-primary);">{{ $product->rating }}</span>
                    <span style="font-size: 14px; color: #666;">/ 5 ({{ $product->reviews->count() }} lượt đánh giá)</span>
                </div>
            </div>

            @if($product->reviews->isEmpty())
            <div style="text-align: center; padding: 36px 20px; color: #666; font-size: 14px;">
                Chưa có đánh giá nào cho sản phẩm này.
            </div>
            @else
            <div style="display: flex; flex-direction: column; gap: 18px;">
                @foreach($product->reviews as $rev)
                <div style="padding-bottom: 18px; border-bottom: 1px solid #f0eae1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 34px; height: 34px; border-radius: 50%; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px;">
                                {{ mb_strtoupper(mb_substr($rev->user->full_name ?? 'K', 0, 1)) }}
                            </div>
                            <div>
                                <strong style="font-size: 14px; display: block;">{{ $rev->user->full_name ?? 'Khách hàng' }}</strong>
                                <span style="font-size: 12px; color: #666;">{{ $rev->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 700; background: #fef3c7; color: #b45309;">
                            <x-star-rating :rating="$rev->rating" size="12" />
                            {{ $rev->rating }}/5 sao
                        </span>
                    </div>
                    @if(!empty($rev->comment))
                    <p style="margin: 0; font-size: 14px; line-height: 1.6; padding-left: 44px;">{{ $rev->comment }}</p>
                    @else
                    <p style="margin: 0; font-size: 13px; color: #999; font-style: italic; padding-left: 44px;">(Không có nhận xét)</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</section>

{{-- SẢN PHẨM LIÊN QUAN --}}
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