<?php

declare(strict_types=1);

namespace App\Features\Products\Controllers;

use App\Features\Products\Requests\GetProductListRequest;
use App\Features\Products\Resources\ProductResource;
use App\Features\Products\Services\ProductService;
use Illuminate\Http\JsonResponse;

/**
 * Class ProductController
 *
 * Skinny Controller — chỉ tiếp nhận Request, gọi Service và trả Response JSON.
 * Không chứa business logic.
 */
class ProductController
{
    /**
     * @var ProductService
     */
    protected ProductService $productService;

    /**
     * Constructor — Dependency Injection cho ProductService.
     */
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * API xem danh sách sản phẩm.
     *
     * Endpoint : GET /api/products
     * Auth     : Public (không cần đăng nhập)
     * Features : Phân trang, Filter, Search, Sort
     *
     * @param  GetProductListRequest  $request  Query parameters đã được validate
     * @return JsonResponse
     */
    public function index(GetProductListRequest $request): JsonResponse
    {
        try {
            $products = $this->productService->getProductList($request);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách sản phẩm thành công.',
                'data'    => ProductResource::collection($products),
                'meta'    => [
                    'current_page' => $products->currentPage(),
                    'last_page'    => $products->lastPage(),
                    'per_page'     => $products->perPage(),
                    'total'        => $products->total(),
                    'from'         => $products->firstItem(),
                    'to'           => $products->lastItem(),
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách sản phẩm.',
                'data'    => null,
                'meta'    => null,
            ], 500);
        }
    }
}
