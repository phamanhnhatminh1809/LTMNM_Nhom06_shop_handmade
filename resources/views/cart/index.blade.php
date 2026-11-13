@extends('layouts.app')

@section('title', 'Giỏ hàng của bạn')

@push('styles')
<style>
    /* CSS cho Modal Xác nhận */
    .modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5); z-index: 9999;
        display: flex; align-items: center; justify-content: center;
        opacity: 0; visibility: hidden; transition: all 0.3s ease;
    }
    .modal-overlay.active {
        opacity: 1; visibility: visible;
    }
    .modal-box {
        background: #fff; width: 100%; max-width: 400px;
        border-radius: 12px; padding: 24px; text-align: center;
        transform: translateY(-20px); transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .modal-overlay.active .modal-box {
        transform: translateY(0);
    }
    .modal-icon {
        width: 60px; height: 60px; margin: 0 auto 16px;
        background: #fef2f2; color: #ef4444; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 28px;
    }
    .modal-title { font-size: 18px; font-weight: 700; margin-bottom: 8px; color: #333; }
    .modal-desc { font-size: 14px; color: #666; margin-bottom: 24px; }
    .modal-actions { display: flex; gap: 12px; justify-content: center; }
    .btn-cancel {
        padding: 10px 20px; border-radius: 8px; border: 1px solid #d1d5db;
        background: #fff; color: #4b5563; font-weight: 600; cursor: pointer; flex: 1;
    }
    .btn-confirm {
        padding: 10px 20px; border-radius: 8px; border: none;
        background: #ef4444; color: #fff; font-weight: 600; cursor: pointer; flex: 1;
    }
    .btn-cancel:hover { background: #f3f4f6; }
    .btn-confirm:hover { background: #dc2626; }
    .cart-layout-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 30px;
        align-items: start;
    }
    @media (max-width: 860px) {
        .cart-layout-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="container" style="padding-top: 28px; padding-bottom: 50px;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" style="margin-bottom: 24px; font-size: 14px; color: var(--color-on-surface-variant); display: flex; align-items: center;">
        <a href="{{ route('home') }}" style="color: inherit; text-decoration: none;">Trang chủ</a>
        <i class="bi bi-chevron-right" style="font-size: 11px; margin: 0 8px; opacity: 0.5;"></i>
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
            <div style="width: 72px; height: 72px; border-radius: 50%; background: #faf7f5; color: var(--color-primary); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 32px;">
                <i class="bi bi-bag-x"></i>
            </div>
            <div style="font-size: 18px; font-weight: 700; color: var(--color-on-surface); margin-bottom: 8px;">
                Giỏ hàng của bạn đang trống
            </div>
            <p style="color: var(--color-on-surface-variant); font-size: 14px; margin-bottom: 24px; max-width: 460px; margin-left: auto; margin-right: auto;">
                Hãy khám phá các sản phẩm thủ công của chúng tôi và chọn cho mình những món đồ phù hợp.
            </p>
            <a href="{{ route('products.index') }}" class="btn btn--primary" style="padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="bi bi-compass"></i>
                <span>Khám phá sản phẩm ngay</span>
            </a>
        </div>
    @else
        @php
            $freeThreshold = (float) config('shop.shipping.free_threshold', 300000);
            $defaultShipFee = (float) config('shop.shipping.default_fee', 30000);
            $subtotal = (float) $cart->total_amount;
            $shippingFee = $subtotal >= $freeThreshold ? 0 : $defaultShipFee;
            $estimatedTotal = $subtotal + $shippingFee;
            $amountNeededForFreeShip = max(0, $freeThreshold - $subtotal);
        @endphp
        <!-- Bố cục 2 cột: Bảng sản phẩm & Tóm tắt đơn hàng -->
        <div class="cart-layout-grid">
            
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
                                        : asset('assets/images/placeholder.svg');
                                @endphp
                                <tr style="border-bottom: 1px solid var(--color-outline-variant, #f0eae1);">
                                    <!-- Cột thông tin sản phẩm -->
                                    <td style="padding: 16px 10px;">
                                        <div style="display: flex; gap: 14px; align-items: center;">
                                            <a href="{{ route('products.show', $item->product_id) }}" style="flex-shrink: 0;">
                                                <img src="{{ $imgSrc }}" alt="{{ $item->name }}" 
                                                     style="width: 64px; height: 64px; object-fit: cover; border-radius: 8px; border: 1px solid #f0eae1;"
                                                     onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
                                            </a>
                                            <div>
                                                <a href="{{ route('products.show', $item->product_id) }}" 
                                                   style="font-weight: 700; color: var(--color-on-surface); text-decoration: none; line-height: 1.4; display: block; margin-bottom: 4px;">
                                                    {{ $item->name }}
                                                </a>
                                                <span style="font-size: 12px; color: var(--color-on-surface-variant); display: block;">
                                                    Mã: {{ $item->product_code }}
                                                </span>
                                                @if($item->product && $item->product->stock_quantity <= $item->product->effectiveMinStockThreshold())
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
                                        @php $maxStock = (int) ($item->product->stock_quantity ?? 999); @endphp
                                        <form action="{{ route('cart.update', $item->id) }}" method="POST" style="display: inline-flex; align-items: center; border: 1px solid #d1d5db; border-radius: 6px; overflow: hidden;">
                                            @csrf
                                            @method('PUT')
                                            <button type="button" 
                                                    onclick="const inp = this.nextElementSibling; const current = parseInt(inp.value, 10) || 1; if (current > 1) { inp.value = current - 1; this.form.submit(); }"
                                                    style="background: #f9fafb; border: none; padding: 6px 10px; cursor: pointer; font-size: 14px; font-weight: 700; color: #4b5563;">
                                                -
                                            </button>
                                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $maxStock }}" 
                                                   onchange="this.form.submit();"
                                                   style="width: 44px; text-align: center; border: none; font-size: 14px; font-weight: 600; padding: 6px 0; -moz-appearance: textfield;">
                                            <button type="button" 
                                                    onclick="const inp = this.previousElementSibling; const current = parseInt(inp.value, 10) || 1; const max = parseInt(inp.max, 10) || 999; if (current >= max) { if (window.showShopToast) window.showShopToast('Đã đạt tối đa tồn kho (' + max + ' sản phẩm).', 'warning'); return; } inp.value = current + 1; this.form.submit();"
                                                    style="background: #f9fafb; border: none; padding: 6px 10px; cursor: pointer; font-size: 14px; font-weight: 700; color: #4b5563;">
                                                +
                                            </button>
                                        </form>
                                    </td>

                                    <!-- Cột thành tiền -->
                                    <td style="padding: 16px 10px; text-align: right; font-weight: 700; color: var(--color-primary, #e76f51);">
                                        {{ number_format($item->subtotal, 0, ',', '.') }}đ
                                    </td>

                                    <!-- Cột thao tác xóa (Dùng Modal thay vì confirm) -->
                                    <td style="padding: 16px 10px; text-align: center;">
                                        <!-- Form thực hiện xóa (ẩn) -->
                                        <form id="delete-form-{{ $item->id }}" action="{{ route('cart.destroy', $item->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <!-- Nút kích hoạt Modal -->
                                        <x-table-action type="delete"
                                                        label="Xóa khỏi giỏ hàng"
                                                        tooltip-align="end"
                                                        onclick="showDeleteModal({{ $item->id }})" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--color-outline-variant, #f0eae1); flex-wrap: wrap; gap: 12px;">
                    <a href="{{ route('products.index') }}" style="color: var(--color-primary); font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-arrow-left"></i>
                        <span>Tiếp tục xem sản phẩm khác</span>
                    </a>
                    <!-- Nút xóa toàn bộ giỏ (Cũng dùng Modal) -->
                    <form id="clear-cart-form" action="{{ route('cart.clear') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <button type="button" onclick="showClearCartModal()" style="background: none; border: 1px solid #d1d5db; padding: 8px 16px; border-radius: 6px; font-size: 13px; color: #4b5563; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-trash3"></i>
                        <span>Xóa toàn bộ giỏ</span>
                    </button>
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
                        @if($shippingFee == 0)
                            <span style="color: #10b981; font-weight: 600;">Miễn phí</span>
                        @else
                            <span style="font-weight: 600; color: var(--color-on-surface);">{{ number_format($shippingFee, 0, ',', '.') }}đ</span>
                        @endif
                    </div>

                    @if($amountNeededForFreeShip > 0)
                        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 8px 10px; font-size: 12px; color: #92400e;">
                            <i class="bi bi-truck"></i>
                            Mua thêm <strong>{{ number_format($amountNeededForFreeShip, 0, ',', '.') }}đ</strong> để được <strong>miễn phí vận chuyển</strong> (từ {{ number_format($freeThreshold, 0, ',', '.') }}đ).
                        </div>
                    @endif

                    <hr style="border: none; border-top: 1px solid var(--color-outline-variant, #f0eae1); margin: 6px 0;">

                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <span style="font-weight: 700; font-size: 16px; color: var(--color-on-surface);">Tổng dự kiến:</span>
                        <strong style="font-size: 20px; font-weight: 800; color: var(--color-primary, #e76f51);">
                            {{ number_format($estimatedTotal, 0, ',', '.') }}đ
                        </strong>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <a href="{{ route('checkout.index') }}" 
                       style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; box-sizing: border-box; text-align: center; padding: 14px 20px; background: var(--color-primary, #e76f51); color: #fff; border-radius: 8px; font-size: 15px; font-weight: 700; text-decoration: none; transition: opacity 0.2s;">
                        <span>Tiến hành đặt hàng</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

                <div style="background: #f9fafb; border-radius: 8px; padding: 12px; font-size: 12px; color: var(--color-on-surface-variant); line-height: 1.6;">
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                        <i class="bi bi-truck" style="color: var(--color-primary);"></i>
                        <span>Miễn phí vận chuyển cho đơn từ {{ number_format($freeThreshold, 0, ',', '.') }}đ.</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-shield-check" style="color: #10b981;"></i>
                        <span>Hỗ trợ COD và chuyển khoản VietQR.</span>
                    </div>
                </div>
            </div>

        </div>
    @endif
</div>

<!-- MODAL XÁC NHẬN XÓA -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon"><i class="bi bi-trash3-fill"></i></div>
        <h3 class="modal-title" id="modalTitle">Xóa sản phẩm?</h3>
        <p class="modal-desc" id="modalDesc">Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng không?</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeModal()">Hủy bỏ</button>
            <button class="btn-confirm" id="btnConfirmDelete" onclick="submitDelete()">Đồng ý xóa</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let formToSubmitId = null;

    // Hàm hiển thị modal khi xóa 1 sản phẩm
    function showDeleteModal(id) {
        formToSubmitId = 'delete-form-' + id;
        document.getElementById('modalTitle').innerText = 'Xóa sản phẩm?';
        document.getElementById('modalDesc').innerText = 'Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng không?';
        document.getElementById('deleteModal').classList.add('active');
    }

    // Hàm hiển thị modal khi xóa toàn bộ giỏ
    function showClearCartModal() {
        formToSubmitId = 'clear-cart-form';
        document.getElementById('modalTitle').innerText = 'Xóa toàn bộ giỏ hàng?';
        document.getElementById('modalDesc').innerText = 'Hành động này sẽ xóa tất cả sản phẩm trong giỏ hàng của bạn. Bạn có chắc chắn muốn tiếp tục?';
        document.getElementById('deleteModal').classList.add('active');
    }

    // Hàm đóng modal
    function closeModal() {
        document.getElementById('deleteModal').classList.remove('active');
        formToSubmitId = null;
    }

    // Hàm thực thi Form xóa khi bấm Đồng ý
    function submitDelete() {
        if (formToSubmitId) {
            document.getElementById(formToSubmitId).submit();
        }
    }
</script>
@endpush
@endsection