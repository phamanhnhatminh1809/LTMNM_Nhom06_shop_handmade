@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng #' . $order->code)

@php $activeMenu = 'orders'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-receipt"></i>
        <span>Chi tiết đơn hàng <span class="mono-code" style="margin-left: 6px; font-size: 14px;">#{{ $order->code }}</span></span>
    </div>
    <div class="top-bar-right">
        <a href="{{ route('admin.orders.index') }}" class="btn-secondary-outline">
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại danh sách đơn</span>
        </a>
    </div>
</div>

<!-- Flash Notifications -->
@if(session('success'))
    <div class="admin-alert success">
        <div class="admin-alert-content">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="admin-alert error">
        <div class="admin-alert-content">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ session('error') }}</span>
        </div>
    </div>
@endif

<!-- Thanh Tiến Trình Trạng Thái Đơn Hàng (Stepper) & Nút Duyệt Nhanh -->
<div class="order-stepper-card">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 22px; padding-bottom: 16px; border-bottom: 1px solid #f4eae6;">
        <div>
            <div style="font-size: 15px; font-weight: 800; color: var(--color-on-surface);">
                Trạng thái xử lý đơn hàng
            </div>
            <div style="font-size: 12.5px; color: var(--color-outline); margin-top: 2px;">
                Đặt lúc {{ $order->created_at->format('H:i — d/m/Y') }}
                @if($order->updated_at)
                    • Cập nhật lần cuối: {{ $order->updated_at->format('H:i — d/m/Y') }}
                @endif
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            @if($order->status === 'pending')
                @if($order->payment_method === 'qr' && !$order->isPaid())
                    <span style="font-size: 12px; font-weight: 700; color: #92400e; background: #fef3c7; padding: 6px 12px; border-radius: 8px; border: 1px solid #fde68a;">
                        <i class="bi bi-exclamation-circle"></i> Đơn QR chưa ghi nhận PAID từ PayOS
                    </span>
                @endif
                <form action="{{ route('admin.orders.updateStatus', $order) }}"
                      method="POST"
                      data-confirm="Xác nhận duyệt đơn hàng #{{ $order->code }} và chuyển sang trạng thái Đang giao?"
                      data-confirm-title="Duyệt và giao đơn hàng?"
                      data-confirm-label="Duyệt và giao hàng"
                      data-confirm-icon="bi-truck"
                      data-confirm-variant="primary">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="shipping">
                    <button type="submit" class="btn-modal-save">
                        <i class="bi bi-truck"></i>
                        <span>Duyệt và giao hàng</span>
                    </button>
                </form>

                <form action="{{ route('admin.orders.updateStatus', $order) }}"
                      method="POST"
                      data-confirm="Xác nhận hủy đơn hàng #{{ $order->code }}? Hành động này sẽ hoàn lại tồn kho sản phẩm."
                      data-confirm-title="Hủy đơn hàng?"
                      data-confirm-label="Đồng ý hủy"
                      data-confirm-icon="bi-x-octagon-fill"
                      data-confirm-variant="danger">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn-inline-action danger" style="padding: 9px 16px;">
                        <i class="bi bi-x-circle"></i>
                        <span>Hủy đơn hàng</span>
                    </button>
                </form>
            @elseif($order->status === 'shipping')
                <form action="{{ route('admin.orders.updateStatus', $order) }}"
                      method="POST"
                      data-confirm="Xác nhận đơn hàng #{{ $order->code }} đã giao thành công?"
                      data-confirm-title="Hoàn thành đơn hàng?"
                      data-confirm-label="Xác nhận hoàn thành"
                      data-confirm-icon="bi-check2-circle"
                      data-confirm-variant="success">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn-inline-action success" style="padding: 10px 18px; font-size: 13px;">
                        <i class="bi bi-check2-circle"></i>
                        <span>Đánh dấu Hoàn thành</span>
                    </button>
                </form>
            @elseif($order->status === 'completed')
                <span class="status-badge completed" style="padding: 8px 14px; font-size: 13px;">
                    <i class="bi bi-check-circle-fill"></i> Đơn hàng đã hoàn thành
                </span>
            @elseif($order->status === 'cancelled')
                <span class="status-badge cancelled" style="padding: 8px 14px; font-size: 13px;">
                    <i class="bi bi-x-circle-fill"></i> Đơn hàng đã bị hủy
                </span>
            @endif
        </div>
    </div>

    @php
        $step1Class = match ($order->status) {
            'pending' => 'active',
            'shipping', 'completed' => 'done',
            'cancelled' => 'done',
            default => '',
        };
        $step2Class = match ($order->status) {
            'shipping' => 'active',
            'completed' => 'done',
            default => '',
        };
        $step3Class = match ($order->status) {
            'completed' => 'done',
            default => '',
        };
    @endphp

    @if($order->status === 'cancelled')
        <div class="order-stepper">
            <div class="step-item done">
                <div class="step-circle"><i class="bi bi-clock"></i></div>
                <span class="step-label">Tiếp nhận đơn</span>
            </div>
            <div class="step-connector"></div>
            <div class="step-item cancelled">
                <div class="step-circle"><i class="bi bi-x-lg"></i></div>
                <span class="step-label" style="color: var(--color-danger);">Đã hủy đơn hàng</span>
            </div>
        </div>
    @else
        <div class="order-stepper">
            <div class="step-item {{ $step1Class }}">
                <div class="step-circle"><i class="bi bi-clock-history"></i></div>
                <span class="step-label">1. Chờ xác nhận</span>
            </div>
            <div class="step-connector {{ in_array($order->status, ['shipping', 'completed']) ? 'done' : '' }}"></div>
            <div class="step-item {{ $step2Class }}">
                <div class="step-circle"><i class="bi bi-truck"></i></div>
                <span class="step-label">2. Đang giao hàng</span>
            </div>
            <div class="step-connector {{ $order->status === 'completed' ? 'done' : '' }}"></div>
            <div class="step-item {{ $step3Class }}">
                <div class="step-circle"><i class="bi bi-check2-circle"></i></div>
                <span class="step-label">3. Hoàn thành</span>
            </div>
        </div>
    @endif
