@extends('layouts.app')

@section('title', 'Liên hệ')

@section('content')
<section class="section">
    <div class="container" style="max-width: 800px; line-height: 1.8;">
        <!-- Breadcrumb -->
        <nav class="breadcrumb-nav" style="margin-bottom: 24px; font-size: 14px; color: var(--color-on-surface-variant); display: flex; align-items: center;">
            <a href="{{ route('home') }}" style="color: inherit; text-decoration: none;">Trang chủ</a>
            <i class="bi bi-chevron-right" style="font-size: 11px; margin: 0 8px; opacity: 0.5;"></i>
            <span style="color: var(--color-primary); font-weight: 600;">Liên hệ</span>
        </nav>

        <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 12px; color: var(--color-on-surface);">Liên hệ với chúng tôi</h1>
        <p style="font-size: 15px; color: var(--color-on-surface-variant); margin-bottom: 32px;">
            Nếu bạn có bất kỳ câu hỏi, góp ý hoặc mong muốn đặt hàng riêng theo yêu cầu (custom handmade), đừng ngần ngại liên hệ với chúng tôi qua các kênh dưới đây:
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 36px;">
            <div style="background: #ffffff; padding: 24px; border-radius: 14px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.02); display: flex; gap: 16px; align-items: flex-start;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: #faf7f5; color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; margin: 0 0 4px; color: var(--color-on-surface);">Địa chỉ cửa hàng</h3>
                    <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant);">{{ config('shop.contact.address') }}</p>
                </div>
            </div>

            <div style="background: #ffffff; padding: 24px; border-radius: 14px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.02); display: flex; gap: 16px; align-items: flex-start;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: #faf7f5; color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="bi bi-telephone-fill"></i>
                </div>
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; margin: 0 0 4px; color: var(--color-on-surface);">Hotline tư vấn</h3>
                    <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant);">{{ config('shop.contact.phone') }} ({{ config('shop.contact.hours') }})</p>
                </div>
            </div>

            <div style="background: #ffffff; padding: 24px; border-radius: 14px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.02); display: flex; gap: 16px; align-items: flex-start;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: #faf7f5; color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="bi bi-envelope-fill"></i>
                </div>
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; margin: 0 0 4px; color: var(--color-on-surface);">Email hỗ trợ</h3>
                    <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant);">{{ config('shop.contact.email') }}</p>
                </div>
            </div>

            <div style="background: #ffffff; padding: 24px; border-radius: 14px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.02); display: flex; gap: 16px; align-items: flex-start;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: #faf7f5; color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="bi bi-clock-fill"></i>
                </div>
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; margin: 0 0 4px; color: var(--color-on-surface);">Giờ mở cửa</h3>
                    <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant);">{{ config('shop.contact.hours') }}</p>
                </div>
            </div>
        </div>

        <a href="{{ route('home') }}" class="btn btn--outline" style="display: inline-flex; align-items: center; gap: 8px;">
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại trang chủ</span>
        </a>
    </div>
</section>
@endsection
