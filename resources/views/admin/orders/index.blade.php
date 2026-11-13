@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@php $activeMenu = 'orders'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-receipt-cutoff"></i>
        <span>QUẢN LÝ ĐƠN HÀNG</span>
    </div>
    <div class="top-bar-right">
        <button class="btn-icon-bell" title="Thông báo hệ thống">
            <i class="bi bi-bell"></i>
            <span class="notification-dot"></span>
        </button>
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

<!-- Hàng 5 Thẻ Thống kê Trạng thái Đơn hàng -->
<div class="metrics-row">
    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-receipt"></i>
        </div>
        <div class="metric-label">Tổng số đơn hàng</div>
        <div class="metric-value">{{ $statusCounts['all'] ?? 0 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div class="metric-label">Chờ xác nhận</div>
        <div class="metric-value">{{ $statusCounts['pending'] ?? 0 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-truck"></i>
        </div>
        <div class="metric-label">Đang giao hàng</div>
        <div class="metric-value">{{ $statusCounts['shipping'] ?? 0 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-check2-circle"></i>
        </div>
        <div class="metric-label">Đã hoàn thành</div>
        <div class="metric-value">{{ $statusCounts['completed'] ?? 0 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-x-octagon"></i>
        </div>
        <div class="metric-label">Đơn đã hủy</div>
        <div class="metric-value">{{ $statusCounts['cancelled'] ?? 0 }}</div>
    </div>
</div>

<!-- Thanh Tabs Lọc Trạng thái & Ô tìm kiếm nhanh -->
<div class="product-filter-bar" style="flex-wrap: wrap; gap: 12px;">
    <div class="status-tabs-bar" style="margin-bottom: 0;">
        <a href="{{ route('admin.orders.index') }}"
           class="status-tab-pill {{ $status === '' ? 'active' : '' }}">
            <span>Tất cả</span>
            <span class="tab-count-badge">{{ $statusCounts['all'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
           class="status-tab-pill {{ $status === 'pending' ? 'active' : '' }}">
            <i class="bi bi-clock"></i>
            <span>Chờ xác nhận</span>
            <span class="tab-count-badge">{{ $statusCounts['pending'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'shipping']) }}"
           class="status-tab-pill {{ $status === 'shipping' ? 'active' : '' }}">
            <i class="bi bi-truck"></i>
            <span>Đang giao</span>
            <span class="tab-count-badge">{{ $statusCounts['shipping'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}"
           class="status-tab-pill {{ $status === 'completed' ? 'active' : '' }}">
            <i class="bi bi-check-circle"></i>
            <span>Hoàn thành</span>
            <span class="tab-count-badge">{{ $statusCounts['completed'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}"
           class="status-tab-pill {{ $status === 'cancelled' ? 'active' : '' }}">
            <i class="bi bi-x-circle"></i>
            <span>Đã hủy</span>
            <span class="tab-count-badge">{{ $statusCounts['cancelled'] ?? 0 }}</span>
        </a>
    </div>

    <div class="search-input-box" style="max-width: 300px; margin-left: auto;">
        <i class="bi bi-search"></i>
        <input type="text" id="orderQuickSearch" placeholder="Lọc nhanh mã ĐH, tên, SĐT...">
    </div>
</div>

<!-- Bảng Danh sách Đơn hàng -->
<div class="table-card">
    <div class="table-header-flex">
        <div>
            <h3 style="font-size: 15px; font-weight: 800; color: var(--color-on-surface); margin-bottom: 3px;">
                Danh sách đơn hàng ({{ $orders->total() }})
            </h3>
            <p style="font-size: 12px; color: var(--color-outline);">
                Quy trình xử lý một chiều: Chờ xác nhận <i class="bi bi-arrow-right"></i> Đang giao <i class="bi bi-arrow-right"></i> Hoàn thành (hoặc Hủy đơn khi đang chờ xác nhận)
            </p>
        </div>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-data-table" id="ordersDataTable">
            <thead>
                <tr>
                    <th>Mã ĐH</th>
                    <th>Khách hàng nhận</th>
                    <th>Địa chỉ giao hàng</th>
                    <th>Số SP</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th style="text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="order-row">
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" style="text-decoration: none;">
                                <span class="mono-code">#{{ $order->code }}</span>
                            </a>
                        </td>
                        <td>
                            <div class="customer-cell">
                                <div class="customer-avatar-abbr">
                                    {{ mb_strtoupper(mb_substr($order->shipping_name, 0, 2)) }}
                                </div>
                                <div class="customer-info-box">
                                    <div class="c-name">{{ $order->shipping_name }}</div>
                                    <div class="c-email" style="font-family: var(--font-mono); font-size: 12px;">
                                        {{ $order->shipping_phone }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="max-width: 240px;">
                            <div style="font-size: 13px; color: var(--color-on-surface-variant); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                 title="{{ $order->shipping_address }}">
                                {{ $order->shipping_address }}
                            </div>
                        </td>
                        <td>
                            <span style="font-weight: 700; color: var(--color-on-surface);">
                                {{ $order->items->count() }} SP
                            </span>
                        </td>
                        <td>
                            <span class="total-amount-text">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                        </td>
                        <td>
                            @if($order->status === 'completed')
                                <span class="status-badge completed">
                                    <i class="bi bi-check-circle"></i> Hoàn thành
                                </span>
                            @elseif($order->status === 'shipping')
                                <span class="status-badge shipping">
                                    <i class="bi bi-truck"></i> Đang giao
                                </span>
                            @elseif($order->status === 'cancelled')
                                <span class="status-badge cancelled">
                                    <i class="bi bi-x-circle"></i> Đã hủy
                                </span>
                            @else
                                <span class="status-badge pending">
                                    <i class="bi bi-clock"></i> Chờ xác nhận
                                </span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 13px; color: var(--color-on-surface-variant);">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: inline-flex; align-items: center; gap: 6px; flex-wrap: nowrap;">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                   class="btn-action-view"
                                   title="Xem chi tiết đơn hàng">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @if($order->status === 'pending')
                                    <form action="{{ route('admin.orders.updateStatus', $order) }}"
                                          method="POST"
                                          style="display: inline;"
                                          onsubmit="return confirm('Xác nhận duyệt và chuyển sang Đang giao đơn #{{ $order->code }}?')">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="shipping">
                                        <button type="submit" class="btn-inline-action primary" title="Duyệt & Giao hàng">
                                            <i class="bi bi-truck"></i>
                                            <span>Duyệt & Giao</span>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.orders.updateStatus', $order) }}"
                                          method="POST"
                                          style="display: inline;"
                                          onsubmit="return confirm('Xác nhận hủy đơn hàng #{{ $order->code }}?')">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn-action-view danger" title="Hủy đơn hàng">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @elseif($order->status === 'shipping')
                                    <form action="{{ route('admin.orders.updateStatus', $order) }}"
                                          method="POST"
                                          style="display: inline;"
                                          onsubmit="return confirm('Xác nhận đơn hàng #{{ $order->code }} đã giao thành công?')">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn-inline-action success" title="Đánh dấu Hoàn thành">
                                            <i class="bi bi-check2-circle"></i>
                                            <span>Hoàn thành</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 32px; color: var(--color-outline);">
                            Không có đơn hàng nào trong trạng thái này.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="padding: 16px 24px;">
        {{ $orders->withQueryString()->links('admin.partials.pagination') }}
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('orderQuickSearch');
    const rows = document.querySelectorAll('#ordersDataTable tbody .order-row');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }
});
</script>
@endpush
