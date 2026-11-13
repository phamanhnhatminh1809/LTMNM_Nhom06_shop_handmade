<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\AdminNotificationService;
use App\Services\PayosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly PayosService $payosService
    ) {}

    private function calculateShippingFee(float $subtotal): float
    {
        $threshold = (float) config('shop.shipping.free_threshold', 300000);
        $defaultFee = (float) config('shop.shipping.default_fee', 30000);

        return $subtotal >= $threshold ? 0.0 : $defaultFee;
    }

    private function authorizeOrderAccess(Order $order): void
    {
        if (Auth::check()) {
            if ($order->user_id !== Auth::id()) {
                abort(403, 'Bạn không có quyền xem đơn hàng này.');
            }
            return;
        }

        $guestIds = (array) session('guest_order_ids', []);
        $lastOrderId = (int) session('last_order_id', 0);

        if ($order->user_id !== null || ($order->id !== $lastOrderId && !in_array($order->id, $guestIds, true))) {
            abort(403, 'Bạn không có quyền xem đơn hàng này.');
        }
    }

    private function rememberGuestOrder(Order $order): void
    {
        session()->put('last_order_id', $order->id);

        $guestIds = (array) session('guest_order_ids', []);
        if (!in_array($order->id, $guestIds, true)) {
            $guestIds[] = $order->id;
            session()->put('guest_order_ids', $guestIds);
        }
    }

    private function markOrderPaid(Order $order, ?string $transactionId = null): bool
    {
        $marked = DB::transaction(function () use ($order, $transactionId): bool {
            $locked = Order::lockForUpdate()->find($order->id);
            if (!$locked || $locked->isPaid()) {
                return false;
            }

            $locked->update([
                'payment_status'         => Order::PAYMENT_STATUS_PAID,
                'payment_transaction_id' => $transactionId ?: $locked->payment_transaction_id,
                'paid_at'                => now(),
            ]);

            return true;
        });

        if ($marked) {
            $order->refresh();
        }

        return $marked;
    }

    private function redirectAfterCancel(Order $order, string $message): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('orders.show', $order->id)->with('error', $message);
        }

        return redirect()->route('cart.index')->with('error', $message);
    }

    public function index(): View|RedirectResponse
    {
        $cart = Cart::getActiveCart();
        $cart->load(['details.product']);

        if ($cart->details->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm trước khi thanh toán.');
        }

        $user = Auth::user();
        $shippingFee = $this->calculateShippingFee((float) $cart->total_amount);
        $freeThreshold = (float) config('shop.shipping.free_threshold', 300000);
        $isPayosConfigured = $this->payosService->isConfigured();

        return view('checkout.index', compact(
            'cart',
            'user',
            'shippingFee',
            'freeThreshold',
            'isPayosConfigured'
        ));
    }

    public function process(Request $request): RedirectResponse
    {
        $cart = Cart::getActiveCart();
        $cart->load(['details.product']);

        if ($cart->details->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $validated = $request->validate([
            'shipping_name'    => 'required|string|max:100',
            'shipping_phone'   => ['required', 'string', 'max:20', 'regex:/^[0-9+\s\-]{8,20}$/'],
            'shipping_address' => 'required|string|max:500',
            'payment_method'   => 'required|in:cod,qr',
        ]);

        $isQr = $validated['payment_method'] === 'qr';
        if ($isQr && !$this->payosService->isConfigured()) {
            return back()
                ->withInput()
                ->with('error', 'Thanh toán QR qua PayOS chưa được cấu hình (PAYOS_CLIENT_ID, PAYOS_API_KEY, PAYOS_CHECKSUM_KEY trong .env). Vui lòng điền đủ cấu hình hoặc chọn COD.');
        }

        try {
            $order = DB::transaction(function () use ($cart, $validated, $isQr) {
                $shippingFee = $this->calculateShippingFee((float) $cart->total_amount);
                $finalTotal = max(0, (float) $cart->total_amount + $shippingFee);

                foreach ($cart->details as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item->product_id);
                    if ($product->stock_quantity < $item->quantity) {
                        throw new \Exception("Sản phẩm \"{$item->name}\" không đủ tồn kho.");
                    }
                }

                do {
                    $orderCode = 'ORD-' . strtoupper(Str::random(8));
                } while (Order::where('code', $orderCode)->exists());

                $order = Order::create([
                    'code'             => $orderCode,
                    'user_id'          => Auth::id(),
                    'shipping_name'    => $validated['shipping_name'],
                    'shipping_phone'   => $validated['shipping_phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'payment_method'   => $validated['payment_method'],
                    'payment_status'   => Order::PAYMENT_STATUS_UNPAID,
                    'payment_gateway'  => $isQr ? 'payos' : null,
                    'shipping_fee'     => $shippingFee,
                    'discount_amount'  => 0,
                    'total_amount'     => $finalTotal,
                    'status'           => 'pending',
                ]);

                foreach ($cart->details as $item) {
                    $product = Product::findOrFail($item->product_id);
                    OrderItem::create([
                        'order_id'    => $order->id,
                        'product_id'  => $item->product_id,
                        'quantity'    => $item->quantity,
                        'unit_price'  => $item->unit_price,
                        'total_price' => $item->subtotal,
                    ]);
                    $product->decrement('stock_quantity', $item->quantity);
                    $product->increment('sold', $item->quantity);
                }

                $cart->status = 'converted';
                $cart->total_quantity = 0;
                $cart->save();
                $cart->details()->delete();

                if ($isQr) {
                    $payosOrderCode = $this->payosService->generateUniqueOrderCode($order);
                    $payosData = $this->payosService->createPaymentLink(
                        $order,
                        $payosOrderCode,
                        route('checkout.payos.return'),
                        route('checkout.payos.cancel')
                    );

                    $order->update([
                        'payment_reference'      => (int) ($payosData['orderCode'] ?? $payosOrderCode),
                        'payment_transaction_id' => $payosData['paymentLinkId'] ?? null,
                        'checkout_url'           => $payosData['checkoutUrl'],
                    ]);
                }

                return $order;
            });

            $this->rememberGuestOrder($order);

            // Phát thông báo đơn hàng mới và kiểm tra ngưỡng tồn kho sản phẩm
            $notificationService = app(AdminNotificationService::class);
            $notificationService->notifyNewOrder($order);
            $order->loadMissing('items.product');
            foreach ($order->items as $orderItem) {
                if ($orderItem->product) {
                    $notificationService->syncProductStockAlert($orderItem->product);
                }
            }

            if ($isQr && !empty($order->checkout_url)) {
                return redirect()->away($order->checkout_url);
            }

            return redirect()->route('checkout.success', $order->id)
                ->with('success', 'Đặt hàng thành công!');

        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Xử lý callback returnUrl từ PayOS sau khi khách hoàn tất chuyển khoản.
     */
    public function payosReturn(Request $request): RedirectResponse
    {
        $payosOrderCode = (int) $request->query('orderCode', 0);
        if ($payosOrderCode <= 0) {
            return redirect()->route('home')->with('error', 'Thiếu mã tham chiếu giao dịch PayOS.');
        }

        $order = Order::where('payment_reference', $payosOrderCode)->first();
        if (!$order) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng tương ứng với mã giao dịch PayOS.');
        }

        $this->authorizeOrderAccess($order);

        if ($order->isPaid()) {
            return redirect()->route('checkout.success', $order->id)
                ->with('success', 'Đơn hàng #' . $order->code . ' đã được thanh toán QR thành công!');
        }

        try {
            $info = $this->payosService->getPaymentRequestInfo($payosOrderCode);
            $payosStatus = strtoupper((string) ($info['status'] ?? ''));
            $amountPaid = (float) ($info['amountPaid'] ?? $info['amount'] ?? 0);

            if ($payosStatus === 'PAID' && $amountPaid >= (float) $order->total_amount) {
                $this->markOrderPaid($order, $info['id'] ?? $order->payment_transaction_id);

                return redirect()->route('checkout.success', $order->id)
                    ->with('success', 'Thanh toán QR qua PayOS thành công cho đơn hàng #' . $order->code . '!');
            }

            if (in_array($payosStatus, ['CANCELLED', 'EXPIRED'], true)) {
                $order->cancelAndRestoreInventory(Order::PAYMENT_STATUS_CANCELLED);

                return $this->redirectAfterCancel(
                    $order,
                    'Giao dịch thanh toán QR đã bị hủy hoặc hết hạn. Đơn hàng #' . $order->code . ' đã được hủy và hoàn lại tồn kho.'
                );
            }

            return redirect()->route('checkout.success', $order->id)
                ->with('warning', 'Đơn hàng #' . $order->code . ' đang chờ PayOS xác nhận thanh toán.');
        } catch (\Throwable $e) {
            return redirect()->route('checkout.success', $order->id)
                ->with('warning', 'Chưa thể đối soát ngay với PayOS. Bạn có thể bấm "Kiểm tra thanh toán" hoặc xem lại trong chi tiết đơn hàng.');
        }
    }

    /**
     * Xử lý callback cancelUrl khi khách bấm hủy thanh toán trên PayOS:
     * xác minh chắc chắn chưa thanh toán rồi hủy đơn và hoàn kho ngay.
     */
    public function payosCancel(Request $request): RedirectResponse
    {
        $payosOrderCode = (int) $request->query('orderCode', 0);
        if ($payosOrderCode <= 0) {
            return redirect()->route('home')->with('error', 'Thiếu mã tham chiếu giao dịch PayOS.');
        }

        $order = Order::where('payment_reference', $payosOrderCode)->first();
        if (!$order) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng tương ứng để hủy.');
        }

        $this->authorizeOrderAccess($order);

        try {
            $info = $this->payosService->getPaymentRequestInfo($payosOrderCode);
            $payosStatus = strtoupper((string) ($info['status'] ?? ''));
            $amountPaid = (float) ($info['amountPaid'] ?? $info['amount'] ?? 0);

            if ($payosStatus === 'PAID' && $amountPaid >= (float) $order->total_amount) {
                $this->markOrderPaid($order, $info['id'] ?? $order->payment_transaction_id);

                return redirect()->route('checkout.success', $order->id)
                    ->with('success', 'Thanh toán QR qua PayOS đã được xác nhận thành công cho đơn #' . $order->code . '!');
            }
        } catch (\Throwable $e) {
            // Tiếp tục xử lý hủy đơn nội bộ nếu giao dịch chưa xác nhận trả tiền
        }

        if ($order->status === 'pending' && !$order->isPaid()) {
            $this->payosService->cancelPaymentLink($payosOrderCode, 'Khách hàng hủy thanh toán trên PayOS');
            $order->cancelAndRestoreInventory(Order::PAYMENT_STATUS_CANCELLED);
        }

        return $this->redirectAfterCancel(
            $order,
            'Bạn đã hủy thanh toán QR. Đơn hàng #' . $order->code . ' đã được hủy và hoàn lại tồn kho.'
        );
    }

    /**
     * Cho phép khách hàng bấm kiểm tra lại trạng thái thanh toán từ PayOS đối với đơn QR chưa đối soát.
     */
    public function payosVerify(Order $order): RedirectResponse
    {
        $this->authorizeOrderAccess($order);

        if ($order->payment_gateway !== 'payos' || !$order->payment_reference) {
            return back()->with('error', 'Đơn hàng này không sử dụng cổng thanh toán PayOS.');
        }

        if ($order->isPaid()) {
            return back()->with('success', 'Đơn hàng #' . $order->code . ' đã được xác nhận thanh toán.');
        }

        try {
            $info = $this->payosService->getPaymentRequestInfo($order->payment_reference);
            $payosStatus = strtoupper((string) ($info['status'] ?? ''));
            $amountPaid = (float) ($info['amountPaid'] ?? $info['amount'] ?? 0);

            if ($payosStatus === 'PAID' && $amountPaid >= (float) $order->total_amount) {
                $this->markOrderPaid($order, $info['id'] ?? $order->payment_transaction_id);

                return back()->with('success', 'Đã đối soát thành công: Đơn hàng #' . $order->code . ' đã thanh toán!');
            }

            if (in_array($payosStatus, ['CANCELLED', 'EXPIRED'], true)) {
                $order->cancelAndRestoreInventory(Order::PAYMENT_STATUS_CANCELLED);

                return back()->with('error', 'Liên kết thanh toán đã hủy hoặc hết hạn. Đơn hàng #' . $order->code . ' đã được hủy và hoàn kho.');
            }

            return back()->with('warning', 'PayOS ghi nhận đơn #' . $order->code . ' vẫn đang chờ thanh toán (' . $payosStatus . ').');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Webhook server-to-server từ PayOS (dùng trên môi trường HTTPS public).
     */
    public function payosWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        if (!$this->payosService->verifyWebhookSignature($payload)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid PayOS webhook signature.',
            ], 400);
        }

        $data = $payload['data'] ?? [];
        $payosOrderCode = (int) ($data['orderCode'] ?? 0);
        $order = $payosOrderCode > 0
            ? Order::where('payment_reference', $payosOrderCode)->first()
            : null;

        if (!$order) {
            return response()->json([
                'success' => true,
                'message' => 'Webhook verified (no matching local order).',
            ]);
        }

        $isSuccessCode = ($payload['code'] ?? '') === '00' && ($payload['success'] ?? true) === true;
        $paidAmount = (float) ($data['amount'] ?? 0);

        if ($isSuccessCode && $paidAmount >= (float) $order->total_amount) {
            $transactionId = $data['paymentLinkId'] ?? $data['reference'] ?? null;
            $this->markOrderPaid($order, is_string($transactionId) ? $transactionId : null);
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook processed.',
        ]);
    }

    public function success(Order $order): View
    {
        $this->authorizeOrderAccess($order);
        $order->load(['items.product']);

        return view('checkout.success', compact('order'));
    }
}
