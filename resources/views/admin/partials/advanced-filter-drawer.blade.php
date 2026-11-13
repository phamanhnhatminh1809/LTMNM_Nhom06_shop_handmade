@php
    $currentSort = request('sort', 'newest');
    $sortLabels = [
        'newest'       => 'Mới nhất',
        'oldest'       => 'Cũ nhất',
        'best_selling' => 'Bán chạy nhất',
        'price_asc'    => 'Giá tăng dần',
        'price_desc'   => 'Giá giảm dần',
    ];
    if (!array_key_exists($currentSort, $sortLabels)) {
        $currentSort = 'newest';
    }
    $currentSortLabel = $sortLabels[$currentSort];
    $currentStockStatus = request('stock_status', '');
    $currentCategory = request('category', '');
    $drawerActiveCount = 0;
    if ($currentCategory !== '') { $drawerActiveCount++; }
    if (request()->filled('min_price') || request()->filled('max_price')) { $drawerActiveCount++; }
    if ($currentStockStatus !== '') { $drawerActiveCount++; }
    if ($currentSort !== 'newest') { $drawerActiveCount++; }
@endphp
<!-- Lớp phủ Backdrop và Drawer Bộ Lọc Nâng Cao -->
<div class="filter-drawer-overlay" id="advancedFilterDrawerOverlay">
    <form method="GET" action="{{ route('admin.products.index') }}" class="filter-drawer" id="advancedFilterForm">
        @if(request('q'))
            <input type="hidden" name="q" value="{{ request('q') }}">
        @endif
        <input type="hidden" name="sort" id="drawerSortInput" value="{{ $currentSort }}">

        <!-- Header Drawer -->
        <div class="drawer-header">
            <h3 class="drawer-title">
                <i class="bi bi-sliders"></i>
                <span>Bộ lọc nâng cao</span>
                @if($drawerActiveCount > 0)
                    <span class="filter-active-badge">{{ $drawerActiveCount }}</span>
                @endif
            </h3>
            <button type="button" class="btn-close-drawer" id="btnCloseFilterDrawer" title="Đóng bộ lọc">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Thân Drawer -->
        <div class="drawer-body">
            <!-- Nhóm danh mục sản phẩm -->
            <div class="drawer-filter-group">
                <label class="drawer-group-label" style="text-transform: none;">Danh mục sản phẩm</label>
                <div class="drawer-radio-list">
                    <label class="drawer-radio-label">
                        <input type="radio" name="category" value="" {{ $currentCategory === '' ? 'checked' : '' }}>
                        <span>Tất cả danh mục</span>
                    </label>
                    @foreach($categories ?? [] as $cat)
                        <label class="drawer-radio-label">
                            <input type="radio" name="category" value="{{ $cat->slug }}" {{ $currentCategory === $cat->slug ? 'checked' : '' }}>
                            <span>{{ $cat->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <hr class="drawer-divider">

            <!-- Nhóm khoảng giá -->
            <div class="drawer-filter-group">
                <label class="drawer-group-label" style="text-transform: none;">Khoảng giá (VNĐ)</label>
                <div class="drawer-price-inputs">
                    <div class="price-input-field">
                        <span>Từ</span>
                        <input type="number" id="priceFrom" name="min_price" value="{{ request('min_price') }}" placeholder="0" min="0" step="1000">
                    </div>
                    <span class="price-separator">-</span>
                    <div class="price-input-field">
                        <span>Đến</span>
                        <input type="number" id="priceTo" name="max_price" value="{{ request('max_price') }}" placeholder="Trở lên" min="0" step="1000">
                    </div>
                </div>
            </div>

            <hr class="drawer-divider">

            <!-- Nhóm trạng thái kho -->
            <div class="drawer-filter-group">
                <label class="drawer-group-label" style="text-transform: none;">Trạng thái kho</label>
                <div class="drawer-radio-list">
                    <label class="drawer-radio-label">
                        <input type="radio" name="stock_status" value="" {{ $currentStockStatus === '' ? 'checked' : '' }}>
                        <span>Tất cả trạng thái</span>
                    </label>
                    <label class="drawer-radio-label">
                        <input type="radio" name="stock_status" value="active" {{ $currentStockStatus === 'active' ? 'checked' : '' }}>
                        <span>Còn hàng nhiều (&gt; ngưỡng)</span>
                    </label>
                    <label class="drawer-radio-label">
                        <input type="radio" name="stock_status" value="low" {{ $currentStockStatus === 'low' ? 'checked' : '' }}>
                        <span>Sắp hết hàng (1 – ngưỡng)</span>
                    </label>
                    <label class="drawer-radio-label">
                        <input type="radio" name="stock_status" value="out" {{ $currentStockStatus === 'out' ? 'checked' : '' }}>
                        <span>Hết hàng (0)</span>
                    </label>
                </div>
            </div>

            <hr class="drawer-divider">

            <!-- Nhóm sắp xếp theo dropdown -->
            <div class="drawer-filter-group">
                <label class="drawer-group-label" style="text-transform: none;">Sắp xếp theo</label>
                <div class="drawer-custom-select-wrapper" id="drawerSortDropdownWrapper">
                    <button type="button" class="drawer-custom-select-btn" id="btnDrawerSortSelect">
                        <span id="selectedDrawerSortText">{{ $currentSortLabel }}</span>
                        <i class="bi bi-chevron-down" style="font-size: 11px;"></i>
                    </button>
                    <div class="drawer-custom-select-menu" id="drawerSortDropdownMenu">
                        @foreach($sortLabels as $sortKey => $sortLabel)
                            <div class="drawer-select-item {{ $currentSort === $sortKey ? 'active' : '' }}" data-sort="{{ $sortKey }}">
                                <span>{{ $sortLabel }}</span>
                                <i class="bi bi-check2 drawer-select-check"></i>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Drawer: Thiết lập lại và Áp dụng -->
        <div class="drawer-footer">
            <button type="button" class="btn-drawer-reset" id="btnDrawerReset" data-reset-url="{{ route('admin.products.index') }}">
                Thiết lập lại
            </button>
            <button type="submit" class="btn-drawer-apply" id="btnDrawerApply">
                <i class="bi bi-funnel"></i>
                <span>Áp dụng</span>
            </button>
        </div>
    </form>
</div>