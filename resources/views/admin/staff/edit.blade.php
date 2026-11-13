@extends('layouts.admin')

@section('title', 'Chỉnh sửa nhân viên: ' . $user->full_name)

@php $activeMenu = 'staff'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-person-gear"></i>
        <span>CHỈNH SỬA THÔNG TIN NHÂN VIÊN</span>
    </div>
    <div class="top-bar-right">
        <a href="{{ route('admin.staff.index') }}" class="btn-secondary-outline">
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại danh sách</span>
        </a>
    </div>
</div>

@if(session('error'))
    <div class="admin-alert error">
        <div class="admin-alert-content">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <span>{{ session('error') }}</span>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="admin-alert error">
        <div class="admin-alert-content">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <div>
                <div>Vui lòng kiểm tra lại thông tin:</div>
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
    <!-- Cột trái: Form chỉnh sửa -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="bi bi-person-vcard"></i>
                <span>Cập nhật thông tin & Cấp quyền</span>
            </h3>
        </div>
        <div class="admin-card-body">
            <form action="{{ route('admin.staff.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-form-row-2" style="margin-bottom: 16px;">
                    <div class="modal-form-group">
                        <label class="modal-label" for="full_name">
                            Họ và tên <span class="required-star">*</span>
                        </label>
                        <input type="text"
                               id="full_name"
                               name="full_name"
                               class="modal-input"
                               value="{{ old('full_name', $user->full_name) }}"
                               required>
                        @error('full_name')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="username">
                            Tên đăng nhập (Username)
                        </label>
                        <input type="text"
                               id="username"
                               class="modal-input"
                               style="font-family: var(--font-mono); background-color: #f3f4f6; color: #6b7280; cursor: not-allowed;"
                               value="{{ $user->username }}"
                               disabled
                               title="Tên đăng nhập cố định không thể sửa đổi">
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
                               value="{{ old('email', $user->email) }}"
                               required>
                        @error('email')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="phone">
                            Số điện thoại
                        </label>
                        <input type="text"
                               id="phone"
                               name="phone"
                               class="modal-input"
                               style="font-family: var(--font-mono);"
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="VD: 0912345678">
                        @error('phone')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Phân quyền vai trò -->
                <div class="modal-form-group" style="margin-bottom: 20px;">
                    <label class="modal-label" for="role_id">
                        Vai trò quyền hạn <span class="required-star">*</span>
                    </label>
                    <select id="role_id"
                            name="role_id"
                            class="modal-input"
                            {{ $isCurrentUser ? 'disabled' : '' }}
                            required>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}" {{ old('role_id', $user->role_id) == $r->id ? 'selected' : '' }}>
                                {{ $r->label }} ({{ $r->name }})
                            </option>
                        @endforeach
                    </select>
                    @if($isCurrentUser)
                        <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                        <div style="font-size: 12px; color: var(--color-outline); margin-top: 4px;">
                            <i class="bi bi-info-circle"></i> Bạn không thể tự thay đổi vai trò của chính mình.
                        </div>
                    @endif
                    @error('role_id')
                        <div class="form-error-text">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Khóa / Mở khóa tài khoản -->
                <div class="modal-form-group" style="margin-bottom: 24px; padding: 16px; background: #faf7f5; border: 1px solid var(--color-outline-variant); border-radius: 10px;">
                    <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; user-select: none;">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               {{ $isCurrentUser ? 'disabled' : '' }}
                               style="margin-top: 3px; transform: scale(1.3); cursor: pointer;">
                        <div>
                            <div style="font-weight: 700; font-size: 14px; color: var(--color-on-surface);">
                                Kích hoạt hoạt động cho tài khoản này
                            </div>
                            <div style="font-size: 12.5px; color: var(--color-on-surface-variant); margin-top: 2px;">
                                Nếu bỏ chọn, tài khoản sẽ chuyển sang trạng thái <strong>Đã khóa</strong> và bị chặn đăng nhập ngay lập tức.
                            </div>
                        </div>
                    </label>
                    @if($isCurrentUser)
                        <input type="hidden" name="is_active" value="1">
                        <div style="font-size: 12px; color: var(--color-outline); margin-top: 6px; padding-left: 28px;">
                            <i class="bi bi-info-circle"></i> Bạn không thể tự khóa tài khoản của chính mình.
                        </div>
                    @endif
                </div>

                <!-- Đổi mật khẩu mới (tùy chọn) -->
                <div style="border-top: 1px solid var(--color-outline-variant); padding-top: 20px; margin-bottom: 24px;">
                    <h4 style="font-size: 15px; font-weight: 700; color: var(--color-on-surface); margin-bottom: 8px;">
                        <i class="bi bi-key"></i> Đặt mật khẩu mới (Tùy chọn)
                    </h4>
                    <p style="font-size: 12.5px; color: var(--color-outline); margin-bottom: 14px;">
                        Để trống nếu muốn giữ nguyên mật khẩu hiện tại của nhân viên.
                    </p>

                    <div class="modal-form-row-2">
                        <div class="modal-form-group">
                            <label class="modal-label" for="password">
                                Mật khẩu mới (Tối thiểu 8 ký tự)
                            </label>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="modal-input"
                                   placeholder="Nhập mật khẩu mới">
                            @error('password')
                                <div class="form-error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-label" for="password_confirmation">
                                Xác nhận mật khẩu mới
                            </label>
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   class="modal-input"
                                   placeholder="Nhập lại mật khẩu mới">
                        </div>
                    </div>
                </div>

                <div class="modal-actions-end" style="display: flex; gap: 12px; justify-content: flex-end;">
                    <a href="{{ route('admin.staff.index') }}" class="btn-secondary-outline">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="btn-primary-action">
                        <i class="bi bi-check-lg"></i>
                        <span>Lưu thông tin thay đổi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cột phải: Thẻ tóm tắt thông tin & Phân quyền -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="bi bi-shield-check"></i>
                <span>Tóm tắt hồ sơ nhân sự</span>
            </h3>
        </div>
        <div class="admin-card-body">
            <div style="text-align: center; padding: 10px 0 20px;">
                <div style="width: 72px; height: 72px; border-radius: 50%; background: #ebdcd7; color: var(--color-primary); font-weight: 800; font-size: 26px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                    {{ mb_strtoupper(mb_substr($user->full_name, 0, 2)) }}
                </div>
                <h4 style="margin: 0 0 4px; font-size: 17px; font-weight: 800; color: var(--color-on-surface);">
                    {{ $user->full_name }}
                </h4>
                <div style="font-family: var(--font-mono); font-size: 13px; color: var(--color-outline);">
                    {{ '@' . $user->username }}
                </div>
                <div style="margin-top: 10px;">
                    @if($user->is_active)
                        <span class="status-badge active-user">
                            <i class="bi bi-check-circle"></i> Đang hoạt động
                        </span>
                    @else
                        <span class="status-badge locked">
                            <i class="bi bi-lock-fill"></i> Tài khoản đã khóa
                        </span>
                    @endif
                </div>
            </div>

            <div style="border-top: 1px solid var(--color-outline-variant); padding-top: 16px; font-size: 13px; color: var(--color-on-surface); line-height: 2;">
                <div><strong>Mã ID:</strong> #{{ $user->id }}</div>
                <div><strong>Ngày tham gia:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</div>
                <div><strong>Lần cập nhật cuối:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</div>
            </div>

            <div style="margin-top: 20px; padding: 14px; background: #fff8f6; border-radius: 8px; border: 1px solid var(--color-outline-variant); font-size: 12.5px; line-height: 1.6; color: var(--color-on-surface-variant);">
                <div style="font-weight: 700; color: var(--color-primary); margin-bottom: 4px;">
                    <i class="bi bi-lightbulb"></i> Quy tắc phân quyền:
                </div>
                <ul style="padding-left: 18px; margin: 0;">
                    <li><strong>Quản trị viên:</strong> Toàn quyền mọi phân hệ.</li>
                    <li><strong>Nhân viên kho:</strong> Quản lý Kho (Danh mục & Sản phẩm).</li>
                    <li><strong>NV Bán hàng:</strong> Quản lý Kinh doanh (Đơn hàng & Đánh giá).</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
