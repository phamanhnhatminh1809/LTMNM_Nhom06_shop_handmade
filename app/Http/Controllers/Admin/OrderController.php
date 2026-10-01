<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
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

        // Đếm số lượng theo từng trạng thái để hiển thị badge tab
        $statusCounts = [
            'all'       => Order::count(),
            'pending'   => Order::where('status', 'pending')->count(),
            'shipping'  => Order::where('status', 'shipping')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
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

        $order->update(['status' => $newStatus]);

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
