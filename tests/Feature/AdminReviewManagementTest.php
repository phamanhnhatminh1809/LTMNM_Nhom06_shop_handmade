<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReviewManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $salesStaff;
    protected User $warehouseStaff;
    protected User $customer;
    protected Product $product1;
    protected Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['id' => 1, 'name' => Role::ADMIN]);
        $roleCustomer = Role::create(['id' => 2, 'name' => Role::CUSTOMER]);
        $roleWarehouse = Role::create(['id' => 3, 'name' => Role::WAREHOUSE_STAFF]);
        $roleSales = Role::create(['id' => 4, 'name' => Role::SALES_STAFF]);

        $this->admin = User::create([
            'role_id'   => $roleAdmin->id,
            'username'  => 'admin_rev',
            'email'     => 'admin_rev@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Quản trị viên',
            'is_active' => true,
        ]);

        $this->salesStaff = User::create([
            'role_id'   => $roleSales->id,
            'username'  => 'sales_rev',
            'email'     => 'sales_rev@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Nhân viên Bán hàng',
            'is_active' => true,
        ]);

        $this->warehouseStaff = User::create([
            'role_id'   => $roleWarehouse->id,
            'username'  => 'warehouse_rev',
            'email'     => 'warehouse_rev@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Nhân viên Kho',
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'role_id'   => $roleCustomer->id,
            'username'  => 'customer_rev',
            'email'     => 'customer_rev@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Lê Thị Đánh Giá',
            'is_active' => true,
        ]);

        $category = Category::create(['name' => 'Đồ len', 'slug' => 'do-len']);

        $this->product1 = Product::create([
            'code'           => 'SP-REV-01',
            'category_id'    => $category->id,
            'name'           => 'Khăn len đan tay',
            'slug'           => 'khan-len-dan-tay',
            'price'          => 150000,
            'stock_quantity' => 10,
            'rating'         => 5.0,
        ]);

        $this->product2 = Product::create([
            'code'           => 'SP-REV-02',
            'category_id'    => $category->id,
            'name'           => 'Móc khóa hoa tulip',
            'slug'           => 'moc-khoa-hoa-tulip',
            'price'          => 50000,
            'stock_quantity' => 15,
            'rating'         => 5.0,
        ]);
    }

    private function createReviewForProduct(Product $product, int $rating, string $comment, string $orderCode): Review
    {
        $order = Order::create([
            'code'             => $orderCode,
            'user_id'          => $this->customer->id,
            'shipping_name'    => $this->customer->full_name,
            'shipping_phone'   => '0912345678',
            'shipping_address' => 'Hà Nội',
            'total_amount'     => $product->price,
            'status'           => 'completed',
        ]);

        $item = OrderItem::create([
            'order_id'    => $order->id,
            'product_id'  => $product->id,
            'quantity'    => 1,
            'unit_price'  => $product->price,
            'total_price' => $product->price,
        ]);

        $review = Review::create([
            'user_id'       => $this->customer->id,
            'product_id'    => $product->id,
            'order_item_id' => $item->id,
            'rating'        => $rating,
            'comment'       => $comment,
        ]);

        $product->recalculateRating();

        return $review;
    }

    // =========================================================================
    // 1. KIỂM THỬ LUỒNG CƠ BẢN (BASIC FLOW)
    // =========================================================================

    /**
     * Admin và Nhân viên bán hàng xem danh sách đánh giá, lọc theo sản phẩm, số sao và tìm kiếm từ khóa
     */
    public function test_sales_staff_can_view_filter_and_search_reviews(): void
    {
        $this->createReviewForProduct($this->product1, 5, 'Khăn len tuyệt vời lắm', 'ORD-R-01');
        $this->createReviewForProduct($this->product2, 2, 'Móc khóa giao hơi chậm', 'ORD-R-02');

        // 1. Xem tất cả đánh giá
        $allResponse = $this->actingAs($this->salesStaff)->get('/admin/reviews');
        $allResponse->assertStatus(200);
        $allResponse->assertSee('Khăn len tuyệt vời lắm');
        $allResponse->assertSee('Móc khóa giao hơi chậm');
        $allResponse->assertViewHas('ratingCounts', function ($counts) {
            return $counts['all'] === 2 && $counts[5] === 1 && $counts[2] === 1;
        });

        // 2. Lọc theo sản phẩm (product_id)
        $byProduct = $this->actingAs($this->salesStaff)->get("/admin/reviews?product_id={$this->product1->id}");
        $byProduct->assertStatus(200);
        $byProduct->assertSee('Khăn len tuyệt vời lắm');
        $byProduct->assertDontSee('Móc khóa giao hơi chậm');

        // 3. Lọc theo số sao (rating = 2)
        $byRating = $this->actingAs($this->salesStaff)->get('/admin/reviews?rating=2');
        $byRating->assertStatus(200);
        $byRating->assertSee('Móc khóa giao hơi chậm');
        $byRating->assertDontSee('Khăn len tuyệt vời lắm');

        // 4. Tìm kiếm theo từ khóa bình luận
        $bySearch = $this->actingAs($this->salesStaff)->get('/admin/reviews?q=tuyệt+vời');
        $bySearch->assertStatus(200);
        $bySearch->assertSee('Khăn len tuyệt vời lắm');
        $bySearch->assertDontSee('Móc khóa giao hơi chậm');
    }

    /**
     * Xóa đánh giá vi phạm và tự động tính lại điểm trung bình của sản phẩm
     */
    public function test_sales_staff_can_delete_review_and_recalculate_product_rating(): void
    {
        $rev1 = $this->createReviewForProduct($this->product1, 5, 'Rất đẹp 5 sao', 'ORD-DEL-01');
        $rev2 = $this->createReviewForProduct($this->product1, 1, 'Spam đánh giá 1 sao', 'ORD-DEL-02');

        // Trung bình ban đầu: (5 + 1) / 2 = 3.0
        $this->assertEquals(3.0, $this->product1->fresh()->rating);

        // Xóa đánh giá 1 sao
        $response = $this->actingAs($this->salesStaff)->delete("/admin/reviews/{$rev2->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('reviews', ['id' => $rev2->id]);
        $this->assertDatabaseHas('reviews', ['id' => $rev1->id]);

        // Điểm trung bình sản phẩm cập nhật lại thành 5.0
        $this->assertEquals(5.0, $this->product1->fresh()->rating);
    }

    // =========================================================================
    // 2. KIỂM THỬ GIÁ TRỊ BIÊN & QUY TẮC NGHIỆP VỤ (BOUNDARY & BUSINESS RULES)
    // =========================================================================

    /**
     * Khi xóa đánh giá cuối cùng của sản phẩm (0 đánh giá còn lại), điểm rating tự động reset về biên mặc định 5.0
     */
    public function test_deleting_last_review_resets_product_rating_to_default_five(): void
    {
        $onlyReview = $this->createReviewForProduct($this->product1, 3, 'Đánh giá duy nhất 3 sao', 'ORD-LAST-01');
        $this->assertEquals(3.0, $this->product1->fresh()->rating);

        $this->actingAs($this->admin)->delete("/admin/reviews/{$onlyReview->id}");

        $this->assertDatabaseCount('reviews', 0);
        $this->assertEquals(5.0, $this->product1->fresh()->rating);
    }

    /**
     * Kiểm tra lọc ở các mức sao biên (1 sao và 5 sao) và khi không có đánh giá nào khớp
     */
    public function test_filter_reviews_at_star_boundaries_and_empty_result(): void
    {
        $this->createReviewForProduct($this->product1, 1, 'Đánh giá biên 1 sao', 'ORD-B-1');
        $this->createReviewForProduct($this->product2, 5, 'Đánh giá biên 5 sao', 'ORD-B-5');

        $star1 = $this->actingAs($this->admin)->get('/admin/reviews?rating=1');
        $star1->assertStatus(200)->assertSee('Đánh giá biên 1 sao')->assertDontSee('Đánh giá biên 5 sao');

        $star5 = $this->actingAs($this->admin)->get('/admin/reviews?rating=5');
        $star5->assertStatus(200)->assertSee('Đánh giá biên 5 sao')->assertDontSee('Đánh giá biên 1 sao');

        // Mức 3 sao không có đánh giá nào
        $star3 = $this->actingAs($this->admin)->get('/admin/reviews?rating=3');
        $star3->assertStatus(200)->assertSee('Không có đánh giá nào phù hợp.');
    }

    // =========================================================================
    // 3. KIỂM THỬ BẢO MẬT & PHÂN QUYỀN (SECURITY & RBAC)
    // =========================================================================

    /**
     * Nhân viên kho (warehouse_staff) KHÔNG được phép xem hoặc xóa đánh giá sản phẩm của Nhân viên bán hàng
     */
    public function test_warehouse_staff_cannot_access_or_delete_reviews(): void
    {
        $review = $this->createReviewForProduct($this->product1, 4, 'Đánh giá khách hàng', 'ORD-SEC-REV');

        $this->actingAs($this->warehouseStaff)->get('/admin/reviews')->assertStatus(403);
        $this->actingAs($this->warehouseStaff)->delete("/admin/reviews/{$review->id}")->assertStatus(403);

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }

    /**
     * Khách hàng (customer) và Khách vãng lai (guest) không được truy cập quản lý đánh giá Admin
     */
    public function test_guest_and_customer_cannot_access_admin_reviews(): void
    {
        $this->get('/admin/reviews')->assertRedirect('/login');
        $this->actingAs($this->customer)->get('/admin/reviews')->assertStatus(403);
    }
}
