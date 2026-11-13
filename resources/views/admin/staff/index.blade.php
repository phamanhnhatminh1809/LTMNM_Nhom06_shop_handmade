@extends('layouts.admin')

@section('title', 'Quản lý nhân viên')

@php
    $activeMenu = 'staff';
@endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-people"></i>
        <span>QUẢN LÝ NHÂN VIÊN</span>
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
        <h2 class="product-welcome-title" style="margin-bottom: 4px;">
            Quản lý nhân viên
        </h2>
    </div>
    <a href="{{ route('admin.staff.create') }}" class="btn-add-product">
        <i class="bi bi-person-plus"></i>
        <span>Tạo tài khoản nhân viên</span>
    </a>
</div>

<!-- 4 Thẻ KPI Chỉ số Nhân sự (Gọn gàng, không có mô tả rườm rà) -->
<div class="metrics-row" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="metric-card">
        <div class="metric-icon-box" style="background: #ffdad2; color: #792a16;">
            <i class="bi bi-shield-fill-check"></i>
        </div>
        <div class="metric-label">Quản trị viên</div>
        <div class="metric-value">{{ $roleCounts['admin'] ?? 0 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box" style="background: #fff1ec; color: #98412b;">
            <i class="bi bi-box-seam"></i>
        </div>
        <div class="metric-label">Nhân viên kho</div>
        <div class="metric-value">{{ $roleCounts['warehouse_staff'] ?? 0 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box" style="background: #e8f5e9; color: #2e7d32;">
            <i class="bi bi-receipt-cutoff"></i>
        </div>
        <div class="metric-label">NV Bán hàng</div>
        <div class="metric-value">{{ $roleCounts['sales_staff'] ?? 0 }}</div>
    </div>

    <div class="metric-card">
        <div class="metric-icon-box" style="background: #eef2ff; color: #4338ca;">
            <i class="bi bi-people"></i>
        </div>
        <div class="metric-label">Khách hàng</div>
        <div class="metric-value">{{ $roleCounts['customer'] ?? 0 }}</div>
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

<!-- Bảng Danh sách Nhân viên (Cấu trúc cột tinh gọn, rõ ràng) -->
<div class="table-card">
    <div class="table-header-flex">
        <div>
            <h3 style="font-size: 15px; font-weight: 800; color: var(--color-on-surface); margin-bottom: 3px;">
                Danh sách nhân sự ({{ $users->total() }})
            </h3>
            <p style="font-size: 12px; color: var(--color-outline);">
                Bấm nút <strong>Sửa</strong> để cập nhật thông tin, thay đổi vai trò hoặc khóa/mở khóa tài khoản.
            </p>
        </div>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nhân sự</th>
                    <th>Email & SĐT</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th style="text-align: center; width: 120px;">Thao tác</th>
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
                                        <a href="{{ route('admin.staff.edit', $u) }}" style="text-decoration: none; color: inherit; font-weight: 700;">
                                            {{ $u->full_name }}
                                        </a>
                                        @if($isCurrentUser)
                                            <span class="stock-tag" style="background:#ffdad2;color:#792a16;font-size:10px;padding:1px 6px;border-radius:4px;">BẠN</span>
                                        @endif
                                    </div>
                                    <div class="mono-code" style="font-size: 12px; color: var(--color-outline);">
                                        {{ '@' . $u->username }}
                                    </div>
                                </div>
                            </div>
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
                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                <a href="{{ route('admin.staff.edit', $u) }}"
                                   class="btn-action-view"
                                   title="Chỉnh sửa chi tiết & phân quyền">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                @if(!$isCurrentUser)
                                    <button type="button"
                                            class="btn-action-view danger"
                                            title="Xóa tài khoản nhân viên"
                                            onclick="openDeleteStaffModal({{ $u->id }}, '{{ addslashes($u->full_name) }}', '{{ route('admin.staff.destroy', $u) }}')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                @else
                                    <span style="font-size: 12px; color: var(--color-outline); width: 28px; text-align: center;">—</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 32px; color: var(--color-outline);">
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

<!-- Modal Xác nhận Xóa Nhân viên -->
<div class="modal-overlay" id="deleteStaffModal">
    <div class="modal-box" style="max-width: 420px; text-align: center; padding: 28px 24px;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: #fee2e2; color: #ba1a1a; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 16px;">
            <i class="bi bi-trash3-fill"></i>
        </div>
        <h3 style="font-size: 18px; font-weight: 800; color: var(--color-on-surface); margin: 0 0 8px;">
            Xóa tài khoản nhân viên?
        </h3>
        <p style="font-size: 13.5px; color: var(--color-on-surface-variant); margin: 0 0 20px; line-height: 1.5;" id="deleteStaffMessage">
            Bạn có chắc chắn muốn xóa tài khoản này khỏi hệ thống? Hành động này không thể hoàn tác.
        </p>

        <form id="deleteStaffForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button type="button" class="btn-cancel-modal" onclick="closeDeleteStaffModal()" style="padding: 10px 20px; border-radius: 8px; border: 1px solid #d1d5db; background: #fff; cursor: pointer; font-weight: 600; flex: 1;">
                    Hủy bỏ
                </button>
                <button type="submit" style="padding: 10px 20px; border-radius: 8px; border: none; background: #ba1a1a; color: #fff; cursor: pointer; font-weight: 700; flex: 1;">
                    Đồng ý xóa
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openDeleteStaffModal(id, name, deleteUrl) {
        const modal = document.getElementById('deleteStaffModal');
        const form = document.getElementById('deleteStaffForm');
        const msg = document.getElementById('deleteStaffMessage');

        form.action = deleteUrl;
        msg.innerHTML = 'Bạn có chắc chắn muốn xóa tài khoản <strong>«' + name + '»</strong>? Hành động này không thể hoàn tác.';
        modal.classList.add('active');
    }

    function closeDeleteStaffModal() {
        const modal = document.getElementById('deleteStaffModal');
        modal.classList.remove('active');
    }

    // Đóng khi click ngoài modal box
    document.getElementById('deleteStaffModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteStaffModal();
        }
    });
</script>
@endpush
@endsection
