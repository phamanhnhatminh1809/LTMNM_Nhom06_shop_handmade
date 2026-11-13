@extends('layouts.admin')

@section('title', 'Quản lý đánh giá sản phẩm')

@php $activeMenu = 'reviews'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-star"></i>
        <span>Quản lý đánh giá sản phẩm</span>
    </div>
    <div class="top-bar-right">
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

<!-- Banner khi đang lọc theo một sản phẩm cụ thể -->
@if($selectedProduct)
    <div class="admin-alert info" style="align-items: center;">
        <div class="admin-alert-content" style="align-items: center;">
            <i class="bi bi-funnel-fill"></i>
            <div>
                Đang hiển thị đánh giá của sản phẩm:
                <strong>{{ $selectedProduct->name }}</strong>
                <span class="mono-code" style="margin: 0 6px;">{{ $selectedProduct->code }}</span>
                — Điểm trung bình hiện tại: <strong>⭐ {{ $selectedProduct->rating }} / 5.0</strong>
            </div>
        </div>
        <a href="{{ route('admin.reviews.index') }}" class="btn-secondary-outline" style="padding: 6px 12px; font-size: 12px;">
            <i class="bi bi-x-lg"></i>
            <span>Xem tất cả sản phẩm</span>
        </a>
    </div>
@endif

