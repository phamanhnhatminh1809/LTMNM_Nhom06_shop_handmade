<!-- Lớp phủ Backdrop và Drawer Bộ Lọc Nâng Cao -->
<div class="filter-drawer-overlay" id="advancedFilterDrawerOverlay">
    <div class="filter-drawer">
        <!-- Header Drawer -->
        <div class="drawer-header">
            <h3 class="drawer-title">
                <i class="bi bi-sliders"></i>
                <span>Bộ lọc nâng cao</span>
            </h3>
            <button type="button" class="btn-close-drawer" id="btnCloseFilterDrawer" title="Đóng bộ lọc">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Thân Drawer -->
        <div class="drawer-body">
            <!-- Nhóm danh mục sản phẩm -->
            <div class="drawer-filter-group">
                <label class="drawer-group-label">DANH MỤC SẢN PHẨM</label>
                <div class="drawer-checkbox-grid">
                    <label class="drawer-check-label">
                        <input type="checkbox" name="filter_category[]" value="Gốm sứ">
                        <span>Gốm sứ</span>
                    </label>
                    <label class="drawer-check-label">
                        <input type="checkbox" name="filter_category[]" value="Đồ len">
                        <span>Đồ len</span>
                    </label>
                    <label class="drawer-check-label">
                        <input type="checkbox" name="filter_category[]" value="Đồ da">
                        <span>Đồ da</span>
                    </label>
                </div>
            </div>

            <hr class="drawer-divider">

            <!-- Nhóm khoảng giá -->
            <div class="drawer-filter-group">
                <label class="drawer-group-label">KHOẢNG GIÁ (VNĐ)</label>
                <div class="drawer-price-inputs">
                    <div class="price-input-field">
                        <span>Từ</span>
                        <input type="number" id="priceFrom" placeholder="0" min="0">
                    </div>
                    <span class="price-separator">-</span>
                    <div class="price-input-field">
                        <span>Đến</span>
                        <input type="number" id="priceTo" placeholder="Trở lên" min="0">
                    </div>
                </div>
            </div>

            <hr class="drawer-divider">

            <!-- Nhóm trạng thái kho -->
            <div class="drawer-filter-group">
                <label class="drawer-group-label">TRẠNG THÁI KHO</label>
                <div class="drawer-radio-list">
                    <label class="drawer-radio-label">
                        <input type="radio" name="filter_stock_status" value="active" checked>
                        <span>Đang bán</span>
                    </label>
                    <label class="drawer-radio-label">
                        <input type="radio" name="filter_stock_status" value="low">
                        <span>Sắp hết hàng</span>
                    </label>
                    <label class="drawer-radio-label">
                        <input type="radio" name="filter_stock_status" value="out">
                        <span>Hết hàng</span>
                    </label>
                    <label class="drawer-radio-label">
                        <input type="radio" name="filter_stock_status" value="discontinued">
                        <span>Ngừng kinh doanh</span>
                    </label>
                </div>
            </div>

            <hr class="drawer-divider">

            <!-- Nhóm sắp xếp theo dropdown -->
            <div class="drawer-filter-group">
                <label class="drawer-group-label">SẮP XẾP THEO</label>
                <div class="drawer-custom-select-wrapper" id="drawerSortDropdownWrapper">
                    <button type="button" class="drawer-custom-select-btn" id="btnDrawerSortSelect">
                        <span id="selectedDrawerSortText">Mới cập nhật</span>
                        <i class="bi bi-chevron-down" style="font-size: 11px;"></i>
                    </button>
                    <div class="drawer-custom-select-menu" id="drawerSortDropdownMenu">
                        <div class="drawer-select-item active" data-sort="newest">Mới cập nhật</div>
                        <div class="drawer-select-item" data-sort="best_selling">Bán chạy nhất</div>
                        <div class="drawer-select-item" data-sort="price_asc">Giá tăng dần</div>
                        <div class="drawer-select-item" data-sort="price_desc">Giá giảm dần</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Drawer: Thiết lập lại & Áp dụng -->
        <div class="drawer-footer">
            <button type="button" class="btn-drawer-reset" id="btnDrawerReset">
                Thiết lập lại
            </button>
            <button type="button" class="btn-drawer-apply" id="btnDrawerApply">
                <i class="bi bi-funnel"></i>
                <span>Áp dụng</span>
            </button>
        </div>
    </div>
</div>