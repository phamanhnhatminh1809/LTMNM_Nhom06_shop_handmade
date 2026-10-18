@extends('layouts.app')

@section('title', 'Quản lý tài khoản cá nhân')

@push('styles')
<style>
    .address-select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--color-outline, #d1d5db);
        border-radius: 8px;
        font-size: 14px;
        background: #fff;
        outline: none;
        cursor: pointer;
        box-sizing: border-box;
    }
    .address-select:focus { border-color: var(--color-primary, #e76f51); }
</style>
@endpush

@section('content')
<div class="container" style="padding-top: 28px; padding-bottom: 50px;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" style="margin-bottom: 24px; font-size: 14px; color: var(--color-on-surface-variant);">
        <a href="{{ route('home') }}" style="color: inherit; text-decoration: none;">Trang chủ</a>
        <span style="margin: 0 8px;">/</span>
        <span style="color: var(--color-primary); font-weight: 600;">Quản lý tài khoản</span>
    </nav>

    <div class="profile-layout" style="display: grid; grid-template-columns: 280px 1fr; gap: 30px; align-items: start;">
        <!-- Cột trái: Tóm tắt thông tin tài khoản -->
        <aside class="profile-sidebar" style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 24px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="width: 80px; height: 80px; margin: 0 auto 12px; border-radius: 50%; background: linear-gradient(135deg, #e76f51, #f4a261); display: flex; align-items: center; justify-content: center; font-size: 32px; color: #fff; font-weight: bold; box-shadow: 0 4px 12px rgba(231,111,81,0.25);">
                    {{ mb_strtoupper(mb_substr($user->full_name, 0, 1)) }}
                </div>
                <h3 style="margin: 0 0 4px; font-size: 18px; font-weight: 700; color: var(--color-on-surface);">{{ $user->full_name }}</h3>
                <p style="margin: 0 0 8px; font-size: 13px; color: var(--color-on-surface-variant);">{{ $user->email }}</p>
                <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #eef2ff; color: #4338ca;">
                    {{ $user->isAdmin() ? 'Quản trị viên' : 'Thành viên' }}
                </span>
            </div>

            <hr style="border: none; border-top: 1px solid var(--color-outline-variant, #f0eae1); margin: 16px 0;">

            <div style="font-size: 13px; color: var(--color-on-surface-variant); line-height: 1.8;">
                <div><strong>Tên đăng nhập:</strong> {{ $user->username }}</div>
                <div><strong>Tham gia từ:</strong> {{ $user->created_at->format('d/m/Y') }}</div>
                <div><strong>Trạng thái:</strong> <span style="color: #10b981; font-weight: 600;">Hoạt động</span></div>
            </div>
        </aside>

        <!-- Cột phải: Form cập nhật thông tin & Đổi mật khẩu -->
        <div class="profile-content" style="display: flex; flex-direction: column; gap: 28px;">

            <!-- Thông báo thành công / lỗi chung -->
            @if(session('profile_success'))
                <div style="background: #ecfdf5; border-left: 4px solid #10b981; color: #065f46; padding: 14px 18px; border-radius: 8px; font-size: 14px;">
                    <span>{{ session('profile_success') }}</span>
                </div>
            @endif

            @if(session('password_success'))
                <div style="background: #ecfdf5; border-left: 4px solid #10b981; color: #065f46; padding: 14px 18px; border-radius: 8px; font-size: 14px;">
                    <span>{{ session('password_success') }}</span>
                </div>
            @endif
            
            @if($errors->any())
                <div style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 14px 18px; border-radius: 8px; font-size: 14px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Card 1: Quản lý thông tin cá nhân -->
            <section style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 28px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                <div style="margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--color-on-surface);">Thông tin cá nhân</h2>
                    <p style="margin: 2px 0 0; font-size: 13px; color: var(--color-on-surface-variant);">Xem và cập nhật họ tên, số điện thoại, địa chỉ nhận hàng của bạn</p>
                </div>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--color-on-surface);">
                                Họ và tên <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" required
                                   style="width: 100%; padding: 10px 14px; border: 1px solid var(--color-outline, #d1d5db); border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--color-on-surface);">
                                Số điện thoại
                            </label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Ví dụ: 0912345678"
                                   style="width: 100%; padding: 10px 14px; border: 1px solid var(--color-outline, #d1d5db); border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--color-on-surface-variant);">
                                Tên tài khoản (Không thể thay đổi)
                            </label>
                            <input type="text" value="{{ $user->username }}" disabled
                                   style="width: 100%; padding: 10px 14px; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; color: #6b7280; box-sizing: border-box;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--color-on-surface);">
                                Địa chỉ Email <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   style="width: 100%; padding: 10px 14px; border: 1px solid var(--color-outline, #d1d5db); border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                        </div>
                    </div>

                    <!-- CHỌN ĐỊA CHỈ 2 CẤP MỚI -->
                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--color-on-surface);">
                            Địa chỉ nhận hàng mặc định
                        </label>
                        @if($user->address)
                            <div style="margin-bottom: 10px; padding: 10px; background: #fdfaf7; border: 1px dashed #d1d5db; border-radius: 8px; font-size: 13px; color: #666;">
                                <strong>Địa chỉ hiện tại:</strong> {{ $user->address }}
                            </div>
                        @endif

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 12px;">
                            <div>
                                <select id="province" class="address-select">
                                    <option value="">-- Chọn Tỉnh/Thành phố --</option>
                                </select>
                            </div>
                            <div>
                                <select id="ward" class="address-select" disabled>
                                    <option value="">-- Chọn Phường/Xã --</option>
                                </select>
                            </div>
                        </div>

                        <input type="text" id="street" placeholder="Số nhà, tên đường (Ví dụ: 123 Nguyễn Trãi)" 
                               style="width: 100%; padding: 10px 14px; border: 1px solid var(--color-outline, #d1d5db); border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                        
                        <!-- Input ẩn gửi lên server -->
                        <input type="hidden" name="address" id="full_address" value="{{ old('address', $user->address) }}">
                    </div>

                    <div style="text-align: right;">
                        <button type="submit" style="padding: 10px 24px; background: var(--color-primary, #e76f51); color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;">
                            Lưu thông tin thay đổi
                        </button>
                    </div>
                </form>
            </section>

            <!-- Card 2: Đổi mật khẩu -->
            <section style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 28px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                <div style="margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--color-on-surface);">Đổi mật khẩu</h2>
                    <p style="margin: 2px 0 0; font-size: 13px; color: var(--color-on-surface-variant);">Để bảo mật, vui lòng nhập mật khẩu hiện tại và tạo mật khẩu mới an toàn</p>
                </div>

                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--color-on-surface);">
                            Mật khẩu hiện tại <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="password" name="current_password" required placeholder="Nhập mật khẩu hiện tại"
                               style="width: 100%; padding: 10px 14px; border: 1px solid var(--color-outline, #d1d5db); border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 22px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--color-on-surface);">
                                Mật khẩu mới <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="password" name="password" required placeholder="Tối thiểu 8 ký tự" minlength="8"
                                   style="width: 100%; padding: 10px 14px; border: 1px solid var(--color-outline, #d1d5db); border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--color-on-surface);">
                                Xác nhận mật khẩu mới <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="password" name="password_confirmation" required placeholder="Nhập lại mật khẩu mới" minlength="8"
                                   style="width: 100%; padding: 10px 14px; border: 1px solid var(--color-outline, #d1d5db); border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <button type="submit" style="padding: 10px 24px; background: #374151; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: opacity 0.2s;">
                            Cập nhật mật khẩu mới
                        </button>
                    </div>
                </form>
            </section>

            <!-- Card 3: Tóm tắt đơn hàng gần đây -->
            @if(isset($recentOrders) && $recentOrders->isNotEmpty())
            <section style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 28px; border: 1px solid var(--color-outline-variant, #f0eae1); box-shadow: 0 4px 16px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 16px;">
                    <div>
                        <h2 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--color-on-surface);">Đơn hàng gần đây</h2>
                        <p style="margin: 2px 0 0; font-size: 13px; color: var(--color-on-surface-variant);">Theo dõi trạng thái các đơn hàng bạn đã đặt</p>
                    </div>
                    <a href="{{ route('orders.index') }}" style="font-size: 13px; font-weight: 600; color: var(--color-primary); text-decoration: none;">
                        Xem tất cả đơn hàng &rarr;
                    </a>
                </div>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--color-outline-variant, #f0eae1); text-align: left; color: var(--color-on-surface-variant);">
                                <th style="padding: 10px;">MÃ ĐƠN</th>
                                <th style="padding: 10px;">NGÀY ĐẶT</th>
                                <th style="padding: 10px;">TỔNG TIỀN</th>
                                <th style="padding: 10px;">TRẠNG THÁI</th>
                                <th style="padding: 10px; text-align: right;">CHI TIẾT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                                <tr style="border-bottom: 1px solid var(--color-outline-variant, #f0eae1);">
                                    <td style="padding: 12px 10px; font-weight: 600;">
                                        <a href="{{ route('orders.show', $order->id) }}" style="color: inherit; text-decoration: none;">
                                            #{{ $order->code }}
                                        </a>
                                    </td>
                                    <td style="padding: 12px 10px; color: var(--color-on-surface-variant);">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td style="padding: 12px 10px; font-weight: 600; color: var(--color-primary);">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                                    <td style="padding: 12px 10px;">
                                        @php
                                            $statusMap = [
                                                'pending'   => ['label' => 'Chờ xử lý', 'bg' => '#fef3c7', 'color' => '#d97706'],
                                                'shipping'  => ['label' => 'Đang giao', 'bg' => '#e0e7ff', 'color' => '#4338ca'],
                                                'completed' => ['label' => 'Hoàn thành', 'bg' => '#d1fae5', 'color' => '#059669'],
                                                'cancelled' => ['label' => 'Đã hủy', 'bg' => '#fee2e2', 'color' => '#dc2626'],
                                            ];
                                            $st = $statusMap[$order->status] ?? ['label' => $order->status, 'bg' => '#f3f4f6', 'color' => '#6b7280'];
                                        @endphp
                                        <span style="display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; background: {{ $st['bg'] }}; color: {{ $st['color'] }};">
                                            {{ $st['label'] }}
                                        </span>
                                    </td>
                                    <td style="padding: 12px 10px; text-align: right;">
                                        <a href="{{ route('orders.show', $order->id) }}" style="font-size: 12px; font-weight: 600; color: var(--color-primary); text-decoration: none;">
                                            Xem
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            @endif

        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const provinceSelect = document.getElementById('province');
        const wardSelect = document.getElementById('ward');
        const streetInput = document.getElementById('street');
        const fullAddressInput = document.getElementById('full_address');

        // API V2 - DỮ LIỆU SAU SÁT NHẬP
        const API_URL = 'https://provinces.open-api.vn/api/v2';

        // 1. Tải Tỉnh/Thành
        axios.get(`${API_URL}/p/`).then(res => {
            res.data.forEach(province => {
                provinceSelect.innerHTML += `<option value="${province.code}" data-name="${province.name}">${province.name}</option>`;
            });
        });

        // 2. Khi chọn Tỉnh -> Tải Phường/Xã
        provinceSelect.addEventListener('change', function() {
            const provinceCode = this.value;
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            wardSelect.disabled = true;
            updateFullAddress();

            if (provinceCode) {
                wardSelect.disabled = false;
                axios.get(`${API_URL}/p/${provinceCode}?depth=2`).then(res => {
                    let wards = res.data.wards || [];
                    wards.forEach(ward => {
                        wardSelect.innerHTML += `<option value="${ward.code}" data-name="${ward.name}">${ward.name}</option>`;
                    });
                });
            }
        });

        wardSelect.addEventListener('change', updateFullAddress);
        streetInput.addEventListener('input', updateFullAddress);

        // Hàm nối chuỗi địa chỉ
        function updateFullAddress() {
            const street = streetInput.value.trim();
            const wardName = wardSelect.options[wardSelect.selectedIndex]?.getAttribute('data-name') || '';
            const provinceName = provinceSelect.options[provinceSelect.selectedIndex]?.getAttribute('data-name') || '';

            let parts = [];
            if (street) parts.push(street);
            if (wardName) parts.push(wardName);
            if (provinceName) parts.push(provinceName);

            fullAddressInput.value = parts.join(', ');
        }
    });
</script>
@endpush
@endsection