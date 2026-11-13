<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PayosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng, có lọc theo trạng thái.
     * Trạng thái: pending | shipping | completed | cancelled
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', '');

        $query = Order::with(['user', 'items.product'])
            ->latest('id');

        if ($status !== '') {
            $query->where('status', $status);
        }

        $orders = $query->paginate(15)->withQueryString();

        // Gộp thành 1 query GROUP BY thay vì 5 query COUNT(*) rời rạc
        $countsByStatus = Order::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $statusCounts = [
            'all'       => (int) $countsByStatus->sum(),
            'pending'   => (int) ($countsByStatus['pending'] ?? 0),
            'shipping'  => (int) ($countsByStatus['shipping'] ?? 0),
            'completed' => (int) ($countsByStatus['completed'] ?? 0),
            'cancelled' => (int) ($countsByStatus['cancelled'] ?? 0),
        ];

        return view('admin.orders.index', compact('orders', 'status', 'statusCounts'));
    }

    /**
     * Xem chi tiết một đơn hàng cụ thể.
     */
    public function show(Order $order): View
    {
        $order->load(['user', 'items.product']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái đơn hàng theo luồng:
     *   pending → shipping → completed
     * Không thể đảo ngược trạng thái.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,shipping,completed,cancelled',
        ]);

        $newStatus  = $request->input('status');
        $oldStatus  = $order->status;

        // Định nghĩa luồng hợp lệ (chỉ được đi tới, không được đi lui)
        $allowedTransitions = [
            'pending'  => ['shipping', 'cancelled'],
            'shipping' => ['completed'],
            'completed' => [],
            'cancelled' => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$oldStatus] ?? [])) {
            return redirect()->back()
                ->with('error', 'Không thể chuyển trạng thái đơn hàng từ "' . $oldStatus . '" sang "' . $newStatus . '".');
        }

        if ($newStatus === 'cancelled') {
            if ($order->payment_gateway === 'payos' && $order->payment_reference && !$order->isPaid()) {
                app(PayosService::class)->cancelPaymentLink($order->payment_reference, 'Cửa hàng hủy đơn hàng');
            }

            $order->cancelAndRestoreInventory(
                $order->payment_method === 'qr' ? Order::PAYMENT_STATUS_CANCELLED : null
            );
        } else {
            $updates = ['status' => $newStatus];
            if ($newStatus === 'completed' && $order->payment_method === 'cod' && !$order->isPaid()) {
                $updates['payment_status'] = Order::PAYMENT_STATUS_PAID;
                $updates['paid_at'] = now();
            }

            $order->update($updates);
        }

        $statusLabels = [
            'pending'   => 'Chờ xác nhận',
            'shipping'  => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        return redirect()->back()
            ->with('success', 'Đơn hàng #' . $order->code . ' đã chuyển sang "' . $statusLabels[$newStatus] . '" thành công!');
    }
}
