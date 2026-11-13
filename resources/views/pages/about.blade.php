@extends('layouts.app')

@section('title', 'Giới thiệu')

@section('content')
<section class="section">
    <div class="container" style="max-width: 800px; line-height: 1.8;">
        <!-- Breadcrumb -->
        <nav class="breadcrumb-nav" style="margin-bottom: 24px; font-size: 14px; color: var(--color-on-surface-variant); display: flex; align-items: center;">
            <a href="{{ route('home') }}" style="color: inherit; text-decoration: none;">Trang chủ</a>
            <i class="bi bi-chevron-right" style="font-size: 11px; margin: 0 8px; opacity: 0.5;"></i>
            <span style="color: var(--color-primary); font-weight: 600;">Giới thiệu</span>
        </nav>

        <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 20px; color: var(--color-on-surface);">Giới thiệu về Shop Handmade</h1>

        <div style="background: #ffffff; padding: 32px; border-radius: 16px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.02); margin-bottom: 30px;">
            <div style="font-size: 28px; color: var(--color-primary); margin-bottom: 12px;">
                <i class="bi bi-quote"></i>
            </div>
            <p style="font-size: 16px; margin-bottom: 16px; color: var(--color-on-surface);">
                Chào mừng bạn đến với <strong>Shop Handmade</strong> – nơi kết tinh vẻ đẹp mộc mạc và sự tỉ mỉ của các nghệ nhân thủ công truyền thống. Chúng tôi tin rằng mỗi món đồ len, chiếc móc khóa, hay túi xách thủ công không chỉ đơn thuần là sản phẩm tiêu dùng, mà còn chứa đựng cả tâm huyết, tình cảm và câu chuyện riêng biệt.
            </p>
            <p style="font-size: 16px; margin-bottom: 0; color: var(--color-on-surface);">
                Với sứ mệnh tôn vinh nét đẹp thủ công và phong cách sống xanh, tất cả các sản phẩm tại Shop Handmade đều được chọn lọc từ sợi cotton tự nhiên, an toàn cho làn da và thân thiện với môi trường.
            </p>
        </div>

        <div style="display: flex; gap: 14px; flex-wrap: wrap;">
            <a href="{{ route('products.index') }}" class="btn btn--primary" style="display: inline-flex; align-items: center; gap: 8px;">
                <span>Khám phá sản phẩm ngay</span>
                <i class="bi bi-arrow-right"></i>
            </a>
            <a href="{{ route('pages.contact') }}" class="btn btn--outline" style="display: inline-flex; align-items: center; gap: 8px;">
                <i class="bi bi-envelope"></i>
                <span>Liên hệ chúng tôi</span>
            </a>
        </div>
    </div>
</section>
@endsection
