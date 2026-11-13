@extends('layouts.admin')

@section('title', 'Quản lý danh mục sản phẩm')

@php $activeMenu = 'categories'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-tags"></i>
        <span>Quản lý danh mục sản phẩm</span>
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
    <h2 class="product-welcome-title">
        <i class="bi bi-tags"></i>
        <span>Quản lý danh mục sản phẩm</span>
    </h2>
    <a href="{{ route('admin.categories.create') }}" class="btn-add-product">
        <i class="bi bi-plus-lg"></i>
        <span>Thêm danh mục</span>
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

<!-- Bảng Danh sách Danh mục sản phẩm -->
<div class="table-card">
    <div class="table-header-flex">
        <div>
            <h3 style="font-size: 15px; font-weight: 800; color: var(--color-on-surface); margin-bottom: 3px;">
                Danh sách danh mục sản phẩm ({{ $categories->count() }})
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
                            <div class="metric-icon-box" style="margin: 0 auto; width: 38px; height: 38px; border-radius: 10px; font-size: 17px; background: #fff5f2; color: var(--color-primary);">
                                <i class="bi {{ $category->display_icon }}"></i>
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
                            Chưa có danh mục nào. Hãy nhấn <strong>Thêm danh mục</strong> để bắt đầu.
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
