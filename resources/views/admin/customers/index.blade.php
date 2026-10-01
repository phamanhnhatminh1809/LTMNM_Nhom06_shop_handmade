@extends('layouts.admin')

@section('title', 'Quản lý khách hàng')

@php $activeMenu = 'customers'; @endphp

@section('content')
{{--
    ================================================================
    [BACKEND ĐÃ CÓ] — Dành cho Frontend
    ================================================================
    Controller : App\Http\Controllers\Admin\CustomerController@index
    Route      : GET /admin/customers  (admin.customers.index)
                 Query params:
                   ?q=<keyword>          tìm theo tên | email | sđt | username
                   ?status=active|locked lọc theo trạng thái

    Dữ liệu truyền vào view:
      - $customers    : LengthAwarePaginator<User>
          Mỗi User có: id, username, email, full_name, phone,
                       is_active (boolean), orders_count (số đơn đã đặt),
                       created_at
      - $keyword      : string — từ khóa tìm kiếm hiện tại
      - $status       : string — filter hiện tại ('active'|'locked'|'')
      - $statusCounts : array
          [
            'all'    => int,
            'active' => int,
            'locked' => int,
          ]

    Flash messages:
      - session('success') : string
      - session('error')   : string

    Các route thao tác:
      - Xem chi tiết : GET  admin.customers.show   /admin/customers/{customer}
      - Khóa TK      : PATCH admin.customers.lock  /admin/customers/{customer}/lock
      - Mở khóa      : PATCH admin.customers.unlock /admin/customers/{customer}/unlock
      (Cần form + @csrf + @method('PATCH'))

    Bảo vệ:
      - Không thể khóa tài khoản admin (403)
      - Không thể tự khóa tài khoản đang đăng nhập (flash error)
      - Tài khoản bị khóa (is_active=false) → không thể đăng nhập
        (AuthController đã tích hợp sẵn: Auth::attempt([..., 'is_active' => true]))
    ================================================================
--}}

{{-- ===== KHU VỰC BACKEND: DANH SÁCH KHÁCH HÀNG ===== --}}
<div style="border: 2px dashed #3b82f6; padding: 16px; margin: 16px;">
    <p><strong>[BACKEND DEMO]</strong> Trang quản lý khách hàng — Frontend thay thế bằng UI hoàn chỉnh.</p>

    {{-- Flash messages --}}
    @if(session('success'))
        <p style="color: green;">✅ {{ session('success') }}</p>
    @endif
    @if(session('error'))
        <p style="color: red;">❌ {{ session('error') }}</p>
    @endif

    <h2>Quản lý khách hàng</h2>

    {{-- Form tìm kiếm --}}
    <form method="GET" action="{{ route('admin.customers.index') }}" style="margin-bottom: 12px;">
        <input type="text" name="q" value="{{ $keyword }}"
               placeholder="Tìm theo tên, email, số điện thoại..."
               style="width: 280px; padding: 6px;">
        {{-- Giữ filter status khi search --}}
        @if($status)
            <input type="hidden" name="status" value="{{ $status }}">
        @endif
        <button type="submit">🔍 Tìm kiếm</button>
        @if($keyword)
            <a href="{{ route('admin.customers.index', $status ? ['status' => $status] : []) }}">✕ Xóa từ khóa</a>
        @endif
    </form>

    {{-- Tab lọc theo trạng thái --}}
    <div style="margin-bottom: 12px;">
        <strong>Lọc trạng thái:</strong>
        <a href="{{ route('admin.customers.index', $keyword ? ['q' => $keyword] : []) }}"
           style="{{ $status === '' ? 'font-weight:bold;' : '' }}">
            Tất cả ({{ $statusCounts['all'] }})
        </a>
        |
        <a href="{{ route('admin.customers.index', array_filter(['q' => $keyword, 'status' => 'active'])) }}"
           style="{{ $status === 'active' ? 'font-weight:bold; color:green;' : '' }}">
            🟢 Đang hoạt động ({{ $statusCounts['active'] }})
        </a>
        |
        <a href="{{ route('admin.customers.index', array_filter(['q' => $keyword, 'status' => 'locked'])) }}"
           style="{{ $status === 'locked' ? 'font-weight:bold; color:red;' : '' }}">
            🔒 Đã khóa ({{ $statusCounts['locked'] }})
        </a>
    </div>

    {{-- Bảng danh sách --}}
    <table border="1" cellpadding="8" style="width:100%;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Username</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <th>Số đơn</th>
                <th>Trạng thái</th>
                <th>Ngày đăng ký</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->id }}</td>
                    <td>{{ $customer->full_name }}</td>
                    <td><code>{{ $customer->username }}</code></td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone ?? '—' }}</td>
                    <td>{{ $customer->orders_count }}</td>
                    <td>
                        @if($customer->is_active)
                            <span style="color: green;">🟢 Đang hoạt động</span>
                        @else
                            <span style="color: red;">🔒 Đã khóa</span>
                        @endif
                    </td>
                    <td>{{ $customer->created_at->format('d/m/Y') }}</td>
                    <td>
                        {{-- Xem chi tiết --}}
                        <a href="{{ route('admin.customers.show', $customer) }}">Xem</a>

                        {{-- Khóa / Mở khóa --}}
                        @if($customer->is_active)
                            <form action="{{ route('admin.customers.lock', $customer) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        onclick="return confirm('Khóa tài khoản của {{ $customer->full_name }}?')">
                                    🔒 Khóa
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.customers.unlock', $customer) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        onclick="return confirm('Mở khóa tài khoản của {{ $customer->full_name }}?')">
                                    🔓 Mở khóa
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;">
                        Không tìm thấy khách hàng nào phù hợp.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Phân trang --}}
    <div style="margin-top: 12px;">
        {{ $customers->links() }}
    </div>
</div>
{{-- ===== KẾT THÚC KHU VỰC BACKEND ===== --}}

@endsection
