@extends('layouts.app')

@section('title', 'Giới thiệu')

@section('content')
<section class="section">
    <div class="container" style="max-width: 920px; line-height: 1.8;">
        <!-- Breadcrumb -->
        <nav class="breadcrumb-nav" style="margin-bottom: 24px; font-size: 14px; color: var(--color-on-surface-variant); display: flex; align-items: center;">
            <a href="{{ route('home') }}" style="color: inherit; text-decoration: none;">Trang chủ</a>
            <i class="bi bi-chevron-right" style="font-size: 11px; margin: 0 8px; opacity: 0.5;"></i>
            <span style="color: var(--color-primary); font-weight: 600;">Giới thiệu</span>
        </nav>

        <h1 style="font-size: 30px; font-weight: 800; margin-bottom: 10px; color: var(--color-on-surface);">Giới thiệu về Shop Handmade</h1>
        <p style="font-size: 15.5px; color: var(--color-on-surface-variant); margin-bottom: 28px;">
            Nơi kết tinh sự tỉ mỉ, chất liệu mộc mạc và tâm huyết của những người nghệ nhân thủ công Việt Nam.
        </p>

        <!-- Câu chuyện thương hiệu -->
        <div style="background: #ffffff; padding: 32px; border-radius: 16px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.03); margin-bottom: 28px;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                <span style="width: 40px; height: 40px; border-radius: 10px; background: #faf7f5; color: var(--color-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="bi bi-quote"></i>
                </span>
                <h2 style="font-size: 20px; font-weight: 800; margin: 0; color: var(--color-on-surface);">Câu chuyện của chúng tôi</h2>
            </div>
            <p style="font-size: 16px; margin-bottom: 14px; color: var(--color-on-surface);">
                Chào mừng bạn đến với <strong>Shop Handmade</strong> – góc nhỏ lưu giữ những món đồ len móc, móc khóa, gấu bông, trang sức và túi xách làm tay. Chúng tôi tin rằng mỗi sản phẩm thủ công không chỉ là một vật dụng thường ngày, mà còn mang theo sự ấm áp, dấu ấn cá nhân và câu chuyện riêng biệt gửi tới người nhận.
            </p>
            <p style="font-size: 16px; margin-bottom: 0; color: var(--color-on-surface);">
                Với định hướng tôn vinh giá trị thủ công bền vững, từng mũi móc và đường khâu đều được thực hiện hoàn toàn bằng tay từ các nguồn sợi cotton tự nhiên, an toàn cho làn da và thân thiện với môi trường.
            </p>
        </div>

        <!-- 4 Giá trị cốt lõi -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px; margin-bottom: 32px;">
            <div style="background: #ffffff; padding: 22px; border-radius: 14px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.02); display: flex; gap: 14px; align-items: flex-start;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: #faf7f5; color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="bi bi-flower1"></i>
                </div>
                <div>
                    <h3 style="font-size: 15.5px; font-weight: 700; margin: 0 0 4px; color: var(--color-on-surface);">Chất liệu chọn lọc</h3>
                    <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant);">Sợi cotton, len milk, vải bố và phụ kiện mộc mạc, êm dịu và bền màu theo thời gian.</p>
                </div>
            </div>

            <div style="background: #ffffff; padding: 22px; border-radius: 14px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.02); display: flex; gap: 14px; align-items: flex-start;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: #faf7f5; color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="bi bi-heart-fill"></i>
                </div>
                <div>
                    <h3 style="font-size: 15.5px; font-weight: 700; margin: 0 0 4px; color: var(--color-on-surface);">Thủ công tỉ mỉ 100%</h3>
                    <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant);">Mỗi sản phẩm đều được hoàn thiện thủ công từng chi tiết và kiểm tra kỹ trước khi giao.</p>
                </div>
            </div>

            <div style="background: #ffffff; padding: 22px; border-radius: 14px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.02); display: flex; gap: 14px; align-items: flex-start;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: #faf7f5; color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="bi bi-palette-fill"></i>
                </div>
                <div>
                    <h3 style="font-size: 15.5px; font-weight: 700; margin: 0 0 4px; color: var(--color-on-surface);">Nhận làm theo yêu cầu</h3>
                    <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant);">Hỗ trợ phối màu, thêu chữ hoặc làm quà tặng custom riêng cho những dịp đặc biệt.</p>
                </div>
            </div>

            <div style="background: #ffffff; padding: 22px; border-radius: 14px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.02); display: flex; gap: 14px; align-items: flex-start;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: #faf7f5; color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    <i class="bi bi-gift-fill"></i>
                </div>
                <div>
                    <h3 style="font-size: 15.5px; font-weight: 700; margin: 0 0 4px; color: var(--color-on-surface);">Đóng gói quà tặng chu đáo</h3>
                    <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant);">Hộp giấy kraft, thiệp viết tay miễn phí và giao hàng cẩn thận trên toàn quốc.</p>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div style="display: flex; gap: 14px; flex-wrap: wrap;">
            <a href="{{ route('products.index') }}" class="btn btn--primary" style="display: inline-flex; align-items: center; gap: 8px;">
                <span>Khám phá sản phẩm ngay</span>
                <i class="bi bi-arrow-right"></i>
            </a>
            <a href="{{ route('contact') }}" class="btn btn--outline" style="display: inline-flex; align-items: center; gap: 8px;">
                <i class="bi bi-envelope"></i>
                <span>Liên hệ chúng tôi</span>
            </a>
        </div>
    </div>
</section>
@endsection
