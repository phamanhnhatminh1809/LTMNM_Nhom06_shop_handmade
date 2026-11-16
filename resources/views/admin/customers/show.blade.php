@extends('layouts.admin')

@section('title', 'Chi tiết khách hàng — ' . $customer->full_name)

@php $activeMenu = 'customers'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-person-lines-fill"></i>
        <span>Hồ sơ khách hàng</span>
    </div>
    <div class="top-bar-right">
        <a href="{{ route('admin.customers.index') }}" class="btn-secondary-outline">
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại danh sách</span>
        </a>
    </div>
</div>

<!-- Flash Notifications -->
@if(session('success'))
    <div class="admin-alert success">
        <div class="admin-alert-content">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="admin-alert error">
        <div class="admin-alert-content">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ session('error') }}</span>
        </div>
    </div>
@endif

<div class="admin-detail-grid" style="grid-template-columns: 1fr 1.6fr;">
    <!-- Cột trái: Thông tin hồ sơ & Khóa/Mở khóa -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-person-circle"></i>
                    <span>Thông tin tài khoản</span>
                </h3>
                @if($customer->is_active)
                    <span class="status-badge active-user">
                        <i class="bi bi-check-circle"></i> Đang hoạt động
                    </span>
                @else
                    <span class="status-badge locked">
                        <i class="bi bi-lock-fill"></i> Đã khóa
                    </span>
                @endif
            </div>

            <div class="admin-card-body">
                <div style="display: flex; align-items: center; gap: 16px; padding-bottom: 18px; margin-bottom: 16px; border-bottom: 1px solid #f4eae6;">
                    <div class="customer-avatar-abbr" style="width: 56px; height: 56px; font-size: 20px; border-radius: 14px;">
                        {{ mb_strtoupper(mb_substr($customer->full_name, 0, 2)) }}
                    </div>
                    <div>
                        <div style="font-size: 18px; font-weight: 800; color: var(--color-on-surface);">
                            {{ $customer->full_name }}
                        </div>
                        <div style="margin-top: 4px;">
                            <span class="mono-code">@{{ $customer->username }}</span>
                        </div>
                    </div>
                </div>

                <div class="info-kv-list">
                    <div class="info-kv-row">
                        <span class="info-kv-label">Mã khách hàng</span>
                        <span class="info-kv-value"><span class="mono-code">#{{ $customer->id }}</span></span>
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">Địa chỉ Email</span>
                        <span class="info-kv-value">{{ $customer->email }}</span>
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">Số điện thoại</span>
                        <span class="info-kv-value">
                            <span class="mono-code">{{ $customer->phone ?? 'Chưa cập nhật' }}</span>
                        </span>
                    </div>
                    @if(!empty($customer->address))
                        <div class="info-kv-row">
                            <span class="info-kv-label">Địa chỉ mặc định</span>
                            <span class="info-kv-value">{{ $customer->address }}</span>
                        </div>
                    @endif
                    <div class="info-kv-row">
                        <span class="info-kv-label">Ngày đăng ký</span>
                        <span class="info-kv-value">{{ $customer->created_at ? $customer->created_at->format('d/m/Y H:i') : '—' }}</span>
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">Quyền đăng nhập</span>
                        <span class="info-kv-value">
                            @if($customer->is_active)
                                <span style="color: var(--color-success);">Được phép đăng nhập</span>
                            @else
                                <span style="color: var(--color-danger);">Đã chặn đăng nhập</span>
                            @endif
                        </span>
                    </div>
                </div>

                <div style="margin-top: 20px; padding-top: 18px; border-top: 1px solid #f4eae6;">
                    @if($customer->is_active)
                        <form action="{{ route('admin.customers.lock', $customer) }}"
                              method="POST"
                              data-confirm="Xác nhận khóa tài khoản của «{{ $customer->full_name }}»? Khách hàng sẽ không thể đăng nhập ngay sau khi khóa."
                              data-confirm-title="Khóa tài khoản khách hàng?"
                              data-confirm-label="Khóa tài khoản"
                              data-confirm-icon="bi-lock-fill"
                              data-confirm-variant="danger">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-inline-action danger" style="width: 100%; justify-content: center; padding: 11px 16px; font-size: 13.5px;">
                                <i class="bi bi-lock-fill"></i>
                                <span>Khóa tài khoản khách hàng này</span>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.customers.unlock', $customer) }}"
                              method="POST"
                              data-confirm="Xác nhận mở khóa tài khoản cho «{{ $customer->full_name }}»?"
                              data-confirm-title="Mở khóa tài khoản?"
                              data-confirm-label="Mở khóa"
                              data-confirm-icon="bi-unlock-fill"
                              data-confirm-variant="success">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-inline-action success" style="width: 100%; justify-content: center; padding: 11px 16px; font-size: 13.5px;">
                                <i class="bi bi-unlock-fill"></i>
                                <span>Mở khóa tài khoản khách hàng này</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Cột phải: Lịch sử 10 đơn hàng gần nhất -->
    <div class="table-card" style="margin-bottom: 0;">
        <div class="table-header-flex">
            <div>
                <h3 style="font-size: 15px; font-weight: 800; color: var(--color-on-surface); margin-bottom: 3px;">
                    Lịch sử đơn hàng gần nhất ({{ $customer->orders->count() }})
                </h3>
                <p style="font-size: 12px; color: var(--color-outline);">Hiển thị tối đa 10 đơn hàng mới nhất của khách hàng này</p>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <table class="admin-data-table">
                <thead>
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Sản phẩm</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                        <th style="text-align: center;">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customer->orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" style="text-decoration: none;">
                                    <span class="mono-code">#{{ $order->code }}</span>
                                </a>
                            </td>
                            <td>
                                <span style="font-weight: 700;">{{ $order->items->count() }} sản phẩm</span>
                            </td>
                            <td>
                                <span class="total-amount-text">{{ number_format($order->total_amount, 0, ',', '.') }}đ</span>
                            </td>
                            <td>
                                @if($order->status === 'completed')
                                    <span class="status-badge completed">
                                        <i class="bi bi-check-circle"></i> Hoàn thành
                                    </span>
                                @elseif($order->status === 'shipping')
                                    <span class="status-badge shipping">
                                        <i class="bi bi-truck"></i> Đang giao
                                    </span>
                                @elseif($order->status === 'cancelled')
                                    <span class="status-badge cancelled">
                                        <i class="bi bi-x-circle"></i> Đã hủy
                                    </span>
                                @else
                                    <span class="status-badge pending">
                                        <i class="bi bi-clock"></i> Chờ xác nhận
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span style="font-size: 13px; color: var(--color-on-surface-variant);">
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <x-table-actions>
                                    <x-table-action type="view" :href="route('admin.orders.show', $order)" label="Xem chi tiết đơn hàng" tooltip-align="end" />
                                </x-table-actions>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 32px; color: var(--color-outline);">
                                Khách hàng này chưa có đơn hàng nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
