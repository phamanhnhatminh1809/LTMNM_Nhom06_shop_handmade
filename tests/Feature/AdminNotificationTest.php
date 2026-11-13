<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $warehouseStaff;
    protected User $salesStaff;
    protected User $customer;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['id' => 1, 'name' => Role::ADMIN]);
        $roleCustomer = Role::create(['id' => 2, 'name' => Role::CUSTOMER]);
        $roleWarehouse = Role::create(['id' => 3, 'name' => Role::WAREHOUSE_STAFF]);
        $roleSales = Role::create(['id' => 4, 'name' => Role::SALES_STAFF]);

        $this->admin = User::create([
            'role_id'   => $roleAdmin->id,
            'username'  => 'admin_notif',
            'email'     => 'admin_notif@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Quản trị viên',
            'is_active' => true,
        ]);

        $this->warehouseStaff = User::create([
            'role_id'   => $roleWarehouse->id,
            'username'  => 'warehouse_notif',
            'email'     => 'warehouse_notif@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Nhân viên Kho',
            'is_active' => true,
        ]);

        $this->salesStaff = User::create([
            'role_id'   => $roleSales->id,
            'username'  => 'sales_notif',
            'email'     => 'sales_notif@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Nhân viên Bán hàng',
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'role_id'   => $roleCustomer->id,
            'username'  => 'cust_notif',
            'email'     => 'cust_notif@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Nguyễn Văn Khách',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Hoa len thủ công',
            'slug' => 'hoa-len-thu-cong',
        ]);
    }

    public function test_checkout_creates_order_and_low_stock_notifications_with_role_scoping(): void
    {
        $this->get('/admin/notifications')->assertRedirect('/login');

        $product = Product::create([
            'code'                => 'SP-NOTIF-01',
            'category_id'         => $this->category->id,
            'name'                => 'Bó hoa hướng dương len',
            'slug'                => 'bo-hoa-huong-duong-len',
            'price'               => 150000,
            'stock_quantity'      => 12,
            'min_stock_threshold' => 8,
            'image'               => 'assets/images/placeholder.svg',
        ]);

        $cart = Cart::create([
            'user_id'        => $this->customer->id,
            'status'         => 'active',
            'total_quantity' => 5,
            'total_amount'   => 750000,
        ]);

        CartDetail::create([
            'cart_id'      => $cart->id,
            'product_id'   => $product->id,
            'product_code' => $product->code,
            'name'         => $product->name,
            'image_url'    => $product->image,
            'unit_price'   => 150000,
            'quantity'     => 5,
        ]);

        $checkoutRes = $this->actingAs($this->customer)->post('/checkout', [
            'shipping_name'    => 'Nguyễn Văn Khách',
            'shipping_phone'   => '0901234567',
            'shipping_address' => '123 Đường Hoa Sứ, TP.HCM',
            'payment_method'   => 'cod',
        ]);

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $checkoutRes->assertRedirect(route('checkout.success', $order->id));

        $product->refresh();
        $this->assertEquals(7, $product->stock_quantity);

        $orderNotif = AdminNotification::where('type', AdminNotification::TYPE_NEW_ORDER)->first();
        $stockNotif = AdminNotification::where('type', AdminNotification::TYPE_LOW_STOCK)->first();

        $this->assertNotNull($orderNotif);
        $this->assertNotNull($stockNotif);
        $this->assertEquals(AdminNotification::SEVERITY_WARNING, $stockNotif->severity);

        // 1. Admin xem được cả 2 loại thông báo
        $adminRes = $this->actingAs($this->admin)->getJson('/admin/notifications');
        $adminRes->assertStatus(200)
            ->assertJsonPath('unread_count', 2)
            ->assertJsonCount(2, 'items');

        // 2. NV Bán hàng chỉ nhận thông báo đơn hàng mới
        $salesRes = $this->actingAs($this->salesStaff)->getJson('/admin/notifications');
        $salesRes->assertStatus(200)
            ->assertJsonPath('unread_count', 1)
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.type', AdminNotification::TYPE_NEW_ORDER);

        // 3. NV Kho chỉ nhận thông báo tồn kho thấp
        $warehouseRes = $this->actingAs($this->warehouseStaff)->getJson('/admin/notifications');
        $warehouseRes->assertStatus(200)
            ->assertJsonPath('unread_count', 1)
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.type', AdminNotification::TYPE_LOW_STOCK);

        // 4. Kiểm tra phân quyền đánh dấu đã đọc chéo loại & tài khoản khách hàng
        $this->actingAs($this->salesStaff)
            ->patchJson("/admin/notifications/{$stockNotif->id}/read")
            ->assertStatus(403);

        $this->actingAs($this->warehouseStaff)
            ->patchJson("/admin/notifications/{$orderNotif->id}/read")
            ->assertStatus(403);

        $this->actingAs($this->customer)
            ->getJson('/admin/notifications')
            ->assertStatus(403);

        // 5. Khách hàng hủy đơn hoàn kho vượt ngưỡng (12 > 8) -> tự động resolve thông báo low_stock
        $this->actingAs($this->customer)
            ->post("/orders/{$order->id}/cancel")
            ->assertRedirect();

        $stockNotif->refresh();
        $this->assertNotNull($stockNotif->resolved_at);
    }

    public function test_mark_as_read_and_read_all_are_tracked_per_user(): void
    {
        $orderNotif = AdminNotification::create([
            'type'       => AdminNotification::TYPE_NEW_ORDER,
            'severity'   => AdminNotification::SEVERITY_INFO,
            'title'      => 'Đơn hàng mới #ORD-TEST-01',
            'message'    => 'Khách A vừa đặt đơn.',
            'action_url' => '/admin/orders/1',
        ]);

        AdminNotification::create([
            'type'       => AdminNotification::TYPE_LOW_STOCK,
            'severity'   => AdminNotification::SEVERITY_WARNING,
            'title'      => 'Sắp hết hàng: Khăn len',
            'message'    => 'Còn 3 sản phẩm.',
            'action_url' => '/admin/products/1/edit',
        ]);

        // Sales đọc thông báo đơn hàng -> unread của sales về 0, admin vẫn còn 2
        $this->actingAs($this->salesStaff)
            ->patchJson("/admin/notifications/{$orderNotif->id}/read")
            ->assertStatus(200)
            ->assertJsonPath('unread_count', 0);

        $this->actingAs($this->admin)
            ->getJson('/admin/notifications')
            ->assertStatus(200)
            ->assertJsonPath('unread_count', 2);

        // Admin đánh dấu đã đọc tất cả -> unread về 0, tab unread rỗng
        $this->actingAs($this->admin)
            ->postJson('/admin/notifications/read-all')
            ->assertStatus(200)
            ->assertJsonPath('unread_count', 0);

        $this->actingAs($this->admin)
            ->getJson('/admin/notifications?filter=unread')
            ->assertStatus(200)
            ->assertJsonPath('unread_count', 0)
            ->assertJsonCount(0, 'items');
    }

    public function test_low_stock_alert_deduplicates_escalates_to_zero_and_resolves_on_restock(): void
    {
        // Tạo sản phẩm dưới ngưỡng 5 -> sinh 1 thông báo warning
        $this->actingAs($this->warehouseStaff)->post('/admin/products', [
            'name'                => 'Gấu bông len nhỏ',
            'code'                => 'SP-STOCK-01',
            'category_id'         => $this->category->id,
            'price'               => 120000,
            'stock_quantity'      => 4,
            'min_stock_threshold' => 5,
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('code', 'SP-STOCK-01')->firstOrFail();
        $this->assertEquals(1, AdminNotification::where('reference_id', $product->id)->count());

        // Cập nhật tồn xuống 2 (vẫn warning) -> cập nhật thông báo đang mở, không tạo trùng
        $this->actingAs($this->warehouseStaff)->put("/admin/products/{$product->id}", [
            'name'                => $product->name,
            'code'                => $product->code,
            'category_id'         => $this->category->id,
            'price'               => 120000,
            'stock_quantity'      => 2,
            'min_stock_threshold' => 5,
        ])->assertRedirect(route('admin.products.index'));

        $this->assertEquals(1, AdminNotification::where('reference_id', $product->id)->count());

        // Cập nhật tồn về 0 -> đóng warning cũ và tạo danger mới
        $this->actingAs($this->warehouseStaff)->put("/admin/products/{$product->id}", [
            'name'                => $product->name,
            'code'                => $product->code,
            'category_id'         => $this->category->id,
            'price'               => 120000,
            'stock_quantity'      => 0,
            'min_stock_threshold' => 5,
        ])->assertRedirect(route('admin.products.index'));

        $dangerAlert = AdminNotification::where('reference_id', $product->id)
            ->whereNull('resolved_at')
            ->first();
        $this->assertNotNull($dangerAlert);
        $this->assertEquals(AdminNotification::SEVERITY_DANGER, $dangerAlert->severity);

        // Nhập bổ sung tồn vượt ngưỡng (15 > 5) -> đánh dấu resolved_at cho toàn bộ cảnh báo
        $this->actingAs($this->warehouseStaff)->put("/admin/products/{$product->id}", [
            'name'                => $product->name,
            'code'                => $product->code,
            'category_id'         => $this->category->id,
            'price'               => 120000,
            'stock_quantity'      => 15,
            'min_stock_threshold' => 5,
        ])->assertRedirect(route('admin.products.index'));

        $this->assertEquals(
            0,
            AdminNotification::where('reference_id', $product->id)->whereNull('resolved_at')->count()
        );
    }

    public function test_admin_pages_render_notification_bell_with_initial_unread_count(): void
    {
        AdminNotification::create([
            'type'       => AdminNotification::TYPE_LOW_STOCK,
            'severity'   => AdminNotification::SEVERITY_WARNING,
            'title'      => 'Sắp hết hàng',
            'message'    => 'Sản phẩm còn ít.',
            'action_url' => '/admin/products',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/products');
        $response->assertStatus(200);
        $response->assertSee('data-admin-bell', false);
        $response->assertSee('data-unread-count="1"', false);
    }
}
