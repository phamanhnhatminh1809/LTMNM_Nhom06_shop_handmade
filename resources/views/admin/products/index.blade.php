@extends('layouts.admin')

@section('title', 'Quản lý sản phẩm')

@php
    $activeMenu = 'products';
@endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-bag"></i>
        <span>Quản lý sản phẩm</span>
    </div>
    <div class="top-bar-right">
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary-outline" title="Quản lý danh mục sản phẩm">
            <i class="bi bi-tags"></i>
            <span>Danh mục sản phẩm</span>
        </a>
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

@if($errors->any())
    <div class="admin-alert error">
        <div class="admin-alert-content">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <div>
                <div>Dữ liệu sản phẩm chưa hợp lệ:</div>
                <ul>
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<!-- Welcome Card Sản Phẩm -->
<div class="product-welcome-card">
    <h2 class="product-welcome-title">
        <i class="bi bi-bag"></i>
        <span>Quản lý sản phẩm</span>
    </h2>
    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <a href="javascript:void(0)" class="btn-secondary-outline" id="btnAddNewProduct" style="background: rgba(255,255,255,0.92); padding: 8px 16px;">
            <i class="bi bi-window-plus"></i>
            <span>Thêm nhanh (Modal)</span>
        </a>
        <a href="{{ route('admin.products.create') }}" class="btn-add-product">
            <i class="bi bi-plus-lg"></i>
            <span>Thêm sản phẩm mới</span>
        </a>
    </div>
</div>

@php
    $activeCategorySlug = trim(request('category', ''));
    $activeCategoryObj = $categories->firstWhere('slug', $activeCategorySlug);
    $activeCategoryName = $activeCategoryObj ? $activeCategoryObj->name : 'Tất cả danh mục';

    $currentSort = trim(request('sort', 'newest'));
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

    $minPrice = request('min_price');
    $maxPrice = request('max_price');
    $stockStatus = trim(request('stock_status', ''));
    $stockStatusLabels = [
        'active' => 'Còn hàng nhiều (> ngưỡng)',
        'low'    => 'Sắp hết hàng (<= ngưỡng)',
        'out'    => 'Hết hàng (0)',
    ];

    $advancedFilterCount = 0;
    if ($minPrice !== null && $minPrice !== '') { $advancedFilterCount++; }
    if ($maxPrice !== null && $maxPrice !== '') { $advancedFilterCount++; }
    if ($stockStatus !== '' && isset($stockStatusLabels[$stockStatus])) { $advancedFilterCount++; }
    if ($currentSort !== 'newest') { $advancedFilterCount++; }

    $hasAnyFilter = request()->filled('q')
        || $activeCategorySlug !== ''
        || $advancedFilterCount > 0;
@endphp

