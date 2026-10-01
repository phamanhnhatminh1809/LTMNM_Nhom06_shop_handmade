@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng #' . $order->code)

@php $activeMenu = 'orders'; @endphp

@section('content')
{{--
    ================================================================
    [BACKEND ĐÃ CÓ] — Dành cho Frontend
    ================================================================
    Controller : App\Http\Controllers\Admin\OrderController@show
    Route      : GET /admin/orders/{order}  (admin.orders.show)

    Dữ liệu truyền vào view:
      - $order : Order — đã load:
          - user      : User (nullable) — khách hàng đặt hàng
          - items     : Collection<OrderItem> — các sản phẩm trong đơn
              Mỗi OrderItem có: id, quantity, unit_price, total_price
              + product (Product object): id, name, code, image

      $order fields:
        id, code, shipping_name, shipping_phone, shipping_address,
        total_amount, status, created_at, updated_at

    Trạng thái luồng & route cập nhật:
      PATCH /admin/orders/{order}/status  (admin.orders.updateStatus)
      pending  → [shipping, cancelled]
      shipping → [completed]

    Luồng trạng thái: pending ➔ shipping ➔ completed
                      pending ➔ cancelled
    ================================================================
--}}

{{-- ===== KHU VỰC BACKEND: CHI TIẾT ĐƠN HÀNG ===== --}}
<div style="border: 2px dashed #3b82f6; padding: 16px; margin: 16px;">
    <p><strong>[BACKEND DEMO]</strong> Trang chi tiết đơn hàng — Frontend thay thế bằng UI hoàn chỉnh.</p>

    {{-- Flash messages --}}
    @if(session('success'))
        <p style="color: green;">✅ {{ session('success') }}</p>
    @endif
    @if(session('error'))
        <p style="color: red;">❌ {{ session('error') }}</p>
    @endif

    <h2>Đơn hàng: #{{ $order->code }}</h2>
    <a href="{{ route('admin.orders.index') }}">← Quay lại danh sách</a>

    {{-- Thông tin giao hàng --}}
    <h3>📦 Thông tin giao hàng</h3>
    <table border="1" cellpadding="8">
        <tr><th>Tên người nhận</th><td>{{ $order->shipping_name }}</td></tr>
        <tr><th>Số điện thoại</th><td>{{ $order->shipping_phone }}</td></tr>
        <tr><th>Địa chỉ</th><td>{{ $order->shipping_address }}</td></tr>
        <tr><th>Tài khoản</th><td>{{ $order->user?->full_name ?? '(Khách vãng lai)' }}</td></tr>
        <tr><th>Tổng tiền</th><td><strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong></td></tr>
        <tr><th>Ngày đặt</th><td>{{ $order->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr>
            <th>Trạng thái</th>
            <td>
                @php
                    $statusLabels = [
                        'pending'   => '⏳ Chờ xác nhận',
                        'shipping'  => '🚚 Đang giao',
                        'completed' => '✅ Hoàn thành',
                        'cancelled' => '❌ Đã hủy',
                    ];
                @endphp
                <strong>{{ $statusLabels[$order->status] ?? $order->status }}</strong>
            </td>
        </tr>
    </table>

    {{-- Nút cập nhật trạng thái --}}
    <h3>🔄 Cập nhật trạng thái</h3>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        @if($order->status === 'pending')
            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="shipping">
                <button type="submit" onclick="return confirm('Duyệt đơn và chuyển sang Đang giao?')">
                    ▶ Duyệt & Giao hàng
                </button>
            </form>
            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" onclick="return confirm('Xác nhận hủy đơn này?')">
                    ✕ Hủy đơn
                </button>
            </form>
        @elseif($order->status === 'shipping')
            <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="completed">
                <button type="submit" onclick="return confirm('Xác nhận đơn đã giao thành công?')">
                    ✅ Đánh dấu Hoàn thành
                </button>
            </form>
        @else
            <em>Đơn hàng đã ở trạng thái cuối, không thể cập nhật thêm.</em>
        @endif
    </div>

    {{-- Danh sách sản phẩm trong đơn --}}
    <h3>🛒 Sản phẩm trong đơn ({{ $order->items->count() }} sản phẩm)</h3>
    <table border="1" cellpadding="8" style="width:100%;">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Mã SKU</th>
                <th>Đơn giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        @if($item->product)
                            <img src="{{ asset(ltrim($item->product->image, '/')) }}"
                                 alt="{{ $item->product->name }}"
                                 style="width:40px; vertical-align:middle; margin-right:6px;"
                                 onerror="this.style.display='none'">
                            {{ $item->product->name }}
                        @else
                            (Sản phẩm đã bị xóa)
                        @endif
                    </td>
                    <td>{{ $item->product?->code ?? '—' }}</td>
                    <td>{{ number_format($item->unit_price, 0, ',', '.') }}đ</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->total_price, 0, ',', '.') }}đ</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right;"><strong>Tổng cộng:</strong></td>
                <td><strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong></td>
            </tr>
        </tfoot>
    </table>
</div>
{{-- ===== KẾT THÚC KHU VỰC BACKEND ===== --}}

@endsection
