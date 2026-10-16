@extends('layouts.app')

@section('title', 'Giỏ hàng của bạn')

@section('content')
<div class="container" style="padding-top: 28px; padding-bottom: 50px;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" style="margin-bottom: 24px; font-size: 14px; color: var(--color-on-surface-variant);">
        <a href="{{ route('home') }}" style="color: inherit; text-decoration: none;">Trang chủ</a>
        <span style="margin: 0 8px;">/</span>
        <span style="color: var(--color-primary); font-weight: 600;">Giỏ hàng</span>
    </nav>

    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 800; color: var(--color-on-surface); margin: 0 0 6px;">
            Giỏ hàng của bạn
        </h1>
        <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant);">
            Quản lý các sản phẩm bạn đã chọn trước khi tiến hành thanh toán
        </p>
    </div>

    <!-- Thông báo Flash -->
    @if(session('success'))
        <div style="background: #ecfdf5; border-left: 4px solid #10b981; color: #065f46; padding: 14px 18px; border-radius: 8px; font-size: 14px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 14px 18px; border-radius: 8px; font-size: 14px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    @if($cart->details->isEmpty())
        <!-- Trạng thái giỏ hàng trống -->
        <div style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 60px 20px; text-align: center; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
            <div style="font-size: 18px; font-weight: 700; color: var(--color-on-surface); margin-bottom: 8px;">
                Giỏ hàng của bạn đang trống
            </div>
            <p style="color: var(--color-on-surface-variant); font-size: 14px; margin-bottom: 24px; max-width: 460px; margin-left: auto; margin-right: auto;">
                Hãy khám phá các sản phẩm thủ công tinh tế của chúng tôi và chọn cho mình những món đồ ưng ý nhất.
            </p>
            <a href="{{ route('products.index') }}" class="btn btn--primary" style="padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block;">
                Khám phá sản phẩm ngay
            </a>
        </div>
    @else
        <!-- Bố cục 2 cột: Bảng sản phẩm & Tóm tắt đơn hàng -->
        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 30px; align-items: start;">
            
            <!-- Cột trái: Danh sách sản phẩm -->
            <div style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 24px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--color-outline-variant, #f0eae1); text-align: left; color: var(--color-on-surface-variant); font-size: 12px; letter-spacing: 0.5px;">
                                <th style="padding: 12px 10px;">SẢN PHẨM</th>
                                <th style="padding: 12px 10px; text-align: right;">ĐƠN GIÁ</th>
                                <th style="padding: 12px 10px; text-align: center;">SỐ LƯỢNG</th>
                                <th style="padding: 12px 10px; text-align: right;">THÀNH TIỀN</th>
                                <th style="padding: 12px 10px; text-align: center;">XÓA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cart->details as $item)
                                @php
                                    $imgSrc = $item->image_url 
                                        ? (str_starts_with($item->image_url, 'http') ? $item->image_url : asset(ltrim($item->image_url, '/')))
                                        : asset('assets/images/logo.png');
                                @endphp
                                <tr style="border-bottom: 1px solid var(--color-outline-variant, #f0eae1);">
                                    <!-- Cột thông tin sản phẩm -->
                                    <td style="padding: 16px 10px;">
                                        <div style="display: flex; gap: 14px; align-items: center;">
                                            <a href="{{ route('products.show', $item->product_id) }}" style="flex-shrink: 0;">
                                                <img src="{{ $imgSrc }}" alt="{{ $item->name }}" 
                                                     style="width: 64px; height: 64px; object-fit: cover; border-radius: 8px; border: 1px solid #f0eae1;"
                                                     onerror="this.src='{{ asset('assets/images/logo.png') }}'">
                                            </a>
                                            <div>
                                                <a href="{{ route('products.show', $item->product_id) }}" 
                                                   style="font-weight: 700; color: var(--color-on-surface); text-decoration: none; line-height: 1.4; display: block; margin-bottom: 4px;">
                                                    {{ $item->name }}
                                                </a>
                                                <span style="font-size: 12px; color: var(--color-on-surface-variant); display: block;">
                                                    Mã: {{ $item->product_code }}
                                                </span>
                                                @if($item->product && $item->product->stock_quantity < 5)
                                                    <span style="font-size: 11px; color: #d97706; font-weight: 600; display: block; margin-top: 2px;">
                                                        Chỉ còn {{ $item->product->stock_quantity }} sản phẩm
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Cột đơn giá -->
                                    <td style="padding: 16px 10px; text-align: right; font-weight: 600; color: var(--color-on-surface);">
                                        {{ number_format($item->unit_price, 0, ',', '.') }}đ
                                    </td>

                                    <!-- Cột số lượng -->
                                    <td style="padding: 16px 10px; text-align: center;">
                                        <form action="{{ route('cart.update', $item->id) }}" method="POST" style="display: inline-flex; align-items: center; border: 1px solid #d1d5db; border-radius: 6px; overflow: hidden;">
                                            @csrf
                                            @method('PUT')
                                            <button type="button" 
                                                    onclick="const inp = this.nextElementSibling; if(parseInt(inp.value) > 1) { inp.value = parseInt(inp.value) - 1; this.form.submit(); }"
                                                    style="background: #f9fafb; border: none; padding: 6px 10px; cursor: pointer; font-size: 14px; font-weight: 700; color: #4b5563;">
                                                -
                                            </button>
                                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock_quantity ?? 999 }}" 
                                                   onchange="this.form.submit();"
                                                   style="width: 44px; text-align: center; border: none; font-size: 14px; font-weight: 600; padding: 6px 0; -moz-appearance: textfield;">
                                            <button type="button" 
                                                    onclick="const inp = this.previousElementSibling; inp.value = parseInt(inp.value) + 1; this.form.submit();"
                                                    style="background: #f9fafb; border: none; padding: 6px 10px; cursor: pointer; font-size: 14px; font-weight: 700; color: #4b5563;">
                                                +
                                            </button>
                                        </form>
                                    </td>

                                    <!-- Cột thành tiền -->
                                    <td style="padding: 16px 10px; text-align: right; font-weight: 700; color: var(--color-primary, #e76f51);">
                                        {{ number_format($item->subtotal, 0, ',', '.') }}đ
                                    </td>

                                    <!-- Cột thao tác xóa -->
                                    <td style="padding: 16px 10px; text-align: center;">
                                        <form action="{{ route('cart.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 13px; padding: 4px 8px; border-radius: 4px; transition: color 0.2s;"
                                                    onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#9ca3af'">
                                                Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--color-outline-variant, #f0eae1);">
                    <a href="{{ route('products.index') }}" style="color: var(--color-primary); font-size: 14px; font-weight: 600; text-decoration: none;">
                        &larr; Tiếp tục xem sản phẩm khác
                    </a>
                    <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?');">
                        @csrf
                        <button type="submit" style="background: none; border: 1px solid #d1d5db; padding: 8px 16px; border-radius: 6px; font-size: 13px; color: #4b5563; cursor: pointer;">
                            Xóa toàn bộ giỏ
                        </button>
                    </form>
                </div>
            </div>

            <!-- Cột phải: Tóm tắt thanh toán -->
            <div style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 24px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                <h2 style="font-size: 18px; font-weight: 700; color: var(--color-on-surface); margin: 0 0 16px;">
                    Tóm tắt đơn hàng
                </h2>

                <div style="font-size: 14px; display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; color: var(--color-on-surface-variant);">
                        <span>Tổng số lượng:</span>
                        <strong style="color: var(--color-on-surface);">{{ $cart->total_quantity }} món</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: var(--color-on-surface-variant);">
                        <span>Tạm tính tiền hàng:</span>
                        <strong style="color: var(--color-on-surface);">{{ number_format($cart->total_amount, 0, ',', '.') }}đ</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: var(--color-on-surface-variant);">
                        <span>Phí vận chuyển:</span>
                        <span style="color: #10b981; font-weight: 600;">Miễn phí</span>
                    </div>

                    <hr style="border: none; border-top: 1px solid var(--color-outline-variant, #f0eae1); margin: 6px 0;">

                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <span style="font-weight: 700; font-size: 16px; color: var(--color-on-surface);">Tổng thanh toán:</span>
                        <strong style="font-size: 20px; font-weight: 800; color: var(--color-primary, #e76f51);">
                            {{ number_format($cart->total_amount, 0, ',', '.') }}đ
                        </strong>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <a href="{{ route('checkout.index') }}" 
                       style="display: block; width: 100%; box-sizing: border-box; text-align: center; padding: 14px 20px; background: var(--color-primary, #e76f51); color: #fff; border-radius: 8px; font-size: 15px; font-weight: 700; text-decoration: none; transition: opacity 0.2s;">
                        Tiến hành đặt hàng
                    </a>
                </div>

                <div style="background: #f9fafb; border-radius: 8px; padding: 12px; font-size: 12px; color: var(--color-on-surface-variant); line-height: 1.6;">
                    <div>Hỗ trợ thanh toán trực tiếp khi nhận hàng (COD).</div>
                    <div>Kiểm tra hàng trước khi thanh toán.</div>
                </div>
            </div>

        </div>
    @endif
</div>
@endsection
