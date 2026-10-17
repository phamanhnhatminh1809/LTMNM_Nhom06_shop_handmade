<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Product $product;
    protected Product $outOfStockProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $roleCustomer = Role::create(['id' => 2, 'name' => 'customer']);

        $this->customer = User::create([
            'role_id'   => $roleCustomer->id,
            'username'  => 'customer_cart',
            'email'     => 'cart_user@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Khách Hàng Giỏ Hàng',
            'phone'     => '0988776655',
            'address'   => '123 Phố Thủ Công, Hà Nội',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Đồ len',
            'slug' => 'do-len',
        ]);

        $this->product = Product::create([
            'code'           => 'SP-TEST-01',
            'category_id'    => $category->id,
            'name'           => 'Túi len handmade',
            'slug'           => 'tui-len-handmade',
            'price'          => 200000,
            'stock_quantity' => 15,
            'sold'           => 2,
            'image'          => 'images/products/tui-01.jpg',
        ]);

        $this->outOfStockProduct = Product::create([
            'code'           => 'SP-TEST-02',
            'category_id'    => $category->id,
            'name'           => 'Móc khóa hết hàng',
            'slug'           => 'moc-khoa-het-hang',
            'price'          => 50000,
            'stock_quantity' => 0,
            'sold'           => 10,
            'image'          => 'images/products/keychain-01.jpg',
        ]);
    }

    /**
     * 1. Khách hàng có thể truy cập trang giỏ hàng
     */
    public function test_user_can_view_cart_page(): void
    {
        $response = $this->actingAs($this->customer)->get('/cart');
        $response->assertStatus(200);
        $response->assertSee('Giỏ hàng của bạn');
        $response->assertSee('Giỏ hàng của bạn đang trống');
    }

    /**
     * 2. Khách hàng thêm sản phẩm vào giỏ hàng thành công
     */
    public function test_user_can_add_product_to_cart(): void
    {
        $response = $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);

        $response->assertRedirect('/cart');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cart_details', [
            'product_id'   => $this->product->id,
            'quantity'     => 2,
            'unit_price'   => 200000,
        ]);

        $cartResponse = $this->actingAs($this->customer)->get('/cart');
        $cartResponse->assertSee('Túi len handmade');
        $cartResponse->assertSee('400.000đ'); // 2 * 200.000đ
    }

    /**
     * 3. Không thể thêm sản phẩm đã hết hàng vào giỏ
     */
    public function test_user_cannot_add_out_of_stock_product_to_cart(): void
    {
        $response = $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->outOfStockProduct->id,
            'quantity'   => 1,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('cart_details', [
            'product_id' => $this->outOfStockProduct->id,
        ]);
    }

    /**
     * 4. Khách hàng cập nhật số lượng sản phẩm trong giỏ hàng
     */
    public function test_user_can_update_cart_item_quantity(): void
    {
        $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);

        $cartDetail = \App\Models\CartDetail::where('product_id', $this->product->id)->first();

        $response = $this->actingAs($this->customer)->put("/cart/{$cartDetail->id}", [
            'quantity' => 5,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(5, $cartDetail->fresh()->quantity);
    }

    /**
     * 5. Khách hàng xóa một sản phẩm khỏi giỏ hàng
     */
    public function test_user_can_remove_item_from_cart(): void
    {
        $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 1,
        ]);

        $cartDetail = \App\Models\CartDetail::where('product_id', $this->product->id)->first();

        $response = $this->actingAs($this->customer)->delete("/cart/{$cartDetail->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('cart_details', [
            'id' => $cartDetail->id,
        ]);
    }

    /**
     * 6. Khách hàng xóa toàn bộ giỏ hàng
     */
    public function test_user_can_clear_cart(): void
    {
        $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);

        $response = $this->actingAs($this->customer)->post('/cart/clear');
        $response->assertRedirect('/cart');
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('cart_details', 0);
    }

    /**
     * 7. Chuyển hướng về giỏ hàng nếu vào trang checkout khi giỏ hàng trống
     */
    public function test_checkout_redirects_if_cart_is_empty(): void
    {
        $response = $this->actingAs($this->customer)->get('/checkout');
        $response->assertRedirect('/cart');
        $response->assertSessionHas('error');
    }

    /**
     * 8. Xem lại đơn hàng trên trang thanh toán khi có sản phẩm trong giỏ
     */
    public function test_checkout_page_is_accessible_with_items_in_cart(): void
    {
        $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);

        $response = $this->actingAs($this->customer)->get('/checkout');
        $response->assertStatus(200);
        $response->assertSee('Xem lại đơn hàng và Thanh toán');
        $response->assertSee('Thanh toán khi nhận hàng (COD)');
        $response->assertSee('Túi len handmade');
        $response->assertSee('400.000đ');
        // Kiểm tra thông tin mặc định của tài khoản được điền sẵn
        $response->assertSee('Khách Hàng Giỏ Hàng');
        $response->assertSee('0988776655');
    }

    /**
     * 9. Đặt hàng thành công bằng phương thức COD
     */
    public function test_checkout_process_creates_order_with_cod(): void
    {
        $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 3,
        ]);

        $initialStock = $this->product->stock_quantity;
        $initialSold = $this->product->sold;

        $response = $this->actingAs($this->customer)->post('/checkout', [
            'shipping_name'    => 'Nguyễn Văn Nhận Hàng',
            'shipping_phone'   => '0912345678',
            'shipping_address' => '456 Phố Mới, Cầu Giấy, Hà Nội',
            'payment_method'   => 'cod',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $response->assertRedirect("/checkout/success/{$order->id}");

        // Kiểm tra thông tin đơn hàng
        $this->assertEquals($this->customer->id, $order->user_id);
        $this->assertEquals('Nguyễn Văn Nhận Hàng', $order->shipping_name);
        $this->assertEquals('0912345678', $order->shipping_phone);
        $this->assertEquals('456 Phố Mới, Cầu Giấy, Hà Nội', $order->shipping_address);
        $this->assertEquals(600000, $order->total_amount); // 3 * 200.000
        $this->assertEquals('pending', $order->status);

        // Kiểm tra chi tiết đơn hàng OrderItem
        $this->assertDatabaseHas('order_items', [
            'order_id'   => $order->id,
            'product_id' => $this->product->id,
            'quantity'   => 3,
            'unit_price' => 200000,
            'total_price'=> 600000,
        ]);

        // Kiểm tra tồn kho bị trừ và số lượng bán tăng
        $this->product->refresh();
        $this->assertEquals($initialStock - 3, $this->product->stock_quantity);
        $this->assertEquals($initialSold + 3, $this->product->sold);

        // Kiểm tra giỏ hàng đã được làm trống
        $this->assertDatabaseCount('cart_details', 0);

        // Kiểm tra xem trang success
        $successResponse = $this->actingAs($this->customer)->get("/checkout/success/{$order->id}");
        $successResponse->assertStatus(200);
        $successResponse->assertSee($order->code);
        $successResponse->assertSee('ĐẶT HÀNG THÀNH CÔNG');
        $successResponse->assertSee('Thanh toán khi nhận hàng (COD)');
    }

    /**
     * 10. Đặt hàng thất bại nếu số điện thoại không đúng định dạng
     */
    public function test_checkout_fails_with_invalid_shipping_phone(): void
    {
        $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 1,
        ]);

        $response = $this->actingAs($this->customer)->post('/checkout', [
            'shipping_name'    => 'Nguyễn Văn Nhận Hàng',
            'shipping_phone'   => 'abcxyz',
            'shipping_address' => '456 Phố Mới, Hà Nội',
            'payment_method'   => 'cod',
        ]);

        $response->assertSessionHasErrors(['shipping_phone']);
        $this->assertDatabaseCount('orders', 0);
    }

    /**
     * 11. Giá trị biên: Thêm hoặc cập nhật số lượng vượt quá tồn kho sẽ tự động giới hạn bằng tồn kho tối đa
     */
    public function test_cart_quantity_exceeding_stock_is_capped_at_stock_quantity(): void
    {
        // Tồn kho hiện tại của product là 15, thử thêm 99 sản phẩm
        $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 99,
        ]);

        $cartDetail = \App\Models\CartDetail::where('product_id', $this->product->id)->first();
        $this->assertNotNull($cartDetail);
        $this->assertEquals(15, $cartDetail->quantity);

        // Thử cập nhật lên 50 -> tự điều chỉnh về 15
        $this->actingAs($this->customer)->put("/cart/{$cartDetail->id}", [
            'quantity' => 50,
        ]);
        $this->assertEquals(15, $cartDetail->fresh()->quantity);
    }

    /**
     * 12. Bảo mật: Khách hàng không thể sửa/xóa mục giỏ hàng hoặc xem trang đặt hàng thành công của người khác (403 Forbidden)
     */
    public function test_user_cannot_modify_other_users_cart_or_view_other_users_checkout_success(): void
    {
        $otherCustomer = User::create([
            'role_id'   => 2,
            'username'  => 'other_cart_user',
            'email'     => 'other_cart@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Khách Hàng Khác',
            'is_active' => true,
        ]);

        // Tạo giỏ hàng của customer 1
        $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);
        $cartDetail = \App\Models\CartDetail::where('product_id', $this->product->id)->first();

        // otherCustomer cố tình sửa hoặc xóa cartDetail của customer 1 -> 403
        $this->actingAs($otherCustomer)->put("/cart/{$cartDetail->id}", ['quantity' => 1])->assertStatus(403);
        $this->actingAs($otherCustomer)->delete("/cart/{$cartDetail->id}")->assertStatus(403);

        // Tạo đơn hàng của customer 1, otherCustomer cố tình xem trang checkout success -> 403
        $order = Order::create([
            'code'             => 'ORD-SEC-CART',
            'user_id'          => $this->customer->id,
            'shipping_name'    => 'Khách 1',
            'shipping_phone'   => '0912345678',
            'shipping_address' => 'Hà Nội',
            'total_amount'     => 400000,
            'status'           => 'pending',
        ]);

        $this->actingAs($otherCustomer)->get("/checkout/success/{$order->id}")->assertStatus(403);
    }
}