</div>

<div class="admin-detail-grid">
    <!-- Cột trái: Danh sách sản phẩm trong đơn -->
    <div class="table-card" style="margin-bottom: 0;">
        <div class="table-header-flex">
            <div>
                <h3 style="font-size: 15px; font-weight: 800; color: var(--color-on-surface); margin-bottom: 3px;">
                    Danh sách sản phẩm đặt mua ({{ $order->items->count() }})
                </h3>
                <p style="font-size: 12px; color: var(--color-outline);">Chi tiết số lượng và đơn giá tại thời điểm đặt hàng</p>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <table class="admin-data-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Mã SKU</th>
                        <th>Đơn giá</th>
                        <th style="text-align: center;">SL</th>
                        <th style="text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        @php
                            $prod = $item->product;
                            $prodImg = ($prod && !empty($prod->image))
                                ? (str_starts_with($prod->image, 'http') ? $prod->image : asset(ltrim($prod->image, '/')))
                                : asset('assets/images/placeholder.svg');
                        @endphp
                        <tr>
                            <td>
                                <div class="customer-cell">
                                    <img src="{{ $prodImg }}" class="stock-thumb" alt="{{ $prod->name ?? 'SP' }}" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
                                    <div class="customer-info-box">
                                        @if($prod)
                                            <a href="{{ route('admin.products.show', $prod) }}"
                                               class="c-name"
                                               style="font-weight: 700; text-decoration: none; color: var(--color-on-surface);">
                                                {{ $prod->name }}
                                            </a>
                                        @else
                                            <div class="c-name" style="color: var(--color-outline); font-style: italic;">
                                                (Sản phẩm đã ngừng kinh doanh)
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="mono-code">{{ $prod?->code ?? '—' }}</span>
                            </td>
                            <td>{{ number_format($item->unit_price, 0, ',', '.') }}đ</td>
                            <td style="text-align: center; font-weight: 700;">x{{ $item->quantity }}</td>
                            <td style="text-align: right;">
                                <span class="total-amount-text">{{ number_format($item->total_price, 0, ',', '.') }}đ</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @if(($order->shipping_fee ?? 0) > 0)
                        <tr style="background-color: #fffdfa;">
                            <td colspan="4" style="text-align: right; font-weight: 600; font-size: 13px; padding: 10px 16px; color: var(--color-outline);">
                                Phí vận chuyển:
                            </td>
                            <td style="text-align: right; font-weight: 700; font-size: 13.5px; padding: 10px 16px;">
                                {{ number_format($order->shipping_fee, 0, ',', '.') }}đ
                            </td>
                        </tr>
                    @endif
                    @if(($order->discount_amount ?? 0) > 0)
                        <tr style="background-color: #fffdfa;">
                            <td colspan="4" style="text-align: right; font-weight: 600; font-size: 13px; padding: 10px 16px; color: #059669;">
                                Giảm giá (đơn cũ):
                            </td>
                            <td style="text-align: right; font-weight: 700; font-size: 13.5px; padding: 10px 16px; color: #059669;">
                                -{{ number_format($order->discount_amount, 0, ',', '.') }}đ
                            </td>
                        </tr>
                    @endif
                    <tr style="background-color: #fff8f6;">
                        <td colspan="4" style="text-align: right; font-weight: 800; font-size: 14px; padding: 16px;">
                            Tổng cộng thanh toán ({{ $order->paymentMethodLabel() }}):
                        </td>
                        <td style="text-align: right; font-weight: 800; font-size: 16px; color: var(--color-primary); padding: 16px;">
                            {{ number_format($order->total_amount, 0, ',', '.') }}đ
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Cột phải: Thông tin người nhận & Khách hàng -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-geo-alt"></i>
                    <span>Thông tin nhận hàng</span>
                </h3>
            </div>
            <div class="admin-card-body">
                <div class="info-kv-list">
                    <div class="info-kv-row">
                        <span class="info-kv-label">Người nhận</span>
                        <span class="info-kv-value">{{ $order->shipping_name }}</span>
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">Số điện thoại</span>
                        <span class="info-kv-value">
                            <span class="mono-code">{{ $order->shipping_phone }}</span>
                        </span>
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">Địa chỉ giao hàng</span>
                        <span class="info-kv-value" style="max-width: 240px;">{{ $order->shipping_address }}</span>
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">Phương thức TT</span>
                        <span class="info-kv-value">{{ $order->paymentMethodLabel() }}</span>
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">Trạng thái TT</span>
                        <span class="info-kv-value">
                            @if($order->isPaid())
                                <span style="display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 700; background: #dcfce7; color: #166534;">
                                    {{ $order->paymentStatusLabel() }}
                                </span>
                                @if($order->paid_at)
                                    <div style="font-size: 11px; color: var(--color-outline); margin-top: 2px;">
                                        {{ $order->paid_at->format('H:i d/m/Y') }}
                                    </div>
                                @endif
                            @elseif($order->payment_status === \App\Models\Order::PAYMENT_STATUS_CANCELLED)
                                <span style="display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 700; background: #fee2e2; color: #991b1b;">
                                    {{ $order->paymentStatusLabel() }}
                                </span>
                            @else
                                <span style="display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 700; background: #fef3c7; color: #92400e;">
                                    {{ $order->paymentStatusLabel() }}
                                </span>
                            @endif
                        </span>
                    </div>
                    @if($order->payment_reference)
                        <div class="info-kv-row">
                            <span class="info-kv-label">Mã GD PayOS</span>
                            <span class="info-kv-value"><span class="mono-code">{{ $order->payment_reference }}</span></span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-person-badge"></i>
                    <span>Tài khoản đặt hàng</span>
                </h3>
            </div>
            <div class="admin-card-body">
                @if($order->user)
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                        <div class="customer-avatar-abbr" style="width: 42px; height: 42px; font-size: 15px;">
                            {{ mb_strtoupper(mb_substr($order->user->full_name, 0, 2)) }}
                        </div>
                        <div>
                            <div style="font-weight: 800; color: var(--color-on-surface);">
                                {{ $order->user->full_name }}
                            </div>
                            <div style="font-size: 12.5px; color: var(--color-outline);">
                                {{ $order->user->email }}
                            </div>
                        </div>
                    </div>

                    @if(Auth::user()?->isAdmin() && $order->user->role_id === 2)
                        <a href="{{ route('admin.customers.show', $order->user) }}"
                           class="btn-secondary-outline"
                           style="width: 100%; justify-content: center;">
                            <i class="bi bi-person-lines-fill"></i>
                            <span>Xem hồ sơ khách hàng</span>
                        </a>
                    @endif
                @else
                    <div style="font-size: 13.5px; color: var(--color-outline);">
                        Đơn hàng được đặt bởi khách vãng lai.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
