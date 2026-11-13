<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Services\PayosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $countsByStatus = Order::where('user_id', $userId)
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

        if ($order->isPaid()) {
            return back()->with('error', 'Đơn hàng này đã thanh toán trực tuyến. Vui lòng liên hệ cửa hàng để được hỗ trợ hoàn tiền trước khi hủy.');
        }

        if ($order->payment_gateway === 'payos' && $order->payment_reference) {
            app(PayosService::class)->cancelPaymentLink($order->payment_reference, 'Khách hàng chủ động hủy đơn');
        }

        $order->cancelAndRestoreInventory(
            $order->payment_method === 'qr' ? Order::PAYMENT_STATUS_CANCELLED : null
        );

        return back()->with('success', 'Đơn hàng #' . $order->code . ' đã được hủy thành công.');
    }

    /**
     * Mua lại đơn hàng cũ (Thêm các sản phẩm vào giỏ hiện tại).
     */
    public function reorder(Order $order): RedirectResponse
    {
        // Kiểm tra quyền sở hữu đơn hàng
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền thực hiện thao tác trên đơn hàng này.');
        }

        // Lấy giỏ hàng đang hoạt động (hoặc tạo mới nếu chưa có)
        // Giả sử Model Cart của bạn có hàm getActiveCart()
        $cart = Cart::getActiveCart();
        
        $addedCount = 0;
        $errorMessages = [];

        foreach ($order->items as $item) {
            $product = $item->product;
            
            // Bỏ qua nếu sản phẩm đã bị xóa khỏi shop hoặc hết sạch hàng
            if (!$product || $product->stock_quantity <= 0) {
                $errorMessages[] = "Sản phẩm \"" . ($product->name ?? 'không xác định') . "\" hiện đã hết hàng.";
                continue;
            }

            $desiredQty = (int) $item->quantity;
            if ($product->stock_quantity < $desiredQty) {
                $desiredQty = (int) $product->stock_quantity;
                $errorMessages[] = "Sản phẩm \"{$product->name}\" chỉ còn {$product->stock_quantity} cái nên đã điều chỉnh số lượng.";
            }

            // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
            $existingDetail = $cart->details()->where('product_id', $product->id)->first();

            if ($existingDetail) {
                // Nếu có rồi, cộng dồn số lượng (không vượt quá tồn kho)
                $newQty = $existingDetail->quantity + $desiredQty;
                if ($newQty > $product->stock_quantity) {
                    $newQty = $product->stock_quantity;
                    $errorMessages[] = "Sản phẩm \"{$product->name}\" đã được thêm tối đa (chỉ còn {$product->stock_quantity} cái).";
                }
                $existingDetail->update([
                    'quantity'   => $newQty,
                    'unit_price' => $product->price,
                    'image_url'  => $product->image,
                ]);
            } else {
                // Nếu chưa có, thêm mới vào giỏ
                $cart->details()->create([
                    'product_id'   => $product->id,
                    'product_code' => $product->code,
                    'name'         => $product->name,
                    'image_url'    => $product->image,
                    'quantity'     => $desiredQty,
                    'unit_price'   => $product->price, // Lấy giá hiện tại, không lấy giá cũ
                ]);
            }
            $addedCount++;
        }

        $cart->recalculateTotals();

        if ($addedCount > 0) {
            $msg = "Đã thêm {$addedCount} sản phẩm vào giỏ hàng.";
            if (!empty($errorMessages)) {
                $msg .= " Lưu ý: " . implode(' ', $errorMessages);
            }
            return redirect()->route('cart.index')->with('success', $msg);
        }

        return redirect()->back()->with('error', 'Không thể mua lại. Sản phẩm có thể đã hết hàng hoặc ngừng kinh doanh.');
    }
}