<!-- Thanh Tìm kiếm & Lọc theo Sản phẩm -->
<div class="product-filter-bar" style="flex-wrap: wrap; gap: 12px;">
    <form method="GET" action="{{ route('admin.reviews.index') }}" id="reviewFilterForm" style="display: contents;">
        @if($rating)
            <input type="hidden" name="rating" value="{{ $rating }}">
        @endif

        <div class="search-input-box" style="flex: 1; min-width: 240px;">
            <i class="bi bi-search"></i>
            <input type="text"
                   name="q"
                   value="{{ $keyword }}"
                   placeholder="Tìm kiếm theo nội dung nhận xét, tên khách hàng, mã SP...">
            @if($keyword)
                <a href="{{ route('admin.reviews.index', array_filter(['product_id' => $productId, 'rating' => $rating])) }}"
                   class="btn-clear-search"
                   style="display:flex;align-items:center;justify-content:center;text-decoration:none;"
                   title="Xóa từ khóa">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>

        <div class="filter-actions-right" style="flex-wrap: wrap;">
            <div class="filter-dropdown-wrapper">
                <select name="product_id"
                        class="filter-btn-select"
                        onchange="document.getElementById('reviewFilterForm').submit()"
                        style="border:none;background:transparent;outline:none;font-size:inherit;font-family:inherit;padding:8px 12px;cursor:pointer;max-width:280px;">
                    <option value="">Tất cả sản phẩm</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}" {{ $productId === $prod->id ? 'selected' : '' }}>
                            [{{ $prod->code }}] {{ $prod->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-inline-action primary" style="padding: 9px 15px;">
                <i class="bi bi-search"></i>
                <span>Lọc</span>
            </button>

            @if($keyword || $productId || $rating)
                <a href="{{ route('admin.reviews.index') }}" class="btn-secondary-outline" style="padding: 8px 14px;">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Đặt lại</span>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Thanh Lọc Nhanh Theo Số Sao -->
<div class="status-tabs-bar">
    <a href="{{ route('admin.reviews.index', array_filter(['product_id' => $productId, 'q' => $keyword])) }}"
       class="status-tab-pill {{ empty($rating) ? 'active' : '' }}">
        <span>Tất cả mức sao</span>
        <span class="tab-count-badge">{{ $ratingCounts['all'] ?? 0 }}</span>
    </a>
    @for($star = 5; $star >= 1; $star--)
        <a href="{{ route('admin.reviews.index', array_filter(['product_id' => $productId, 'q' => $keyword, 'rating' => $star])) }}"
           class="status-tab-pill {{ (int) $rating === $star ? 'active' : '' }}">
            <span>{{ $star }} ⭐</span>
            <span class="tab-count-badge">{{ $ratingCounts[$star] ?? 0 }}</span>
        </a>
    @endfor
</div>

<!-- Bảng Danh sách Đánh giá -->
<div class="table-card">
    <div class="table-header-flex">
        <div>
            <h3 style="font-size: 15px; font-weight: 800; color: var(--color-on-surface); margin-bottom: 3px;">
                Danh sách đánh giá từ khách hàng ({{ $reviews->total() }})
            </h3>
            <p style="font-size: 12px; color: var(--color-outline);">
                Khi xóa đánh giá không hợp lệ, hệ thống sẽ tự động tính toán lại điểm trung bình của sản phẩm tương ứng.
            </p>
        </div>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>Sản phẩm được đánh giá</th>
                    <th>Khách hàng</th>
                    <th>Đơn hàng</th>
                    <th>Mức đánh giá</th>
                    <th>Nội dung nhận xét</th>
                    <th>Ngày gửi</th>
                    <th style="text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    @php
                        $prod = $review->product;
                        $prodImg = ($prod && !empty($prod->image))
                            ? (str_starts_with($prod->image, 'http') ? $prod->image : asset(ltrim($prod->image, '/')))
                            : asset('assets/images/placeholder.svg');
                    @endphp
                    <tr>
                        <td style="min-width: 240px;">
                            @if($prod)
                                <div class="customer-cell">
                                    <img src="{{ $prodImg }}" class="stock-thumb" alt="{{ $prod->name }}" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
                                    <div class="customer-info-box">
                                        <a href="{{ route('admin.reviews.index', ['product_id' => $prod->id]) }}"
                                           class="c-name"
                                           style="font-weight: 700; text-decoration: none; color: var(--color-on-surface);"
                                           title="Chỉ xem đánh giá của sản phẩm này">
                                            {{ $prod->name }}
                                        </a>
                                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                            <span class="mono-code" style="font-size: 11px; padding: 1px 6px;">{{ $prod->code }}</span>
                                            <span style="font-size: 11.5px; color: var(--color-outline); font-weight: 700;">TB: ⭐ {{ $prod->rating }}</span>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span style="color: var(--color-outline); font-style: italic;">(Sản phẩm đã bị xóa)</span>
                            @endif
                        </td>
                        <td>
                            <div class="customer-cell">
                                <div class="customer-avatar-abbr">
                                    {{ mb_strtoupper(mb_substr($review->user->full_name ?? 'KH', 0, 2)) }}
                                </div>
                                <div class="customer-info-box">
                                    <div class="c-name">{{ $review->user->full_name ?? 'Khách hàng' }}</div>
                                    <div class="c-email">{{ $review->user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($review->orderItem && $review->orderItem->order)
                                <a href="{{ route('admin.orders.show', $review->orderItem->order) }}" style="text-decoration: none;">
                                    <span class="mono-code">#{{ $review->orderItem->order->code }}</span>
                                </a>
                            @else
                                <span style="color: var(--color-outline);">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="star-rating-group" title="{{ $review->rating }} / 5 sao">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star-fill {{ $i <= $review->rating ? '' : 'star-empty' }}"></i>
                                @endfor
                                <span style="margin-left: 5px; font-weight: 800; color: var(--color-on-surface); font-size: 12.5px;">
                                    {{ $review->rating }}/5
                                </span>
                            </div>
                        </td>
                        <td style="max-width: 320px;">
                            @if(!empty($review->comment))
                                <div style="font-size: 13.5px; color: var(--color-on-surface); line-height: 1.5;">
                                    {{ $review->comment }}
                                </div>
                            @else
                                <span style="font-size: 12.5px; color: var(--color-outline); font-style: italic;">
                                    (Không để lại lời nhắn)
                                </span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 13px; color: var(--color-on-surface-variant);">
                                {{ $review->created_at ? $review->created_at->format('d/m/Y H:i') : '—' }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <x-table-actions>
                                <x-table-action type="delete"
                                                :action="route('admin.reviews.destroy', $review)"
                                                method="DELETE"
                                                confirm="Xác nhận xóa đánh giá này? Điểm trung bình của sản phẩm sẽ được tự động tính lại."
                                                label="Xóa đánh giá"
                                                tooltip-align="end" />
                            </x-table-actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 32px; color: var(--color-outline);">
                            Không có đánh giá nào phù hợp.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="padding: 16px 24px;">
        {{ $reviews->links('admin.partials.pagination') }}
    </div>
</div>
@endsection
