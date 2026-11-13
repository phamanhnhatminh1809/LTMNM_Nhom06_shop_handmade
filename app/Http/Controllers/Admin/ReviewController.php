<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        // Cache ngắn danh sách sản phẩm trong dropdown lọc (tự xóa cache khi Product thay đổi)
        $products = Cache::remember('admin_review_filter_products', 300, function () {
            return Product::select('id', 'name', 'code')
                ->orderBy('name')
                ->get();
        });

        // Gộp thành 1 query GROUP BY rating thay vì 6 query COUNT(*)
        $countsByRating = Review::query()
            ->filterProduct($productId)
            ->selectRaw('rating, COUNT(*) as aggregate')
            ->groupBy('rating')
            ->pluck('aggregate', 'rating');

        $ratingCounts = [
            'all' => (int) $countsByRating->sum(),
            5     => (int) ($countsByRating[5] ?? 0),
            4     => (int) ($countsByRating[4] ?? 0),
            3     => (int) ($countsByRating[3] ?? 0),
            2     => (int) ($countsByRating[2] ?? 0),
            1     => (int) ($countsByRating[1] ?? 0),
        ];

        $selectedProduct = $productId ? $products->firstWhere('id', $productId) : null;

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

        DB::transaction(function () use ($review) {
            $review->delete();
        });

        return redirect()->back()
            ->with('success', 'Đã xóa đánh giá và cập nhật lại điểm sao trung bình của sản phẩm!');
    }
}
