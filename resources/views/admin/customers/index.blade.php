@extends('layouts.admin')

@section('title', 'Quản lý khách hàng')

@php $activeMenu = 'customers'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-people"></i>
        <span>QUẢN LÝ KHÁCH HÀNG</span>
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

<!-- Hàng Thẻ Chỉ số Khách hàng -->
<div class="metrics-row" style="grid-template-columns: repeat(3, 1fr);">
    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-people"></i>
        </div>
        <div class="metric-label">Tổng tài khoản khách hàng</div>
        <div class="metric-value">{{ $statusCounts['all'] ?? 0 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-person-check"></i>
        </div>
        <div class="metric-label">Đang hoạt động</div>
        <div class="metric-value" style="color: var(--color-success);">{{ $statusCounts['active'] ?? 0 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box">
            <i class="bi bi-person-lock"></i>
        </div>
        <div class="metric-label">Tài khoản đã khóa</div>
        <div class="metric-value" style="color: var(--color-danger);">{{ $statusCounts['locked'] ?? 0 }}</div>
    </div>
</div>

<!-- Thanh Tìm kiếm & Lọc Trạng thái -->
<div class="product-filter-bar" style="flex-wrap: wrap; gap: 12px;">
    <div class="status-tabs-bar" style="margin-bottom: 0;">
        <a href="{{ route('admin.customers.index', $keyword ? ['q' => $keyword] : []) }}"
           class="status-tab-pill {{ $status === '' ? 'active' : '' }}">
            <span>Tất cả</span>
            <span class="tab-count-badge">{{ $statusCounts['all'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.customers.index', array_filter(['q' => $keyword, 'status' => 'active'])) }}"
           class="status-tab-pill {{ $status === 'active' ? 'active' : '' }}">
            <i class="bi bi-check-circle"></i>
            <span>Đang hoạt động</span>
            <span class="tab-count-badge">{{ $statusCounts['active'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.customers.index', array_filter(['q' => $keyword, 'status' => 'locked'])) }}"
           class="status-tab-pill {{ $status === 'locked' ? 'active' : '' }}">
            <i class="bi bi-lock"></i>
            <span>Đã khóa</span>
            <span class="tab-count-badge">{{ $statusCounts['locked'] ?? 0 }}</span>
        </a>
    </div>

    <form method="GET" action="{{ route('admin.customers.index') }}" style="display: flex; align-items: center; gap: 8px; margin-left: auto; min-width: 320px;">
        @if($status)
            <input type="hidden" name="status" value="{{ $status }}">
        @endif
        <div class="search-input-box" style="flex: 1;">
            <i class="bi bi-search"></i>
            <input type="text"
                   name="q"
                   value="{{ $keyword }}"
                   placeholder="Tìm theo tên, email, SĐT, username...">
            @if($keyword)
                <a href="{{ route('admin.customers.index', $status ? ['status' => $status] : []) }}"
                   class="btn-clear-search"
                   style="display:flex;align-items:center;justify-content:center;text-decoration:none;"
                   title="Xóa từ khóa">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
        <button type="submit" class="btn-inline-action primary" style="padding: 9px 14px;">
            <i class="bi bi-search"></i>
            <span>Tìm</span>
        </button>
    </form>
</div>

<!-- Bảng Danh sách Khách hàng -->
<div class="table-card">
    <div class="table-header-flex">
        <div>
            <h3 style="font-size: 15px; font-weight: 800; color: var(--color-on-surface); margin-bottom: 3px;">
                Danh sách tài khoản khách hàng ({{ $customers->total() }})
            </h3>
            <p style="font-size: 12px; color: var(--color-outline);">
                Tài khoản bị khóa sẽ bị chặn đăng nhập ngay lập tức trên hệ thống.
            </p>
        </div>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th style="width: 65px;">ID</th>
                    <th>Khách hàng</th>
                    <th>Tên đăng nhập</th>
                    <th>Email & Điện thoại</th>
                    <th style="text-align: center;">Đơn đã đặt</th>
                    <th>Trạng thái</th>
                    <th>Ngày đăng ký</th>
                    <th style="text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>
                            <span class="order-code-text">#{{ $customer->id }}</span>
                        </td>
                        <td>
                            <div class="customer-cell">
                                <div class="customer-avatar-abbr">
                                    {{ mb_strtoupper(mb_substr($customer->full_name, 0, 2)) }}
                                </div>
                                <div class="customer-info-box">
                                    <a href="{{ route('admin.customers.show', $customer) }}"
                                       class="c-name"
                                       style="font-weight: 700; text-decoration: none; color: var(--color-on-surface);">
                                        {{ $customer->full_name }}
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="mono-code">{{ $customer->username }}</span>
                        </td>
                        <td>
                            <div style="font-size: 13px; font-weight: 600; color: var(--color-on-surface);">
                                {{ $customer->email }}
                            </div>
                            <div style="font-size: 12px; color: var(--color-outline); font-family: var(--font-mono);">
                                {{ $customer->phone ?? 'Chưa cập nhật SĐT' }}
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <span style="font-weight: 800; color: var(--color-primary);">
                                {{ $customer->orders_count }} đơn
                            </span>
                        </td>
                        <td>
                            @if($customer->is_active)
                                <span class="status-badge active-user">
                                    <i class="bi bi-check-circle"></i> Đang hoạt động
                                </span>
                            @else
                                <span class="status-badge locked">
                                    <i class="bi bi-lock-fill"></i> Đã khóa
                                </span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 13px; color: var(--color-on-surface-variant);">
                                {{ $customer->created_at ? $customer->created_at->format('d/m/Y') : '—' }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                <a href="{{ route('admin.customers.show', $customer) }}"
                                   class="btn-action-view"
                                   title="Xem chi tiết khách hàng">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @if($customer->is_active)
                                    <form action="{{ route('admin.customers.lock', $customer) }}"
                                          method="POST"
                                          style="display: inline;"
                                          onsubmit="return confirm('Xác nhận khóa tài khoản khách hàng «{{ $customer->full_name }}»? Tài khoản này sẽ không thể đăng nhập.')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-inline-action danger" title="Khóa tài khoản">
                                            <i class="bi bi-lock"></i>
                                            <span>Khóa</span>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.customers.unlock', $customer) }}"
                                          method="POST"
                                          style="display: inline;"
                                          onsubmit="return confirm('Mở khóa tài khoản khách hàng «{{ $customer->full_name }}»?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-inline-action success" title="Mở khóa tài khoản">
                                            <i class="bi bi-unlock"></i>
                                            <span>Mở khóa</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 32px; color: var(--color-outline);">
                            Không tìm thấy tài khoản khách hàng nào phù hợp.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="padding: 16px 24px;">
        {{ $customers->links('admin.partials.pagination') }}
    </div>
</div>
@endsection
