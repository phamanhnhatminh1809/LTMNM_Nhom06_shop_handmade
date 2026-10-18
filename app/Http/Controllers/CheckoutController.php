<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Coupon;
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
    private function calculateShippingFee($subtotal)
    {
        return $subtotal >= 450000 ? 0 : 30000;
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
        $shippingFee = $this->calculateShippingFee($cart->total_amount);
        
        // Lấy thông tin mã giảm giá từ session
        $couponData = session('applied_coupon');
        $discountAmount = $couponData ? $couponData['discount'] : 0;

        return view('checkout.index', compact('cart', 'user', 'shippingFee', 'couponData', 'discountAmount'));
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['coupon_code' => 'required|string']);
        
        $code = strtoupper(trim($request->coupon_code));
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return back()->with('coupon_error', 'Mã giảm giá không tồn tại.');
        }

        $cart = Cart::getActiveCart();
        $check = $coupon->isValid($cart->total_amount);

        if ($check !== true) {
            return back()->with('coupon_error', $check);
        }

        session(['applied_coupon' => [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'discount' => $coupon->calculateDiscount($cart->total_amount),
        ]]);

        return back()->with('coupon_success', 'Đã áp dụng mã giảm giá thành công!');
    }

    public function removeCoupon()
    {
        session()->forget('applied_coupon');
        return back()->with('coupon_success', 'Đã gỡ bỏ mã giảm giá.');
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

        try {
            $order = DB::transaction(function () use ($cart, $validated) {
                $shippingFee = $this->calculateShippingFee($cart->total_amount);
                
                $couponData = session('applied_coupon');
                $discountAmount = $couponData ? $couponData['discount'] : 0;
                
                $finalTotal = max(0, $cart->total_amount - $discountAmount + $shippingFee);

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
                    'shipping_fee'     => $shippingFee,
                    'discount_amount'  => $discountAmount,
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

                if ($couponData) {
                    $coupon = Coupon::find($couponData['id']);
                    if ($coupon) { $coupon->increment('used_count'); }
                }

                $cart->status = 'converted';
                $cart->total_quantity = 0;
                $cart->save();
                $cart->details()->delete();

                return $order;
            });

            session()->forget('applied_coupon');
            session()->flash('last_order_id', $order->id);

            return redirect()->route('checkout.success', $order->id)
                ->with('success', 'Đặt hàng thành công!');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function success(Order $order): View
    {
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