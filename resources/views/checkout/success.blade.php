@extends('layouts.app')

@section('title', 'Đặt hàng thành công')

@push('styles')
<style>
    /* CSS cho Modal Xác nhận Hủy đơn */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5); z-index: 9999;
        display: flex; align-items: center; justify-content: center;
        opacity: 0; visibility: hidden; transition: all 0.3s ease;
    }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    .modal-box {
        background: #fff; width: 100%; max-width: 400px;
        border-radius: 12px; padding: 24px; text-align: center;
        transform: translateY(-20px); transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .modal-overlay.active .modal-box { transform: translateY(0); }
    .modal-icon {
        width: 60px; height: 60px; margin: 0 auto 16px;
        background: #fef2f2; color: #ef4444; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 28px;
    }
    .modal-title { font-size: 18px; font-weight: 700; margin-bottom: 8px; color: #333; }
    .modal-desc { font-size: 14px; color: #666; margin-bottom: 24px; }
    .modal-actions { display: flex; gap: 12px; justify-content: center; }
    .btn-cancel-modal {
        padding: 10px 20px; border-radius: 8px; border: 1px solid #d1d5db;
        background: #fff; color: #4b5563; font-weight: 600; cursor: pointer; flex: 1;
    }
    .btn-confirm-modal {
        padding: 10px 20px; border-radius: 8px; border: none;
        background: #ef4444; color: #fff; font-weight: 600; cursor: pointer; flex: 1;
    }
</style>
@endpush

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
                <div><strong>Phương thức thanh toán:</strong> 
                    @if($order->payment_method === 'qr')
                        Chuyển khoản qua mã QR (VietQR)
                    @else
                        Thanh toán khi nhận hàng (COD)
                    @endif
                </div>
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
                            <th style="padding: 10px 14px; text-align: center;">SL</th>
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
                    </tbody>
                    <tfoot>
                        <!-- 1. Tạm tính (Đã thêm ?? 0 để tránh lỗi NULL) -->
                        <tr style="background: #fff;">
                            <td colspan="3" style="padding: 10px 14px; text-align: right; color: var(--color-on-surface-variant);">
                                Tạm tính tiền hàng:
                            </td>
                            <td style="padding: 10px 14px; text-align: right; font-weight: 600;">
                                {{ number_format($order->total_amount - ($order->shipping_fee ?? 0) + ($order->discount_amount ?? 0), 0, ',', '.') }}đ
                            </td>
                        </tr>
                        
                        <!-- 2. Giảm giá -->
                        @if(($order->discount_amount ?? 0) > 0)
                        <tr style="background: #fff;">
                            <td colspan="3" style="padding: 10px 14px; text-align: right; color: #10b981;">
                                Giảm giá:
                            </td>
                            <td style="padding: 10px 14px; text-align: right; font-weight: 600; color: #10b981;">
                                -{{ number_format($order->discount_amount, 0, ',', '.') }}đ
                            </td>
                        </tr>
                        @endif
                        
                        <!-- 3. Phí vận chuyển -->
                        <tr style="background: #fff; border-bottom: 1px solid #f0eae1;">
                            <td colspan="3" style="padding: 10px 14px; text-align: right; color: var(--color-on-surface-variant);">
                                Phí vận chuyển:
                            </td>
                            <td style="padding: 10px 14px; text-align: right; font-weight: 600;">
                                @if(($order->shipping_fee ?? 0) > 0)
                                    {{ number_format($order->shipping_fee, 0, ',', '.') }}đ
                                @else
                                    <span style="color: #10b981;">Miễn phí</span>
                                @endif
                            </td>
                        </tr>
                        
                        <!-- 4. Tổng thanh toán -->
                        <tr style="background: #fafaf9;">
                            <td colspan="3" style="padding: 14px; text-align: right; font-weight: 700; font-size: 14px;">
                                Tổng thanh toán:
                            </td>
                            <td style="padding: 14px; text-align: right; font-weight: 800; font-size: 16px; color: var(--color-primary, #e76f51);">
                                {{ number_format($order->total_amount, 0, ',', '.') }}đ
                            </td>
                        </tr>
                    </tfoot>
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
            @if($order->status === 'pending')
                <button type="button" onclick="showCancelModal()" 
                        style="padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; border: 1px solid #ef4444; color: #ef4444; background: #fff; cursor: pointer;">
                    Hủy đơn hàng
                </button>
            @endif
        </div>

    </div>
</div>

<!-- MODAL XÁC NHẬN HỦY ĐƠN -->
<div class="modal-overlay" id="cancelOrderModal">
    <div class="modal-box">
        <div class="modal-icon">⚠️</div>
        <h3 class="modal-title">Hủy đơn hàng?</h3>
        <p class="modal-desc">Bạn có chắc chắn muốn hủy đơn hàng này không? Đơn hàng đã đặt sẽ không thể khôi phục.</p>
        <div class="modal-actions">
            <button class="btn-cancel-modal" onclick="closeCancelModal()">Suy nghĩ lại</button>
            <button class="btn-confirm-modal" onclick="submitCancelOrder()">Đồng ý hủy</button>
        </div>
    </div>
</div>

<!-- Form Ẩn để thực hiện Hủy -->
<form id="cancel-order-form" action="{{ route('orders.cancel', $order->id) }}" method="POST" style="display: none;">
    @csrf
</form>

@push('scripts')
<script>
    function showCancelModal() {
        document.getElementById('cancelOrderModal').classList.add('active');
    }
    function closeCancelModal() {
        document.getElementById('cancelOrderModal').classList.remove('active');
    }
    function submitCancelOrder() {
        document.getElementById('cancel-order-form').submit();
    }
</script>
@endpush
@endsection