@extends('layouts.admin')

@section('title', 'Tổng quan hệ thống')

@php
    $activeMenu = 'dashboard';
    $chartPalette = ['#792a16', '#c49b4d', '#49624e', '#98412b', '#72594f', '#89726d'];
    $hasDynamicCategories = !empty($categoryShareChart['labels']) && count($categoryShareChart['labels']) > 0;
@endphp

@section('content')
<!-- Top bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-grid-1x2"></i>
        <span>Tổng quan hệ thống</span>
    </div>
    <div class="top-bar-right">
        @include('admin.partials.notification-bell')
    </div>
</div>

<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="welcome-text">
        <h2 class="welcome-title">Chào ngày mới, {{ Auth::user()->full_name ?? 'Quản trị viên' }}</h2>
        <p class="welcome-sub">Tổng quan hoạt động kinh doanh và vận hành của Handmade House. Hệ thống đang hoạt động ổn định.</p>
    </div>
    <div class="welcome-actions">
        <!-- Component Dropdown chọn khoảng thời gian -->
        @include('admin.partials.date-filter-modal')

        <a href="{{ route('admin.reports.revenue.export') }}"
           data-export-base="{{ route('admin.reports.revenue.export') }}"
           id="btnExportRevenueXlsx"
           class="btn-export"
           download
           data-no-pjax
           data-no-prefetch>
            <i class="bi bi-file-earmark-excel"></i>
            <span>Xuất báo cáo (.xlsx)</span>
        </a>
    </div>
</div>

