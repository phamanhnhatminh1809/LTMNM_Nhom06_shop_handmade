<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchAndFilterTest extends TestCase
{
    use RefreshDatabase;

    protected Category $categoryLen;
    protected Category $categoryGom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryLen = Category::create([
            'name' => 'Đồ len',
            'slug' => 'do-len',
        ]);

        $this->categoryGom = Category::create([
            'name' => 'Đồ gốm',
            'slug' => 'do-gom',
        ]);

        // Tạo 4 sản phẩm mẫu để test tìm kiếm, lọc và sắp xếp
        Product::create([
            'code'           => 'SP-01',
            'category_id'    => $this->categoryLen->id,
            'name'           => 'Khăn len cashmere đan tay',
            'slug'           => 'khan-len-cashmere-dan-tay',
            'description'    => 'Sợi len tự nhiên giữ ấm cực tốt trong mùa đông',
            'price'          => 150000,
            'stock_quantity' => 10,
            'sold'           => 12,
            'rating'         => 4.8,
            'image'          => 'images/products/khan-01.jpg',
            'created_at'     => now()->subDays(3),
        ]);

        Product::create([
            'code'           => 'SP-02',
            'category_id'    => $this->categoryLen->id,
            'name'           => 'Mũ len quả bông ấm áp',
            'slug'           => 'mu-len-qua-bong-am-ap',
            'description'    => 'Thiết kế dễ thương phù hợp đi chơi chụp ảnh',
            'price'          => 80000,
            'stock_quantity' => 20,
            'sold'           => 30,
            'rating'         => 4.5,
            'image'          => 'images/products/mu-01.jpg',
            'created_at'     => now()->subDays(1),
        ]);

        Product::create([
            'code'           => 'SP-03',
            'category_id'    => $this->categoryGom->id,
            'name'           => 'Cốc gốm men hỏa biến',
            'slug'           => 'coc-gom-men-hoa-bien',
            'description'    => 'Gốm Bát Tràng tráng men cao cấp nung ở 1300 độ C',
            'price'          => 250000,
            'stock_quantity' => 5,
            'sold'           => 5,
            'rating'         => 5.0,
            'image'          => 'images/products/coc-01.jpg',
            'created_at'     => now()->subDays(4),
        ]);

        Product::create([
            'code'           => 'SP-04',
            'category_id'    => $this->categoryGom->id,
            'name'           => 'Bình hoa gốm mộc',
            'slug'           => 'binh-hoa-gom-moc',
            'description'    => 'Kiểu dáng vintage phù hợp cắm hoa khô hoặc hoa tươi',
            'price'          => 450000,
            'stock_quantity' => 8,
            'sold'           => 2,
            'rating'         => 4.9,
            'image'          => 'images/products/binh-01.jpg',
            'created_at'     => now(),
        ]);
    }

    /**
     * 1. Xem danh sách sản phẩm phân trang
     */
    public function test_user_can_view_paginated_product_list(): void
    {
        $response = $this->get('/products');
        $response->assertStatus(200);
        $response->assertSee('Danh sách sản phẩm');
        $response->assertSee('Khăn len cashmere đan tay');
        $response->assertSee('Cốc gốm men hỏa biến');
    }

    /**
     * 2. Tìm kiếm sản phẩm theo tên
     */
    public function test_user_can_search_products_by_name(): void
    {
        $response = $this->get('/products?q=cashmere');
        $response->assertStatus(200);
        $response->assertSee('Khăn len cashmere đan tay');
        $response->assertDontSee('Cốc gốm men hỏa biến');
    }

    /**
     * 3. Tìm kiếm sản phẩm theo nội dung mô tả
     */
    public function test_user_can_search_products_by_description(): void
    {
        $response = $this->get('/products?q=vintage');
        $response->assertStatus(200);
        $response->assertSee('Bình hoa gốm mộc');
        $response->assertDontSee('Mũ len quả bông ấm áp');
    }

    /**
     * 4. Lọc sản phẩm theo danh mục
     */
    public function test_user_can_filter_products_by_category(): void
    {
        $response = $this->get('/products?category=do-len');
        $response->assertStatus(200);
        $response->assertSee('Khăn len cashmere đan tay');
        $response->assertSee('Mũ len quả bông ấm áp');
        $response->assertDontSee('Cốc gốm men hỏa biến');
        $response->assertDontSee('Bình hoa gốm mộc');
    }

    /**
     * 5. Lọc sản phẩm theo khoảng giá (min_price và max_price)
     */
    public function test_user_can_filter_products_by_price_range(): void
    {
        // Lọc dưới hoặc bằng 100.000đ (chỉ có Mũ len 80.000đ)
        $responseUnder100k = $this->get('/products?max_price=100000');
        $responseUnder100k->assertStatus(200);
        $responseUnder100k->assertSee('Mũ len quả bông ấm áp');
        $responseUnder100k->assertDontSee('Khăn len cashmere đan tay');
        $responseUnder100k->assertDontSee('Cốc gốm men hỏa biến');

        // Lọc từ 200.000đ đến 300.000đ (chỉ có Cốc gốm 250.000đ)
        $response200to300k = $this->get('/products?min_price=200000&max_price=300000');
        $response200to300k->assertStatus(200);
        $response200to300k->assertSee('Cốc gốm men hỏa biến');
        $response200to300k->assertDontSee('Mũ len quả bông ấm áp');
        $response200to300k->assertDontSee('Bình hoa gốm mộc');
    }

    /**
     * 6. Sắp xếp sản phẩm theo giá tăng dần và giảm dần
     */
    public function test_user_can_sort_products_by_price(): void
    {
        // Sắp xếp giá tăng dần (price_asc: 80.000 -> 150.000 -> 250.000 -> 450.000)
        $responseAsc = $this->get('/products?sort=price_asc');
        $responseAsc->assertStatus(200);
        $contentAsc = $responseAsc->getContent();
        $posMu = strpos($contentAsc, 'Mũ len quả bông');
        $posBinh = strpos($contentAsc, 'Bình hoa gốm mộc');
        $this->assertTrue($posMu < $posBinh);

        // Sắp xếp giá giảm dần (price_desc: 450.000 -> 250.000 -> 150.000 -> 80.000)
        $responseDesc = $this->get('/products?sort=price_desc');
        $responseDesc->assertStatus(200);
        $contentDesc = $responseDesc->getContent();
        $posBinhDesc = strpos($contentDesc, 'Bình hoa gốm mộc');
        $posMuDesc = strpos($contentDesc, 'Mũ len quả bông');
        $this->assertTrue($posBinhDesc < $posMuDesc);
    }

    /**
     * 6b. Sắp xếp sản phẩm theo mới nhất và cũ nhất
     */
    public function test_user_can_sort_products_by_latest_and_oldest(): void
    {
        $sp1 = Product::where('code', 'SP-01')->firstOrFail();
        $sp1->forceFill(['created_at' => now()->subDays(10)])->save();

        $responseLatest = $this->get('/products?sort=latest');
        $responseLatest->assertStatus(200);
        $contentLatest = $responseLatest->getContent();
        $this->assertTrue(strpos($contentLatest, 'Bình hoa gốm mộc') < strpos($contentLatest, 'Khăn len cashmere'));

        $responseOldest = $this->get('/products?sort=oldest');
        $responseOldest->assertStatus(200);
        $contentOldest = $responseOldest->getContent();
        $this->assertTrue(strpos($contentOldest, 'Khăn len cashmere') < strpos($contentOldest, 'Bình hoa gốm mộc'));
    }

    /**
     * 7. Tìm kiếm không có kết quả hiển thị thông báo trống phù hợp
     */
    public function test_search_with_no_matches_displays_empty_state(): void
    {
        $response = $this->get('/products?q=khongtontaisanphamnay');
        $response->assertStatus(200);
        $response->assertSee('Không tìm thấy sản phẩm phù hợp');
    }
}
