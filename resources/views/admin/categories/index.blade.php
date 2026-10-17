@extends('layouts.admin')

@section('title', 'Quản lý loại sản phẩm')

@php $activeMenu = 'categories'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-tags"></i>
        <span>QUẢN LÝ LOẠI SẢN PHẨM</span>
    </div>
    <div class="top-bar-right">
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

<!-- Welcome Card -->
<div class="product-welcome-card">
    <div>
        <h2 class="product-welcome-title">
            Phân loại & tổ chức các dòng sản phẩm thủ công<br>trong cửa hàng Handmade House
        </h2>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn-add-product">
        <i class="bi bi-plus-lg"></i>
        <span>Thêm loại sản phẩm</span>
    </a>
</div>

<!-- Thanh tìm kiếm & Thống kê nhanh -->
<div class="product-filter-bar">
    <div class="search-input-box">
        <i class="bi bi-search"></i>
        <input type="text" id="categoryTableSearch" placeholder="Tìm nhanh theo tên danh mục hoặc mã slug...">
    </div>

    <div class="filter-actions-right">
        <span class="status-tab-pill" style="cursor: default;">
            <i class="bi bi-folder2-open"></i>
            <span>Tổng danh mục</span>
            <span class="tab-count-badge">{{ $categories->count() }}</span>
        </span>
        <span class="status-tab-pill" style="cursor: default;">
            <i class="bi bi-bag-check"></i>
            <span>Tổng sản phẩm</span>
            <span class="tab-count-badge">{{ $categories->sum('products_count') }}</span>
        </span>
    </div>
</div>

<!-- Bảng Danh sách Loại sản phẩm -->
<div class="table-card">
    <div class="table-header-flex">
        <div>
            <h3 style="font-size: 15px; font-weight: 800; color: var(--color-on-surface); margin-bottom: 3px;">
                Danh sách loại sản phẩm ({{ $categories->count() }})
            </h3>
            <p style="font-size: 12px; color: var(--color-outline);">
                Lưu ý: Hệ thống tự động chặn xóa đối với những danh mục đang có sản phẩm liên kết.
            </p>
        </div>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-data-table" id="categoriesDataTable">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th style="width: 90px; text-align: center;">Biểu tượng</th>
                    <th>Tên danh mục</th>
                    <th>Mã định danh (Slug)</th>
                    <th>Số sản phẩm</th>
                    <th>Ngày tạo</th>
                    <th style="text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr class="category-row">
                        <td>
                            <span class="order-code-text">#{{ $category->id }}</span>
                        </td>
                        <td style="text-align: center;">
                            <div class="metric-icon-box" style="margin: 0 auto; width: 38px; height: 38px; border-radius: 10px; font-size: 17px;">
                                @if(!empty($category->icon) && str_starts_with($category->icon, 'bi-'))
                                    <i class="bi {{ $category->icon }}"></i>
                                @elseif(!empty($category->icon))
                                    <span>{{ $category->icon }}</span>
                                @else
                                    <i class="bi bi-tag"></i>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 800; color: var(--color-on-surface); font-size: 14px;">
                                {{ $category->name }}
                            </div>
                        </td>
                        <td>
                            <span class="mono-code">{{ $category->slug }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.products.index', ['category' => $category->slug]) }}"
                               style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                @if($category->products_count > 0)
                                    <span class="status-badge completed">
                                        <i class="bi bi-box-seam"></i> {{ $category->products_count }} sản phẩm
                                    </span>
                                @else
                                    <span class="status-badge pending">
                                        <i class="bi bi-inbox"></i> 0 sản phẩm
                                    </span>
                                @endif
                            </a>
                        </td>
                        <td>
                            <span style="color: var(--color-on-surface-variant); font-weight: 600;">
                                {{ $category->created_at ? $category->created_at->format('d/m/Y') : '—' }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                <a href="{{ route('admin.products.index', ['category' => $category->slug]) }}"
                                   class="btn-action-view"
                                   title="Xem sản phẩm thuộc danh mục này">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.categories.edit', $category) }}"
                                   class="btn-action-view"
                                   title="Chỉnh sửa danh mục">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category) }}"
                                      method="POST"
                                      style="display: inline;"
                                      onsubmit="return confirm('Xác nhận xóa danh mục «{{ $category->name }}»?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn-action-view danger"
                                            title="Xóa danh mục">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 32px; color: var(--color-outline);">
                            Chưa có loại sản phẩm nào. Hãy nhấn <strong>Thêm loại sản phẩm</strong> để bắt đầu.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('categoryTableSearch');
    const rows = document.querySelectorAll('#categoriesDataTable tbody .category-row');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }
});
</script>
@endpush
