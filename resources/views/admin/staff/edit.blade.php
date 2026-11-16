@extends('layouts.admin')

@section('title', 'Chỉnh sửa nhân viên: ' . $user->full_name)

@php $activeMenu = 'staff'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-person-gear"></i>
        <span>Chỉnh sửa thông tin nhân viên</span>
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
                <span>Cập nhật thông tin và phân quyền</span>
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
                               value="{{ trim((string) old('email', $user->email)) }}"
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
                               value="{{ trim((string) old('phone', $user->phone)) }}"
                               placeholder="VD: 0912345678">
                        @error('phone')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Phân quyền vai trò -->
                @php
                    $roleIconMap = [
                        'admin'           => 'bi-shield-fill-check',
                        'warehouse_staff' => 'bi-box-seam',
                        'sales_staff'     => 'bi-receipt-cutoff',
                        'customer'        => 'bi-person',
                    ];
                    $roleSelectOptions = $roles->map(fn ($r) => [
                        'value' => $r->id,
                        'label' => trim((string) $r->label),
                        'icon'  => $roleIconMap[$r->name] ?? 'bi-person-badge',
                    ])->all();
                @endphp
                <div class="modal-form-group" style="margin-bottom: 20px;">
                    <label class="modal-label" for="role_id">
                        Vai trò quyền hạn <span class="required-star">*</span>
                    </label>
                    <x-select id="role_id"
                              name="role_id"
                              :options="$roleSelectOptions"
                              :value="old('role_id', $user->role_id)"
                              :disabled="$isCurrentUser"
                              :required="true" />
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

                <div class="admin-form-actions">
                    <x-button variant="cancel" :href="route('admin.staff.index')">
                        Hủy bỏ
                    </x-button>
                    <x-button variant="primary" type="submit" icon="bi-check-lg">
                        <span>Lưu thông tin thay đổi</span>
                    </x-button>
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

            <!-- Quy tắc phân quyền trực quan theo từng vai trò -->
            <div style="margin-top: 20px; border-top: 1px solid var(--color-outline-variant); padding-top: 16px;">
                <div style="font-size: 13px; font-weight: 800; color: var(--color-primary); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-shield-lock"></i>
                    <span>Quy tắc phân quyền chi tiết</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- 1. Quản trị viên -->
                    <div style="padding: 10px 12px; background: #fff8f6; border-radius: 8px; border: 1px solid #f2dcd4; font-size: 12px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <strong style="color: var(--color-primary); font-size: 12.5px;">Quản trị viên</strong>
                            <span class="stock-tag" style="background: #ffdad2; color: #792a16; font-size: 10px; padding: 1px 6px;">Toàn quyền</span>
                        </div>
                        <div style="color: var(--color-on-surface-variant); line-height: 1.5;">
                            Toàn quyền mọi phân hệ: Bảng điều khiển, Kho hàng, Bán hàng, Khách hàng và Phân quyền nhân sự.
                        </div>
                    </div>

                    <!-- 2. Nhân viên kho -->
                    <div style="padding: 10px 12px; background: #fafaf9; border-radius: 8px; border: 1px solid #e7e5e4; font-size: 12px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <strong style="color: #292524; font-size: 12.5px;">Nhân viên kho</strong>
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
                    <div style="padding: 10px 12px; background: #fafaf9; border-radius: 8px; border: 1px solid #e7e5e4; font-size: 12px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <strong style="color: #292524; font-size: 12.5px;">Nhân viên bán hàng</strong>
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
