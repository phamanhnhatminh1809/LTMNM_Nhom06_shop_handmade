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
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">
        <div class="section__header">
            <h2 class="section__title">Danh mục sản phẩm</h2>
            <a href="{{ route('products.index') }}" class="link-more">Xem tất cả →</a>
        </div>
        <div class="category-grid" style="display: grid !important; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important; gap: 15px !important;">
            @foreach ($categories as $cat)
                <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="category-card">
                    <div class="category-card__icon">{{ $cat->icon ?? '🧶' }}</div>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="section section--alt" id="featured-section">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">
        <div class="section__header">
            <h2 class="section__title">Sản phẩm nổi bật</h2>
            <a href="{{ route('products.index') }}" class="link-more">Xem tất cả →</a>
        </div>
        
        <!-- Lưới sản phẩm gốc (Sẽ bị JS thay thế) -->
        <div id="featured-grid-source" style="display: none;">
            @foreach ($featured as $p)
                @if($p)
                    <x-product-card :product="$p" />
                @endif
            @endforeach
        </div>
        <!-- Nơi JS sẽ hiển thị lưới mới -->
        <div id="featured-grid-target" style="display: grid !important; grid-template-columns: repeat(4, 1fr) !important; gap: 20px !important; width: 100% !important;"></div>
    </div>
</section>

<section class="section" id="newest-section">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 15px;">
        <div class="section__header">
            <h2 class="section__title">Sản phẩm mới</h2>
            <a href="{{ route('products.index') }}" class="link-more">Xem tất cả →</a>
        </div>
        
        <!-- Lưới sản phẩm gốc (Sẽ bị JS thay thế) -->
        <div id="newest-grid-source" style="display: none;">
            @foreach ($newest as $p)
                @if($p)
                    <x-product-card :product="$p" />
                @endif
            @endforeach
        </div>
        <!-- Nơi JS sẽ hiển thị lưới mới -->
        <div id="newest-grid-target" style="display: grid !important; grid-template-columns: repeat(4, 1fr) !important; gap: 20px !important; width: 100% !important;"></div>
    </div>
</section>

<section class="section section--alt">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 15px;" class="features">
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hàm di chuyển sản phẩm sang lưới mới
        function cloneGrid(sourceId, targetId) {
            const source = document.getElementById(sourceId);
            const target = document.getElementById(targetId);
            if (source && target) {
                target.innerHTML = source.innerHTML;
            }
        }
        cloneGrid('featured-grid-source', 'featured-grid-target');
        cloneGrid('newest-grid-source', 'newest-grid-target');
    });
</script>
@endpush
@endsection