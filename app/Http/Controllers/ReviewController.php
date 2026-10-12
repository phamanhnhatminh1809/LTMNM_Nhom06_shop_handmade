<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Lưu đánh giá của khách hàng cho sản phẩm trong một đơn hàng đã hoàn thành.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'rating'        => 'required|integer|min:1|max:5',
            'comment'       => 'nullable|string|max:1000',
        ], [
            'order_item_id.required' => 'Thiếu thông tin sản phẩm trong đơn hàng cần đánh giá.',
            'order_item_id.exists'   => 'Sản phẩm trong đơn hàng không tồn tại.',
            'rating.required'        => 'Vui lòng chọn số sao đánh giá (1 đến 5).',
            'rating.integer'         => 'Số sao đánh giá phải là số nguyên.',
            'rating.min'             => 'Điểm đánh giá tối thiểu là 1 sao.',
            'rating.max'             => 'Điểm đánh giá tối đa là 5 sao.',
            'comment.max'            => 'Nội dung nhận xét tối đa 1000 ký tự.',
        ]);

        $orderItem = OrderItem::with(['order', 'product'])->findOrFail($validated['order_item_id']);

        // 1. Kiểm tra quyền sở hữu đơn hàng
        if ($orderItem->order->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền đánh giá đơn hàng này.');
        }

        // 2. Kiểm tra điều kiện đơn hàng đã hoàn thành (completed)
        if ($orderItem->order->status !== 'completed') {
            $msg = 'Chỉ có thể đánh giá sản phẩm khi đơn hàng đã ở trạng thái Hoàn thành.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // 3. Kiểm tra xem mục này đã được đánh giá trước đó hay chưa
        if ($orderItem->review()->exists()) {
            $msg = 'Bạn đã gửi đánh giá cho sản phẩm này trong đơn hàng rồi.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // 4. Lưu đánh giá và cập nhật điểm rating trung bình của sản phẩm
        DB::transaction(function () use ($orderItem, $validated) {
            Review::create([
                'user_id'       => Auth::id(),
                'product_id'    => $orderItem->product_id,
                'order_item_id' => $orderItem->id,
                'rating'        => (int) $validated['rating'],
                'comment'       => $validated['comment'] ?? null,
            ]);

            if ($orderItem->product) {
                $orderItem->product->recalculateRating();
            }
        });

        $successMsg = 'Đánh giá sản phẩm thành công! Cảm ơn phản hồi của bạn.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
            ]);
        }

        return back()->with('success', $successMsg);
    }
}