<!-- Thanh tìm kiếm và Bộ lọc -->
<div class="product-filter-bar">
    <form method="GET" action="{{ route('admin.products.index') }}" id="adminSearchForm" class="product-filter-form">
        <input type="hidden" name="category" id="categoryFilterInput" value="{{ $activeCategorySlug }}">
        <input type="hidden" name="sort" id="sortFilterInput" value="{{ $currentSort }}">
        @foreach(['min_price', 'max_price', 'stock_status'] as $extraFilter)
            @if(request()->filled($extraFilter))
                <input type="hidden" name="{{ $extraFilter }}" value="{{ request($extraFilter) }}">
            @endif
        @endforeach

        <div class="search-input-box">
            <i class="bi bi-search"></i>
            <input type="text" name="q" id="productSearchInput" placeholder="Tìm kiếm theo tên, SKU..." value="{{ request('q') }}">
            @if(request('q'))
                <a href="{{ route('admin.products.index', request()->except(['q', 'page'])) }}"
                   class="btn-clear-search show"
                   id="btnClearSearch"
                   title="Xóa từ khóa tìm kiếm"
                   style="text-decoration:none;">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>

        <div class="filter-actions-right">
            <!-- Dropdown Danh mục (Custom UI) -->
            <div class="filter-dropdown-wrapper" id="categoryDropdownWrapper">
                <button type="button"
                        class="filter-btn-select {{ $activeCategorySlug !== '' ? 'is-active' : '' }}"
                        id="btnCategorySelect"
                        aria-haspopup="listbox"
                        aria-expanded="false">
                    <i class="bi bi-tags"></i>
                    <span id="selectedCategoryText">{{ $activeCategoryName }}</span>
                    <i class="bi bi-chevron-down filter-chevron"></i>
                </button>
                <div class="filter-dropdown-menu is-align-right" id="categoryDropdownMenu" role="listbox" aria-label="Lọc theo danh mục">
                    <button type="button"
                            class="filter-menu-item {{ $activeCategorySlug === '' ? 'active' : '' }}"
                            data-category="">
                        <span>Tất cả danh mục</span>
                        @if($activeCategorySlug === '')
                            <i class="bi bi-check2"></i>
                        @endif
                    </button>
                    @foreach($categories as $cat)
                        <button type="button"
                                class="filter-menu-item {{ $activeCategorySlug === $cat->slug ? 'active' : '' }}"
                                data-category="{{ $cat->slug }}">
                            <span>{{ $cat->name }}</span>
                            @if($activeCategorySlug === $cat->slug)
                                <i class="bi bi-check2"></i>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Dropdown Sắp xếp nhanh (Mới nhất / Cũ nhất / Giá / Bán chạy) -->
            <div class="filter-dropdown-wrapper" id="sortDropdownWrapper">
                <button type="button"
                        class="filter-btn-select {{ $currentSort !== 'newest' ? 'is-active' : '' }}"
                        id="btnSortSelect"
                        aria-haspopup="listbox"
                        aria-expanded="false">
                    <i class="bi bi-arrow-down-up"></i>
                    <span id="selectedSortText">{{ $currentSortLabel }}</span>
                    <i class="bi bi-chevron-down filter-chevron"></i>
                </button>
                <div class="filter-dropdown-menu is-align-right" id="sortDropdownMenu" role="listbox" aria-label="Sắp xếp sản phẩm">
                    @foreach($sortLabels as $sortKey => $sortLabel)
                        <button type="button"
                                class="filter-menu-item sort-menu-item {{ $currentSort === $sortKey ? 'active' : '' }}"
                                data-sort="{{ $sortKey }}">
                            <span>{{ $sortLabel }}</span>
                            @if($currentSort === $sortKey)
                                <i class="bi bi-check2"></i>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Nút mở Drawer Bộ lọc nâng cao -->
            <button type="button"
                    class="filter-btn-outline {{ $advancedFilterCount > 0 ? 'is-active' : '' }}"
                    id="btnOpenAdvancedFilter"
                    aria-label="Mở bộ lọc nâng cao">
                <i class="bi bi-sliders"></i>
                <span>Bộ lọc</span>
                @if($advancedFilterCount > 0)
                    <span class="filter-active-badge">{{ $advancedFilterCount }}</span>
                @endif
            </button>
        </div>
    </form>

    @if($hasAnyFilter)
        <div class="active-filter-chips">
            <span class="active-filter-caption">
                <i class="bi bi-funnel"></i> Đang lọc:
            </span>
            @if(request()->filled('q'))
                <a href="{{ route('admin.products.index', request()->except(['q', 'page'])) }}" class="active-filter-chip" title="Bỏ từ khóa tìm kiếm">
                    <span>Từ khóa: "{{ request('q') }}"</span>
                    <i class="bi bi-x"></i>
                </a>
            @endif
            @if($activeCategorySlug !== '')
                <a href="{{ route('admin.products.index', request()->except(['category', 'page'])) }}" class="active-filter-chip" title="Bỏ lọc danh mục">
                    <span>Danh mục: {{ $activeCategoryName }}</span>
                    <i class="bi bi-x"></i>
                </a>
            @endif
            @if(($minPrice !== null && $minPrice !== '') || ($maxPrice !== null && $maxPrice !== ''))
                <a href="{{ route('admin.products.index', request()->except(['min_price', 'max_price', 'page'])) }}" class="active-filter-chip" title="Bỏ lọc khoảng giá">
                    <span>
                        Giá:
                        {{ ($minPrice !== null && $minPrice !== '') ? number_format((float) $minPrice, 0, ',', '.') . 'đ' : '0đ' }}
                        –
                        {{ ($maxPrice !== null && $maxPrice !== '') ? number_format((float) $maxPrice, 0, ',', '.') . 'đ' : 'Trở lên' }}
                    </span>
                    <i class="bi bi-x"></i>
                </a>
            @endif
            @if($stockStatus !== '' && isset($stockStatusLabels[$stockStatus]))
                <a href="{{ route('admin.products.index', request()->except(['stock_status', 'page'])) }}" class="active-filter-chip" title="Bỏ lọc tồn kho">
                    <span>Kho: {{ $stockStatusLabels[$stockStatus] }}</span>
                    <i class="bi bi-x"></i>
                </a>
            @endif
            @if($currentSort !== 'newest')
                <a href="{{ route('admin.products.index', request()->except(['sort', 'page'])) }}" class="active-filter-chip" title="Về mặc định mới nhất">
                    <span>Sắp xếp: {{ $currentSortLabel }}</span>
                    <i class="bi bi-x"></i>
                </a>
            @endif
            <a href="{{ route('admin.products.index') }}" class="btn-clear-all-filters">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span>Đặt lại tất cả</span>
            </a>
        </div>
    @endif
</div>

