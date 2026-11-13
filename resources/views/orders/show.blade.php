@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng #' . $order->code)

@push('styles')
<style>
    /* CSS cho Modal Xác nhận Hủy đơn */
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
    .btn-cancel-modal {
        padding: 10px 20px; border-radius: 8px; border: 1px solid #d1d5db;
        background: #fff; color: #4b5563; font-weight: 600; cursor: pointer; flex: 1;
    }
    .btn-confirm-modal {
        padding: 10px 20px; border-radius: 8px; border: none;
        background: #ef4444; color: #fff; font-weight: 600; cursor: pointer; flex: 1;
    }
    .btn-cancel-modal:hover { background: #f3f4f6; }
    .btn-confirm-modal:hover { background: #dc2626; }
</style>
@endpush

@section('content')
<div class="container" style="padding-top: 28px; padding-bottom: 50px;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" style="margin-bottom: 24px; font-size: 14px; color: var(--color-on-surface-variant); display: flex; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('home') }}" style="color: inherit; text-decoration: none;">Trang chủ</a>
        <i class="bi bi-chevron-right" style="font-size: 11px; margin: 0 8px; opacity: 0.5;"></i>
        <a href="{{ route('orders.index') }}" style="color: inherit; text-decoration: none;">Đơn hàng của tôi</a>
        <i class="bi bi-chevron-right" style="font-size: 11px; margin: 0 8px; opacity: 0.5;"></i>
        <span style="color: var(--color-primary); font-weight: 600;">Chi tiết #{{ $order->code }}</span>
    </nav>

    <!-- Header & Trạng thái -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-size: 24px; font-weight: 800; color: var(--color-on-surface); margin: 0 0 6px;">
                Đơn hàng #{{ $order->code }}
            </h1>
            <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant);">
                Ngày đặt: {{ $order->created_at->format('d/m/Y H:i:s') }}
            </p>
        </div>

        @php
            $statusMap = [
                'pending'   => ['label' => 'Chờ xử lý', 'bg' => '#fef3c7', 'color' => '#d97706'],
                'shipping'  => ['label' => 'Đang giao hàng', 'bg' => '#e0e7ff', 'color' => '#4338ca'],
                'completed' => ['label' => 'Đã hoàn thành', 'bg' => '#d1fae5', 'color' => '#059669'],
                'cancelled' => ['label' => 'Đã hủy', 'bg' => '#fee2e2', 'color' => '#dc2626'],
            ];
            $st = $statusMap[$order->status] ?? ['label' => $order->status, 'bg' => '#f3f4f6', 'color' => '#6b7280'];
        @endphp

        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span style="display: inline-block; padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; background: {{ $st['bg'] }}; color: {{ $st['color'] }};">
                {{ $st['label'] }}
            </span>

            @if($order->status === 'pending' && !$order->isPaid())
                <!-- Nút kích hoạt Modal Hủy đơn -->
                <button type="button" onclick="showCancelOrderModal()" 
                        style="padding: 6px 16px; border: 1px solid #ef4444; background: #fff; color: #ef4444; border-radius: 20px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-x-circle"></i>
                    <span>Hủy đơn hàng</span>
                </button>
                
                <!-- Form ẩn để thực hiện hủy -->
                <form id="cancel-order-form" action="{{ route('orders.cancel', $order->id) }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @endif

            @if(in_array($order->status, ['completed', 'cancelled']))
                <form action="{{ route('orders.reorder', $order->id) }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit"
                            style="padding: 6px 16px; border: 1px solid #10b981; background: #ecfdf5; color: #059669; border-radius: 20px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-arrow-repeat"></i>
                        <span>Mua lại đơn này</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Thanh tiến trình trạng thái đơn hàng -->
    <div style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 20px 24px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.02); margin-bottom: 24px;">
        @if($order->status === 'cancelled')
            <div style="display: flex; align-items: center; gap: 12px; color: #dc2626; font-weight: 700; font-size: 14px;">
                <i class="bi bi-x-octagon-fill" style="font-size: 22px;"></i>
                <span>Đơn hàng này đã bị hủy. Bạn có thể bấm "Mua lại đơn này" để đặt lại các sản phẩm còn hàng.</span>
            </div>
        @else
            @php
                $steps = [
                    ['key' => 'pending',   'label' => 'Chờ xác nhận', 'icon' => 'bi-clipboard-check'],
                    ['key' => 'shipping',  'label' => 'Đang giao hàng', 'icon' => 'bi-truck'],
                    ['key' => 'completed', 'label' => 'Hoàn thành', 'icon' => 'bi-patch-check-fill'],
                ];
                $currentIndex = match ($order->status) {
                    'shipping'  => 1,
                    'completed' => 2,
                    default     => 0,
                };
            @endphp
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; text-align: center;">
                @foreach($steps as $idx => $step)
                    @php
                        $isDone = $idx < $currentIndex || ($order->status === 'completed' && $idx === $currentIndex);
                        $isActive = $idx === $currentIndex && $order->status !== 'completed';
                        $circleBg = $isDone ? '#10b981' : ($isActive ? 'var(--color-primary, #792a16)' : '#e5e7eb');
                        $circleColor = ($isDone || $isActive) ? '#ffffff' : '#6b7280';
                    @endphp
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: {{ $circleBg }}; color: {{ $circleColor }}; display: flex; align-items: center; justify-content: center; font-size: 17px;">
                            <i class="bi {{ $step['icon'] }}"></i>
                        </div>
                        <span style="font-size: 13px; font-weight: {{ ($isDone || $isActive) ? '700' : '500' }}; color: {{ ($isDone || $isActive) ? 'var(--color-on-surface)' : '#6b7280' }};">
                            {{ $step['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
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

    @if(session('warning'))
        <div style="background: #fffbeb; border-left: 4px solid #d97706; color: #92400e; padding: 14px 18px; border-radius: 8px; font-size: 14px; margin-bottom: 20px;">
            {{ session('warning') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 30px; align-items: start;">
        
        <!-- Cột trái: Chi tiết sản phẩm trong đơn -->
        <div style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 24px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
            <h2 style="font-size: 17px; font-weight: 700; color: var(--color-on-surface); margin: 0 0 16px;">
                Danh sách sản phẩm đã đặt
            </h2>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--color-outline-variant, #f0eae1); text-align: left; color: var(--color-on-surface-variant); font-size: 12px;">
                            <th style="padding: 12px 10px;">SẢN PHẨM</th>
                            <th style="padding: 12px 10px; text-align: right;">ĐƠN GIÁ</th>
                            <th style="padding: 12px 10px; text-align: center;">SỐ LƯỢNG</th>
                            <th style="padding: 12px 10px; text-align: right;">THÀNH TIỀN</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            @php
                                $img = $item->product->image ?? null;
                                $imgSrc = $img 
                                    ? (str_starts_with($img, 'http') ? $img : asset(ltrim($img, '/')))
                                    : asset('assets/images/placeholder.svg');
                            @endphp
                            <tr style="border-bottom: 1px solid var(--color-outline-variant, #f0eae1);">
                                <td style="padding: 14px 10px;">
                                    <div style="display: flex; gap: 14px; align-items: center;">
                                        @if($item->product)
                                            <a href="{{ route('products.show', $item->product_id) }}" style="flex-shrink: 0;">
                                                <img src="{{ $imgSrc }}" alt="{{ $item->product->name }}" 
                                                     style="width: 54px; height: 54px; object-fit: cover; border-radius: 8px; border: 1px solid #f0eae1;"
                                                     onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
                                            </a>
                                            <div>
                                                <a href="{{ route('products.show', $item->product_id) }}" 
                                                   style="font-weight: 700; color: var(--color-on-surface); text-decoration: none; line-height: 1.4; display: block; margin-bottom: 4px;">
                                                    {{ $item->product->name }}
                                                </a>
                                                <span style="font-size: 12px; color: var(--color-on-surface-variant);">
                                                    Mã: {{ $item->product->code }}
                                                </span>
                                            </div>
                                        @else
                                            <div style="font-weight: 600; color: var(--color-on-surface);">Sản phẩm không còn tồn tại</div>
                                        @endif
                                    </div>
                                </td>
                                <td style="padding: 14px 10px; text-align: right; color: var(--color-on-surface); font-weight: 600;">
                                    {{ number_format($item->unit_price, 0, ',', '.') }}đ
                                </td>
                                <td style="padding: 14px 10px; text-align: center; font-weight: 600;">
                                    {{ $item->quantity }}
                                </td>
                                <td style="padding: 14px 10px; text-align: right; font-weight: 700; color: var(--color-on-surface);">
                                    {{ number_format($item->total_price, 0, ',', '.') }}đ
                                </td>
                            </tr>

                            @if($order->status === 'completed')
                                <tr style="border-bottom: 1px solid var(--color-outline-variant, #f0eae1); background: #fafaf9;">
                                    <td colspan="4" style="padding: 12px 14px;">
                                        @if($item->review)
                                            <div style="font-size: 13px; color: var(--color-on-surface);">
                                                <div style="font-weight: 700; color: #059669; margin-bottom: 2px;">
                                                    Đã đánh giá: {{ $item->review->rating }}/5 sao
                                                </div>
                                                @if($item->review->comment)
                                                    <div style="color: var(--color-on-surface-variant); font-style: italic;">
                                                        "{{ $item->review->comment }}"
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                                                <span style="font-size: 13px; font-weight: 600; color: var(--color-on-surface-variant);">
                                                    Bạn đã nhận được sản phẩm này? Hãy để lại cảm nhận của bạn:
                                                </span>
                                                <button type="button" 
                                                        onclick="const form = document.getElementById('review-form-{{ $item->id }}'); form.style.display = form.style.display === 'none' ? 'block' : 'none';"
                                                        style="padding: 6px 14px; background: var(--color-primary, #e76f51); color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                                    <i class="bi bi-star"></i>
                                                    <span>Đánh giá sản phẩm</span>
                                                </button>
                                            </div>
                                            
                                            <form id="review-form-{{ $item->id }}" action="{{ route('reviews.store') }}" method="POST" 
                                                  style="display: none; margin-top: 14px; padding-top: 12px; border-top: 1px dashed #e5e7eb;">
                                                @csrf
                                                <input type="hidden" name="order_item_id" value="{{ $item->id }}">

                                                <div style="margin-bottom: 10px;">
                                                    <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px; color: var(--color-on-surface);">
                                                        Mức độ hài lòng:
                                                    </label>
                                                    <select name="rating" required 
                                                            style="padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; background: #fff;">
                                                        <option value="5" selected>5/5 sao - Rất hài lòng</option>
                                                        <option value="4">4/5 sao - Hài lòng</option>
                                                        <option value="3">3/5 sao - Bình thường</option>
                                                        <option value="2">2/5 sao - Chưa hài lòng</option>
                                                        <option value="1">1/5 sao - Rất tệ</option>
                                                    </select>
                                                </div>

                                                <div style="margin-bottom: 10px;">
                                                    <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px; color: var(--color-on-surface);">
                                                        Nhận xét chi tiết (tùy chọn):
                                                    </label>
                                                    <textarea name="comment" rows="2" placeholder="Chất lượng sản phẩm, độ hoàn thiện, đóng gói..."
                                                              style="width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; box-sizing: border-box; font-family: inherit;"></textarea>
                                                </div>

                                                <div style="text-align: right;">
                                                    <button type="submit" 
                                                            style="padding: 6px 16px; background: #374151; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                                        Gửi đánh giá
                                                    </button>
                                                </div>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--color-outline-variant, #f0eae1);">
                <a href="{{ route('orders.index') }}" style="color: var(--color-primary); font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-arrow-left"></i>
                    <span>Quay lại danh sách đơn hàng</span>
                </a>
            </div>
        </div>

        <!-- Cột phải: Thông tin giao hàng & Tóm tắt chi phí -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- Thẻ 1: Thông tin người nhận -->
            <div style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 22px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--color-on-surface); margin: 0 0 14px; border-bottom: 1px solid var(--color-outline-variant, #f0eae1); padding-bottom: 8px;">
                    Thông tin nhận hàng
                </h3>
                <div style="font-size: 13px; line-height: 1.8; color: var(--color-on-surface);">
                    <div><strong>Người nhận:</strong> {{ $order->shipping_name }}</div>
                    <div><strong>Số điện thoại:</strong> {{ $order->shipping_phone }}</div>
                    <div><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</div>
                    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed var(--color-outline-variant, #f0eae1);">
                        <strong>Phương thức thanh toán:</strong><br>
                        <span>{{ $order->paymentMethodLabel() }}</span>
                        <div style="margin-top: 4px;">
                            @if($order->isPaid())
                                <span style="display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 12px; font-weight: 700; background: #d1fae5; color: #065f46;">
                                    <i class="bi bi-patch-check-fill"></i> {{ $order->paymentStatusLabel() }}
                                    @if($order->paid_at) ({{ $order->paid_at->format('d/m/Y H:i') }}) @endif
                                </span>
                            @elseif($order->payment_method === 'qr')
                                <span style="display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 12px; font-weight: 700; background: #eef2ff; color: #4338ca;">
                                    <i class="bi bi-clock-history"></i> {{ $order->paymentStatusLabel() }}
                                </span>
                            @else
                                <span style="font-size: 12px; color: var(--color-on-surface-variant);">
                                    ({{ $order->paymentStatusLabel() }})
                                </span>
                            @endif
                        </div>

                        @if($order->payment_method === 'qr' && $order->payment_gateway === 'payos' && !$order->isPaid() && $order->status === 'pending')
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px;">
                                @if($order->checkout_url)
                                    <a href="{{ $order->checkout_url }}"
                                       style="padding: 8px 12px; border-radius: 8px; background: #4338ca; color: #fff; text-decoration: none; font-size: 12.5px; font-weight: 700; text-align: center;">
                                        <i class="bi bi-qr-code-scan"></i> Thanh toán ngay qua PayOS
                                    </a>
                                @endif
                                <form action="{{ route('checkout.payos.verify', $order->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit"
                                            style="width: 100%; padding: 7px 12px; border-radius: 8px; border: 1px solid #4338ca; background: #fff; color: #4338ca; font-size: 12.5px; font-weight: 700; cursor: pointer;">
                                        <i class="bi bi-arrow-clockwise"></i> Kiểm tra đã thanh toán
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Thẻ 2: Tóm tắt thanh toán -->
            @php
                $subtotalCalc = max(0, ($order->total_amount ?? 0) - ($order->shipping_fee ?? 0) + ($order->discount_amount ?? 0));
            @endphp
            <div style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 22px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--color-on-surface); margin: 0 0 14px; border-bottom: 1px solid var(--color-outline-variant, #f0eae1); padding-bottom: 8px;">
                    Tổng kết thanh toán
                </h3>
                <div style="font-size: 13px; display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; color: var(--color-on-surface-variant);">
                        <span>Tạm tính tiền hàng:</span>
                        <strong style="color: var(--color-on-surface);">
                            {{ number_format($subtotalCalc, 0, ',', '.') }}đ
                        </strong>
                    </div>
                    @if(($order->discount_amount ?? 0) > 0)
                        <div style="display: flex; justify-content: space-between; color: #059669;">
                            <span>Giảm giá (đơn cũ):</span>
                            <strong>-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</strong>
                        </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; color: var(--color-on-surface-variant);">
                        <span>Phí giao hàng:</span>
                        @if(($order->shipping_fee ?? 0) > 0)
                            <strong style="color: var(--color-on-surface);">{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</strong>
                        @else
                            <span style="color: #10b981; font-weight: 600;">Miễn phí</span>
                        @endif
                    </div>

                    <hr style="border: none; border-top: 1px solid var(--color-outline-variant, #f0eae1); margin: 4px 0;">

                    <!-- Tổng thanh toán = Tạm tính + Phí ship -->
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <span style="font-weight: 700; font-size: 15px; color: var(--color-on-surface);">Tổng thanh toán:</span>
                        <strong style="font-size: 18px; font-weight: 800; color: var(--color-primary, #e76f51);">
                            {{ number_format($order->total_amount, 0, ',', '.') }}đ
                        </strong>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- MODAL XÁC NHẬN HỦY ĐƠN HÀNG -->
<div class="modal-overlay" id="cancelOrderModal">
    <div class="modal-box">
        <div class="modal-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <h3 class="modal-title">Hủy đơn hàng?</h3>
        <p class="modal-desc">Bạn có chắc chắn muốn hủy đơn hàng này không? Hành động này không thể hoàn tác.</p>
        <div class="modal-actions">
            <button class="btn-cancel-modal" onclick="closeCancelModal()">Suy nghĩ lại</button>
            <button class="btn-confirm-modal" onclick="submitCancelOrder()">Đồng ý hủy</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showCancelOrderModal() {
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