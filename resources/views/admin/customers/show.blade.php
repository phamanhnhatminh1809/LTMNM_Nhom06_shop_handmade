@extends('layouts.admin')

@section('title', 'Chi tiết khách hàng — ' . $customer->full_name)

@php $activeMenu = 'customers'; @endphp

@section('content')
{{--
    ================================================================
    [BACKEND ĐÃ CÓ] — Dành cho Frontend
    ================================================================
    Controller : App\Http\Controllers\Admin\CustomerController@show
    Route      : GET /admin/customers/{customer}  (admin.customers.show)

    Dữ liệu truyền vào view:
      - $customer : User — khách hàng (role_id = 2, nếu không → 404)
          Đã load:
            - orders (10 đơn gần nhất, sắp xếp mới nhất trước)
                mỗi Order có: id, code, total_amount, status, created_at
                mỗi Order.items: quantity, unit_price, total_price
                    + product: name, code

          Fields: id, username, email, full_name, phone, is_active, created_at

    Các route thao tác:
      - Khóa TK   : PATCH admin.customers.lock   /admin/customers/{customer}/lock
      - Mở khóa   : PATCH admin.customers.unlock /admin/customers/{customer}/unlock
      - Quay lại  : admin.customers.index

    Trạng thái đăng nhập:
      is_active = true  → Đang hoạt động (có thể đăng nhập)
      is_active = false → Đã khóa (không thể đăng nhập,
                          AuthController::login() từ chối vì is_active=false)
    ================================================================
--}}

{{-- ===== KHU VỰC BACKEND: CHI TIẾT KHÁCH HÀNG ===== --}}
<div style="border: 2px dashed #3b82f6; padding: 16px; margin: 16px;">
    <p><strong>[BACKEND DEMO]</strong> Trang chi tiết khách hàng — Frontend thay thế bằng UI hoàn chỉnh.</p>

    {{-- Flash messages --}}
    @if(session('success'))
        <p style="color: green;">✅ {{ session('success') }}</p>
    @endif
    @if(session('error'))
        <p style="color: red;">❌ {{ session('error') }}</p>
    @endif

    <a href="{{ route('admin.customers.index') }}">← Quay lại danh sách</a>

    {{-- Thông tin tài khoản --}}
    <h2>👤 {{ $customer->full_name }}</h2>

    <table border="1" cellpadding="8">
        <tr><th>ID</th><td>{{ $customer->id }}</td></tr>
        <tr><th>Tên đăng nhập</th><td><code>{{ $customer->username }}</code></td></tr>
        <tr><th>Email</th><td>{{ $customer->email }}</td></tr>
        <tr><th>Số điện thoại</th><td>{{ $customer->phone ?? '—' }}</td></tr>
        <tr>
            <th>Trạng thái tài khoản</th>
            <td>
                @if($customer->is_active)
                    <span style="color: green; font-weight:bold;">🟢 Đang hoạt động</span>
                @else
                    <span style="color: red; font-weight:bold;">🔒 Đã khóa — Không thể đăng nhập</span>
                @endif
            </td>
        </tr>
        <tr><th>Ngày đăng ký</th><td>{{ $customer->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><th>Tổng đơn hàng</th><td>{{ $customer->orders->count() }} đơn (hiển thị 10 gần nhất)</td></tr>
    </table>

    {{-- Nút khóa / mở khóa --}}
    <div style="margin: 12px 0;">
        @if($customer->is_active)
            <form action="{{ route('admin.customers.lock', $customer) }}" method="POST" style="display:inline;">
                @csrf
                @method('PATCH')
                <button type="submit"
                        onclick="return confirm('Xác nhận khóa tài khoản của {{ $customer->full_name }}?\nHọ sẽ không thể đăng nhập ngay sau đó.')">
                    🔒 Khóa tài khoản này
                </button>
            </form>
        @else
            <form action="{{ route('admin.customers.unlock', $customer) }}" method="POST" style="display:inline;">
                @csrf
                @method('PATCH')
                <button type="submit"
                        onclick="return confirm('Mở khóa tài khoản của {{ $customer->full_name }}?')">
                    🔓 Mở khóa tài khoản này
                </button>
            </form>
        @endif
    </div>

    {{-- Lịch sử đơn hàng --}}
    <h3>🛒 Lịch sử đơn hàng gần nhất</h3>
    @if($customer->orders->isEmpty())
        <p><em>Khách hàng này chưa có đơn hàng nào.</em></p>
    @else
        <table border="1" cellpadding="8" style="width:100%;">
            <thead>
                <tr>
                    <th>Mã ĐH</th>
                    <th>Số sản phẩm</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customer->orders as $order)
                    @php
                        $statusLabels = [
                            'pending'   => '⏳ Chờ xác nhận',
                            'shipping'  => '🚚 Đang giao',
                            'completed' => '✅ Hoàn thành',
                            'cancelled' => '❌ Đã hủy',
                        ];
                    @endphp
                    <tr>
                        <td><strong>#{{ $order->code }}</strong></td>
                        <td>{{ $order->items->count() }} sản phẩm</td>
                        <td>{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                        <td>{{ $statusLabels[$order->status] ?? $order->status }}</td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}">Xem đơn</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
{{-- ===== KẾT THÚC KHU VỰC BACKEND ===== --}}

@endsection
