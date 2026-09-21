<?php
$activeMenu = 'products';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Quản trị Handmade House</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/features/admin/assets/css/admin.css?v=<?= time() ?>">
</head>

<body>
    <div class="admin-layout">
        <!-- Sidebar dùng chung -->
        <?php include __DIR__ . '/../components/admin_sidebar.php'; ?>

        <!-- Nội dung chính Quản lý sản phẩm bên phải -->
        <main class="admin-main-content">
            <!-- Top Bar -->
            <div class="admin-top-bar">
                <div class="page-main-title">
                    <i class="bi bi-bag"></i>
                    <span>QUẢN LÝ SẢN PHẨM</span>
                </div>
                <div class="top-bar-right">
                    <button class="btn-icon-bell" title="Thông báo hệ thống">
                        <i class="bi bi-bell"></i>
                        <span class="notification-dot"></span>
                    </button>
                </div>
            </div>

            <!-- Welcome Card Sản Phẩm -->
            <div class="product-welcome-card">
                <h2 class="product-welcome-title">
                    Quản lý các danh sách sản phẩm thủ công hiện có<br>trong cửa hàng
                </h2>
                <a href="#" class="btn-add-product" id="btnAddNewProduct">
                    <i class="bi bi-plus-lg"></i>
                    <span>Thêm sản phẩm</span>
                </a>
            </div>

            <!-- Thanh tìm kiếm & Bộ lọc -->
            <div class="product-filter-bar">
                <div class="search-input-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="productSearchInput" placeholder="Tìm kiếm theo tên, SKU...">
                    <button type="button" class="btn-clear-search" id="btnClearSearch" title="Xóa từ khóa tìm kiếm">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="filter-actions-right">
                    <!-- Dropdown Danh mục -->
                    <div class="filter-dropdown-wrapper" id="categoryDropdownWrapper">
                        <button type="button" class="filter-btn-select" id="btnCategorySelect">
                            <span id="selectedCategoryText">Tất cả danh mục</span>
                            <i class="bi bi-chevron-down" style="font-size: 11px; margin-left: 2px;"></i>
                        </button>
                        <div class="filter-dropdown-menu" id="categoryDropdownMenu">
                            <div class="filter-menu-item active" data-category="all">Tất cả danh mục</div>
                            <div class="filter-menu-item" data-category="Gốm sứ">Gốm sứ</div>
                            <div class="filter-menu-item" data-category="Đồ len">Đồ len</div>
                            <div class="filter-menu-item" data-category="Đồ da">Đồ da</div>
                        </div>
                    </div>

                    <!-- Nút Bộ lọc nâng cao -->
                    <button type="button" class="filter-btn-outline" id="btnAdvancedFilter">
                        <i class="bi bi-filter"></i>
                        <span>Bộ lọc nâng cao</span>
                    </button>

                    <!-- Nút Xuất file -->
                    <button type="button" class="filter-btn-outline" id="btnExportFile">
                        <i class="bi bi-download"></i>
                        <span>Xuất file</span>
                    </button>
                </div>
            </div>

            <!-- Bảng Dữ Liệu Sản Phẩm -->
            <div class="product-table-card">
                <div class="product-table-wrapper">
                    <table class="product-data-table">
                        <thead>
                            <tr>
                                <th>SẢN PHẨM</th>
                                <th>MÃ SKU</th>
                                <th>DANH MỤC</th>
                                <th>GIÁ BÁN</th>
                                <th>TỒN KHO</th>
                                <th>TRẠNG THÁI</th>
                                <th style="text-align: center;">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody id="productTableBody">
                            <!-- Dòng 1: Gấu bông len -->
                            <tr data-category="Gốm sứ">
                                <td>
                                    <div class="product-info-cell">
                                        <img src="/assets/images/gaubonglen.jpg" class="product-table-thumb" alt="Gấu bông len" onerror="this.src='/assets/images/logo.png'">
                                        <div class="product-text-details">
                                            <span class="product-item-name">Gấu bông len</span>
                                            <span class="product-item-variant">Màu nâu</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="sku-code-text">CER-092</span></td>
                                <td><span class="category-pill">Gốm sứ</span></td>
                                <td><span class="price-value-text">320.000đ</span></td>
                                <td><span class="stock-value-cell">45</span></td>
                                <td>
                                    <span class="status-pill active">
                                        <span class="dot-indicator"></span> Đang bán
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons-group" style="justify-content: center;">
                                        <button type="button" class="btn-tbl-action view" title="Xem chi tiết"><i class="bi bi-eye"></i></button>
                                        <button type="button" class="btn-tbl-action edit" title="Chỉnh sửa"><i class="bi bi-pencil"></i></button>
                                        <button type="button" class="btn-tbl-action delete" title="Xóa"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Dòng 2: Móc khóa len -->
                            <tr data-category="Đồ len">
                                <td>
                                    <div class="product-info-cell">
                                        <img src="/assets/images/mockhoalen.jpg" class="product-table-thumb" alt="Móc khóa len" onerror="this.src='/assets/images/logo.png'">
                                        <div class="product-text-details">
                                            <span class="product-item-name">Móc khóa len</span>
                                            <span class="product-item-variant">Màu xanh</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="sku-code-text">KNT-114</span></td>
                                <td><span class="category-pill">Đồ len</span></td>
                                <td><span class="price-value-text">550.000đ</span></td>
                                <td><span class="stock-value-cell warning">4</span></td>
                                <td>
                                    <span class="status-pill low">
                                        <span class="dot-indicator"></span> Sắp hết
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons-group" style="justify-content: center;">
                                        <button type="button" class="btn-tbl-action view" title="Xem chi tiết"><i class="bi bi-eye"></i></button>
                                        <button type="button" class="btn-tbl-action edit" title="Chỉnh sửa"><i class="bi bi-pencil"></i></button>
                                        <button type="button" class="btn-tbl-action delete" title="Xóa"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Dòng 3: Bó hoa len -->
                            <tr data-category="Đồ da">
                                <td>
                                    <div class="product-info-cell">
                                        <img src="/assets/images/bohoalen.jpg" class="product-table-thumb" alt="Bó hoa len" onerror="this.src='/assets/images/logo.png'">
                                        <div class="product-text-details">
                                            <span class="product-item-name">Bó hoa len</span>
                                            <span class="product-item-variant">Màu đỏ</span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="sku-code-text">LTH-032</span></td>
                                <td><span class="category-pill">Đồ da</span></td>
                                <td><span class="price-value-text">450.000đ</span></td>
                                <td><span class="stock-value-cell danger">0</span></td>
                                <td>
                                    <span class="status-pill out">
                                        <span class="dot-indicator"></span> Hết hàng
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons-group" style="justify-content: center;">
                                        <button type="button" class="btn-tbl-action view" title="Xem chi tiết"><i class="bi bi-eye"></i></button>
                                        <button type="button" class="btn-tbl-action edit" title="Chỉnh sửa"><i class="bi bi-pencil"></i></button>
                                        <button type="button" class="btn-tbl-action delete" title="Xóa"><i class="bi bi-trash"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Dòng thông báo khi không tìm thấy sản phẩm -->
                            <tr id="noProductFoundRow" style="display: none;">
                                <td colspan="7" style="text-align: center; padding: 48px 20px;">
                                    <div class="empty-state-box">
                                        <div class="empty-state-icon">
                                            <i class="bi bi-search"></i>
                                        </div>
                                        <h4 class="empty-state-title">Không tìm thấy sản phẩm phù hợp</h4>
                                        <p class="empty-state-desc">Vui lòng thử lại với từ khóa khác hoặc xóa bộ lọc để xem toàn bộ danh sách.</p>
                                        <button type="button" class="btn-reset-filter" id="btnResetSearchFilter">
                                            <i class="bi bi-arrow-clockwise"></i> Xem tất cả sản phẩm
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Phân Trang -->
                <div class="product-pagination-footer">
                    <span class="pagination-info-text">
                        Hiển thị <strong>1 - 10</strong> trong tổng số <strong>124</strong> sản phẩm
                    </span>

                    <div class="pagination-controls">
                        <button type="button" class="btn-page-nav" disabled title="Trang trước">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="page-num-btn active">1</button>
                        <button type="button" class="page-num-btn">2</button>
                        <button type="button" class="page-num-btn">3</button>
                        <span class="page-dots">...</span>
                        <button type="button" class="page-num-btn">13</button>
                        <button type="button" class="btn-page-nav" title="Trang sau">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Drawer Bộ lọc nâng cao -->
    <?php include __DIR__ . '/../components/advanced_filter_drawer.php'; ?>

    <!-- Modal Thêm sản phẩm mới -->
    <?php include __DIR__ . '/../components/add_product_modal.php'; ?>

    <!-- Modal Thêm biến thể sản phẩm -->
    <?php include __DIR__ . '/../components/add_variant_modal.php'; ?>

    <!-- Modal Thêm danh mục mới -->
    <?php include __DIR__ . '/../components/add_category_modal.php'; ?>

    <!-- Script Xử Lý Quản Lý Sản Phẩm -->
    <script src="/features/admin/assets/js/products.js?v=<?= time() ?>"></script>
</body>

</html>