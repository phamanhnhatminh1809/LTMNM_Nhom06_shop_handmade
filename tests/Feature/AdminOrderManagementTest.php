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

class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $salesStaff;
    protected User $warehouseStaff;
    protected User $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['id' => 1, 'name' => Role::ADMIN]);
        $roleCustomer = Role::create(['id' => 2, 'name' => Role::CUSTOMER]);
        $roleWarehouse = Role::create(['id' => 3, 'name' => Role::WAREHOUSE_STAFF]);
        $roleSales = Role::create(['id' => 4, 'name' => Role::SALES_STAFF]);

        $this->admin = User::create([
            'role_id'   => $roleAdmin->id,
            'username'  => 'admin_order',
            'email'     => 'admin_order@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Quản trị viên',
            'is_active' => true,
        ]);

        $this->salesStaff = User::create([
            'role_id'   => $roleSales->id,
            'username'  => 'sales_order',
            'email'     => 'sales_order@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Nhân viên Bán hàng',
            'is_active' => true,
        ]);

        $this->warehouseStaff = User::create([
            'role_id'   => $roleWarehouse->id,
            'username'  => 'warehouse_order',
            'email'     => 'warehouse_order@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Nhân viên Kho',
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'role_id'   => $roleCustomer->id,
            'username'  => 'customer_order',
            'email'     => 'customer_order@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Khách hàng Đặt đơn',
            'is_active' => true,
        ]);

        $category = Category::create(['name' => 'Đồ len', 'slug' => 'do-len']);
        $this->product = Product::create([
            'code'           => 'SP-ORD-01',
            'category_id'    => $category->id,
            'name'           => 'Túi len đeo chéo',
            'slug'           => 'tui-len-deo-cheo',
            'price'          => 250000,
            'stock_quantity' => 20,
        ]);
    }

    // =========================================================================
    // 1. KIỂM THỬ LUỒNG CƠ BẢN (BASIC FLOW)
    // =========================================================================

    /**
     * Admin và Nhân viên bán hàng xem danh sách đơn hàng, lọc theo trạng thái và xem chi tiết đơn
     */
    public function test_sales_staff_can_view_and_filter_orders_and_view_detail(): void
    {
        $orderPending = Order::create([
            'code'             => 'ORD-ADM-01',
            'user_id'          => $this->customer->id,
            'shipping_name'    => 'Nguyễn Văn A',
            'shipping_phone'   => '0912345678',
            'shipping_address' => '123 Cầu Giấy, Hà Nội',
            'total_amount'     => 250000,
            'status'           => 'pending',
        ]);

        OrderItem::create([
            'order_id'    => $orderPending->id,
            'product_id'  => $this->product->id,
            'quantity'    => 1,
            'unit_price'  => 250000,
            'total_price' => 250000,
        ]);

        $orderShipping = Order::create([
            'code'             => 'ORD-ADM-02',
            'user_id'          => $this->customer->id,
            'shipping_name'    => 'Trần Thị B',
            'shipping_phone'   => '0987654321',
            'shipping_address' => '456 Quận 1, TP.HCM',
            'total_amount'     => 500000,
            'status'           => 'shipping',
        ]);

        // Xem tất cả đơn hàng
        $allResponse = $this->actingAs($this->salesStaff)->get('/admin/orders');
        $allResponse->assertStatus(200);
        $allResponse->assertSee('ORD-ADM-01');
        $allResponse->assertSee('ORD-ADM-02');
        $allResponse->assertViewHas('statusCounts', function ($counts) {
            return $counts['all'] === 2 && $counts['pending'] === 1 && $counts['shipping'] === 1;
        });

        // Lọc theo trạng thái pending
        $pendingResponse = $this->actingAs($this->salesStaff)->get('/admin/orders?status=pending');
        $pendingResponse->assertStatus(200);
        $pendingResponse->assertSee('ORD-ADM-01');
        $pendingResponse->assertDontSee('ORD-ADM-02');

        // Xem chi tiết đơn hàng
        $detailResponse = $this->actingAs($this->salesStaff)->get("/admin/orders/{$orderPending->id}");
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('ORD-ADM-01');
        $detailResponse->assertSee('Túi len đeo chéo');
    }

    /**
     * Duyệt và cập nhật trạng thái đơn hàng theo đúng luồng 1 chiều: pending -> shipping -> completed
     */
    public function test_sales_staff_can_advance_order_status_from_pending_to_shipping_to_completed(): void
    {
        $order = Order::create([
            'code'             => 'ORD-FLOW-01',
            'user_id'          => $this->customer->id,
            'shipping_name'    => 'Khách Hàng Luồng Chuẩn',
            'shipping_phone'   => '0911223344',
            'shipping_address' => 'Hà Nội',
            'total_amount'     => 250000,
            'status'           => 'pending',
        ]);

        // Bước 1: pending -> shipping
        $step1 = $this->actingAs($this->salesStaff)->patch("/admin/orders/{$order->id}/status", [
            'status' => 'shipping',
        ]);
        $step1->assertSessionHas('success');
        $this->assertEquals('shipping', $order->fresh()->status);

        // Bước 2: shipping -> completed
        $step2 = $this->actingAs($this->salesStaff)->patch("/admin/orders/{$order->id}/status", [
            'status' => 'completed',
        ]);
        $step2->assertSessionHas('success');
        $this->assertEquals('completed', $order->fresh()->status);
    }

    /**
     * Hủy đơn hàng khi đang ở trạng thái pending (pending -> cancelled)
     */
    public function test_sales_staff_can_cancel_pending_order(): void
    {
        $order = Order::create([
            'code'             => 'ORD-CANCEL-ADM',
            'user_id'          => $this->customer->id,
            'shipping_name'    => 'Khách Hủy Đơn',
            'shipping_phone'   => '0911223344',
            'shipping_address' => 'Hà Nội',
            'total_amount'     => 250000,
            'status'           => 'pending',
        ]);

        $response = $this->actingAs($this->salesStaff)->patch("/admin/orders/{$order->id}/status", [
            'status' => 'cancelled',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    // =========================================================================
    // 2. KIỂM THỬ GIÁ TRỊ BIÊN & QUY TẮC NGHIỆP VỤ (BOUNDARY & STATE TRANSITIONS)
    // =========================================================================

    /**
     * Chặn nhảy cóc trạng thái (pending -> completed) và chặn đảo ngược trạng thái đơn hàng
     */
    public function test_cannot_skip_or_reverse_order_status_transitions(): void
    {
        // 1. Không thể nhảy cóc từ pending -> completed
        $pendingOrder = Order::create([
            'code'             => 'ORD-SKIP-01',
            'user_id'          => $this->customer->id,
            'shipping_name'    => 'Khách A',
            'shipping_phone'   => '0911223344',
            'shipping_address' => 'Hà Nội',
            'total_amount'     => 250000,
            'status'           => 'pending',
        ]);

        $skipResponse = $this->actingAs($this->admin)->patch("/admin/orders/{$pendingOrder->id}/status", [
            'status' => 'completed',
        ]);
        $skipResponse->assertSessionHas('error');
        $this->assertEquals('pending', $pendingOrder->fresh()->status);

        // 2. Không thể lùi từ shipping -> pending hoặc hủy khi đang shipping
        $shippingOrder = Order::create([
            'code'             => 'ORD-REV-01',
            'user_id'          => $this->customer->id,
            'shipping_name'    => 'Khách B',
            'shipping_phone'   => '0911223344',
            'shipping_address' => 'Hà Nội',
            'total_amount'     => 250000,
            'status'           => 'shipping',
        ]);

        $this->actingAs($this->admin)->patch("/admin/orders/{$shippingOrder->id}/status", [
            'status' => 'pending',
        ])->assertSessionHas('error');
        $this->actingAs($this->admin)->patch("/admin/orders/{$shippingOrder->id}/status", [
            'status' => 'cancelled',
        ])->assertSessionHas('error');
        $this->assertEquals('shipping', $shippingOrder->fresh()->status);

        // 3. Đơn đã completed hoặc cancelled là trạng thái kết thúc (terminal state), không thể đổi sang bất kỳ trạng thái nào
        $completedOrder = Order::create([
            'code'             => 'ORD-DONE-01',
            'user_id'          => $this->customer->id,
            'shipping_name'    => 'Khách C',
            'shipping_phone'   => '0911223344',
            'shipping_address' => 'Hà Nội',
            'total_amount'     => 250000,
            'status'           => 'completed',
        ]);

        foreach (['pending', 'shipping', 'cancelled'] as $targetStatus) {
            $this->actingAs($this->admin)->patch("/admin/orders/{$completedOrder->id}/status", [
                'status' => $targetStatus,
            ])->assertSessionHas('error');
        }
        $this->assertEquals('completed', $completedOrder->fresh()->status);
    }

    /**
     * Kiểm tra giá trị trạng thái không hợp lệ (ngoài danh sách enum)
     */
    public function test_update_order_status_fails_with_invalid_status_value(): void
    {
        $order = Order::create([
            'code'             => 'ORD-INVALID-ST',
            'user_id'          => $this->customer->id,
            'shipping_name'    => 'Khách D',
            'shipping_phone'   => '0911223344',
            'shipping_address' => 'Hà Nội',
            'total_amount'     => 250000,
            'status'           => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->patch("/admin/orders/{$order->id}/status", [
            'status' => 'refunded_invalid',
        ]);

        $response->assertSessionHasErrors(['status']);
        $this->assertEquals('pending', $order->fresh()->status);
    }

    // =========================================================================
    // 3. KIỂM THỬ BẢO MẬT & PHÂN QUYỀN (SECURITY & RBAC)
    // =========================================================================

    /**
     * Nhân viên kho (warehouse_staff) KHÔNG được phép thực hiện chức năng quản lý đơn hàng của Nhân viên bán hàng
     */
    public function test_warehouse_staff_cannot_access_or_update_orders(): void
    {
        $order = Order::create([
            'code'             => 'ORD-SEC-01',
            'user_id'          => $this->customer->id,
            'shipping_name'    => 'Khách Bảo Mật',
            'shipping_phone'   => '0911223344',
            'shipping_address' => 'Hà Nội',
            'total_amount'     => 250000,
            'status'           => 'pending',
        ]);

        $this->actingAs($this->warehouseStaff)->get('/admin/orders')->assertStatus(403);
        $this->actingAs($this->warehouseStaff)->get("/admin/orders/{$order->id}")->assertStatus(403);
        $this->actingAs($this->warehouseStaff)->patch("/admin/orders/{$order->id}/status", [
            'status' => 'shipping',
        ])->assertStatus(403);

        $this->assertEquals('pending', $order->fresh()->status);
    }

    /**
     * Khách hàng (customer) và Khách vãng lai (guest) không được phép truy cập quản lý đơn hàng Admin
     */
    public function test_guest_and_customer_cannot_access_admin_orders(): void
    {
        $this->get('/admin/orders')->assertRedirect('/login');
        $this->actingAs($this->customer)->get('/admin/orders')->assertStatus(403);
    }
}
