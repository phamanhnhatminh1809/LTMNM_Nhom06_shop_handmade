@extends('layouts.admin')

@section('title', 'Thêm tài khoản nhân viên')

@php $activeMenu = 'staff'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-person-plus"></i>
        <span>Thêm tài khoản nhân viên và phân quyền</span>
    </div>
    <div class="top-bar-right">
        <a href="{{ route('admin.staff.index') }}" class="btn-secondary-outline">
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại danh sách</span>
        </a>
    </div>
</div>

@if($errors->any())
    <div class="admin-alert error">
        <div class="admin-alert-content">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <div>
                <div>Vui lòng kiểm tra lại thông tin tài khoản nhân viên:</div>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<div class="admin-form-grid">
    <!-- Cột trái: Form tạo tài khoản nhân viên -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="bi bi-person-vcard"></i>
                <span>Thông tin nhân sự mới</span>
            </h3>
        </div>
        <div class="admin-card-body">
            <form action="{{ route('admin.staff.store') }}" method="POST">
                @csrf

                <div class="modal-form-row-2" style="margin-bottom: 16px;">
                    <div class="modal-form-group">
                        <label class="modal-label" for="full_name">
                            Họ và tên nhân viên <span class="required-star">*</span>
                        </label>
                        <input type="text"
                               id="full_name"
                               name="full_name"
                               class="modal-input"
                               value="{{ old('full_name') }}"
                               placeholder="VD: Nguyễn Thị Minh Anh"
                               required>
                        @error('full_name')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="username">
                            Tên đăng nhập <span class="required-star">*</span>
                        </label>
                        <input type="text"
                               id="username"
                               name="username"
                               class="modal-input"
                               style="font-family: var(--font-mono);"
                               value="{{ old('username') }}"
                               placeholder="VD: nvkho01"
                               required>
                        @error('username')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-form-row-2" style="margin-bottom: 16px;">
                    <div class="modal-form-group">
                        <label class="modal-label" for="email">
                            Địa chỉ email <span class="required-star">*</span>
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="modal-input"
                               value="{{ old('email') }}"
                               placeholder="VD: nvkho01@shophandmade.com"
                               required>
                        @error('email')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="phone">
                            Số điện thoại liên hệ
                        </label>
                        <input type="text"
                               id="phone"
                               name="phone"
                               class="modal-input"
                               style="font-family: var(--font-mono);"
                               value="{{ old('phone') }}"
                               placeholder="VD: 0912345678">
                        @error('phone')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @php
                    $roleIconMap = [
                        'admin'           => 'bi-shield-fill-check',
                        'warehouse_staff' => 'bi-box-seam',
                        'sales_staff'     => 'bi-receipt-cutoff',
                        'customer'        => 'bi-person',
                    ];
                    $staffRoleOptions = $staffRoles->map(fn ($r) => [
                        'value' => $r->id,
                        'label' => trim((string) $r->label),
                        'icon'  => $roleIconMap[$r->name] ?? 'bi-person-badge',
                    ])->all();
                @endphp
                <div class="modal-form-group" style="margin-bottom: 16px;">
                    <label class="modal-label" for="role_id">
                        Phân quyền chức vụ <span class="required-star">*</span>
                    </label>
                    <x-select id="role_id"
                              name="role_id"
                              :options="$staffRoleOptions"
                              :value="old('role_id', $staffRoles->first()?->id)"
                              :required="true" />
                    @error('role_id')
                        <div class="form-error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="modal-form-row-2" style="margin-bottom: 16px;">
                    <div class="modal-form-group">
                        <label class="modal-label" for="password">
                            Mật khẩu khởi tạo <span class="required-star">*</span>
                        </label>
                        <input type="password"
                               id="password"
                               name="password"
                               class="modal-input"
                               placeholder="Tối thiểu 8 ký tự"
                               required>
                        @error('password')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="password_confirmation">
                            Xác nhận mật khẩu <span class="required-star">*</span>
                        </label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               class="modal-input"
                               placeholder="Nhập lại mật khẩu"
                               required>
                    </div>
                </div>

                <div class="admin-form-actions">
                    <x-button variant="cancel" :href="route('admin.staff.index')">
                        Hủy bỏ
                    </x-button>
                    <x-button variant="primary" type="submit" icon="bi-person-check">
                        <span>Tạo nhân viên và phân quyền</span>
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cột phải: Bảng mô tả phạm vi quyền hạn -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-shield-check"></i>
                    <span>Quy tắc phân quyền chi tiết</span>
                </h3>
            </div>
            <div class="admin-card-body">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- 1. Quản trị viên -->
                    <div style="padding: 12px 14px; background: #fff8f6; border: 1px solid #f2dcd4; border-radius: 10px; font-size: 12.5px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <span class="role-badge admin" style="margin: 0;">
                                <i class="bi bi-shield-fill-check"></i> Quản trị viên
                            </span>
                            <span class="stock-tag" style="background: #ffdad2; color: #792a16; font-size: 10px; padding: 1px 6px;">Toàn quyền</span>
                        </div>
                        <div style="color: var(--color-on-surface-variant); line-height: 1.5;">
                            Toàn quyền mọi phân hệ: Bảng điều khiển, Kho hàng, Bán hàng, Khách hàng và Phân quyền nhân sự.
                        </div>
                    </div>

                    <!-- 2. Nhân viên kho -->
                    <div style="padding: 12px 14px; background: #fafaf9; border: 1px solid #e7e5e4; border-radius: 10px; font-size: 12.5px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <span class="role-badge warehouse_staff" style="margin: 0;">
                                <i class="bi bi-box-seam"></i> Nhân viên kho
                            </span>
                            <span class="stock-tag" style="background: #f5f5f4; color: #57534e; font-size: 10px; padding: 1px 6px;">Phân hệ kho</span>
                        </div>
                        <div style="color: var(--color-on-surface-variant); line-height: 1.5; margin-bottom: 4px;">
                            <span style="color: #15803d; font-weight: 600;"><i class="bi bi-check-circle-fill"></i> Được cấp:</span> Quản lý danh mục sản phẩm, thêm, sửa, xóa sản phẩm và điều chỉnh tồn kho.
                        </div>
                        <div style="color: #78716c; line-height: 1.4; font-size: 11px;">
                            <i class="bi bi-dash-circle"></i> Không truy cập đơn hàng, đánh giá, khách hàng và nhân sự.
                        </div>
                    </div>

                    <!-- 3. Nhân viên bán hàng -->
                    <div style="padding: 12px 14px; background: #fafaf9; border: 1px solid #e7e5e4; border-radius: 10px; font-size: 12.5px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <span class="role-badge sales_staff" style="margin: 0;">
                                <i class="bi bi-receipt-cutoff"></i> Nhân viên bán hàng
                            </span>
                            <span class="stock-tag" style="background: #f5f5f4; color: #57534e; font-size: 10px; padding: 1px 6px;">Phân hệ bán hàng</span>
                        </div>
                        <div style="color: var(--color-on-surface-variant); line-height: 1.5; margin-bottom: 4px;">
                            <span style="color: #15803d; font-weight: 600;"><i class="bi bi-check-circle-fill"></i> Được cấp:</span> Tiếp nhận, duyệt và chuyển trạng thái đơn hàng; kiểm duyệt và xóa đánh giá sản phẩm.
                        </div>
                        <div style="color: #78716c; line-height: 1.4; font-size: 11px;">
                            <i class="bi bi-dash-circle"></i> Không can thiệp sản phẩm, kho bãi, khách hàng và nhân sự.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