<!-- Hàng 5 Thẻ Chỉ số -->
<div class="metrics-row">
    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-cash-stack"></i>
        </div>
        <div class="metric-label">Tổng doanh thu hợp lệ</div>
        <div class="metric-value">{{ number_format($revenueToday ?? 0, 0, ',', '.') }}đ</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-bag-check"></i>
        </div>
        <div class="metric-label">Đơn hàng chờ xác nhận</div>
        <div class="metric-value">{{ $newOrdersCount ?? 0 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="metric-label">Đơn đang giao</div>
        <div class="metric-value">{{ $processingOrdersCount ?? 0 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-box-seam"></i>
        </div>
        <div class="metric-label">Sản phẩm sắp hết</div>
        <div class="metric-value">{{ $lowStockCount ?? 0 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-person-plus"></i>
        </div>
        <div class="metric-label">Tổng khách hàng</div>
        <div class="metric-value">{{ $newCustomersCount ?? 0 }}</div>
    </div>
</div>

<!-- Khu vực Biểu đồ và cảnh báo tồn kho -->
<div class="analytics-grid">
    <!-- Cột trái: Biểu đồ đường Doanh thu -->
    <div class="dashboard-card">
        <div class="card-header-flex">
            <div>
                <h3>Doanh thu theo thời gian</h3>
                <p id="revenueChartSubLabel">7 ngày gần nhất (Đơn vị: triệu VNĐ)</p>
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

    <!-- Cột phải: Biểu đồ tròn danh mục và sắp hết hàng -->
    <div class="right-column-stack">
        <!-- Tỷ trọng bán ra theo danh mục -->
        <div class="dashboard-card">
            <div class="card-header-flex" style="margin-bottom: 12px;">
                <h3>Tỷ trọng theo danh mục</h3>
            </div>
            <div class="donut-chart-container">
                <canvas id="categoryDonutChart"></canvas>
                <div class="donut-center-text">
                    <span class="num">{{ $totalProducts ?? 0 }}</span>
                    <span class="txt">Sản phẩm</span>
                </div>
            </div>
            <div class="category-legends">
                @if($hasDynamicCategories)
                    @foreach($categoryShareChart['labels'] as $idx => $catLabel)
                        @php
                            $pct = $categoryShareChart['percentages'][$idx] ?? 0;
                            $dotColor = $chartPalette[$idx % count($chartPalette)];
                        @endphp
                        <div class="legend-item" title="{{ $catLabel }} ({{ $pct }}%)">
                            <span class="legend-dot" style="background-color: {{ $dotColor }};"></span>
                            <span class="legend-name">{{ \Illuminate\Support\Str::limit($catLabel, 10) }}</span>
                            <span class="legend-percent">{{ $pct }}%</span>
                        </div>
                    @endforeach
                @else
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
                @endif
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
                        $itemImg = !empty($item->image)
                            ? (str_starts_with($item->image, 'http') ? $item->image : asset(ltrim($item->image, '/')))
                            : asset('assets/images/placeholder.svg');
                    @endphp
                    <div class="stock-item">
                        <img src="{{ $itemImg }}" class="stock-thumb" alt="{{ $item->name }}" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
                        <div class="stock-details">
                            <div class="stock-name">{{ $item->name }}</div>
                            <div class="stock-code">{{ $item->code }}</div>
                        </div>
                        <div class="stock-status-right">
                            <div class="stock-ratio">{{ $item->stock_quantity }} <span style="font-weight: 500; color: #89726d;">/10</span></div>
                            <span class="stock-tag {{ $item->stock_quantity <= 2 ? 'danger' : 'warning' }}">
                                {{ $item->stock_quantity <= 2 ? 'Cảnh báo' : 'Thấp' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <a href="{{ route('admin.products.index') }}" class="btn-view-inventory">
                Xem tất cả kho <i class="bi bi-arrow-right" style="margin-left: 4px;"></i>
            </a>
        </div>
    </div>
</div>

<!-- Bảng Top Mặt Hàng Bán Chạy Nhất -->
<div class="table-card" style="margin-bottom: 24px;">
    <div class="table-header-flex">
        <div>
            <h3 style="font-size: 15px; font-weight: 800; color: var(--color-on-surface); margin-bottom: 3px;">
                <i class="bi bi-fire" style="color: var(--color-primary); margin-right: 4px;"></i>
                Top mặt hàng bán chạy nhất
            </h3>
            <p style="font-size: 12px; color: var(--color-outline);">
                Xếp hạng sản phẩm có sản lượng tiêu thụ và doanh thu thực tế cao nhất tại cửa hàng
            </p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn-outline-link">Quản lý kho sản phẩm</a>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th style="width: 70px; text-align: center;">Hạng</th>
                    <th>Sản phẩm thủ công</th>
                    <th>Mã SKU</th>
                    <th>Danh mục</th>
                    <th>Giá niêm yết</th>
                    <th style="text-align: center;">Đã bán</th>
                    <th style="text-align: center;">SL đơn hợp lệ</th>
                    <th>Doanh thu thực tế</th>
                    <th>Tồn kho</th>
                    <th style="text-align: center;">Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topSellingProducts ?? [] as $index => $topProd)
                    @php
                        $rank = $index + 1;
                        $topImg = !empty($topProd->image)
                            ? (str_starts_with($topProd->image, 'http') ? $topProd->image : asset(ltrim($topProd->image, '/')))
                            : asset('assets/images/placeholder.svg');
                    @endphp
                    <tr>
                        <td style="text-align: center;">
                            <span class="rank-badge {{ $rank <= 3 ? 'rank-' . $rank : '' }}">
                                #{{ $rank }}
                            </span>
                        </td>
                        <td>
                            <div class="customer-cell">
                                <img src="{{ $topImg }}" class="stock-thumb" alt="{{ $topProd->name }}" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
                                <div class="customer-info-box">
                                    <a href="{{ route('admin.products.show', $topProd) }}"
                                       class="c-name"
                                       style="font-weight: 700; text-decoration: none; color: var(--color-on-surface);">
                                        {{ $topProd->name }}
                                    </a>
                                    <div style="font-size: 11.5px; color: var(--color-outline);">
                                        ⭐ {{ $topProd->rating }} / 5.0
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="mono-code">{{ $topProd->code }}</span>
                        </td>
                        <td>{{ $topProd->category->name ?? '—' }}</td>
                        <td>{{ number_format($topProd->price, 0, ',', '.') }}đ</td>
                        <td style="text-align: center;">
                            <span style="font-weight: 800; color: var(--color-primary);">{{ $topProd->sold }}</span>
                        </td>
                        <td style="text-align: center; font-weight: 700;">
                            {{ (int) ($topProd->valid_ordered_qty ?? 0) }}
                        </td>
                        <td>
                            <span class="total-amount-text">
                                {{ number_format((float) ($topProd->total_revenue ?? 0), 0, ',', '.') }}đ
                            </span>
                        </td>
                        <td>
                            @if($topProd->stock_quantity <= 2)
                                <span class="stock-tag danger">Còn {{ $topProd->stock_quantity }}</span>
                            @elseif($topProd->stock_quantity <= 5)
                                <span class="stock-tag warning">Còn {{ $topProd->stock_quantity }}</span>
                            @else
                                <span style="font-weight: 700; color: var(--color-success);">{{ $topProd->stock_quantity }}</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <x-table-actions>
                                <x-table-action type="view" :href="route('admin.products.show', $topProd)" label="Xem chi tiết sản phẩm" tooltip-align="end" />
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 24px; color: var(--color-outline);">
                            Chưa có dữ liệu thống kê sản phẩm bán chạy.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Bảng Đơn hàng gần đây -->
<div class="table-card">
    <div class="table-header-flex">
        <div>
            <h3 style="font-size: 15px; font-weight: 800; color: var(--color-on-surface); margin-bottom: 3px;">Đơn hàng gần đây</h3>
            <p style="font-size: 12px; color: var(--color-outline);">Danh sách 5 đơn hàng mới cập nhật trên hệ thống</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn-outline-link">Xem tất cả đơn</a>
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
                            @elseif ($order->status === 'cancelled')
                                <span class="status-badge cancelled">
                                    <i class="bi bi-x-circle"></i> Đã hủy
                                </span>
                            @else
                                <span class="status-badge pending">
                                    <i class="bi bi-clock"></i> Chờ xác nhận
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <x-table-actions>
                                <x-table-action type="view" :href="route('admin.orders.show', $order)" label="Xem chi tiết đơn hàng" tooltip-align="end" />
                            </x-table-actions>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
<script>
    window.dashboardChartData = {
        revenueWeek: @json($revenueWeekChart ?? null),
        revenueMonth: @json($revenueMonthChart ?? null),
        categoryShare: @json($categoryShareChart ?? null)
    };
</script>
<script src="{{ asset('js/admin/dashboard.js') }}?v={{ filemtime(public_path('js/admin/dashboard.js')) }}" defer></script>
@endpush
