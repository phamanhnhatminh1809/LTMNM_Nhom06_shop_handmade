<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Hiển thị trang xem lại đơn hàng và điền thông tin thanh toán (COD).
     */
    public function index(): View|RedirectResponse
    {
        $cart = Cart::getActiveCart();
        $cart->load(['details.product']);

        if ($cart->details->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm trước khi thanh toán.');
        }

        $user = Auth::user();

        return view('checkout.index', compact('cart', 'user'));
    }

    /**
     * Xử lý đặt hàng và tạo đơn hàng thanh toán COD.
     */
    public function process(Request $request): RedirectResponse
    {
        $cart = Cart::getActiveCart();
        $cart->load(['details.product']);

        if ($cart->details->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $validated = $request->validate([
            'shipping_name'    => 'required|string|max:100',
            'shipping_phone'   => ['required', 'string', 'max:20', 'regex:/^[0-9+\s\-]{8,20}$/'],
            'shipping_address' => 'required|string|max:500',
            'payment_method'   => 'required|in:cod',
        ], [
            'shipping_name.required'    => 'Vui lòng nhập họ và tên người nhận hàng.',
            'shipping_name.max'         => 'Họ và tên tối đa 100 ký tự.',
            'shipping_phone.required'   => 'Vui lòng nhập số điện thoại người nhận.',
            'shipping_phone.regex'      => 'Số điện thoại không đúng định dạng.',
            'shipping_phone.max'        => 'Số điện thoại tối đa 20 ký tự.',
            'shipping_address.required' => 'Vui lòng cung cấp địa chỉ nhận hàng.',
            'shipping_address.max'      => 'Địa chỉ tối đa 500 ký tự.',
            'payment_method.required'   => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in'         => 'Phương thức thanh toán không hợp lệ.',
        ]);

        try {
            $order = DB::transaction(function () use ($cart, $validated) {
                // Kiểm tra lại tồn kho của toàn bộ sản phẩm
                foreach ($cart->details as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item->product_id);
                    if ($product->stock_quantity < $item->quantity) {
                        throw new \Exception("Sản phẩm \"{$item->name}\" không đủ tồn kho (chỉ còn {$product->stock_quantity} sản phẩm).");
                    }
                }

                // Sinh mã đơn hàng duy nhất: ORD-XXXXXXXX
                do {
                    $orderCode = 'ORD-' . strtoupper(Str::random(8));
                } while (Order::where('code', $orderCode)->exists());

                // Tạo bản ghi đơn hàng
                $order = Order::create([
                    'code'             => $orderCode,
                    'user_id'          => Auth::id(),
                    'shipping_name'    => $validated['shipping_name'],
                    'shipping_phone'   => $validated['shipping_phone'],
                    'shipping_address' => $validated['shipping_address'],
                    'total_amount'     => $cart->total_amount,
                    'status'           => 'pending',
                ]);

                // Tạo các chi tiết đơn hàng (OrderItem) và trừ kho
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

                // Chuyển trạng thái giỏ hàng và làm trống
                $cart->status = 'converted';
                $cart->total_quantity = 0;
                $cart->save();
                $cart->details()->delete();

                return $order;
            });

            session()->flash('last_order_id', $order->id);

            return redirect()->route('checkout.success', $order->id)
                ->with('success', 'Đặt hàng thành công! Đơn hàng của bạn đang được xử lý.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Hiển thị trang đặt hàng thành công.
     */
    public function success(Order $order): View
    {
        // Kiểm tra quyền xem đơn hàng
        if (Auth::check() && $order->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem đơn hàng này.');
        }

        if (!Auth::check() && session('last_order_id') !== $order->id) {
            abort(403, 'Bạn không có quyền xem đơn hàng này.');
        }

        $order->load(['items.product']);

        return view('checkout.success', compact('order'));
    }
}
