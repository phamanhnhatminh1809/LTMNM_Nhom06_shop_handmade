@extends('layouts.admin')

@section('title', 'Thêm tài khoản nhân viên')

@php $activeMenu = 'staff'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-person-plus"></i>
        <span>THÊM TÀI KHOẢN NHÂN VIÊN & CẤP QUYỀN</span>
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
                            Tên đăng nhập (Username) <span class="required-star">*</span>
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
                            Địa chỉ Email <span class="required-star">*</span>
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

                <div class="modal-form-group" style="margin-bottom: 16px;">
                    <label class="modal-label" for="role_id">
                        Phân quyền chức vụ <span class="required-star">*</span>
                    </label>
                    <select id="role_id" name="role_id" class="modal-input" required>
                        @foreach($staffRoles as $role)
                            <option value="{{ $role->id }}" {{ (int) old('role_id') === $role->id ? 'selected' : '' }}>
                                {{ $role->label }} ({{ $role->name }})
                            </option>
                        @endforeach
                    </select>
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
                    <a href="{{ route('admin.staff.index') }}" class="btn-modal-cancel" style="text-decoration: none;">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="btn-modal-save">
                        <i class="bi bi-person-check"></i>
                        <span>Tạo nhân viên & Cấp quyền</span>
                    </button>
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
                    <span>Phạm vi quyền hạn theo chức vụ</span>
                </h3>
            </div>
            <div class="admin-card-body">
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="padding: 12px 14px; background: #fff8f6; border: 1px solid #f2dcd4; border-radius: 10px;">
                        <div style="margin-bottom: 6px;">
                            <span class="role-badge warehouse_staff">
                                <i class="bi bi-box-seam"></i> Nhân viên kho (`warehouse_staff`)
                            </span>
                        </div>
                        <div style="font-size: 12.5px; color: var(--color-on-surface-variant); line-height: 1.5;">
                            • Quản lý danh mục sản phẩm (Thêm, sửa, xóa).<br>
                            • Quản lý sản phẩm, hình ảnh và số lượng tồn kho.<br>
                            • Không truy cập được Đơn hàng, Khách hàng hay Phân quyền.
                        </div>
                    </div>

                    <div style="padding: 12px 14px; background: #fff8f6; border: 1px solid #f2dcd4; border-radius: 10px;">
                        <div style="margin-bottom: 6px;">
                            <span class="role-badge sales_staff">
                                <i class="bi bi-receipt-cutoff"></i> Nhân viên bán hàng (`sales_staff`)
                            </span>
                        </div>
                        <div style="font-size: 12.5px; color: var(--color-on-surface-variant); line-height: 1.5;">
                            • Xem danh sách, duyệt đơn và cập nhật trạng thái giao hàng.<br>
                            • Quản lý và kiểm duyệt đánh giá sản phẩm từ khách hàng.<br>
                            • Không chỉnh sửa kho hàng hay tài khoản người dùng.
                        </div>
                    </div>

                    <div style="padding: 12px 14px; background: #fff8f6; border: 1px solid #f2dcd4; border-radius: 10px;">
                        <div style="margin-bottom: 6px;">
                            <span class="role-badge admin">
                                <i class="bi bi-shield-fill-check"></i> Quản trị viên (`admin`)
                            </span>
                        </div>
                        <div style="font-size: 12.5px; color: var(--color-on-surface-variant); line-height: 1.5;">
                            • Toàn quyền truy cập và điều hành tất cả các phân hệ trong hệ thống.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
