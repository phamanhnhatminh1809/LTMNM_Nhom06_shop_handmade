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

class ProductReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer1;
    protected User $customer2;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $roleCustomer = Role::create(['id' => 2, 'name' => 'customer']);

        $this->customer1 = User::create([
            'role_id'   => $roleCustomer->id,
            'username'  => 'reviewer_1',
            'email'     => 'reviewer1@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Khách Hàng Đánh Giá',
            'phone'     => '0911223344',
            'address'   => '123 Phố Test, Hà Nội',
            'is_active' => true,
        ]);

        $this->customer2 = User::create([
            'role_id'   => $roleCustomer->id,
            'username'  => 'reviewer_2',
            'email'     => 'reviewer2@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Khách Hàng Khác',
            'phone'     => '0988776655',
            'address'   => '456 Phố Test, Đà Nẵng',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Đồ len',
            'slug' => 'do-len',
        ]);

        $this->product = Product::create([
            'code'           => 'SP-REVIEW-01',
            'category_id'    => $category->id,
            'name'           => 'Mũ len quả bông',
            'slug'           => 'mu-len-qua-bong',
            'price'          => 150000,
            'stock_quantity' => 10,
            'sold'           => 5,
            'rating'         => 5.0,
            'image'          => 'images/products/mu-01.jpg',
        ]);
    }

    /**
     * 1. Khách vãng lai chưa đăng nhập không thể gửi đánh giá
     */
    public function test_guest_cannot_submit_review(): void
    {
        $response = $this->post('/reviews', [
            'order_item_id' => 1,
            'rating'        => 5,
            'comment'       => 'Sản phẩm rất đẹp',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * 2. Không thể đánh giá khi đơn hàng chưa ở trạng thái 'completed' (vd: pending hoặc shipping)
     */
    public function test_customer_cannot_review_if_order_is_not_completed(): void
    {
        $orderPending = Order::create([
            'code'             => 'ORD-PENDING-REV',
            'user_id'          => $this->customer1->id,
            'shipping_name'    => 'Khách Hàng Đánh Giá',
            'shipping_phone'   => '0911223344',
            'shipping_address' => '123 Phố Test',
            'total_amount'     => 150000,
            'status'           => 'pending',
        ]);

        $orderItem = OrderItem::create([
            'order_id'    => $orderPending->id,
            'product_id'  => $this->product->id,
            'quantity'    => 1,
            'unit_price'  => 150000,
            'total_price' => 150000,
        ]);

        $response = $this->actingAs($this->customer1)->post('/reviews', [
            'order_item_id' => $orderItem->id,
            'rating'        => 5,
            'comment'       => 'Sản phẩm tốt',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('reviews', 0);
    }

    /**
     * 3. Khách hàng không thể đánh giá sản phẩm thuộc đơn hàng của người khác (403 Forbidden)
     */
    public function test_customer_cannot_review_other_customers_order(): void
    {
        $orderCompleted = Order::create([
            'code'             => 'ORD-OTHER-REV',
            'user_id'          => $this->customer2->id,
            'shipping_name'    => 'Khách Hàng Khác',
            'shipping_phone'   => '0988776655',
            'shipping_address' => '456 Phố Test',
            'total_amount'     => 150000,
            'status'           => 'completed',
        ]);

        $orderItem = OrderItem::create([
            'order_id'    => $orderCompleted->id,
            'product_id'  => $this->product->id,
            'quantity'    => 1,
            'unit_price'  => 150000,
            'total_price' => 150000,
        ]);

        $response = $this->actingAs($this->customer1)->post('/reviews', [
            'order_item_id' => $orderItem->id,
            'rating'        => 5,
            'comment'       => 'Thử hack đánh giá',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('reviews', 0);
    }

    /**
     * 4. Đánh giá sản phẩm thành công khi đơn hàng đã completed
     */
    public function test_customer_can_review_product_when_order_is_completed(): void
    {
        $orderCompleted = Order::create([
            'code'             => 'ORD-DONE-REV',
            'user_id'          => $this->customer1->id,
            'shipping_name'    => 'Khách Hàng Đánh Giá',
            'shipping_phone'   => '0911223344',
            'shipping_address' => '123 Phố Test',
            'total_amount'     => 150000,
            'status'           => 'completed',
        ]);

        $orderItem = OrderItem::create([
            'order_id'    => $orderCompleted->id,
            'product_id'  => $this->product->id,
            'quantity'    => 1,
            'unit_price'  => 150000,
            'total_price' => 150000,
        ]);

        $response = $this->actingAs($this->customer1)->post('/reviews', [
            'order_item_id' => $orderItem->id,
            'rating'        => 4,
            'comment'       => 'Len mềm mại, đan rất tỉ mỉ và đẹp!',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'user_id'       => $this->customer1->id,
            'product_id'    => $this->product->id,
            'order_item_id' => $orderItem->id,
            'rating'        => 4,
            'comment'       => 'Len mềm mại, đan rất tỉ mỉ và đẹp!',
        ]);

        // Kiểm tra điểm rating sản phẩm được cập nhật lại
        $this->product->refresh();
        $this->assertEquals(4.0, $this->product->rating);
    }

    /**
     * 5. Không thể đánh giá 2 lần cho cùng một sản phẩm trong đơn hàng
     */
    public function test_customer_cannot_review_same_order_item_twice(): void
    {
        $orderCompleted = Order::create([
            'code'             => 'ORD-DOUBLE-REV',
            'user_id'          => $this->customer1->id,
            'shipping_name'    => 'Khách Hàng Đánh Giá',
            'shipping_phone'   => '0911223344',
            'shipping_address' => '123 Phố Test',
            'total_amount'     => 150000,
            'status'           => 'completed',
        ]);

        $orderItem = OrderItem::create([
            'order_id'    => $orderCompleted->id,
            'product_id'  => $this->product->id,
            'quantity'    => 1,
            'unit_price'  => 150000,
            'total_price' => 150000,
        ]);

        Review::create([
            'user_id'       => $this->customer1->id,
            'product_id'    => $this->product->id,
            'order_item_id' => $orderItem->id,
            'rating'        => 5,
            'comment'       => 'Đánh giá lần đầu',
        ]);

        // Thử gửi lại lần thứ hai
        $response = $this->actingAs($this->customer1)->post('/reviews', [
            'order_item_id' => $orderItem->id,
            'rating'        => 4,
            'comment'       => 'Thử gửi đè',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('reviews', 1);
    }

    /**
     * 6. Kiểm tra validation số sao (chỉ chấp nhận từ 1 đến 5)
     */
    public function test_review_rating_validation(): void
    {
        $orderCompleted = Order::create([
            'code'             => 'ORD-VALID-REV',
            'user_id'          => $this->customer1->id,
            'shipping_name'    => 'Khách Hàng Đánh Giá',
            'shipping_phone'   => '0911223344',
            'shipping_address' => '123 Phố Test',
            'total_amount'     => 150000,
            'status'           => 'completed',
        ]);

        $orderItem = OrderItem::create([
            'order_id'    => $orderCompleted->id,
            'product_id'  => $this->product->id,
            'quantity'    => 1,
            'unit_price'  => 150000,
            'total_price' => 150000,
        ]);

        // Số sao vượt quá 5
        $responseInvalidHigh = $this->actingAs($this->customer1)->post('/reviews', [
            'order_item_id' => $orderItem->id,
            'rating'        => 6,
        ]);
        $responseInvalidHigh->assertSessionHasErrors(['rating']);

        // Số sao nhỏ hơn 1
        $responseInvalidLow = $this->actingAs($this->customer1)->post('/reviews', [
            'order_item_id' => $orderItem->id,
            'rating'        => 0,
        ]);
        $responseInvalidLow->assertSessionHasErrors(['rating']);
    }

    /**
     * 7. Trang chi tiết sản phẩm hiển thị danh sách đánh giá của khách hàng
     */
    public function test_product_detail_page_displays_customer_reviews(): void
    {
        $order = Order::create([
            'code'             => 'ORD-DETAIL-REV',
            'user_id'          => $this->customer1->id,
            'shipping_name'    => 'Khách Hàng Đánh Giá',
            'shipping_phone'   => '0911223344',
            'shipping_address' => '123 Phố Test',
            'total_amount'     => 150000,
            'status'           => 'completed',
        ]);

        $orderItem = OrderItem::create([
            'order_id'    => $order->id,
            'product_id'  => $this->product->id,
            'quantity'    => 1,
            'unit_price'  => 150000,
            'total_price' => 150000,
        ]);

        Review::create([
            'user_id'       => $this->customer1->id,
            'product_id'    => $this->product->id,
            'order_item_id' => $orderItem->id,
            'rating'        => 5,
            'comment'       => 'Sản phẩm handmade chất lượng đỉnh cao!',
        ]);

        $response = $this->get("/products/{$this->product->id}");
        $response->assertStatus(200);
        $response->assertSee('Đánh giá từ khách hàng');
        $response->assertSee('Khách Hàng Đánh Giá');
        $response->assertSee('5/5 sao');
        $response->assertSee('Sản phẩm handmade chất lượng đỉnh cao!');
    }
}