<!-- Bảng Danh Sách Sản Phẩm -->
<div class="table-card">
    <div class="table-header-flex">
        <div>
            <h3 style="font-size: 15px; font-weight: 800; color: var(--color-on-surface); margin-bottom: 3px;">
                Danh sách sản phẩm ({{ $products->total() }})
            </h3>
            <p style="font-size: 12px; color: var(--color-outline);">Hiển thị các sản phẩm thủ công đang được mở bán hoặc quản lý tồn kho</p>
        </div>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">
                        <input type="checkbox" id="checkAllProducts">
                    </th>
                    <th>Sản phẩm</th>
                    <th>Mã SKU</th>
                    <th>Danh mục</th>
                    <th>Giá bán</th>
                    <th>Tồn kho</th>
                    <th>Đã bán</th>
                    <th>Đánh giá</th>
                    <th style="text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody id="productTableBody">
                @forelse ($products as $prod)
                    @php
                        $prodImg = !empty($prod->image)
                            ? (str_starts_with($prod->image, 'http') ? $prod->image : asset(ltrim($prod->image, '/')))
                            : asset('assets/images/placeholder.svg');
                    @endphp
                    <tr data-category="{{ $prod->category->slug ?? 'all' }}">
                        <td style="text-align: center;">
                            <input type="checkbox" class="product-checkbox" value="{{ $prod->id }}">
                        </td>
                        <td>
                            <div class="customer-cell">
                                <img src="{{ $prodImg }}" class="stock-thumb" alt="{{ $prod->name }}" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
                                <div class="customer-info-box">
                                    <a href="{{ route('admin.products.show', $prod) }}"
                                       class="c-name product-item-name"
                                       style="font-weight: 700; text-decoration: none; color: var(--color-on-surface);">
                                        {{ $prod->name }}
                                    </a>
                                    @if($prod->is_featured)
                                        <span class="stock-tag" style="background:#ffdad2;color:#792a16;font-size:10px;padding:2px 6px;border-radius:4px;">Nổi bật</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="mono-code">{{ $prod->code }}</span>
                        </td>
                        <td>
                            {{ $prod->category->name ?? 'Chưa phân loại' }}
                        </td>
                        <td>
                            <span class="total-amount-text">{{ number_format($prod->price, 0, ',', '.') }}đ</span>
                            @if(!empty($prod->old_price))
                                <div style="font-size: 11px; color: #89726d; margin-top: 2px;">
                                    Giá gốc: {{ number_format($prod->old_price, 0, ',', '.') }}đ
                                </div>
                            @endif
                        </td>
                        <td>
                            @php
                                $minThreshold = $prod->effectiveMinStockThreshold();
                            @endphp
                            @if($prod->stock_quantity <= 0)
                                <span class="stock-tag danger" title="Ngưỡng cảnh báo tối thiểu: {{ $minThreshold }}">Hết hàng (0)</span>
                            @elseif($prod->stock_quantity <= $minThreshold)
                                <span class="stock-tag warning" title="Ngưỡng cảnh báo tối thiểu: {{ $minThreshold }}">Còn {{ $prod->stock_quantity }} / ≤{{ $minThreshold }}</span>
                            @else
                                <span style="font-weight: 700; color: #324a37;" title="Ngưỡng cảnh báo tối thiểu: {{ $minThreshold }}">{{ $prod->stock_quantity }}</span>
                            @endif
                        </td>
                        <td>{{ $prod->sold }}</td>
                        <td>
                            <a href="{{ route('admin.reviews.index', ['product_id' => $prod->id]) }}"
                               style="text-decoration: none; color: inherit; font-weight: 700;"
                               title="Xem đánh giá của sản phẩm này">
                                ⭐ {{ $prod->rating }}
                            </a>
                        </td>
                        <td style="text-align: center;">
                            <x-table-actions>
                                <x-table-action type="view"
                                                :href="route('admin.products.show', $prod)"
                                                label="Xem chi tiết sản phẩm" />
                                <x-table-action type="store"
                                                :href="route('products.show', $prod->id)"
                                                target="_blank"
                                                label="Xem ngoài cửa hàng" />
                                <x-table-action type="edit"
                                                :href="route('admin.products.edit', $prod)"
                                                label="Chỉnh sửa sản phẩm" />
                                <x-table-action type="delete"
                                                class="btn-trigger-delete-product"
                                                :data-name="$prod->name"
                                                :data-code="$prod->code"
                                                :data-category="$prod->category->name ?? 'Chưa phân loại'"
                                                :data-image="$prodImg"
                                                :data-url="route('admin.products.destroy', $prod)"
                                                label="Xóa sản phẩm"
                                                tooltip-align="end" />
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 30px;">
                            Không tìm thấy sản phẩm nào phù hợp
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="padding: 16px 24px;">
        {{ $products->links('admin.partials.pagination') }}
    </div>
</div>

<!-- Modals -->
@include('admin.partials.delete-product-modal')
@include('admin.partials.add-product-modal')
@include('admin.partials.add-category-modal')
@include('admin.partials.add-variant-modal')
@include('admin.partials.advanced-filter-drawer')
@endsection

@push('scripts')
<script src="{{ asset('js/admin/products.js') }}?v={{ filemtime(public_path('js/admin/products.js')) }}"></script>
@endpush
