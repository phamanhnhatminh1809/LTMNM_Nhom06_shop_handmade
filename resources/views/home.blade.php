@extends('layouts.app')

@section('title', 'Trang chủ')

@section('content')
<div class="banner-slider">
    @foreach ($banners as $i => $b)
        <div class="banner-slide {{ $i === 0 ? 'banner-slide--active' : '' }}">
            <img src="{{ $b['image'] }}" alt="{{ $b['title'] }}">
            <div class="banner-slide__content">
                <h2>{{ $b['title'] }}</h2>
                <p>{{ $b['subtitle'] }}</p>
                <a href="{{ $b['link'] }}" class="btn btn--primary btn--lg">{{ $b['cta'] }} →</a>
            </div>
        </div>
    @endforeach

    <button class="banner-prev" aria-label="Trước">‹</button>
    <button class="banner-next" aria-label="Sau">›</button>

    <div class="banner-dots">
        @foreach ($banners as $i => $b)
            <button class="banner-dot {{ $i === 0 ? 'banner-dot--active' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
        @endforeach
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title">Danh mục sản phẩm</h2>
            <a href="{{ route('products.index') }}" class="link-more">Xem tất cả →</a>
        </div>
        <div class="category-grid">
            @foreach ($categories as $cat)
                <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="category-card">
                    <div class="category-card__icon">{{ $cat->icon ?? '🧶' }}</div>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title">⭐ Sản phẩm nổi bật</h2>
            <a href="{{ route('products.index') }}" class="link-more">Xem tất cả →</a>
        </div>
        <div class="product-grid">
            @foreach ($featured as $p)
                <x-product-card :product="$p" />
            @endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title">🆕 Sản phẩm mới</h2>
            <a href="{{ route('products.index') }}" class="link-more">Xem tất cả →</a>
        </div>
        <div class="product-grid">
            @foreach ($newest as $p)
                <x-product-card :product="$p" />
            @endforeach
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container features">
        <div class="feature">
            <div class="feature__icon">🚚</div>
            <h3>Miễn phí vận chuyển</h3>
            <p>Đơn từ 300.000đ</p>
        </div>
        <div class="feature">
            <div class="feature__icon">💯</div>
            <h3>Hàng chính hãng</h3>
            <p>100% handmade</p>
        </div>
        <div class="feature">
            <div class="feature__icon">🔄</div>
            <h3>Đổi trả 7 ngày</h3>
            <p>Nếu lỗi từ shop</p>
        </div>
        <div class="feature">
            <div class="feature__icon">💬</div>
            <h3>Hỗ trợ 24/7</h3>
            <p>Hotline luôn mở</p>
        </div>
    </div>
</section>
@endsection
