@extends('layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('content')
<div class="container" style="padding-top: 28px; padding-bottom: 50px;">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav" style="margin-bottom: 24px; font-size: 14px; color: var(--color-on-surface-variant);">
        <a href="{{ route('home') }}" style="color: inherit; text-decoration: none;">Trang chủ</a>
        <span style="margin: 0 8px;">/</span>
        <a href="{{ route('profile.show') }}" style="color: inherit; text-decoration: none;">Tài khoản</a>
        <span style="margin: 0 8px;">/</span>
        <span style="color: var(--color-primary); font-weight: 600;">Đơn hàng của tôi</span>
    </nav>

    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 800; color: var(--color-on-surface); margin: 0 0 6px;">
            Đơn hàng của tôi
        </h1>
        <p style="margin: 0; font-size: 14px; color: var(--color-on-surface-variant);">
            Theo dõi tiến độ giao hàng và lịch sử đặt hàng của bạn
        </p>
    </div>

    <!-- Thông báo Flash -->
    @if(session('success'))
        <div style="background: #ecfdf5; border-left: 4px solid #10b981; color: #065f46; padding: 14px 18px; border-radius: 8px; font-size: 14px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 14px 18px; border-radius: 8px; font-size: 14px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    <!-- Bộ lọc trạng thái (Status Tabs) -->
    <div style="display: flex; gap: 8px; border-bottom: 2px solid var(--color-outline-variant, #f0eae1); margin-bottom: 24px; overflow-x: auto; padding-bottom: 2px;">
        @php
            $tabs = [
                ''          => ['label' => 'Tất cả', 'key' => 'all'],
                'pending'   => ['label' => 'Chờ xử lý', 'key' => 'pending'],
                'shipping'  => ['label' => 'Đang giao', 'key' => 'shipping'],
                'completed' => ['label' => 'Hoàn thành', 'key' => 'completed'],
                'cancelled' => ['label' => 'Đã hủy', 'key' => 'cancelled'],
            ];
        @endphp

        @foreach($tabs as $tabKey => $tab)
            @php
                $isActive = ($status === $tabKey);
                $count = $statusCounts[$tab['key']] ?? 0;
            @endphp
            <a href="{{ route('orders.index', $tabKey !== '' ? ['status' => $tabKey] : []) }}" 
               style="padding: 10px 18px; font-size: 14px; font-weight: {{ $isActive ? '700' : '600' }}; text-decoration: none; border-bottom: 2px solid {{ $isActive ? 'var(--color-primary, #e76f51)' : 'transparent' }}; margin-bottom: -2px; color: {{ $isActive ? 'var(--color-primary, #e76f51)' : 'var(--color-on-surface-variant)' }}; white-space: nowrap; transition: all 0.2s;">
                {{ $tab['label'] }} ({{ $count }})
            </a>
        @endforeach
    </div>

    @if($orders->isEmpty())
        <!-- Trạng thái trống -->
        <div style="background: var(--color-surface, #ffffff); border-radius: 16px; padding: 50px 20px; text-align: center; border: 1px solid var(--color-outline-variant, #f0eae1);">
            <div style="font-size: 16px; font-weight: 700; color: var(--color-on-surface); margin-bottom: 6px;">
                Không có đơn hàng nào
            </div>
            <p style="color: var(--color-on-surface-variant); font-size: 14px; margin-bottom: 20px;">
                Bạn chưa có đơn hàng nào trong mục này.
            </p>
            <a href="{{ route('products.index') }}" class="btn btn--primary" style="padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block;">
                Mua sắm ngay
            </a>
        </div>
    @else
        <!-- Danh sách đơn hàng -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            @foreach($orders as $order)
                @php
                    $statusMap = [
                        'pending'   => ['label' => 'Chờ xử lý', 'bg' => '#fef3c7', 'color' => '#d97706'],
                        'shipping'  => ['label' => 'Đang giao hàng', 'bg' => '#e0e7ff', 'color' => '#4338ca'],
                        'completed' => ['label' => 'Hoàn thành', 'bg' => '#d1fae5', 'color' => '#059669'],
                        'cancelled' => ['label' => 'Đã hủy', 'bg' => '#fee2e2', 'color' => '#dc2626'],
                    ];
                    $st = $statusMap[$order->status] ?? ['label' => $order->status, 'bg' => '#f3f4f6', 'color' => '#6b7280'];
                @endphp

                <div style="background: var(--color-surface, #ffffff); border-radius: 16px; border: 1px solid var(--color-outline-variant, #f0eae1); padding: 22px; box-shadow: 0 4px 16px rgba(0,0,0,0.02);">
                    <!-- Header của Card đơn hàng -->
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-outline-variant, #f0eae1); padding-bottom: 14px; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <span style="font-weight: 800; font-size: 15px; color: var(--color-on-surface); margin-right: 10px;">
                                Đơn hàng #{{ $order->code }}
                            </span>
                            <span style="font-size: 13px; color: var(--color-on-surface-variant);">
                                Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <span style="display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; background: {{ $st['bg'] }}; color: {{ $st['color'] }};">
                            {{ $st['label'] }}
                        </span>
                    </div>

                    <!-- Danh sách các sản phẩm trong đơn -->
                    <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px;">
                        @foreach($order->items as $item)
                            @php
                                $img = $item->product->image ?? null;
                                $imgSrc = $img 
                                    ? (str_starts_with($img, 'http') ? $img : asset(ltrim($img, '/')))
                                    : asset('images/logo.png');
                            @endphp
                            <div style="display: flex; gap: 14px; align-items: center;">
                                <img src="{{ $imgSrc }}" alt="{{ $item->product->name ?? 'Sản phẩm' }}" 
                                     style="width: 56px; height: 56px; object-fit: cover; border-radius: 8px; border: 1px solid #f0eae1; flex-shrink: 0;"
                                     onerror="this.src='{{ asset('images/logo.png') }}'">
                                <div style="flex-grow: 1; min-width: 0;">
                                    <div style="font-weight: 600; font-size: 14px; color: var(--color-on-surface); line-height: 1.4;">
                                        {{ $item->product->name ?? 'Sản phẩm' }}
                                    </div>
                                    <div style="font-size: 12px; color: var(--color-on-surface-variant); margin-top: 2px;">
                                        Số lượng: {{ $item->quantity }} x {{ number_format($item->unit_price, 0, ',', '.') }}đ
                                    </div>
                                </div>
                                <div style="font-weight: 700; font-size: 14px; color: var(--color-on-surface); flex-shrink: 0;">
                                    {{ number_format($item->total_price, 0, ',', '.') }}đ
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Footer của Card đơn hàng: Tổng tiền & Các nút thao tác -->
                    <div style="border-top: 1px solid var(--color-outline-variant, #f0eae1); padding-top: 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                        <div style="font-size: 14px;">
                            <span style="color: var(--color-on-surface-variant);">Tổng thanh toán: </span>
                            <strong style="font-size: 17px; font-weight: 800; color: var(--color-primary, #e76f51);">
                                {{ number_format($order->total_amount, 0, ',', '.') }}đ
                            </strong>
                        </div>

                        <div style="display: flex; gap: 10px; align-items: center;">
                            @if($order->status === 'pending')
                                <form action="{{ route('orders.cancel', $order->id) }}" method="POST" 
                                      onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng #{{ $order->code }}?');">
                                    @csrf
                                    <button type="submit" 
                                            style="padding: 8px 16px; border: 1px solid #ef4444; background: #fff; color: #ef4444; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background 0.2s;"
                                            onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
                                        Hủy đơn hàng
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('orders.show', $order->id) }}" 
                               style="padding: 8px 18px; background: #374151; color: #fff; border-radius: 6px; font-size: 13px; font-weight: 600; text-decoration: none;">
                                Chi tiết đơn hàng
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Phân trang -->
            <div style="margin-top: 10px;">
                {{ $orders->links('pagination::bootstrap-4') }}
            </div>
        </div>
    @endif

</div>
@endsection
