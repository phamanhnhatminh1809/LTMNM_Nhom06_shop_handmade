@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@php $activeMenu = 'orders'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-receipt-cutoff"></i>
        <span>Quản lý đơn hàng</span>
    </div>
    <div class="top-bar-right">
        @include('admin.partials.notification-bell')
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
<div class="product-filter-bar is-inline">
    <div class="status-tabs-bar">
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

    <div class="admin-toolbar-search" style="flex: 0 1 300px;">
        <div class="search-input-box">
            <i class="bi bi-search"></i>
            <input type="text" id="orderQuickSearch" placeholder="Lọc nhanh mã ĐH, tên, SĐT...">
        </div>
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
                            <x-table-actions>
                                <x-table-action type="view"
                                                :href="route('admin.orders.show', $order)"
                                                label="Xem chi tiết đơn hàng"
                                                :tooltip-align="in_array($order->status, ['completed', 'cancelled']) ? 'end' : 'auto'" />

                                @if($order->status === 'pending')
                                    <form action="{{ route('admin.orders.updateStatus', $order) }}"
                                          method="POST"
                                          style="display: inline;"
                                          data-confirm="Xác nhận duyệt và chuyển sang Đang giao đơn #{{ $order->code }}?"
                                          data-confirm-title="Duyệt và giao đơn hàng?"
                                          data-confirm-label="Duyệt và giao hàng"
                                          data-confirm-icon="bi-truck"
                                          data-confirm-variant="primary">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="shipping">
                                        <button type="submit" class="btn-inline-action primary" title="Duyệt và giao hàng">
                                            <i class="bi bi-truck"></i>
                                            <span>Duyệt và giao hàng</span>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.orders.updateStatus', $order) }}"
                                          method="POST"
                                          class="table-action-form"
                                          data-confirm="Xác nhận hủy đơn hàng #{{ $order->code }}? Hành động này sẽ hoàn lại tồn kho sản phẩm."
                                          data-confirm-title="Hủy đơn hàng?"
                                          data-confirm-label="Đồng ý hủy"
                                          data-confirm-icon="bi-x-octagon-fill"
                                          data-confirm-variant="danger">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit"
                                                class="table-action-btn is-danger is-tooltip-end"
                                                data-tooltip="Hủy đơn hàng"
                                                aria-label="Hủy đơn hàng">
                                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                @elseif($order->status === 'shipping')
                                    <form action="{{ route('admin.orders.updateStatus', $order) }}"
                                          method="POST"
                                          style="display: inline;"
                                          data-confirm="Xác nhận đơn hàng #{{ $order->code }} đã giao thành công tới khách hàng?"
                                          data-confirm-title="Hoàn thành đơn hàng?"
                                          data-confirm-label="Xác nhận hoàn thành"
                                          data-confirm-icon="bi-check2-circle"
                                          data-confirm-variant="success">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn-inline-action success" title="Đánh dấu Hoàn thành">
                                            <i class="bi bi-check2-circle"></i>
                                            <span>Hoàn thành</span>
                                        </button>
                                    </form>
                                @endif
                            </x-table-actions>
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
window.AdminApp.mount(() => {
    const searchInput = document.getElementById('orderQuickSearch');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('#ordersDataTable tbody .order-row').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }
});
</script>
@endpush
