@extends('layouts.app')

@section('title', 'Xem lại đơn hàng và Thanh toán')

@section('content')
<div class="container" style="padding-top: 28px; padding-bottom: 50px;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" style="margin-bottom: 24px; font-size: 14px; color: var(--color-on-surface-variant);">
        <a href="{{ route('home') }}" style="color: inherit; text-decoration: none;">Trang chủ</a>
        <span style="margin: 0 8px;">/</span>
        <a href="{{ route('cart.index') }}" style="color: inherit; text-decoration: none;">Giỏ hàng</a>
        <span style="margin: 0 8px;">/</span>
        <span style="color: var(--color-primary); font-weight: 600;">Thanh toán</span>
    </nav>

    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 800; color: var(--color-on-surface); margin: 0 0 6px;">
            Xem lại đơn hàng và Thanh toán
        </h1>
        <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant);">
            Vui lòng kiểm tra lại danh sách sản phẩm và cung cấp thông tin nhận hàng
        </p>
    </div>

    <!-- Thông báo lỗi chung -->
    @if(session('error'))
        <div style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 14px 18px; border-radius: 8px; font-size: 14px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('checkout.process') }}" method="POST">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 30px; align-items: start;">
            
            <!-- Cột trái: Thông tin nhận hàng & Phương thức thanh toán -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                
                <!-- Khối 1: Thông tin người nhận -->
                <section style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 24px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                    <h2 style="font-size: 18px; font-weight: 700; color: var(--color-on-surface); margin: 0 0 16px;">
                        1. Thông tin giao hàng
                    </h2>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--color-on-surface);">
                                Họ và tên người nhận <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="shipping_name" 
                                   value="{{ old('shipping_name', $user->full_name ?? '') }}" required
                                   placeholder="Ví dụ: Nguyễn Văn A"
                                   style="width: 100%; padding: 10px 14px; border: 1px solid var(--color-outline, #d1d5db); border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                            @error('shipping_name')
                                <span style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--color-on-surface);">
                                Số điện thoại nhận hàng <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="shipping_phone" 
                                   value="{{ old('shipping_phone', $user->phone ?? '') }}" required
                                   placeholder="Ví dụ: 0912345678"
                                   style="width: 100%; padding: 10px 14px; border: 1px solid var(--color-outline, #d1d5db); border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                            @error('shipping_phone')
                                <span style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div style="margin-bottom: 8px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--color-on-surface);">
                            Địa chỉ giao hàng chi tiết <span style="color: #ef4444;">*</span>
                        </label>
                        <textarea name="shipping_address" rows="3" required
                                  placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..."
                                  style="width: 100%; padding: 10px 14px; border: 1px solid var(--color-outline, #d1d5db); border-radius: 8px; font-size: 14px; box-sizing: border-box; font-family: inherit;">{{ old('shipping_address', $user->address ?? '') }}</textarea>
                        @error('shipping_address')
                            <span style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                        @enderror
                    </div>
                </section>

                <!-- Khối 2: Phương thức thanh toán -->
                <section style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 24px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                    <h2 style="font-size: 18px; font-weight: 700; color: var(--color-on-surface); margin: 0 0 16px;">
                        2. Phương thức thanh toán
                    </h2>

                    <div style="border: 2px solid var(--color-primary, #e76f51); background: #fffaf7; border-radius: 12px; padding: 16px; display: flex; gap: 14px; align-items: flex-start;">
                        <input type="radio" name="payment_method" id="pay_cod" value="cod" checked 
                               style="margin-top: 4px; accent-color: var(--color-primary, #e76f51); width: 18px; height: 18px;">
                        <div>
                            <label for="pay_cod" style="font-weight: 700; font-size: 15px; color: var(--color-on-surface); display: block; margin-bottom: 4px; cursor: pointer;">
                                Thanh toán khi nhận hàng (COD)
                            </label>
                            <p style="margin: 0; font-size: 13px; color: var(--color-on-surface-variant); line-height: 1.5;">
                                Bạn thanh toán bằng tiền mặt trực tiếp cho nhân viên vận chuyển ngay khi nhận và kiểm tra hàng.
                            </p>
                        </div>
                    </div>
                    @error('payment_method')
                        <span style="color: #ef4444; font-size: 12px; margin-top: 6px; display: block;">{{ $message }}</span>
                    @enderror
                </section>

            </div>

            <!-- Cột phải: Xem lại đơn hàng & Nút đặt hàng -->
            <aside style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 24px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.03); position: sticky; top: 20px;">
                <h2 style="font-size: 18px; font-weight: 700; color: var(--color-on-surface); margin: 0 0 16px;">
                    Xem lại đơn hàng
                </h2>

                <!-- Danh sách sản phẩm rút gọn -->
                <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 20px; max-height: 320px; overflow-y: auto; padding-right: 4px;">
                    @foreach($cart->details as $item)
                        @php
                            $imgSrc = $item->image_url 
                                ? (str_starts_with($item->image_url, 'http') ? $item->image_url : asset(ltrim($item->image_url, '/')))
                                : asset('images/logo.png');
                        @endphp
                        <div style="display: flex; gap: 12px; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--color-outline-variant, #f0eae1);">
                            <img src="{{ $imgSrc }}" alt="{{ $item->name }}" 
                                 style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #f0eae1; flex-shrink: 0;"
                                 onerror="this.src='{{ asset('images/logo.png') }}'">
                            <div style="flex-grow: 1; min-width: 0;">
                                <div style="font-size: 13px; font-weight: 600; color: var(--color-on-surface); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $item->name }}
                                </div>
                                <div style="font-size: 12px; color: var(--color-on-surface-variant); margin-top: 2px;">
                                    Số lượng: {{ $item->quantity }} x {{ number_format($item->unit_price, 0, ',', '.') }}đ
                                </div>
                            </div>
                            <div style="font-size: 13px; font-weight: 700; color: var(--color-on-surface); flex-shrink: 0;">
                                {{ number_format($item->subtotal, 0, ',', '.') }}đ
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Tóm tắt chi phí -->
                <div style="font-size: 14px; display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
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
                        <span style="font-weight: 700; font-size: 15px; color: var(--color-on-surface);">Tổng thanh toán:</span>
                        <strong style="font-size: 20px; font-weight: 800; color: var(--color-primary, #e76f51);">
                            {{ number_format($cart->total_amount, 0, ',', '.') }}đ
                        </strong>
                    </div>
                </div>

                <!-- Nút xác nhận đặt hàng -->
                <button type="submit" 
                        style="width: 100%; padding: 14px 20px; background: var(--color-primary, #e76f51); color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: opacity 0.2s;">
                    Xác nhận đặt hàng
                </button>

                <div style="text-align: center; margin-top: 14px;">
                    <a href="{{ route('cart.index') }}" style="color: var(--color-on-surface-variant); font-size: 13px; text-decoration: none;">
                        &larr; Chỉnh sửa lại giỏ hàng
                    </a>
                </div>
            </aside>

        </div>
    </form>
</div>
@endsection
