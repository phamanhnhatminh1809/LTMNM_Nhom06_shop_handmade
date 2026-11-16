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
        <span>Quản lý nhân viên</span>
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

<!-- Welcome Card -->
<div class="product-welcome-card">
    <h2 class="product-welcome-title">
        <i class="bi bi-people"></i>
        <span>Quản lý nhân viên</span>
    </h2>
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
<div class="product-filter-bar is-inline">
    <div class="status-tabs-bar">
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

    <form method="GET" action="{{ route('admin.staff.index') }}" class="admin-toolbar-search">
        <input type="hidden" name="role" value="{{ $roleFilter }}">
        <div class="search-input-box">
            <i class="bi bi-search"></i>
            <input type="text"
                   name="q"
                   value="{{ $keyword }}"
                   placeholder="Tìm tên, username, email, SĐT...">
            @if($keyword)
                <a href="{{ route('admin.staff.index', ['role' => $roleFilter]) }}"
                   class="btn-clear-search show"
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
                    <th>Email và số điện thoại</th>
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
                            <x-table-actions>
                                <x-table-action type="edit"
                                                :href="route('admin.staff.edit', $u)"
                                                label="Chỉnh sửa và phân quyền"
                                                :tooltip-align="$isCurrentUser ? 'end' : 'auto'" />

                                @if(!$isCurrentUser)
                                    <x-table-action type="delete"
                                                    label="Xóa tài khoản nhân viên"
                                                    tooltip-align="end"
                                                    onclick="openDeleteStaffModal({{ $u->id }}, {{ json_encode($u->full_name, JSON_UNESCAPED_UNICODE) }}, '{{ route('admin.staff.destroy', $u) }}')" />
                                @else
                                    <span style="font-size: 12px; color: var(--color-outline); width: var(--table-action-size); text-align: center;">—</span>
                                @endif
                            </x-table-actions>
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
<div class="modal-overlay" id="deleteStaffModal" role="dialog" aria-modal="true" aria-labelledby="deleteStaffModalTitle">
    <div class="modal-box" style="max-width: 420px;">
        <div class="modal-confirm-icon">
            <i class="bi bi-trash3-fill"></i>
        </div>
        <h3 class="modal-confirm-title" id="deleteStaffModalTitle">
            Xóa tài khoản nhân viên?
        </h3>
        <p class="modal-confirm-desc" id="deleteStaffMessage">
            Bạn có chắc chắn muốn xóa tài khoản này khỏi hệ thống? Hành động này không thể hoàn tác.
        </p>

        <form id="deleteStaffForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="modal-confirm-actions">
                <button type="button" class="btn-cancel-modal" onclick="closeDeleteStaffModal()">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn-confirm-modal-danger">
                    Đồng ý xóa
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    window.openDeleteStaffModal = function(id, name, deleteUrl) {
        const modal = document.getElementById('deleteStaffModal');
        const form = document.getElementById('deleteStaffForm');
        const msg = document.getElementById('deleteStaffMessage');
        if (!modal || !form || !msg) return;

        form.action = deleteUrl;
        msg.textContent = '';
        msg.append('Bạn có chắc chắn muốn xóa tài khoản ');
        const strongName = document.createElement('strong');
        strongName.textContent = '«' + String(name ?? '') + '»';
        msg.append(strongName, '? Hành động này không thể hoàn tác.');

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    window.closeDeleteStaffModal = function() {
        const modal = document.getElementById('deleteStaffModal');
        modal?.classList.remove('active');
        document.body.style.overflow = '';
    };

    window.AdminApp.mount((ctx) => {
        const modal = document.getElementById('deleteStaffModal');
        if (!modal) return;

        // Đóng khi click ngoài modal box
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                window.closeDeleteStaffModal();
            }
        }, { signal: ctx?.signal });

        // Đóng khi nhấn phím Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                window.closeDeleteStaffModal();
            }
        }, { signal: ctx?.signal });
    });
</script>
@endpush
@endsection
