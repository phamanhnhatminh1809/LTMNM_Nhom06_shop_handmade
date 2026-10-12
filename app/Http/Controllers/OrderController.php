<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng của khách hàng hiện tại, có lọc theo trạng thái.
     * Trạng thái: pending | shipping | completed | cancelled
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $status = $request->input('status', '');

        $query = Order::where('user_id', $userId)
            ->with(['items.product'])
            ->latest('id');

        if ($status !== '' && in_array($status, ['pending', 'shipping', 'completed', 'cancelled'])) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(10)->withQueryString();

        $statusCounts = [
            'all'       => Order::where('user_id', $userId)->count(),
            'pending'   => Order::where('user_id', $userId)->where('status', 'pending')->count(),
            'shipping'  => Order::where('user_id', $userId)->where('status', 'shipping')->count(),
            'completed' => Order::where('user_id', $userId)->where('status', 'completed')->count(),
            'cancelled' => Order::where('user_id', $userId)->where('status', 'cancelled')->count(),
        ];

        return view('orders.index', compact('orders', 'status', 'statusCounts'));
    }

    /**
     * Xem chi tiết một đơn hàng cụ thể của khách hàng.
     */
    public function show(Order $order): View
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem đơn hàng này.');
        }

        $order->load(['items.product', 'items.review']);

        return view('orders.show', compact('order'));
    }

    /**
     * Hủy đơn hàng khi đơn hàng còn ở trạng thái chờ xử lý (pending).
     * Hoàn lại số lượng tồn kho và cập nhật số lượng đã bán.
     */
    public function cancel(Order $order): RedirectResponse
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác trên đơn hàng này.');
        }

        if ($order->status !== 'pending') {
            return back()->with('error', 'Chỉ có thể hủy đơn hàng khi đơn hàng đang ở trạng thái Chờ xác nhận.');
        }

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'cancelled']);

            // Hoàn lại tồn kho cho từng sản phẩm
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock_quantity', $item->quantity);
                        if ($product->sold >= $item->quantity) {
                            $product->decrement('sold', $item->quantity);
                        }
                    }
                }
            }
        });

        return back()->with('success', 'Đơn hàng #' . $order->code . ' đã được hủy thành công.');
    }
}
