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

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $warehouseStaff;
    protected User $salesStaff;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['id' => 1, 'name' => Role::ADMIN]);
        $roleCustomer = Role::create(['id' => 2, 'name' => Role::CUSTOMER]);
        $roleWarehouse = Role::create(['id' => 3, 'name' => Role::WAREHOUSE_STAFF]);
        $roleSales = Role::create(['id' => 4, 'name' => Role::SALES_STAFF]);

        $this->admin = User::create([
            'role_id'   => $roleAdmin->id,
            'username'  => 'admin_dash',
            'email'     => 'admin_dash@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Quản trị viên',
            'is_active' => true,
        ]);

        $this->warehouseStaff = User::create([
            'role_id'   => $roleWarehouse->id,
            'username'  => 'warehouse_dash',
            'email'     => 'warehouse_dash@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Nhân viên Kho',
            'is_active' => true,
        ]);

        $this->salesStaff = User::create([
            'role_id'   => $roleSales->id,
            'username'  => 'sales_dash',
            'email'     => 'sales_dash@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Nhân viên Bán hàng',
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'role_id'   => $roleCustomer->id,
            'username'  => 'customer_dash',
            'email'     => 'customer_dash@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Khách hàng',
            'is_active' => true,
        ]);
    }

    // =========================================================================
    // 1. KIỂM THỬ LUỒNG CƠ BẢN (BASIC FLOW)
    // =========================================================================

    /**
     * Dashboard thống kê chính xác các chỉ số tổng quan, Top mặt hàng bán chạy và dữ liệu biểu đồ
     */
    public function test_dashboard_displays_metrics_top_selling_products_and_chart_data(): void
    {
        $catLen = Category::create(['name' => 'Đồ len', 'slug' => 'do-len']);
        $catGom = Category::create(['name' => 'Đồ gốm', 'slug' => 'do-gom']);

        $prodTop1 = Product::create([
            'code'           => 'SP-TOP-1',
            'category_id'    => $catLen->id,
            'name'           => 'Gấu bông len bán chạy nhất',
            'slug'           => 'gau-bong-len-ban-chay-nhat',
            'price'          => 200000,
            'stock_quantity' => 3, // <= 5: sắp hết hàng
            'sold'           => 50,
            'image'          => 'images/products/gau-01.jpg',
        ]);

        $prodTop2 = Product::create([
            'code'           => 'SP-TOP-2',
            'category_id'    => $catGom->id,
            'name'           => 'Cốc gốm bán chạy nhì',
            'slug'           => 'coc-gom-ban-chay-nhi',
            'price'          => 150000,
            'stock_quantity' => 10,
            'sold'           => 20,
            'image'          => 'images/products/coc-01.jpg',
        ]);

        $orderCompleted = Order::create([
            'code'             => 'ORD-DASH-01',
            'user_id'          => $this->customer->id,
            'shipping_name'    => 'Khách Hàng A',
            'shipping_phone'   => '0912345678',
            'shipping_address' => 'Hà Nội',
            'total_amount'     => 400000,
            'status'           => 'completed',
        ]);

        OrderItem::create([
            'order_id'    => $orderCompleted->id,
            'product_id'  => $prodTop1->id,
            'quantity'    => 2,
            'unit_price'  => 200000,
            'total_price' => 400000,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('TỔNG QUAN HỆ THỐNG');
        $response->assertSee('Gấu bông len bán chạy nhất');
        $response->assertSee('Cốc gốm bán chạy nhì');

        // Kiểm tra các biến truyền ra view
        $response->assertViewHas('revenueToday', 400000);
        $response->assertViewHas('lowStockCount', 1);
        $response->assertViewHas('totalProducts', 2);
        $response->assertViewHas('topSellingProducts', function ($topProducts) use ($prodTop1) {
            $first = $topProducts->first();
            return $first->id === $prodTop1->id
                && (int) $first->valid_ordered_qty === 2
                && (float) $first->total_revenue === 400000.0;
        });
        $response->assertViewHas('revenueWeekChart', function ($chart) {
            return count($chart['labels']) === 7 && count($chart['data']) === 7;
        });
        $response->assertViewHas('revenueMonthChart', function ($chart) {
            return count($chart['labels']) === 4 && count($chart['data']) === 4;
        });
        $response->assertViewHas('categoryShareChart', function ($chart) {
            return in_array('Đồ len', $chart['labels']) && in_array('Đồ gốm', $chart['labels']);
        });
    }

    // =========================================================================
    // 2. KIỂM THỬ GIÁ TRỊ BIÊN & QUY TẮC NGHIỆP VỤ (BOUNDARY & BUSINESS RULES)
    // =========================================================================

    /**
     * Khi cơ sở dữ liệu trống hoàn toàn (0 sản phẩm, 0 đơn hàng, 0 danh mục), Dashboard không bị lỗi chia cho 0
     */
    public function test_dashboard_handles_empty_database_without_division_by_zero(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertViewHas('revenueToday', 0);
        $response->assertViewHas('totalProducts', 0);
        $response->assertViewHas('topSellingProducts', fn ($items) => $items->isEmpty());
    }

    /**
     * Đơn hàng đã hủy (cancelled) không được tính vào doanh thu Dashboard và doanh thu Top sản phẩm bán chạy;
     * kiểm tra biên tồn kho thấp (stock_quantity = 5 vs 6) và giới hạn đúng Top 5 sản phẩm
     */
    public function test_dashboard_excludes_cancelled_orders_and_respects_low_stock_and_top_5_boundaries(): void
    {
        $cat = Category::create(['name' => 'Đồ len', 'slug' => 'do-len']);

        // Tạo 6 sản phẩm để kiểm tra giới hạn Top 5 và biên tồn kho (5 vs 6)
        for ($i = 1; $i <= 6; $i++) {
            Product::create([
                'code'           => "SP-B-{$i}",
                'category_id'    => $cat->id,
                'name'           => "Sản phẩm {$i}",
                'slug'           => "san-pham-{$i}",
                'price'          => 100000,
                'stock_quantity' => $i === 1 ? 5 : 6, // Chỉ có SP 1 có stock = 5 (<= 5), còn lại = 6 (> 5)
                'sold'           => $i * 10,
                'image'          => 'images/products/tui-01.jpg',
            ]);
        }

        $topProduct = Product::where('code', 'SP-B-6')->first();

        // Đơn hợp lệ (pending): 200.000đ
        $validOrder = Order::create([
            'code'             => 'ORD-VALID-01',
            'user_id'          => $this->customer->id,
            'shipping_name'    => 'Khách Hợp Lệ',
            'shipping_phone'   => '0911223344',
            'shipping_address' => 'Hà Nội',
            'total_amount'     => 200000,
            'status'           => 'pending',
        ]);
        OrderItem::create([
            'order_id'    => $validOrder->id,
            'product_id'  => $topProduct->id,
            'quantity'    => 2,
            'unit_price'  => 100000,
            'total_price' => 200000,
        ]);

        // Đơn đã hủy (cancelled): 900.000đ -> KHÔNG được tính vào doanh thu
        $cancelledOrder = Order::create([
            'code'             => 'ORD-CANCEL-99',
            'user_id'          => $this->customer->id,
            'shipping_name'    => 'Khách Hủy',
            'shipping_phone'   => '0911223344',
            'shipping_address' => 'Hà Nội',
            'total_amount'     => 900000,
            'status'           => 'cancelled',
        ]);
        OrderItem::create([
            'order_id'    => $cancelledOrder->id,
            'product_id'  => $topProduct->id,
            'quantity'    => 9,
            'unit_price'  => 100000,
            'total_price' => 900000,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);

        // Chỉ tính 200.000đ của đơn hợp lệ
        $response->assertViewHas('revenueToday', 200000);
        // Chỉ có 1 sản phẩm có stock_quantity = 5 (<= 5)
        $response->assertViewHas('lowStockCount', 1);
        // Top selling products chỉ lấy tối đa 5 sản phẩm và không cộng đơn cancelled
        $response->assertViewHas('topSellingProducts', function ($topProducts) use ($topProduct) {
            $first = $topProducts->first();
            return $topProducts->count() === 5
                && $first->id === $topProduct->id
                && (int) $first->valid_ordered_qty === 2
                && (float) $first->total_revenue === 200000.0;
        });
    }

    // =========================================================================
    // 3. KIỂM THỬ BẢO MẬT & PHÂN QUYỀN (SECURITY & RBAC)
    // =========================================================================

    /**
     * Admin, Nhân viên kho, Nhân viên bán hàng được vào Dashboard; Khách hàng bị chặn 403; Khách vãng lai bị chuyển về /login
     */
    public function test_dashboard_access_permissions_across_roles(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->actingAs($this->customer)->get('/admin/dashboard')->assertStatus(403);

        $this->actingAs($this->admin)->get('/admin/dashboard')->assertStatus(200);
        $this->actingAs($this->warehouseStaff)->get('/admin/dashboard')->assertStatus(200);
        $this->actingAs($this->salesStaff)->get('/admin/dashboard')->assertStatus(200);
    }
}
