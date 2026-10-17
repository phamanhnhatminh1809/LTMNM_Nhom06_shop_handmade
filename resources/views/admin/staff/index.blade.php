@extends('layouts.admin')

@section('title', 'Quản lý nhân viên & Phân quyền')

@php
    $activeMenu = ($roleFilter ?? 'staff_only') === 'all' ? 'permissions' : 'staff';
@endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-shield-lock"></i>
        <span>QUẢN LÝ NHÂN VIÊN & PHÂN QUYỀN</span>
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
        Quản lý đội ngũ vận hành và phân quyền truy cập<br>theo từng bộ phận chuyên trách
    </h2>
    <a href="{{ route('admin.staff.create') }}" class="btn-add-product">
        <i class="bi bi-person-plus"></i>
        <span>Tạo tài khoản nhân viên</span>
    </a>
</div>

<!-- 4 Thẻ Tóm tắt Quy tắc Phân quyền -->
<div class="role-summary-grid">
    <div class="role-summary-card">
        <div class="role-summary-header">
            <span class="role-badge admin">
                <i class="bi bi-shield-fill-check"></i> Quản trị viên
            </span>
            <span class="role-summary-count">{{ $roleCounts['admin'] ?? 0 }}</span>
        </div>
        <div class="role-summary-desc">
            Toàn quyền quản trị hệ thống: Kho hàng, Đơn hàng, Đánh giá, Khách hàng và Phân quyền nhân sự.
        </div>
    </div>

    <div class="role-summary-card">
        <div class="role-summary-header">
            <span class="role-badge warehouse_staff">
                <i class="bi bi-box-seam"></i> Nhân viên kho
            </span>
            <span class="role-summary-count">{{ $roleCounts['warehouse_staff'] ?? 0 }}</span>
        </div>
        <div class="role-summary-desc">
            Chuyên trách phân hệ Kho: Quản lý <strong>Loại sản phẩm</strong> và <strong>Sản phẩm</strong> (Thêm, sửa, xóa, tồn kho).
        </div>
    </div>

    <div class="role-summary-card">
        <div class="role-summary-header">
            <span class="role-badge sales_staff">
                <i class="bi bi-receipt-cutoff"></i> NV Bán hàng
            </span>
            <span class="role-summary-count">{{ $roleCounts['sales_staff'] ?? 0 }}</span>
        </div>
        <div class="role-summary-desc">
            Chuyên trách Kinh doanh: Duyệt & cập nhật trạng thái <strong>Đơn hàng</strong>, quản lý <strong>Đánh giá sản phẩm</strong>.
        </div>
    </div>

    <div class="role-summary-card">
        <div class="role-summary-header">
            <span class="role-badge customer">
                <i class="bi bi-person"></i> Khách hàng
            </span>
            <span class="role-summary-count">{{ $roleCounts['customer'] ?? 0 }}</span>
        </div>
        <div class="role-summary-desc">
            Tài khoản mua sắm ngoài cửa hàng, không có quyền truy cập khu vực quản trị <code>/admin</code>.
        </div>
    </div>
</div>

