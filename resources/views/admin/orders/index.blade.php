@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@php $activeMenu = 'orders'; @endphp

@section('content')
{{--
    ================================================================
    [BACKEND ĐÃ CÓ] — Dành cho Frontend
    ================================================================
    Controller : App\Http\Controllers\Admin\OrderController@index
    Route      : GET /admin/orders  (admin.orders.index)
                 Query param: ?status=pending|shipping|completed|cancelled

    Dữ liệu truyền vào view:
      - $orders       : LengthAwarePaginator<Order>
          Mỗi Order có: id, code, user, shipping_name, shipping_phone,
                        shipping_address, total_amount, status, created_at
          Relation: user (User), items (OrderItem collection)

      - $status       : string — filter hiện tại ('pending'|'shipping'|'completed'|'cancelled'|'')
      - $statusCounts : array — đếm đơn theo từng trạng thái
          [
            'all'       => int,
            'pending'   => int,
            'shipping'  => int,
            'completed' => int,
            'cancelled' => int,
          ]

    Flash messages:
      - session('success') : string
      - session('error')   : string

    Trạng thái luồng (chỉ 1 chiều, không đảo ngược):
      pending → shipping → completed
      pending → cancelled  (hủy đơn)

    Route update status:
      PATCH /admin/orders/{order}/status  (admin.orders.updateStatus)
      Body: status=shipping|completed|cancelled
    ================================================================
--}}

{{-- ===== KHU VỰC BACKEND: DANH SÁCH ĐƠN HÀNG ===== --}}
<div style="border: 2px dashed #3b82f6; padding: 16px; margin: 16px;">
    <p><strong>[BACKEND DEMO]</strong> Trang quản lý đơn hàng — Frontend thay thế bằng UI hoàn chỉnh.</p>

    {{-- Flash message --}}
    @if(session('success'))
        <p style="color: green;">✅ {{ session('success') }}</p>
    @endif
    @if(session('error'))
        <p style="color: red;">❌ {{ session('error') }}</p>
    @endif

    <h2>Quản lý đơn hàng</h2>

    {{-- Tab lọc theo trạng thái với badge số lượng --}}
    <div style="margin: 12px 0;">
        <strong>Lọc theo trạng thái:</strong><br>
        <a href="{{ route('admin.orders.index') }}"
           style="{{ $status === '' ? 'font-weight:bold;' : '' }}">
            Tất cả ({{ $statusCounts['all'] }})
        </a>
        |
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
           style="{{ $status === 'pending' ? 'font-weight:bold;' : '' }}">
            ⏳ Chờ xác nhận ({{ $statusCounts['pending'] }})
        </a>
        |
        <a href="{{ route('admin.orders.index', ['status' => 'shipping']) }}"
           style="{{ $status === 'shipping' ? 'font-weight:bold;' : '' }}">
            🚚 Đang giao ({{ $statusCounts['shipping'] }})
        </a>
        |
        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}"
           style="{{ $status === 'completed' ? 'font-weight:bold;' : '' }}">
            ✅ Hoàn thành ({{ $statusCounts['completed'] }})
        </a>
        |
        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}"
           style="{{ $status === 'cancelled' ? 'font-weight:bold;' : '' }}">
            ❌ Đã hủy ({{ $statusCounts['cancelled'] }})
        </a>
    </div>

    {{-- Bảng đơn hàng --}}
    <table border="1" cellpadding="8" style="width:100%; margin-top: 8px;">
        <thead>
            <tr>
                <th>Mã ĐH</th>
                <th>Khách hàng</th>
                <th>SĐT</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày đặt</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td><strong>#{{ $order->code }}</strong></td>
                    <td>{{ $order->shipping_name }}</td>
                    <td>{{ $order->shipping_phone }}</td>
                    <td>{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                    <td>
                        @php
                            $statusLabels = [
                                'pending'   => '⏳ Chờ xác nhận',
                                'shipping'  => '🚚 Đang giao',
                                'completed' => '✅ Hoàn thành',
                                'cancelled' => '❌ Đã hủy',
                            ];
                        @endphp
                        {{ $statusLabels[$order->status] ?? $order->status }}
                    </td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        {{-- Xem chi tiết --}}
                        <a href="{{ route('admin.orders.show', $order) }}">Xem</a>

                        {{-- Duyệt đơn: pending → shipping --}}
                        @if($order->status === 'pending')
                            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="shipping">
                                <button type="submit" onclick="return confirm('Xác nhận duyệt và giao đơn #{{ $order->code }}?')">
                                    ▶ Duyệt & Giao
                                </button>
                            </form>
                            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" onclick="return confirm('Hủy đơn #{{ $order->code }}?')">
                                    ✕ Hủy đơn
                                </button>
                            </form>
                        @endif

                        {{-- Cập nhật: shipping → completed --}}
                        @if($order->status === 'shipping')
                            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" onclick="return confirm('Xác nhận hoàn thành đơn #{{ $order->code }}?')">
                                    ✅ Hoàn thành
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">Không có đơn hàng nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Phân trang --}}
    <div style="margin-top: 12px;">
        {{ $orders->withQueryString()->links() }}
    </div>
</div>
{{-- ===== KẾT THÚC KHU VỰC BACKEND ===== --}}

@endsection
