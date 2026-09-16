<?php

declare(strict_types=1);

namespace App\Features\Products\Services;

use App\Features\Products\Models\Product;
use App\Features\Products\Requests\GetProductListRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class ProductService
 *
 * Service layer chứa toàn bộ business logic cho Product.
 * Xử lý: filter, search, sort, paginate với Eager Loading.
 */
class ProductService
{
    /**
     * Lấy danh sách sản phẩm có phân trang, hỗ trợ filter và sort.
     *
     * Logic flow:
     * 1. Khởi tạo query với Eager Loading (with('category')) để chống N+1
     * 2. Áp dụng các filter scope nếu có params tương ứng
     * 3. Sắp xếp theo trường và chiều được chỉ định (mặc định: name asc)
     * 4. Phân trang (mặc định: 10 items/page)
     * 5. Giữ query params trên pagination links
     */
    public function getProductList(GetProductListRequest $request): LengthAwarePaginator
    {
        $query = Product::query()->with('category');

        // --- Apply Filters ---

        // Lọc theo danh mục
        if ($request->filled('category_id')) {
            $query->byCategory((int) $request->input('category_id'));
        }

        // Tìm kiếm theo tên sản phẩm
        if ($request->filled('search')) {
            $query->searchByName($request->input('search'));
        }

        // Lọc theo khoảng giá
        if ($request->filled('price_min') || $request->filled('price_max')) {
            $query->byPriceRange(
                $request->filled('price_min') ? (float) $request->input('price_min') : null,
                $request->filled('price_max') ? (float) $request->input('price_max') : null,
            );
        }

        // Lọc sản phẩm còn hàng
        if ($request->boolean('in_stock')) {
            $query->inStock();
        }

        // --- Apply Sorting ---
        $sortBy        = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');

        $query->orderBy($sortBy, $sortDirection);

        // --- Paginate ---
        $perPage = (int) $request->input('per_page', 10);

        return $query->paginate($perPage)->appends($request->query());
    }
}
