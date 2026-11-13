<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpClientRequest;
use Illuminate\Support\Facades\Http;
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

    /**
     * 13. Miễn phí vận chuyển thống nhất 300.000đ & xóa bỏ toàn bộ route coupon
     */
    public function test_shipping_threshold_300k_and_removed_coupon_routes(): void
    {
        // Route coupon cũ đã bị gỡ bỏ -> 404
        $this->postJson('/checkout/apply-coupon', ['code' => 'SALE10'])->assertStatus(404);
        $this->postJson('/checkout/remove-coupon')->assertStatus(404);

        // Đơn hàng < 300.000đ (1 * 200.000đ) -> cộng 30.000đ phí ship
        $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 1,
        ]);

        $response = $this->actingAs($this->customer)->post('/checkout', [
            'shipping_name'    => 'Khách Dưới 300k',
            'shipping_phone'   => '0912345678',
            'shipping_address' => '123 Đường Test, Hà Nội',
            'payment_method'   => 'cod',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id'         => $this->customer->id,
            'shipping_fee'    => 30000,
            'discount_amount' => 0,
            'total_amount'    => 230000,
            'payment_method'  => 'cod',
        ]);
    }

    /**
     * 14. Chặn QR nếu thiếu cấu hình PAYOS_* và tạo link PayOS đúng chữ ký khi cấu hình đủ
     */
    public function test_payos_qr_checkout_requires_config_and_redirects_to_checkout_url(): void
    {
        $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 2, // 400.000đ >= 300.000đ -> freeship
        ]);

        // Khi chưa cấu hình PAYOS_* -> chặn tạo đơn qr
        config([
            'shop.payos.client_id'    => '',
            'shop.payos.api_key'      => '',
            'shop.payos.checksum_key' => '',
        ]);

        $blockedResponse = $this->actingAs($this->customer)->post('/checkout', [
            'shipping_name'    => 'Khách QR Thiếu Config',
            'shipping_phone'   => '0912345678',
            'shipping_address' => '123 Đường Test, Hà Nội',
            'payment_method'   => 'qr',
        ]);
        $blockedResponse->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);

        // Khi cấu hình PayOS đủ -> gọi API PayOS tạo link & redirect checkoutUrl
        config([
            'shop.payos.client_id'    => 'test-client-id',
            'shop.payos.api_key'      => 'test-api-key',
            'shop.payos.checksum_key' => 'test-checksum-secret',
            'shop.payos.base_url'     => 'https://api-merchant.payos.vn',
        ]);

        Http::fake([
            'https://api-merchant.payos.vn/v2/payment-requests' => function (HttpClientRequest $req) {
                $body = $req->data();

                return Http::response([
                    'code' => '00',
                    'desc' => 'success',
                    'data' => [
                        'orderCode'     => $body['orderCode'],
                        'paymentLinkId' => 'payos_link_999',
                        'checkoutUrl'   => 'https://pay.payos.vn/web/payos_link_999',
                        'status'        => 'PENDING',
                    ],
                ], 200);
            },
        ]);

        $allowedResponse = $this->actingAs($this->customer)->post('/checkout', [
            'shipping_name'    => 'Khách QR PayOS',
            'shipping_phone'   => '0912345678',
            'shipping_address' => '123 Đường Test, Hà Nội',
            'payment_method'   => 'qr',
        ]);

        $allowedResponse->assertRedirect('https://pay.payos.vn/web/payos_link_999');

        Http::assertSent(function (HttpClientRequest $req) {
            $data = $req->data();
            $expectedRaw = implode('&', [
                'amount=' . $data['amount'],
                'cancelUrl=' . $data['cancelUrl'],
                'description=' . $data['description'],
                'orderCode=' . $data['orderCode'],
                'returnUrl=' . $data['returnUrl'],
            ]);
            $expectedSig = hash_hmac('sha256', $expectedRaw, 'test-checksum-secret');

            return $req->url() === 'https://api-merchant.payos.vn/v2/payment-requests'
                && $req->hasHeader('x-client-id', 'test-client-id')
                && $req->hasHeader('x-api-key', 'test-api-key')
                && $data['amount'] === 400000
                && hash_equals($expectedSig, $data['signature']);
        });

        $this->assertDatabaseHas('orders', [
            'user_id'                => $this->customer->id,
            'shipping_name'          => 'Khách QR PayOS',
            'shipping_fee'           => 0,
            'total_amount'           => 400000,
            'payment_method'         => 'qr',
            'payment_gateway'        => 'payos',
            'payment_status'         => 'unpaid',
            'payment_transaction_id' => 'payos_link_999',
            'checkout_url'           => 'https://pay.payos.vn/web/payos_link_999',
        ]);
    }

    /**
     * 15. Khi API PayOS trả lỗi, transaction đặt hàng rollback sạch (giữ tồn kho & giỏ hàng)
     */
    public function test_payos_checkout_rolls_back_when_api_fails(): void
    {
        $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);

        config([
            'shop.payos.client_id'    => 'test-client-id',
            'shop.payos.api_key'      => 'test-api-key',
            'shop.payos.checksum_key' => 'test-checksum-secret',
            'shop.payos.base_url'     => 'https://api-merchant.payos.vn',
        ]);

        Http::fake([
            'https://api-merchant.payos.vn/v2/payment-requests' => Http::response([
                'code' => '20',
                'desc' => 'Invalid API credentials',
            ], 401),
        ]);

        $response = $this->actingAs($this->customer)->post('/checkout', [
            'shipping_name'    => 'Khách Lỗi PayOS',
            'shipping_phone'   => '0912345678',
            'shipping_address' => '123 Đường Test, Hà Nội',
            'payment_method'   => 'qr',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
        $this->assertEquals(15, $this->product->fresh()->stock_quantity);
        $this->assertDatabaseHas('carts', [
            'user_id'        => $this->customer->id,
            'status'         => 'active',
            'total_quantity' => 2,
        ]);
    }

    /**
     * 16. Callback returnUrl của PayOS kiểm tra server-to-server và chốt payment_status = paid
     */
    public function test_payos_return_verifies_server_status_and_marks_order_paid(): void
    {
        config([
            'shop.payos.client_id'    => 'test-client-id',
            'shop.payos.api_key'      => 'test-api-key',
            'shop.payos.checksum_key' => 'test-checksum-secret',
            'shop.payos.base_url'     => 'https://api-merchant.payos.vn',
        ]);

        $order = Order::create([
            'code'              => 'ORD-PAYOS-OK',
            'user_id'           => $this->customer->id,
            'shipping_name'     => 'Khách PayOS OK',
            'shipping_phone'    => '0912345678',
            'shipping_address'  => 'Hà Nội',
            'payment_method'    => 'qr',
            'payment_status'    => 'unpaid',
            'payment_gateway'   => 'payos',
            'payment_reference' => 261113140001,
            'total_amount'      => 400000,
            'status'            => 'pending',
        ]);

        Http::fake([
            'https://api-merchant.payos.vn/v2/payment-requests/261113140001' => Http::response([
                'code' => '00',
                'desc' => 'success',
                'data' => [
                    'id'         => 'payos_link_done',
                    'orderCode'  => 261113140001,
                    'amount'     => 400000,
                    'amountPaid' => 400000,
                    'status'     => 'PAID',
                ],
            ], 200),
        ]);

        $res = $this->actingAs($this->customer)->get('/checkout/payos/return?orderCode=261113140001');
        $res->assertRedirect(route('checkout.success', $order->id));
        $res->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('payos_link_done', $order->payment_transaction_id);
        $this->assertNotNull($order->paid_at);
    }

    /**
     * 17. Khách bấm hủy thanh toán trên PayOS -> hủy đơn, đóng link PayOS & hoàn tồn kho ngay
     */
    public function test_payos_cancel_restores_inventory_and_cancels_order(): void
    {
        $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);

        config([
            'shop.payos.client_id'    => 'test-client-id',
            'shop.payos.api_key'      => 'test-api-key',
            'shop.payos.checksum_key' => 'test-checksum-secret',
            'shop.payos.base_url'     => 'https://api-merchant.payos.vn',
        ]);

        Http::fake([
            'https://api-merchant.payos.vn/v2/payment-requests' => function (HttpClientRequest $req) {
                $body = $req->data();

                return Http::response([
                    'code' => '00',
                    'data' => [
                        'orderCode'     => $body['orderCode'],
                        'paymentLinkId' => 'payos_link_cancel_test',
                        'checkoutUrl'   => 'https://pay.payos.vn/web/cancel_test',
                    ],
                ], 200);
            },
            'https://api-merchant.payos.vn/v2/payment-requests/*' => Http::response([
                'code' => '00',
                'data' => [
                    'status'     => 'PENDING',
                    'amountPaid' => 0,
                ],
            ], 200),
            'https://api-merchant.payos.vn/v2/payment-requests/*/cancel' => Http::response([
                'code' => '00',
                'data' => ['status' => 'CANCELLED'],
            ], 200),
        ]);

        $this->actingAs($this->customer)->post('/checkout', [
            'shipping_name'    => 'Khách Hủy PayOS',
            'shipping_phone'   => '0912345678',
            'shipping_address' => 'Hà Nội',
            'payment_method'   => 'qr',
        ]);

        $order = Order::latest('id')->first();
        $this->assertEquals(13, $this->product->fresh()->stock_quantity);

        $cancelRes = $this->actingAs($this->customer)->get('/checkout/payos/cancel?orderCode=' . $order->payment_reference);
        $cancelRes->assertRedirect(route('orders.show', $order->id));
        $cancelRes->assertSessionHas('error');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('cancelled', $order->payment_status);
        $this->assertEquals(15, $this->product->fresh()->stock_quantity);
    }

    /**
     * 18. Webhook PayOS kiểm tra chữ ký HMAC-SHA256 hợp lệ, chống giả mạo & idempotent
     */
    public function test_payos_webhook_verifies_hmac_signature_and_is_idempotent(): void
    {
        config([
            'shop.payos.client_id'    => 'test-client-id',
            'shop.payos.api_key'      => 'test-api-key',
            'shop.payos.checksum_key' => 'test-checksum-secret',
        ]);

        $order = Order::create([
            'code'              => 'ORD-PAYOS-WH',
            'user_id'           => $this->customer->id,
            'shipping_name'     => 'Khách Webhook',
            'shipping_phone'    => '0912345678',
            'shipping_address'  => 'Hà Nội',
            'payment_method'    => 'qr',
            'payment_status'    => 'unpaid',
            'payment_gateway'   => 'payos',
            'payment_reference' => 261113149999,
            'total_amount'      => 230000,
            'status'            => 'pending',
        ]);

        $data = [
            'accountNumber' => '12345678',
            'amount'        => 230000,
            'description'   => 'TT ORDPAYOSWH',
            'orderCode'     => 261113149999,
            'paymentLinkId' => 'payos_wh_tx_1',
        ];
        ksort($data);
        $signStr = collect($data)->map(fn ($v, $k) => $k . '=' . $v)->implode('&');
        $validSig = hash_hmac('sha256', $signStr, 'test-checksum-secret');

        // Chữ ký giả mạo -> 400
        $this->postJson('/checkout/payos/webhook', [
            'code'      => '00',
            'success'   => true,
            'data'      => $data,
            'signature' => 'invalid_signature',
        ])->assertStatus(400);
        $this->assertEquals('unpaid', $order->fresh()->payment_status);

        // Chữ ký đúng -> 200 & paid
        $this->postJson('/checkout/payos/webhook', [
            'code'      => '00',
            'success'   => true,
            'data'      => $data,
            'signature' => $validSig,
        ])->assertStatus(200);
        $this->assertEquals('paid', $order->fresh()->payment_status);

        // Gửi lặp lần 2 -> vẫn 200 & idempotent
        $this->postJson('/checkout/payos/webhook', [
            'code'      => '00',
            'success'   => true,
            'data'      => $data,
            'signature' => $validSig,
        ])->assertStatus(200);
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }

    /**
     * 19. Bảo mật: User khác không thể truy cập return/cancel/verify PayOS của người khác (403)
     */
    public function test_user_cannot_access_other_users_payos_callbacks(): void
    {
        $otherCustomer = User::create([
            'role_id'   => 2,
            'username'  => 'other_payos_user',
            'email'     => 'other_payos@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Người Khác',
            'is_active' => true,
        ]);

        $order = Order::create([
            'code'              => 'ORD-PAYOS-403',
            'user_id'           => $this->customer->id,
            'shipping_name'     => 'Khách 1',
            'shipping_phone'    => '0912345678',
            'shipping_address'  => 'Hà Nội',
            'payment_method'    => 'qr',
            'payment_status'    => 'unpaid',
            'payment_gateway'   => 'payos',
            'payment_reference' => 261113148888,
            'total_amount'      => 200000,
            'status'            => 'pending',
        ]);

        $this->actingAs($otherCustomer)->get('/checkout/payos/return?orderCode=261113148888')->assertStatus(403);
        $this->actingAs($otherCustomer)->get('/checkout/payos/cancel?orderCode=261113148888')->assertStatus(403);
        $this->actingAs($otherCustomer)->post("/checkout/payos/{$order->id}/verify")->assertStatus(403);
    }

    /**
     * 20. AJAX POST /cart trả tổng số lượng và trả 422 khi giỏ đã đạt tồn kho tối đa
     */
    public function test_ajax_add_to_cart_returns_total_quantity_and_rejects_above_stock(): void
    {
        $first = $this->actingAs($this->customer)->postJson('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 15, // chạm trần stock_quantity = 15
        ]);
        $first->assertStatus(200)->assertJson([
            'success'        => true,
            'total_quantity' => 15,
        ])->assertJsonStructure(['cart_url']);

        $second = $this->actingAs($this->customer)->postJson('/cart', [
            'product_id' => $this->product->id,
            'quantity'   => 1,
        ]);
        $second->assertStatus(422)->assertJson([
            'success' => false,
        ]);
    }
}
