<?php

$activeMenu = 'dashboard';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tổng quan hệ thống - Quản trị Handmade House</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="/features/admin/assets/css/admin.css?v=<?= time() ?>">
</head>

<body>
    <div class="admin-layout">
        <!-- Sidebar bên trái (Dùng chung) -->
        <?php include __DIR__ . '/../components/admin_sidebar.php'; ?>

        <!-- Nội dung chính Dashboard bên phải -->
        <main class="admin-main-content">
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
                    <h2 class="welcome-title">Chào buổi sáng, Đăng Khoa</h2>
                    <p class="welcome-sub">Tổng quan hoạt động của shop trong 24 giờ qua. Hệ thống đang hoạt động ổn định.</p>
                </div>
                <div class="welcome-actions">
                    <!-- Component Dropdown chọn khoảng thời gian -->
                    <?php include __DIR__ . '/../components/date_filter_modal.php'; ?>

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
                    <div class="metric-value">5.200.000đ</div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon-box">
                        <i class="bi bi-bag-check"></i>
                    </div>
                    <div class="metric-label">Đơn hàng mới</div>
                    <div class="metric-value">18</div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon-box">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="metric-label">Đang xử lý</div>
                    <div class="metric-value">45</div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon-box">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="metric-label">Sản phẩm sắp hết</div>
                    <div class="metric-value">5</div>
                </div>

                <div class="metric-card">
                    <div class="metric-icon-box">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="metric-label">Khách hàng mới</div>
                    <div class="metric-value">12</div>
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
                                <span class="num">124</span>
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
                            <span class="stock-count-badge">5</span>
                        </div>

                        <div class="stock-items-list">
                            <!-- Item 1: Gấu bông len -->
                            <div class="stock-item">
                                <img src="/assets/images/gaubonglen.jpg" class="stock-thumb" alt="Gấu bông len" onerror="this.src='/assets/images/logo.png'">
                                <div class="stock-details">
                                    <div class="stock-name">Gấu bông len</div>
                                    <div class="stock-code">VD-001</div>
                                </div>
                                <div class="stock-status-right">
                                    <div class="stock-ratio">2 <span style="font-weight: 500; color: #89726d;">/5</span></div>
                                    <span class="stock-tag danger">CẢNH BÁO</span>
                                </div>
                            </div>

                            <!-- Item 2: Móc khóa len -->
                            <div class="stock-item">
                                <img src="/assets/images/mockhoalen.jpg" class="stock-thumb" alt="Móc khóa len" onerror="this.src='/assets/images/logo.png'">
                                <div class="stock-details">
                                    <div class="stock-name">Móc khóa len</div>
                                    <div class="stock-code">KL-042</div>
                                </div>
                                <div class="stock-status-right">
                                    <div class="stock-ratio warning">4 <span style="font-weight: 500; color: #89726d;">/10</span></div>
                                    <span class="stock-tag warning">THẤP</span>
                                </div>
                            </div>

                            <!-- Item 3: Bó hoa len -->
                            <div class="stock-item">
                                <img src="/assets/images/bohoalen.jpg" class="stock-thumb" alt="Bó hoa len" onerror="this.src='/assets/images/logo.png'">
                                <div class="stock-details">
                                    <div class="stock-name">Bó hoa len</div>
                                    <div class="stock-code">CG-112</div>
                                </div>
                                <div class="stock-status-right">
                                    <div class="stock-ratio">1 <span style="font-weight: 500; color: #89726d;">/8</span></div>
                                    <span class="stock-tag danger">CẢNH BÁO</span>
                                </div>
                            </div>
                        </div>

                        <a href="#" class="btn-view-inventory">
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
                            <!-- Đơn 1 -->
                            <tr>
                                <td><span class="order-code-text">#ORD-9023</span></td>
                                <td>
                                    <div class="customer-cell">
                                        <div class="customer-avatar-abbr">NA</div>
                                        <div class="customer-info-box">
                                            <div class="c-name">Nguyễn Văn An</div>
                                            <div class="c-email">nguyenvanan@gmail.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td>24/10, 14:30</td>
                                <td><span class="total-amount-text">1.250.000đ</span></td>
                                <td>
                                    <span class="status-badge pending">
                                        <i class="bi bi-clock"></i> Chờ xử lý
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-action-view" title="Xem chi tiết đơn">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Đơn 2 -->
                            <tr>
                                <td><span class="order-code-text">#ORD-9022</span></td>
                                <td>
                                    <div class="customer-cell">
                                        <div class="customer-avatar-abbr" style="background-color: #3b503f; color: #ffffff;">TB</div>
                                        <div class="customer-info-box">
                                            <div class="c-name">Trần Thị Bình</div>
                                            <div class="c-email">tranbinh@mail.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td>24/10, 10:15</td>
                                <td><span class="total-amount-text">850.000đ</span></td>
                                <td>
                                    <span class="status-badge shipping">
                                        <i class="bi bi-truck"></i> Đang giao
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-action-view" title="Xem chi tiết đơn">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Đơn 3 -->
                            <tr>
                                <td><span class="order-code-text">#ORD-9021</span></td>
                                <td>
                                    <div class="customer-cell">
                                        <div class="customer-avatar-abbr">LM</div>
                                        <div class="customer-info-box">
                                            <div class="c-name">Lê Văn Minh</div>
                                            <div class="c-email">levanminh@gmail.com</div>
                                        </div>
                                    </div>
                                </td>
                                <td>23/10, 18:45</td>
                                <td><span class="total-amount-text">3.100.000đ</span></td>
                                <td>
                                    <span class="status-badge completed">
                                        <i class="bi bi-check-circle"></i> Hoàn thành
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-action-view" title="Xem chi tiết đơn">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Script Dashboard -->
    <script src="/features/admin/assets/js/dashboard.js?v=<?= time() ?>"></script>
</body>

</html>