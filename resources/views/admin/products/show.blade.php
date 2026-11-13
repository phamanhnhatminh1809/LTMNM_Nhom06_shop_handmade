@extends('layouts.admin')

@section('title', 'Chi tiết sản phẩm — ' . $product->name)

@php
    $activeMenu = 'products';
    $mainImg = !empty($product->image)
        ? (str_starts_with($product->image, 'http') ? $product->image : asset(ltrim($product->image, '/')))
        : asset('images/logo.png');
@endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-box-seam"></i>
        <span>CHI TIẾT SẢN PHẨM</span>
    </div>
    <div class="top-bar-right" style="display: flex; align-items: center; gap: 10px;">
        <a href="{{ route('products.show', $product->id) }}" target="_blank" class="btn-secondary-outline">
            <i class="bi bi-box-arrow-up-right"></i>
            <span>Xem ngoài cửa hàng</span>
        </a>
        <a href="{{ route('admin.products.edit', $product) }}" class="btn-add-product" style="padding: 9px 16px;">
            <i class="bi bi-pencil"></i>
            <span>Chỉnh sửa</span>
        </a>
        <a href="{{ route('admin.products.index') }}" class="btn-secondary-outline">
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại</span>
        </a>
    </div>
</div>

<!-- Hàng Thẻ Chỉ Số Sản Phẩm -->
<div class="metrics-row" style="grid-template-columns: repeat(4, 1fr);">
    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-tag"></i>
        </div>
        <div class="metric-label">Giá bán niêm yết</div>
        <div class="metric-value">
            {{ number_format($product->price, 0, ',', '.') }}đ
            @if(!empty($product->old_price))
                <span style="font-size: 13px; color: var(--color-outline); text-decoration: line-through; font-weight: 600; margin-left: 6px;">
                    {{ number_format($product->old_price, 0, ',', '.') }}đ
                </span>
            @endif
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-boxes"></i>
        </div>
        <div class="metric-label">Số lượng tồn kho</div>
        <div class="metric-value">
            {{ $product->stock_quantity }}
            @if($product->stock_quantity <= 2)
                <span class="stock-tag danger" style="font-size: 11px; vertical-align: middle; margin-left: 6px;">SẮP HẾT</span>
            @elseif($product->stock_quantity <= 5)
                <span class="stock-tag warning" style="font-size: 11px; vertical-align: middle; margin-left: 6px;">THẤP</span>
            @endif
        </div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-bag-heart"></i>
        </div>
        <div class="metric-label">Tổng số lượng đã bán</div>
        <div class="metric-value">{{ $product->sold }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-star"></i>
        </div>
        <div class="metric-label">Điểm đánh giá trung bình</div>
        <div class="metric-value">⭐ {{ $product->rating }} / 5.0</div>
    </div>
</div>

<div class="admin-detail-grid">
    <!-- Cột trái: Mô tả, Bảo quản & Thông số -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-card-text"></i>
                    <span>{{ $product->name }}</span>
                </h3>
                @if($product->is_featured)
                    <span class="status-badge completed">⭐ Sản phẩm nổi bật</span>
                @endif
            </div>
            <div class="admin-card-body">
                <div style="margin-bottom: 20px;">
                    <h4 style="font-size: 13px; font-weight: 800; color: var(--color-outline); text-transform: uppercase; margin-bottom: 8px;">
                        Mô tả chi tiết sản phẩm
                    </h4>
                    <div style="font-size: 14px; line-height: 1.7; color: var(--color-on-surface); white-space: pre-line; background: #fff8f6; padding: 14px 16px; border-radius: 10px; border: 1px solid #f2e2dc;">
                        {{ $product->description ?: 'Chưa có mô tả chi tiết cho sản phẩm này.' }}
                    </div>
                </div>

                <div>
                    <h4 style="font-size: 13px; font-weight: 800; color: var(--color-outline); text-transform: uppercase; margin-bottom: 8px;">
                        Hướng dẫn sử dụng & Bảo quản
                    </h4>
                    <div style="font-size: 14px; line-height: 1.7; color: var(--color-on-surface-variant); white-space: pre-line; background: #fff8f6; padding: 14px 16px; border-radius: 10px; border: 1px solid #f2e2dc;">
                        {{ $product->instruction_preserve ?: 'Chưa có hướng dẫn bảo quản riêng.' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-sliders"></i>
                    <span>Thông số định danh & Quản trị</span>
                </h3>
            </div>
            <div class="admin-card-body">
                <div class="info-kv-list">
                    <div class="info-kv-row">
                        <span class="info-kv-label">Mã SKU</span>
                        <span class="info-kv-value"><span class="mono-code">{{ $product->code }}</span></span>
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">Đường dẫn tĩnh (Slug)</span>
                        <span class="info-kv-value"><span class="mono-code">{{ $product->slug }}</span></span>
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">Danh mục</span>
                        <span class="info-kv-value">{{ $product->category->name ?? 'Chưa phân loại' }}</span>
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">Ngày khởi tạo</span>
                        <span class="info-kv-value">{{ $product->created_at ? $product->created_at->format('d/m/Y H:i') : '—' }}</span>
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">Cập nhật lần cuối</span>
                        <span class="info-kv-value">{{ $product->updated_at ? $product->updated_at->format('d/m/Y H:i') : '—' }}</span>
                    </div>
                </div>

                <div class="admin-form-actions" style="justify-content: space-between;">
                    <a href="{{ route('admin.reviews.index', ['product_id' => $product->id]) }}" class="btn-secondary-outline">
                        <i class="bi bi-star"></i>
                        <span>Xem đánh giá của sản phẩm</span>
                    </a>

                    <button type="button"
                            class="btn-inline-action danger btn-trigger-delete-product"
                            data-name="{{ $product->name }}"
                            data-code="{{ $product->code }}"
                            data-category="{{ $product->category->name ?? 'Chưa phân loại' }}"
                            data-image="{{ $mainImg }}"
                            data-url="{{ route('admin.products.destroy', $product) }}"
                            style="padding: 9px 16px;">
                        <i class="bi bi-trash"></i>
                        <span>Xóa sản phẩm này</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cột phải: Hình ảnh đại diện & Bộ sưu tập -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-image"></i>
                    <span>Hình ảnh sản phẩm</span>
                </h3>
            </div>
            <div class="admin-card-body">
                @php
                    $mainImg = !empty($product->image)
                        ? (str_starts_with($product->image, 'http') ? $product->image : asset(ltrim($product->image, '/')))
                        : asset('assets/images/placeholder.svg');
                @endphp
                <div style="margin-bottom: 14px;">
                    <img id="detailMainImage"
                         src="{{ $mainImg }}"
                         alt="{{ $product->name }}"
                         style="width: 100%; height: 320px; object-fit: cover; border-radius: 12px; border: 1px solid #ebdcd7;"
                         onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
                </div>

                @if(!empty($product->gallery) && is_array($product->gallery) && count($product->gallery) > 0)
                    <div class="form-help-text" style="margin-bottom: 8px; font-weight: 700;">
                        Bộ sưu tập ảnh phụ ({{ count($product->gallery) }} ảnh):
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <img src="{{ $mainImg }}"
                             alt="Main"
                             onclick="document.getElementById('detailMainImage').src = this.src"
                             style="width: 68px; height: 68px; object-fit: cover; border-radius: 8px; border: 2px solid var(--color-primary); cursor: pointer;">
                        @foreach($product->gallery as $galImg)
                            @php
                                $galUrl = str_starts_with($galImg, 'http') ? $galImg : asset(ltrim($galImg, '/'));
                            @endphp
                            <img src="{{ $galUrl }}"
                                 alt="Gallery"
                                 onclick="document.getElementById('detailMainImage').src = this.src"
                                 style="width: 68px; height: 68px; object-fit: cover; border-radius: 8px; border: 1px solid #ebdcd7; cursor: pointer;"
                                 onerror="this.style.display='none'">
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Xác nhận Xóa -->
@include('admin.partials.delete-product-modal')
@endsection

@push('scripts')
<script src="{{ asset('js/admin/products.js') }}"></script>
@endpush
