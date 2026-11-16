@extends('layouts.admin')

@section('title', 'Hồ sơ cá nhân')

@php $activeMenu = 'profile'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-person-circle"></i>
        <span>Hồ sơ cá nhân</span>
    </div>
    <div class="top-bar-right">
        <a href="{{ route('admin.dashboard') }}" class="btn-secondary-outline">
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại tổng quan</span>
        </a>
    </div>
</div>

@if(session('success'))
    <x-alert type="success" :message="session('success')" style="margin-bottom: 20px;" />
@endif

@if(session('password_success'))
    <x-alert type="success" :message="session('password_success')" style="margin-bottom: 20px;" />
@endif

@if(session('error'))
    <x-alert type="error" :message="session('error')" style="margin-bottom: 20px;" />
@endif

@if($errors->any())
    <div class="admin-alert error" style="margin-bottom: 20px;">
        <div class="admin-alert-content">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <div>
                <div>Vui lòng kiểm tra lại thông tin:</div>
                <ul style="margin: 4px 0 0; padding-left: 18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<div class="admin-form-grid">
    <!-- Cột trái: Form cập nhật thông tin cá nhân và đổi mật khẩu -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Card 1: Thông tin cá nhân -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-person-vcard"></i>
                    <span>Cập nhật thông tin cá nhân</span>
                </h3>
            </div>
            <div class="admin-card-body">
                <form action="{{ route('admin.profile.update') }}" method="POST">
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
                                   value="{{ trim((string) old('full_name', $user->full_name)) }}"
                                   required>
                            @error('full_name')
                                <div class="form-error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-label" for="username">
                                Tên đăng nhập
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
                                Địa chỉ email <span class="required-star">*</span>
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

                    <!-- Vai trò hiện tại (chỉ hiển thị, không được sửa) -->
                    <div class="modal-form-group" style="margin-bottom: 24px;">
                        <label class="modal-label">
                            Vai trò trong hệ thống
                        </label>
                        <div style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; background: #faf7f5; border: 1px solid var(--color-outline-variant); border-radius: 8px;">
                            <span class="role-badge {{ $user->role->name ?? 'admin' }}" style="margin: 0;">
                                <i class="bi bi-shield-check"></i> {{ $user->role->label ?? 'Quản trị viên' }}
                            </span>
                            <span style="font-size: 12px; color: var(--color-outline);">
                                (Vai trò do quản trị viên phân công, không thể tự thay đổi)
                            </span>
                        </div>
                    </div>

                    <div class="admin-form-actions" style="margin-top: 10px;">
                        <x-button variant="cancel" :href="route('admin.dashboard')">
                            Hủy bỏ
                        </x-button>
                        <x-button variant="primary" type="submit" icon="bi-check-lg">
                            <span>Lưu thông tin thay đổi</span>
                        </x-button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Card 2: Đổi mật khẩu -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-key"></i>
                    <span>Đổi mật khẩu</span>
                </h3>
            </div>
            <div class="admin-card-body">
                <form action="{{ route('admin.profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="modal-form-group" style="margin-bottom: 16px;">
                        <label class="modal-label" for="current_password">
                            Mật khẩu hiện tại <span class="required-star">*</span>
                        </label>
                        <input type="password"
                               id="current_password"
                               name="current_password"
                               class="modal-input"
                               placeholder="Nhập mật khẩu đang sử dụng"
                               required>
                        @error('current_password')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-form-row-2" style="margin-bottom: 24px;">
                        <div class="modal-form-group">
                            <label class="modal-label" for="new_password">
                                Mật khẩu mới <span class="required-star">*</span>
                            </label>
                            <input type="password"
                                   id="new_password"
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
                                Xác nhận mật khẩu mới <span class="required-star">*</span>
                            </label>
                            <input type="password"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   class="modal-input"
                                   placeholder="Nhập lại mật khẩu mới"
                                   required>
                        </div>
                    </div>

                    <div class="admin-form-actions">
                        <x-button variant="primary" type="submit" icon="bi-shield-lock">
                            <span>Cập nhật mật khẩu</span>
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cột phải: Thẻ tóm tắt thông tin hồ sơ (không có quy tắc phân quyền) -->
    <div>
        <div class="admin-card" style="position: sticky; top: 88px;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-shield-check"></i>
                    <span>Tóm tắt hồ sơ</span>
                </h3>
            </div>
            <div class="admin-card-body">
                <div style="text-align: center; padding: 10px 0 20px;">
                    <div style="width: 76px; height: 76px; border-radius: 50%; background: #ebdcd7; color: var(--color-primary); font-weight: 800; font-size: 28px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; box-shadow: 0 2px 6px rgba(121,42,22,0.12);">
                        {{ mb_strtoupper(mb_substr($user->full_name, 0, 2)) }}
                    </div>
                    <h4 style="margin: 0 0 4px; font-size: 18px; font-weight: 800; color: var(--color-on-surface);">
                        {{ $user->full_name }}
                    </h4>
                    <div style="font-family: var(--font-mono); font-size: 13px; color: var(--color-outline);">
                        {{ '@' . $user->username }}
                    </div>
                    <div style="margin-top: 12px; display: flex; justify-content: center; gap: 8px;">
                        <span class="role-badge {{ $user->role->name ?? 'admin' }}" style="margin: 0;">
                            {{ $user->role->label ?? 'Quản trị viên' }}
                        </span>
                        <span class="status-badge active-user">
                            <i class="bi bi-check-circle"></i> Đang hoạt động
                        </span>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--color-outline-variant); padding-top: 16px; font-size: 13px; color: var(--color-on-surface); line-height: 2.2;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--color-outline);">Mã tài khoản:</span>
                        <strong>#{{ $user->id }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--color-outline);">Ngày tham gia:</span>
                        <span>{{ $user->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--color-outline);">Lần cập nhật cuối:</span>
                        <span>{{ $user->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
