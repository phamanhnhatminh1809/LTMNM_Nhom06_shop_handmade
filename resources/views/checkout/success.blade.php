@extends('layouts.app')

@section('title', 'Đặt hàng thành công')

@section('content')
<div class="container" style="padding-top: 36px; padding-bottom: 60px; max-width: 760px;">
    <!-- Thông báo thành công -->
    <div style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 36px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 20px rgba(0,0,0,0.03); text-align: center;">
        
        <div style="display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; background: #ecfdf5; color: #059669; margin-bottom: 16px;">
            ĐẶT HÀNG THÀNH CÔNG
        </div>

        <h1 style="font-size: 24px; font-weight: 800; color: var(--color-on-surface); margin: 0 0 8px;">
            Cảm ơn bạn đã đặt hàng!
        </h1>
        <p style="font-size: 14px; color: var(--color-on-surface-variant); margin: 0 0 24px;">
            Mã đơn hàng của bạn là <strong style="color: var(--color-primary, #e76f51);">#{{ $order->code }}</strong>. Chúng tôi sẽ sớm liên hệ để xác nhận đơn.
        </p>

        <!-- Thẻ chi tiết đơn hàng -->
        <div style="text-align: left; background: #fafaf9; border-radius: 12px; padding: 20px; margin-bottom: 24px; border: 1px solid #f0eae1;">
            <h2 style="font-size: 15px; font-weight: 700; color: var(--color-on-surface); margin: 0 0 14px; border-bottom: 1px solid #f0eae1; padding-bottom: 10px;">
                Thông tin nhận hàng
            </h2>

            <div style="font-size: 13px; line-height: 1.8; color: var(--color-on-surface);">
                <div><strong>Người nhận:</strong> {{ $order->shipping_name }}</div>
                <div><strong>Số điện thoại:</strong> {{ $order->shipping_phone }}</div>
                <div><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</div>
                <div><strong>Phương thức thanh toán:</strong> Thanh toán khi nhận hàng (COD)</div>
                <div><strong>Trạng thái đơn hàng:</strong> 
                    <span style="display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: 600; background: #fef3c7; color: #d97706;">
                        Chờ xác nhận
                    </span>
                </div>
            </div>
        </div>

        <!-- Bảng sản phẩm đã đặt -->
        <div style="text-align: left; margin-bottom: 28px;">
            <h2 style="font-size: 15px; font-weight: 700; color: var(--color-on-surface); margin: 0 0 12px;">
                Chi tiết sản phẩm đã đặt
            </h2>

            <div style="border: 1px solid #f0eae1; border-radius: 12px; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: #fafaf9; border-bottom: 1px solid #f0eae1; text-align: left; color: var(--color-on-surface-variant);">
                            <th style="padding: 10px 14px;">Sản phẩm</th>
                            <th style="padding: 10px 14px; text-align: center;">Số lượng</th>
                            <th style="padding: 10px 14px; text-align: right;">Đơn giá</th>
                            <th style="padding: 10px 14px; text-align: right;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr style="border-bottom: 1px solid #f0eae1;">
                                <td style="padding: 12px 14px; font-weight: 600; color: var(--color-on-surface);">
                                    {{ $item->product->name ?? 'Sản phẩm' }}
                                </td>
                                <td style="padding: 12px 14px; text-align: center;">
                                    {{ $item->quantity }}
                                </td>
                                <td style="padding: 12px 14px; text-align: right; color: var(--color-on-surface-variant);">
                                    {{ number_format($item->unit_price, 0, ',', '.') }}đ
                                </td>
                                <td style="padding: 12px 14px; text-align: right; font-weight: 700; color: var(--color-on-surface);">
                                    {{ number_format($item->total_price, 0, ',', '.') }}đ
                                </td>
                            </tr>
                        @endforeach
                        <tr style="background: #fafaf9;">
                            <td colspan="3" style="padding: 14px; text-align: right; font-weight: 700; font-size: 14px;">
                                Tổng thanh toán:
                            </td>
                            <td style="padding: 14px; text-align: right; font-weight: 800; font-size: 16px; color: var(--color-primary, #e76f51);">
                                {{ number_format($order->total_amount, 0, ',', '.') }}đ
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Các nút điều hướng -->
        <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('products.index') }}" 
               style="padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; background: var(--color-primary, #e76f51); color: #fff;">
                Tiếp tục mua hàng
            </a>
            <a href="{{ route('profile.show') }}" 
               style="padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; border: 1px solid #d1d5db; color: #374151; background: #fff;">
                Quản lý tài khoản
            </a>
        </div>

    </div>
</div>
@endsection
