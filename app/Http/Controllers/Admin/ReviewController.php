<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * Hiển thị danh sách đánh giá sản phẩm.
     * Hỗ trợ:
     * - Lọc theo sản phẩm (?product_id=...)
     * - Lọc theo số sao (?rating=1..5)
     * - Tìm kiếm theo từ khóa (?q=...)
     */
    public function index(Request $request): View
    {
        $keyword = trim($request->input('q', ''));
        $productId = $request->filled('product_id') ? (int) $request->input('product_id') : null;
        $rating = $request->filled('rating') ? (int) $request->input('rating') : null;

        $reviews = Review::with(['user', 'product', 'orderItem.order'])
            ->filterProduct($productId)
            ->filterRating($rating)
            ->search($keyword)
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        // Danh sách sản phẩm để hiển thị trong bộ lọc
        $products = Product::select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        // Thống kê số lượng đánh giá theo từng mức sao (có áp dụng bộ lọc sản phẩm nếu đang chọn 1 sản phẩm)
        $baseCountQuery = Review::query()->filterProduct($productId);
        $ratingCounts = [
            'all' => (clone $baseCountQuery)->count(),
            5     => (clone $baseCountQuery)->where('rating', 5)->count(),
            4     => (clone $baseCountQuery)->where('rating', 4)->count(),
            3     => (clone $baseCountQuery)->where('rating', 3)->count(),
            2     => (clone $baseCountQuery)->where('rating', 2)->count(),
            1     => (clone $baseCountQuery)->where('rating', 1)->count(),
        ];

        $selectedProduct = $productId ? Product::find($productId) : null;

        return view('admin.reviews.index', compact(
            'reviews',
            'products',
            'selectedProduct',
            'productId',
            'rating',
            'keyword',
            'ratingCounts'
        ));
    }

    /**
     * Xóa đánh giá vi phạm và tính lại điểm trung bình của sản phẩm.
     */
    public function destroy(Review $review): RedirectResponse
    {
        $product = $review->product;

        DB::transaction(function () use ($review, $product) {
            $review->delete();

            if ($product) {
                $product->recalculateRating();
            }
        });

        return redirect()->back()
            ->with('success', 'Đã xóa đánh giá và cập nhật lại điểm sao trung bình của sản phẩm!');
    }
}
