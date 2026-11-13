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

<!-- Thanh tìm kiếm và Bộ lọc -->
<div class="product-filter-bar">
    <form method="GET" action="{{ route('admin.products.index') }}" id="adminSearchForm" style="display:contents;">
        @foreach(['min_price', 'max_price', 'stock_status', 'sort'] as $extraFilter)
            @if(request()->filled($extraFilter))
                <input type="hidden" name="{{ $extraFilter }}" value="{{ request($extraFilter) }}">
            @endif
        @endforeach
        <div class="search-input-box">
            <i class="bi bi-search"></i>
            <input type="text" name="q" id="productSearchInput" placeholder="Tìm kiếm theo tên, SKU..." value="{{ request('q') }}">
            @if(request('q'))
                <a href="{{ route('admin.products.index', request('category') ? ['category' => request('category')] : []) }}"
                   class="btn-clear-search"
                   id="btnClearSearch"
                   title="Xóa từ khóa tìm kiếm"
                   style="display:flex;align-items:center;justify-content:center;text-decoration:none;">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>

        <div class="filter-actions-right">
            <!-- Dropdown Danh mục -->
            <div class="filter-dropdown-wrapper" id="categoryDropdownWrapper">
                <select name="category" class="filter-btn-select" onchange="document.getElementById('adminSearchForm').submit()" style="border:none;background:transparent;outline:none;font-size:inherit;font-family:inherit;padding:8px 12px;cursor:pointer;">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <a href="javascript:void(0)" class="btn-filter-icon" id="btnOpenAdvancedFilter" title="Bộ lọc nâng cao">
                <i class="bi bi-sliders"></i>
                <span>Bộ lọc</span>
            </a>
        </div>
    </form>
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
                                <div style="font-size: 11px; text-decoration: line-through; color: #89726d;">
                                    {{ number_format($prod->old_price, 0, ',', '.') }}đ
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($prod->stock_quantity <= 2)
                                <span class="stock-tag danger">Còn {{ $prod->stock_quantity }}</span>
                            @elseif($prod->stock_quantity <= 5)
                                <span class="stock-tag warning">Còn {{ $prod->stock_quantity }}</span>
                            @else
                                <span style="font-weight: 700; color: #324a37;">{{ $prod->stock_quantity }}</span>
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
                            <div style="display: inline-flex; gap: 6px; align-items: center;">
                                <a href="{{ route('admin.products.show', $prod) }}" class="btn-action-view" title="Chi tiết sản phẩm">
                                    <i class="bi bi-info-circle"></i>
                                </a>
                                <a href="{{ route('products.show', $prod->id) }}" target="_blank" class="btn-action-view" title="Xem ngoài cửa hàng">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $prod) }}" class="btn-action-view" title="Chỉnh sửa sản phẩm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button"
                                        class="btn-action-view danger btn-trigger-delete-product"
                                        data-name="{{ $prod->name }}"
                                        data-code="{{ $prod->code }}"
                                        data-category="{{ $prod->category->name ?? 'Chưa phân loại' }}"
                                        data-image="{{ $prodImg }}"
                                        data-url="{{ route('admin.products.destroy', $prod) }}"
                                        title="Xóa sản phẩm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
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
<script src="{{ asset('js/admin/products.js') }}"></script>
@endpush
