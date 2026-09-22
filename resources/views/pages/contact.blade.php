@extends('layouts.app')

@section('title', 'Liên hệ')

@section('content')
<section class="section">
    <div class="container" style="max-width: 800px; line-height: 1.8;">
        <h1 style="margin-bottom: 20px;">Liên hệ với chúng tôi</h1>
        <p style="font-size: 16px; margin-bottom: 24px;">
            Nếu bạn có bất kỳ câu hỏi, góp ý hoặc mong muốn đặt hàng riêng theo yêu cầu (custom handmade), đừng ngần ngại liên hệ với chúng tôi qua các kênh dưới đây:
        </p>

        <div style="background: #fff8f6; padding: 24px; border-radius: 12px; border: 1px solid #dcc1ba; margin-bottom: 24px;">
            <p>📍 <strong>Địa chỉ cửa hàng:</strong> 123 Nguyễn Văn Cừ, Quận 5, TP. Hồ Chí Minh</p>
            <p>📞 <strong>Hotline tư vấn & đặt hàng:</strong> 0123 456 789 (8h00 - 21h00 hàng ngày)</p>
            <p>✉️ <strong>Email hỗ trợ:</strong> hello@shophandmade.vn</p>
            <p>💬 <strong>Zalo Official:</strong> 0123 456 789</p>
        </div>

        <a href="{{ route('home') }}" class="btn btn--primary">← Quay lại trang chủ</a>
    </div>
</section>
@endsection
