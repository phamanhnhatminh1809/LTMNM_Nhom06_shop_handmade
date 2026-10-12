<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderTrackingTest extends TestCase
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
            'username'  => 'customer_one',
            'email'     => 'cust1@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Khách Hàng Một',
            'phone'     => '0911223344',
            'address'   => '123 Đường Số 1, Hà Nội',
            'is_active' => true,
        ]);

        $this->customer2 = User::create([
            'role_id'   => $roleCustomer->id,
            'username'  => 'customer_two',
            'email'     => 'cust2@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Khách Hàng Hai',
            'phone'     => '0988776655',
            'address'   => '456 Đường Số 2, Đà Nẵng',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Đồ len',
            'slug' => 'do-len',
        ]);

        $this->product = Product::create([
            'code'           => 'SP-ORDER-01',
            'category_id'    => $category->id,
            'name'           => 'Khăn quàng len',
            'slug'           => 'khan-quang-len',
            'price'          => 120000,
            'stock_quantity' => 20,
            'sold'           => 10,
            'image'          => 'images/products/khan-01.jpg',
        ]);
    }

    /**
     * 1. Khách vãng lai chưa đăng nhập bị chuyển hướng về trang login
     */
    public function test_guest_cannot_access_orders_page(): void
    {
        $response = $this->get('/orders');
        $response->assertRedirect('/login');
    }

    /**
     * 2. Khách hàng xem danh sách đơn hàng và lọc theo trạng thái
     */
    public function test_customer_can_view_orders_with_status_filtering(): void
    {
        // Tạo các đơn hàng với các trạng thái khác nhau
        $orderPending = Order::create([
            'code'             => 'ORD-PENDING-01',
            'user_id'          => $this->customer1->id,
            'shipping_name'    => 'Khách Hàng Một',
            'shipping_phone'   => '0911223344',
            'shipping_address' => '123 Đường Số 1',
            'total_amount'     => 120000,
            'status'           => 'pending',
        ]);

        $orderShipping = Order::create([
            'code'             => 'ORD-SHIP-01',
            'user_id'          => $this->customer1->id,
            'shipping_name'    => 'Khách Hàng Một',
            'shipping_phone'   => '0911223344',
            'shipping_address' => '123 Đường Số 1',
            'total_amount'     => 240000,
            'status'           => 'shipping',
        ]);

        // Đơn của customer2 để kiểm tra không bị lẫn
        Order::create([
            'code'             => 'ORD-CUST2-01',
            'user_id'          => $this->customer2->id,
            'shipping_name'    => 'Khách Hàng Hai',
            'shipping_phone'   => '0988776655',
            'shipping_address' => '456 Đường Số 2',
            'total_amount'     => 500000,
            'status'           => 'pending',
        ]);

        // Xem tất cả đơn của customer1
        $response = $this->actingAs($this->customer1)->get('/orders');
        $response->assertStatus(200);
        $response->assertSee('ORD-PENDING-01');
        $response->assertSee('ORD-SHIP-01');
        $response->assertDontSee('ORD-CUST2-01');

        // Lọc tab pending
        $pendingResponse = $this->actingAs($this->customer1)->get('/orders?status=pending');
        $pendingResponse->assertStatus(200);
        $pendingResponse->assertSee('ORD-PENDING-01');
        $pendingResponse->assertDontSee('ORD-SHIP-01');

        // Lọc tab shipping
        $shippingResponse = $this->actingAs($this->customer1)->get('/orders?status=shipping');
        $shippingResponse->assertStatus(200);
        $shippingResponse->assertSee('ORD-SHIP-01');
        $shippingResponse->assertDontSee('ORD-PENDING-01');
    }

    /**
     * 3. Khách hàng không thể xem chi tiết đơn hàng của người khác (403 Forbidden)
     */
    public function test_customer_cannot_view_other_customers_order(): void
    {
        $orderCust2 = Order::create([
            'code'             => 'ORD-SECRET-02',
            'user_id'          => $this->customer2->id,
            'shipping_name'    => 'Khách Hàng Hai',
            'shipping_phone'   => '0988776655',
            'shipping_address' => '456 Đường Số 2',
            'total_amount'     => 500000,
            'status'           => 'pending',
        ]);

        $response = $this->actingAs($this->customer1)->get("/orders/{$orderCust2->id}");
        $response->assertStatus(403);
    }

    /**
     * 4. Khách hàng xem chi tiết đơn hàng của chính mình thành công
     */
    public function test_customer_can_view_own_order_detail(): void
    {
        $order = Order::create([
            'code'             => 'ORD-DETAIL-01',
            'user_id'          => $this->customer1->id,
            'shipping_name'    => 'Khách Hàng Một',
            'shipping_phone'   => '0911223344',
            'shipping_address' => '123 Đường Số 1, Hà Nội',
            'total_amount'     => 240000,
            'status'           => 'pending',
        ]);

        OrderItem::create([
            'order_id'    => $order->id,
            'product_id'  => $this->product->id,
            'quantity'    => 2,
            'unit_price'  => 120000,
            'total_price' => 240000,
        ]);

        $response = $this->actingAs($this->customer1)->get("/orders/{$order->id}");
        $response->assertStatus(200);
        $response->assertSee('ORD-DETAIL-01');
        $response->assertSee('Khăn quàng len');
        $response->assertSee('240.000đ');
        $response->assertSee('123 Đường Số 1, Hà Nội');
        $response->assertSee('Hủy đơn hàng');
    }

    /**
     * 5. Khách hàng hủy đơn hàng khi trạng thái còn pending và được hoàn tồn kho
     */
    public function test_customer_can_cancel_pending_order_and_inventory_is_restored(): void
    {
        $initialStock = $this->product->stock_quantity; // 20
        $initialSold = $this->product->sold; // 10

        $order = Order::create([
            'code'             => 'ORD-CANCEL-01',
            'user_id'          => $this->customer1->id,
            'shipping_name'    => 'Khách Hàng Một',
            'shipping_phone'   => '0911223344',
            'shipping_address' => '123 Đường Số 1',
            'total_amount'     => 240000,
            'status'           => 'pending',
        ]);

        OrderItem::create([
            'order_id'    => $order->id,
            'product_id'  => $this->product->id,
            'quantity'    => 2,
            'unit_price'  => 120000,
            'total_price' => 240000,
        ]);

        $response = $this->actingAs($this->customer1)->post("/orders/{$order->id}/cancel");
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        // Kiểm tra hoàn trả tồn kho: 20 + 2 = 22, sold: 10 - 2 = 8
        $this->product->refresh();
        $this->assertEquals($initialStock + 2, $this->product->stock_quantity);
        $this->assertEquals($initialSold - 2, $this->product->sold);
    }

    /**
     * 6. Khách hàng không thể hủy đơn hàng khi đã chuyển sang shipping hoặc completed
     */
    public function test_customer_cannot_cancel_shipping_or_completed_order(): void
    {
        $orderShipping = Order::create([
            'code'             => 'ORD-SHIPPING-NO-CANCEL',
            'user_id'          => $this->customer1->id,
            'shipping_name'    => 'Khách Hàng Một',
            'shipping_phone'   => '0911223344',
            'shipping_address' => '123 Đường Số 1',
            'total_amount'     => 120000,
            'status'           => 'shipping',
        ]);

        $response = $this->actingAs($this->customer1)->post("/orders/{$orderShipping->id}/cancel");
        $response->assertSessionHas('error');

        $orderShipping->refresh();
        $this->assertEquals('shipping', $orderShipping->status);
    }

    /**
     * 7. Khách hàng không thể hủy đơn hàng của người khác (403 Forbidden)
     */
    public function test_customer_cannot_cancel_other_customers_order(): void
    {
        $orderCust2 = Order::create([
            'code'             => 'ORD-CUST2-PENDING',
            'user_id'          => $this->customer2->id,
            'shipping_name'    => 'Khách Hàng Hai',
            'shipping_phone'   => '0988776655',
            'shipping_address' => '456 Đường Số 2',
            'total_amount'     => 120000,
            'status'           => 'pending',
        ]);

        $response = $this->actingAs($this->customer1)->post("/orders/{$orderCust2->id}/cancel");
        $response->assertStatus(403);

        $orderCust2->refresh();
        $this->assertEquals('pending', $orderCust2->status);
    }
}
