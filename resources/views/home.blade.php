@extends('layouts.app')

@section('title', 'Trang chủ - Đồ thủ công handmade tinh tế')

@section('content')
<div class="banner-slider">
    @foreach ($banners as $i => $b)
        <div class="banner-slide {{ $i === 0 ? 'banner-slide--active' : '' }}">
            <img src="{{ $b['image'] }}" alt="{{ $b['title'] }}" onerror="this.onerror=null; this.src='{{ asset('assets/images/banner-placeholder.svg') }}';">
            <div class="banner-slide__content">
                <h2>{{ $b['title'] }}</h2>
                <p>{{ $b['subtitle'] }}</p>
                <a href="{{ $b['link'] }}" class="btn btn--primary btn--lg" style="display: inline-flex; align-items: center; gap: 8px;">
                    <span>{{ $b['cta'] }}</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    @endforeach
    <button class="banner-prev" aria-label="Trước" style="display: flex; align-items: center; justify-content: center;">
        <i class="bi bi-chevron-left"></i>
    </button>
    <button class="banner-next" aria-label="Sau" style="display: flex; align-items: center; justify-content: center;">
        <i class="bi bi-chevron-right"></i>
    </button>
    <div class="banner-dots">
        @foreach ($banners as $i => $b)
            <button class="banner-dot {{ $i === 0 ? 'banner-dot--active' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
        @endforeach
    </div>
</div>

{{-- DANH MỤC SẢN PHẨM --}}
<section class="section" style="padding: 48px 0 36px; background: #ffffff;">
    <div class="container">
        <div class="section__header" style="margin-bottom: 24px;">
            <div>
                <h2 class="section__title" style="margin: 0 0 4px;">Danh mục sản phẩm</h2>
                <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant, #55423e);">Khám phá các dòng sản phẩm thủ công được tạo tác tỉ mỉ</p>
            </div>
            <a href="{{ route('products.index') }}" class="link-more" style="display: inline-flex; align-items: center; gap: 4px;">
                <span>Xem tất cả danh mục</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="category-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px;">
            @foreach ($categories as $cat)
                <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="category-card">
                    <div class="category-card__icon-wrapper">
                        <i class="bi {{ $cat->display_icon }}"></i>
                    </div>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- SẢN PHẨM NỔI BẬT --}}
<section class="section section--alt" id="featured-section" style="padding: 48px 0;">
    <div class="container">
        <div class="section__header" style="margin-bottom: 28px;">
            <div>
                <h2 class="section__title" style="margin: 0 0 4px;">Sản phẩm nổi bật</h2>
                <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant, #55423e);">Những món đồ handmade được yêu thích và đánh giá cao nhất</p>
            </div>
            <a href="{{ route('products.index') }}" class="link-more" style="display: inline-flex; align-items: center; gap: 4px;">
                <span>Xem tất cả</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        
        <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 24px; width: 100%;">
            @forelse ($featured as $p)
                @if($p)
                    <x-product-card :product="$p" />
                @endif
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #888;">
                    Chưa có sản phẩm nổi bật
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- SẢN PHẨM MỚI --}}
<section class="section" id="newest-section" style="padding: 48px 0; background: #ffffff;">
    <div class="container">
        <div class="section__header" style="margin-bottom: 28px;">
            <div>
                <h2 class="section__title" style="margin: 0 0 4px;">Sản phẩm mới ra mắt</h2>
                <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant, #55423e);">Các tác phẩm thủ công vừa được hoàn thiện tại xưởng</p>
            </div>
            <a href="{{ route('products.index') }}" class="link-more" style="display: inline-flex; align-items: center; gap: 4px;">
                <span>Xem tất cả</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        
        <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 24px; width: 100%;">
            @forelse ($newest as $p)
                @if($p)
                    <x-product-card :product="$p" />
                @endif
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #888;">
                    Chưa có sản phẩm mới
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- KHỐI TIỆN ÍCH DỊCH VỤ --}}
<section class="section section--alt" style="padding: 44px 0;">
    <div class="container">
        <div class="features">
            <div class="feature">
                <div class="feature__icon-wrapper">
                    <i class="bi bi-truck"></i>
                </div>
                <h3>Miễn phí vận chuyển</h3>
                <p>Áp dụng cho đơn từ 300.000đ toàn quốc</p>
            </div>
            <div class="feature">
                <div class="feature__icon-wrapper">
                    <i class="bi bi-patch-check"></i>
                </div>
                <h3>100% Thủ công</h3>
                <p>Tỉ mỉ từng đường kim, sợi len tuyển chọn</p>
            </div>
            <div class="feature">
                <div class="feature__icon-wrapper">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <h3>Đổi trả linh hoạt</h3>
                <p>Hỗ trợ đổi trả trong 7 ngày nếu có lỗi</p>
            </div>
            <div class="feature">
                <div class="feature__icon-wrapper">
                    <i class="bi bi-headset"></i>
                </div>
                <h3>Tư vấn tận tâm</h3>
                <p>Hỗ trợ tư vấn đặt làm theo yêu cầu 24/7</p>
            </div>
        </div>
    </div>
</section>
@endsection