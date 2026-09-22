@extends('layouts.admin')

@section('title', 'Tổng quan hệ thống')

@php
    $activeMenu = 'dashboard';
@endphp

@section('content')
<!-- Top bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-grid-1x2"></i>
        <span>TỔNG QUAN HỆ THỐNG</span>
    </div>
    <div class="top-bar-right">
        <button class="btn-icon-bell" title="Thông báo hệ thống">
            <i class="bi bi-bell"></i>
            <span class="notification-dot"></span>
        </button>
    </div>
</div>

<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="welcome-text">
        <h2 class="welcome-title">Chào buổi sáng, {{ Auth::user()->full_name ?? 'Đăng Khoa' }}</h2>
        <p class="welcome-sub">Tổng quan hoạt động của shop trong 24 giờ qua. Hệ thống đang hoạt động ổn định.</p>
    </div>
    <div class="welcome-actions">
        <!-- Component Dropdown chọn khoảng thời gian -->
        @include('admin.partials.date-filter-modal')

        <button type="button" class="btn-export">
            <i class="bi bi-download"></i>
            <span>Xuất báo cáo</span>
        </button>
    </div>
</div>

<!-- Hàng 5 Thẻ Chỉ số -->
<div class="metrics-row">
    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-cash-stack"></i>
        </div>
        <div class="metric-label">Doanh thu hôm nay</div>
        <div class="metric-value">{{ number_format($revenueToday ?? 5200000, 0, ',', '.') }}đ</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-bag-check"></i>
        </div>
        <div class="metric-label">Đơn hàng mới</div>
        <div class="metric-value">{{ $newOrdersCount ?? 18 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="metric-label">Đang xử lý</div>
        <div class="metric-value">{{ $processingOrdersCount ?? 45 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-box-seam"></i>
        </div>
        <div class="metric-label">Sản phẩm sắp hết</div>
        <div class="metric-value">{{ $lowStockCount ?? 5 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-person-plus"></i>
        </div>
        <div class="metric-label">Khách hàng mới</div>
        <div class="metric-value">{{ $newCustomersCount ?? 12 }}</div>
    </div>
</div>

<!-- Khu vực Biểu đồ & Cảnh báo tồn kho -->
<div class="analytics-grid">
    <!-- Cột trái: Biểu đồ đường Doanh thu -->
    <div class="dashboard-card">
        <div class="card-header-flex">
            <div>
                <h3>Doanh thu theo thời gian</h3>
                <p>7 ngày gần nhất</p>
            </div>
            <div class="period-toggle-group">
                <button type="button" class="btn-period active" data-period="week">Tuần</button>
                <button type="button" class="btn-period" data-period="month">Tháng</button>
            </div>
        </div>
        <div class="chart-wrapper">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Cột phải: Biểu đồ tròn Danh mục & Sắp hết hàng -->
    <div class="right-column-stack">
        <!-- Doanh thu theo danh mục -->
        <div class="dashboard-card">
            <div class="card-header-flex" style="margin-bottom: 12px;">
                <h3>Doanh thu theo danh mục</h3>
            </div>
            <div class="donut-chart-container">
                <canvas id="categoryDonutChart"></canvas>
                <div class="donut-center-text">
                    <span class="num">{{ $totalProducts ?? 50 }}</span>
                    <span class="txt">Sản phẩm</span>
                </div>
            </div>
            <div class="category-legends">
                <div class="legend-item" title="Gấu bông len (45%)">
                    <span class="legend-dot" style="background-color: #792a16;"></span>
                    <span class="legend-name">Gấu...</span>
                    <span class="legend-percent">45%</span>
                </div>
                <div class="legend-item" title="Móc khóa len (30%)">
                    <span class="legend-dot" style="background-color: #c49b4d;"></span>
                    <span class="legend-name">Móc...</span>
                    <span class="legend-percent">30%</span>
                </div>
                <div class="legend-item" title="Bó hoa len (25%)">
                    <span class="legend-dot" style="background-color: #49624e;"></span>
                    <span class="legend-name">Bó hoa</span>
                    <span class="legend-percent">25%</span>
                </div>
            </div>
        </div>

        <!-- Card Sắp hết hàng -->
        <div class="card-low-stock">
            <div class="low-stock-title">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Sắp hết hàng</span>
                </div>
                <span class="stock-count-badge">{{ count($lowStockProducts) }}</span>
            </div>

            <div class="stock-items-list">
                @foreach ($lowStockProducts as $item)
                    @php
                        $itemImg = str_starts_with($item->image, 'http') ? $item->image : asset(ltrim($item->image, '/'));
                    @endphp
                    <div class="stock-item">
                        <img src="{{ $itemImg }}" class="stock-thumb" alt="{{ $item->name }}" onerror="this.src='{{ asset('images/logo.png') }}'">
                        <div class="stock-details">
                            <div class="stock-name">{{ $item->name }}</div>
                            <div class="stock-code">{{ $item->code }}</div>
                        </div>
                        <div class="stock-status-right">
                            <div class="stock-ratio">{{ $item->stock_quantity }} <span style="font-weight: 500; color: #89726d;">/10</span></div>
                            <span class="stock-tag {{ $item->stock_quantity <= 2 ? 'danger' : 'warning' }}">
                                {{ $item->stock_quantity <= 2 ? 'CẢNH BÁO' : 'THẤP' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <a href="{{ route('admin.products.index') }}" class="btn-view-inventory">
                XEM TẤT CẢ KHO <i class="bi bi-arrow-right" style="margin-left: 4px;"></i>
            </a>
        </div>
    </div>
</div>

<!-- Bảng Đơn hàng gần đây -->
<div class="table-card">
    <div class="table-header-flex">
        <div>
            <h3 style="font-size: 15px; font-weight: 800; color: var(--color-on-surface); margin-bottom: 3px;">Đơn hàng gần đây</h3>
            <p style="font-size: 12px; color: var(--color-outline);">Danh sách các đơn hàng mới cập nhật trong ngày</p>
        </div>
        <a href="#" class="btn-outline-link">Xem tất cả đơn</a>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>Mã ĐH</th>
                    <th>Khách hàng</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th style="text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentOrders as $order)
                    <tr>
                        <td><span class="order-code-text">#{{ $order->code }}</span></td>
                        <td>
                            <div class="customer-cell">
                                <div class="customer-avatar-abbr">
                                    {{ mb_strtoupper(mb_substr($order->shipping_name, 0, 2)) }}
                                </div>
                                <div class="customer-info-box">
                                    <div class="c-name">{{ $order->shipping_name }}</div>
                                    <div class="c-email">{{ $order->shipping_phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $order->created_at->format('d/m, H:i') }}</td>
                        <td><span class="total-amount-text">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span></td>
                        <td>
                            @if ($order->status === 'completed')
                                <span class="status-badge completed">
                                    <i class="bi bi-check-circle"></i> Hoàn thành
                                </span>
                            @elseif ($order->status === 'shipping')
                                <span class="status-badge shipping">
                                    <i class="bi bi-truck"></i> Đang giao
                                </span>
                            @else
                                <span class="status-badge pending">
                                    <i class="bi bi-clock"></i> Chờ xử lý
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-action-view" title="Xem chi tiết đơn">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">Chưa có đơn hàng nào</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin/dashboard.js') }}"></script>
@endpush
