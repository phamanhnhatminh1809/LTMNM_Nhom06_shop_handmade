@extends('layouts.app')

@section('title', 'Xem lại đơn hàng và Thanh toán')

@section('content')
<div class="container checkout-wrap">

    {{-- Breadcrumb --}}
    <nav class="breadcrumb">
        <a href="{{ route('home') }}">Trang chủ</a>
        <span>/</span>
        <a href="{{ route('cart.index') }}">Giỏ hàng</a>
        <span>/</span>
        <span class="breadcrumb__current">Thanh toán</span>
    </nav>

    <div class="checkout-header">
        <h1 class="checkout-title">Xem lại đơn hàng và Thanh toán</h1>
        <p class="checkout-subtitle">Vui lòng kiểm tra lại danh sách sản phẩm và cung cấp thông tin nhận hàng</p>
    </div>

    @if(session('error'))
        <div class="alert alert--error">{{ session('error') }}</div>
    @endif

    <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
        @csrf

        <div class="checkout-grid">

            {{-- Cột trái --}}
            <div class="checkout-left">

                {{-- Khối 1: Thông tin giao hàng --}}
                <section class="checkout-card">
                    <h2 class="checkout-card__title">1. Thông tin giao hàng</h2>

                    <div class="checkout-row-2">
                        <div class="form-group">
                            <label class="form-label">
                                Họ và tên người nhận <span class="required">*</span>
                            </label>
                            <input type="text" name="shipping_name"
                                   value="{{ old('shipping_name', $user->full_name ?? '') }}" required
                                   placeholder="Ví dụ: Nguyễn Văn A"
                                   class="form-input">
                            @error('shipping_name')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Số điện thoại nhận hàng <span class="required">*</span>
                            </label>
                            <input type="text" name="shipping_phone"
                                   value="{{ old('shipping_phone', $user->phone ?? '') }}" required
                                   placeholder="Ví dụ: 0912345678"
                                   class="form-input">
                            @error('shipping_phone')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Địa chỉ giao hàng chi tiết <span class="required">*</span>
                        </label>
                        <textarea name="shipping_address" rows="3" required
                                  placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..."
                                  class="form-input form-textarea">{{ old('shipping_address', $user->address ?? '') }}</textarea>
                        @error('shipping_address')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </section>

                {{-- Khối 2: Phương thức thanh toán --}}
                <section class="checkout-card">
                    <h2 class="checkout-card__title">2. Phương thức thanh toán</h2>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cod" checked class="payment-option__radio">
                        <div class="payment-option__body">
                            <span class="payment-option__title">Thanh toán khi nhận hàng (COD)</span>
                            <span class="payment-option__desc">
                                Bạn thanh toán bằng tiền mặt trực tiếp cho nhân viên vận chuyển ngay khi nhận và kiểm tra hàng.
                            </span>
                        </div>
                    </label>
                    @error('payment_method')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </section>

            </div>

            {{-- Cột phải --}}
            <aside class="checkout-summary">
                <h2 class="checkout-card__title">Xem lại đơn hàng</h2>

                <div class="checkout-items">
                    @foreach($cart->details as $item)
                        @php
                            $imgSrc = $item->image_url
                                ? (str_starts_with($item->image_url, 'http') ? $item->image_url : asset(ltrim($item->image_url, '/')))
                                : asset('assets/images/logo.png');
                        @endphp
                        <div class="checkout-item">
                            <img src="{{ $imgSrc }}" alt="{{ $item->name }}"
                                 class="checkout-item__img"
                                 onerror="this.src='{{ asset('assets/images/logo.png') }}'">
                            <div class="checkout-item__info">
                                <div class="checkout-item__name">{{ $item->name }}</div>
                                <div class="checkout-item__qty">
                                    Số lượng: {{ $item->quantity }} × {{ number_format($item->unit_price, 0, ',', '.') }}đ
                                </div>
                            </div>
                            <div class="checkout-item__price">
                                {{ number_format($item->subtotal, 0, ',', '.') }}đ
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="checkout-totals">
                    <div class="checkout-total-row">
                        <span>Tạm tính tiền hàng:</span>
                        <strong>{{ number_format($cart->total_amount, 0, ',', '.') }}đ</strong>
                    </div>
                    <div class="checkout-total-row">
                        <span>Phí vận chuyển:</span>
                        <span class="text-success">Miễn phí</span>
                    </div>

                    <hr class="checkout-divider">

                    <div class="checkout-total-row checkout-total-row--final">
                        <span>Tổng thanh toán:</span>
                        <strong>{{ number_format($cart->total_amount, 0, ',', '.') }}đ</strong>
                    </div>
                </div>

                <button type="submit" id="placeOrderBtn" class="btn btn--primary btn--block btn--lg">
                    Xác nhận đặt hàng
                </button>

                <div class="checkout-back">
                    <a href="{{ route('cart.index') }}">← Chỉnh sửa lại giỏ hàng</a>
                </div>
            </aside>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('checkoutForm').addEventListener('submit', function () {
    var btn = document.getElementById('placeOrderBtn');
    btn.disabled = true;
    btn.textContent = 'Đang xử lý đơn hàng...';
    btn.style.opacity = '0.7';
});
</script>
@endpush