@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng #' . $order->code)

@php $activeMenu = 'orders'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-receipt"></i>
        <span>CHI TIẾT ĐƠN HÀNG <span class="mono-code" style="margin-left: 6px; font-size: 14px;">#{{ $order->code }}</span></span>
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
                <form action="{{ route('admin.orders.updateStatus', $order) }}"
                      method="POST"
                      onsubmit="return confirm('Duyệt đơn hàng #{{ $order->code }} và chuyển sang trạng thái Đang giao?')">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="shipping">
                    <button type="submit" class="btn-modal-save">
                        <i class="bi bi-truck"></i>
                        <span>Duyệt & Giao hàng</span>
                    </button>
                </form>

                <form action="{{ route('admin.orders.updateStatus', $order) }}"
                      method="POST"
                      onsubmit="return confirm('Xác nhận hủy đơn hàng #{{ $order->code }}?')">
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
                      onsubmit="return confirm('Xác nhận đơn hàng #{{ $order->code }} đã giao thành công?')">
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
                                : asset('images/logo.png');
                        @endphp
                        <tr>
                            <td>
                                <div class="customer-cell">
                                    <img src="{{ $prodImg }}" class="stock-thumb" alt="{{ $prod->name ?? 'SP' }}" onerror="this.src='{{ asset('images/logo.png') }}'">
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
                    <tr style="background-color: #fff8f6;">
                        <td colspan="4" style="text-align: right; font-weight: 800; font-size: 14px; padding: 16px;">
                            TỔNG CỘNG THANH TOÁN (COD):
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
                        <span class="info-kv-value">Thanh toán khi nhận hàng (COD)</span>
                    </div>
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
