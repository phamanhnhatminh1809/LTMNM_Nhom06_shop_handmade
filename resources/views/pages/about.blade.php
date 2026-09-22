@extends('layouts.app')

@section('title', 'Giới thiệu')

@section('content')
<section class="section">
    <div class="container" style="max-width: 800px; line-height: 1.8;">
        <h1 style="margin-bottom: 20px;">Giới thiệu về Shop Handmade</h1>
        <p style="font-size: 16px; margin-bottom: 16px;">
            Chào mừng bạn đến với <strong>Shop Handmade</strong> – nơi kết tinh vẻ đẹp mộc mạc và sự tỉ mỉ của các nghệ nhân thủ công truyền thống. Chúng tôi tin rằng mỗi món đồ len, chiếc móc khóa, hay túi xách thủ công không chỉ đơn thuần là sản phẩm tiêu dùng, mà còn chứa đựng cả tâm huyết, tình cảm và câu chuyện riêng biệt.
        </p>
        <p style="font-size: 16px; margin-bottom: 16px;">
            Với sứ mệnh tôn vinh nét đẹp thủ công và phong cách sống xanh, tất cả các sản phẩm tại Shop Handmade đều được chọn lọc từ sợi cotton tự nhiên, an toàn cho làn da và thân thiện với môi trường.
        </p>
        <div style="margin-top: 30px;">
            <a href="{{ route('products.index') }}" class="btn btn--primary">Khám phá sản phẩm ngay →</a>
        </div>
    </div>
</section>
@endsection
