@extends('layouts.admin')

@section('title', 'Quản lý sản phẩm')

@php
    $activeMenu = 'products';
@endphp

@section('content')
{{--
    ================================================================
    [BACKEND ĐÃ CÓ] — Cập nhật dành cho Frontend
    ================================================================
    Backend đã bổ sung thêm các action sau (chưa có trước đây):

    1. XEM CHI TIẾT sản phẩm:
       Route: GET /admin/products/{product}  → admin.products.show
       View : resources/views/admin/products/show.blade.php

    2. FORM SỬA sản phẩm:
       Route: GET  /admin/products/{product}/edit  → admin.products.edit
              PUT  /admin/products/{product}        → admin.products.update
       View : resources/views/admin/products/edit.blade.php

    3. FORM TẠO sản phẩm (trang riêng, thay cho modal):
       Route: GET  /admin/products/create  → admin.products.create
              POST /admin/products          → admin.products.store
       View : resources/views/admin/products/create.blade.php

    4. XÓA sản phẩm:
       Route: DELETE /admin/products/{product}  → admin.products.destroy
       Cần form với @method('DELETE')

    Các nút trong bảng hiện tại:
      - Nút "Xem ngoài web" (btn-action-view + bi-eye): đã đúng → route('products.show', $prod->id)
      - Nút "Chỉnh sửa" (btn-action-view + bi-pencil): cần wire vào → route('admin.products.edit', $prod)
    ================================================================
--}}

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
    <a href="javascript:void(0)" class="btn-add-product" id="btnAddNewProduct">
        <i class="bi bi-plus-lg"></i>
        <span>Thêm sản phẩm</span>
    </a>
</div>

<!-- Thanh tìm kiếm & Bộ lọc -->
<div class="product-filter-bar">
    <form method="GET" action="{{ route('admin.products.index') }}" id="adminSearchForm" style="display:contents;">
        <div class="search-input-box">
            <i class="bi bi-search"></i>
            <input type="text" name="q" id="productSearchInput" placeholder="Tìm kiếm theo tên, SKU..." value="{{ request('q') }}">
            @if(request('q'))
                <a href="{{ route('admin.products.index') }}" class="btn-clear-search" id="btnClearSearch" title="Xóa từ khóa tìm kiếm" style="display:flex;align-items:center;justify-content:center;text-decoration:none;">
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
            <tbody>
                @forelse ($products as $prod)
                    @php
                        $prodImg = str_starts_with($prod->image, 'http') ? $prod->image : asset(ltrim($prod->image, '/'));
                    @endphp
                    <tr>
                        <td style="text-align: center;">
                            <input type="checkbox" class="product-checkbox" value="{{ $prod->id }}">
                        </td>
                        <td>
                            <div class="customer-cell">
                                <img src="{{ $prodImg }}" class="stock-thumb" alt="{{ $prod->name }}" onerror="this.src='{{ asset('assets/images/logo.png') }}'">
                                <div class="customer-info-box">
                                    <div class="c-name" style="font-weight: 700;">{{ $prod->name }}</div>
                                    @if($prod->is_featured)
                                        <span class="stock-tag" style="background:#e0f2fe;color:#0369a1;font-size:10px;padding:2px 6px;border-radius:4px;">NỔI BẬT</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="order-code-text">{{ $prod->code }}</span>
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
                                <span style="font-weight: 600; color: #324a37;">{{ $prod->stock_quantity }}</span>
                            @endif
                        </td>
                        <td>{{ $prod->sold }}</td>
                        <td>⭐ {{ $prod->rating }}</td>
                        <td style="text-align: center;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="{{ route('products.show', $prod->id) }}" target="_blank" class="btn-action-view" title="Xem ngoài web">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button" class="btn-action-view" title="Chỉnh sửa">
                                    <i class="bi bi-pencil"></i>
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
        {{ $products->links() }}
    </div>
</div>

<!-- Modals -->
@include('admin.partials.add-product-modal')
@include('admin.partials.add-category-modal')
@include('admin.partials.add-variant-modal')
@include('admin.partials.advanced-filter-drawer')
@endsection

@push('scripts')
<script src="{{ asset('js/admin/products.js') }}"></script>
@endpush