<!-- Thanh Tabs Lọc Theo Nhóm Quyền & Tìm kiếm -->
<div class="product-filter-bar" style="flex-wrap: wrap; gap: 12px;">
    <div class="status-tabs-bar" style="margin-bottom: 0;">
        <a href="{{ route('admin.staff.index', array_filter(['q' => $keyword, 'role' => 'staff_only'])) }}"
           class="status-tab-pill {{ $roleFilter === 'staff_only' ? 'active' : '' }}">
            <span>Tất cả Nhân sự</span>
            <span class="tab-count-badge">{{ $roleCounts['staff_only'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.staff.index', array_filter(['q' => $keyword, 'role' => 'admin'])) }}"
           class="status-tab-pill {{ $roleFilter === 'admin' ? 'active' : '' }}">
            <span>Quản trị viên</span>
            <span class="tab-count-badge">{{ $roleCounts['admin'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.staff.index', array_filter(['q' => $keyword, 'role' => 'warehouse_staff'])) }}"
           class="status-tab-pill {{ $roleFilter === 'warehouse_staff' ? 'active' : '' }}">
            <span>Nhân viên kho</span>
            <span class="tab-count-badge">{{ $roleCounts['warehouse_staff'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.staff.index', array_filter(['q' => $keyword, 'role' => 'sales_staff'])) }}"
           class="status-tab-pill {{ $roleFilter === 'sales_staff' ? 'active' : '' }}">
            <span>NV Bán hàng</span>
            <span class="tab-count-badge">{{ $roleCounts['sales_staff'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.staff.index', array_filter(['q' => $keyword, 'role' => 'all'])) }}"
           class="status-tab-pill {{ $roleFilter === 'all' ? 'active' : '' }}">
            <span>Mọi tài khoản (Kể cả KH)</span>
            <span class="tab-count-badge">{{ $roleCounts['all'] ?? 0 }}</span>
        </a>
    </div>

    <form method="GET" action="{{ route('admin.staff.index') }}" style="display: flex; align-items: center; gap: 8px; margin-left: auto; min-width: 300px;">
        <input type="hidden" name="role" value="{{ $roleFilter }}">
        <div class="search-input-box" style="flex: 1;">
            <i class="bi bi-search"></i>
            <input type="text"
                   name="q"
                   value="{{ $keyword }}"
                   placeholder="Tìm tên, username, email, SĐT...">
            @if($keyword)
                <a href="{{ route('admin.staff.index', ['role' => $roleFilter]) }}"
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

<!-- Bảng Danh sách Nhân viên & Cấp/Đổi quyền trực tiếp -->
<div class="table-card">
    <div class="table-header-flex">
        <div>
            <h3 style="font-size: 15px; font-weight: 800; color: var(--color-on-surface); margin-bottom: 3px;">
                Danh sách tài khoản & Phân quyền ({{ $users->total() }})
            </h3>
            <p style="font-size: 12px; color: var(--color-outline);">
                Chọn vai trò mới trong cột <strong>Cấp / Đổi quyền</strong> và nhấn <strong>Lưu quyền</strong> để áp dụng ngay lập tức.
            </p>
        </div>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Họ tên nhân sự</th>
                    <th>Username</th>
                    <th>Email & SĐT</th>
                    <th>Quyền hiện tại</th>
                    <th>Cấp / Đổi quyền</th>
                    <th>Trạng thái</th>
                    <th style="text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    @php
                        $roleName = $u->role->name ?? 'customer';
                        $isCurrentUser = ($u->id === Auth::id());
                    @endphp
                    <tr>
                        <td>
                            <span class="order-code-text">#{{ $u->id }}</span>
                        </td>
                        <td>
                            <div class="customer-cell">
                                <div class="customer-avatar-abbr">
                                    {{ mb_strtoupper(mb_substr($u->full_name, 0, 2)) }}
                                </div>
                                <div class="customer-info-box">
                                    <div class="c-name" style="display: flex; align-items: center; gap: 6px;">
                                        <span>{{ $u->full_name }}</span>
                                        @if($isCurrentUser)
                                            <span class="stock-tag" style="background:#ffdad2;color:#792a16;font-size:10px;padding:1px 6px;border-radius:4px;">BẠN</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="mono-code">{{ $u->username }}</span>
                        </td>
                        <td>
                            <div style="font-size: 13px; font-weight: 600; color: var(--color-on-surface);">
                                {{ $u->email }}
                            </div>
                            <div style="font-size: 12px; color: var(--color-outline); font-family: var(--font-mono);">
                                {{ $u->phone ?? '—' }}
                            </div>
                        </td>
                        <td>
                            <span class="role-badge {{ $roleName }}">
                                @if($roleName === 'admin')
                                    <i class="bi bi-shield-fill-check"></i> Quản trị viên
                                @elseif($roleName === 'warehouse_staff')
                                    <i class="bi bi-box-seam"></i> Nhân viên kho
                                @elseif($roleName === 'sales_staff')
                                    <i class="bi bi-receipt-cutoff"></i> NV Bán hàng
                                @else
                                    <i class="bi bi-person"></i> Khách hàng
                                @endif
                            </span>
                        </td>
                        <td>
                            <form action="{{ route('admin.staff.updateRole', $u) }}"
                                  method="POST"
                                  style="display: inline-flex; align-items: center; gap: 6px;">
                                @csrf
                                @method('PATCH')
                                <select name="role_id"
                                        class="modal-input"
                                        style="padding: 6px 10px; font-size: 12.5px; width: auto; min-width: 190px;"
                                        {{ $isCurrentUser ? 'disabled title=Không_thể_tự_hạ_quyền_chính_mình' : '' }}>
                                    @foreach($roles as $r)
                                        <option value="{{ $r->id }}" {{ $u->role_id === $r->id ? 'selected' : '' }}>
                                            {{ $r->label }}
                                        </option>
                                    @endforeach
                                </select>
                                @if(!$isCurrentUser)
                                    <button type="submit" class="btn-inline-action primary" title="Lưu vai trò mới">
                                        <i class="bi bi-check2"></i>
                                        <span>Lưu quyền</span>
                                    </button>
                                @endif
                            </form>
                        </td>
                        <td>
                            @if($u->is_active)
                                <span class="status-badge active-user">
                                    <i class="bi bi-check-circle"></i> Hoạt động
                                </span>
                            @else
                                <span class="status-badge locked">
                                    <i class="bi bi-lock-fill"></i> Đã khóa
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if(!$isCurrentUser)
                                <form action="{{ route('admin.staff.toggleStatus', $u) }}"
                                      method="POST"
                                      style="display: inline;"
                                      onsubmit="return confirm('Xác nhận đổi trạng thái khóa/mở khóa tài khoản «{{ $u->full_name }}»?')">
                                    @csrf
                                    @method('PATCH')
                                    @if($u->is_active)
                                        <button type="submit" class="btn-inline-action danger" title="Khóa tài khoản">
                                            <i class="bi bi-lock"></i>
                                            <span>Khóa</span>
                                        </button>
                                    @else
                                        <button type="submit" class="btn-inline-action success" title="Mở khóa tài khoản">
                                            <i class="bi bi-unlock"></i>
                                            <span>Mở khóa</span>
                                        </button>
                                    @endif
                                </form>
                            @else
                                <span style="font-size: 12px; color: var(--color-outline);">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 32px; color: var(--color-outline);">
                            Không tìm thấy tài khoản nào phù hợp với bộ lọc.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="padding: 16px 24px;">
        {{ $users->links('admin.partials.pagination') }}
    </div>
</div>
@endsection
