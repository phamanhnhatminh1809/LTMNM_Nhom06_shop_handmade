<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Hiển thị danh sách sản phẩm trong giỏ hàng.
     */
    public function index(): View
    {
        $cart = Cart::getActiveCart();
        $cart->load(['details.product']);

        return view('cart.index', compact('cart'));
    }

    /**
     * Thêm sản phẩm vào giỏ hàng.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'buy_now'    => 'nullable|boolean',
        ], [
            'product_id.required' => 'Vui lòng chọn sản phẩm.',
            'product_id.exists'   => 'Sản phẩm không tồn tại.',
            'quantity.required'   => 'Vui lòng nhập số lượng.',
            'quantity.integer'    => 'Số lượng phải là số nguyên.',
            'quantity.min'        => 'Số lượng tối thiểu là 1.',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->stock_quantity <= 0) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm này hiện đã hết hàng trong kho.',
                ], 422);
            }

            return back()->with('error', 'Sản phẩm này hiện đã hết hàng trong kho.');
        }

        $cart = Cart::getActiveCart();
        $detail = $cart->details()->where('product_id', $product->id)->first();

        $requestedQty = (int) $validated['quantity'];

        if ($detail) {
            $newQty = $detail->quantity + $requestedQty;
            if ($newQty > $product->stock_quantity) {
                $newQty = $product->stock_quantity;
            }
            $detail->update([
                'quantity'   => $newQty,
                'unit_price' => $product->price,
            ]);
        } else {
            $initialQty = min($requestedQty, $product->stock_quantity);
            $cart->details()->create([
                'product_id'   => $product->id,
                'product_code' => $product->code,
                'name'         => $product->name,
                'image_url'    => $product->image,
                'quantity'     => $initialQty,
                'unit_price'   => $product->price,
            ]);
        }

        $cart->recalculateTotals();

        if (!empty($validated['buy_now'])) {
            return redirect()->route('checkout.index');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'        => true,
                'message'        => 'Đã thêm sản phẩm vào giỏ hàng thành công.',
                'total_quantity' => $cart->total_quantity,
                'total_amount'   => $cart->total_amount,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Đã thêm sản phẩm vào giỏ hàng thành công.');
    }

    /**
     * Cập nhật số lượng của một mặt hàng trong giỏ.
     */
    public function update(Request $request, CartDetail $cartDetail): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ], [
            'quantity.required' => 'Vui lòng cung cấp số lượng.',
            'quantity.integer'  => 'Số lượng phải là số nguyên.',
            'quantity.min'      => 'Số lượng tối thiểu là 1.',
        ]);

        $cart = Cart::getActiveCart();

        if ($cartDetail->cart_id !== $cart->id) {
            abort(403, 'Bạn không có quyền thao tác trên mục này.');
        }

        $product = $cartDetail->product;
        $newQty = (int) $validated['quantity'];
        $message = 'Cập nhật số lượng thành công.';

        if ($product && $newQty > $product->stock_quantity) {
            $newQty = max(1, $product->stock_quantity);
            $message = "Số lượng đã được tự động điều chỉnh về mức tồn kho tối đa ({$newQty}).";
        }

        $cartDetail->update([
            'quantity' => $newQty,
        ]);

        $cart->recalculateTotals();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'        => true,
                'message'        => $message,
                'item_quantity'  => $cartDetail->quantity,
                'item_subtotal'  => $cartDetail->subtotal,
                'total_quantity' => $cart->total_quantity,
                'total_amount'   => $cart->total_amount,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Xóa một mặt hàng khỏi giỏ.
     */
    public function destroy(Request $request, CartDetail $cartDetail): RedirectResponse|JsonResponse
    {
        $cart = Cart::getActiveCart();

        if ($cartDetail->cart_id !== $cart->id) {
            abort(403, 'Bạn không có quyền thao tác trên mục này.');
        }

        $cartDetail->delete();
        $cart->recalculateTotals();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'        => true,
                'message'        => 'Đã xóa sản phẩm khỏi giỏ hàng.',
                'total_quantity' => $cart->total_quantity,
                'total_amount'   => $cart->total_amount,
            ]);
        }

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    /**
     * Xóa toàn bộ sản phẩm trong giỏ hàng.
     */
    public function clear(): RedirectResponse
    {
        $cart = Cart::getActiveCart();
        $cart->details()->delete();
        $cart->recalculateTotals();

        return redirect()->route('cart.index')->with('success', 'Đã xóa toàn bộ sản phẩm trong giỏ hàng.');
    }
}
