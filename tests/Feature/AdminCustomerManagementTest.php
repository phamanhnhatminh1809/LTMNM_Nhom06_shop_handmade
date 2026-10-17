<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $warehouseStaff;
    protected User $salesStaff;
    protected User $activeCustomer;
    protected User $lockedCustomer;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['id' => 1, 'name' => Role::ADMIN]);
        $roleCustomer = Role::create(['id' => 2, 'name' => Role::CUSTOMER]);
        $roleWarehouse = Role::create(['id' => 3, 'name' => Role::WAREHOUSE_STAFF]);
        $roleSales = Role::create(['id' => 4, 'name' => Role::SALES_STAFF]);

        $this->admin = User::create([
            'role_id'   => $roleAdmin->id,
            'username'  => 'admin_cust',
            'email'     => 'admin_cust@example.com',
            'password'  => Hash::make('password123'),
            'full_name' => 'Quản trị viên Hệ thống',
            'is_active' => true,
        ]);

        $this->warehouseStaff = User::create([
            'role_id'   => $roleWarehouse->id,
            'username'  => 'warehouse_cust',
            'email'     => 'warehouse_cust@example.com',
            'password'  => Hash::make('password123'),
            'full_name' => 'Nhân viên Kho',
            'is_active' => true,
        ]);

        $this->salesStaff = User::create([
            'role_id'   => $roleSales->id,
            'username'  => 'sales_cust',
            'email'     => 'sales_cust@example.com',
            'password'  => Hash::make('password123'),
            'full_name' => 'Nhân viên Bán hàng',
            'is_active' => true,
        ]);

        $this->activeCustomer = User::create([
            'role_id'   => $roleCustomer->id,
            'username'  => 'khach_active',
            'email'     => 'khach_active@example.com',
            'password'  => Hash::make('customer_pass123'),
            'full_name' => 'Phạm Minh Hoạt Động',
            'phone'     => '0909111222',
            'is_active' => true,
        ]);

        $this->lockedCustomer = User::create([
            'role_id'   => $roleCustomer->id,
            'username'  => 'khach_locked',
            'email'     => 'khach_locked@example.com',
            'password'  => Hash::make('customer_pass123'),
            'full_name' => 'Trần Văn Bị Khóa',
            'phone'     => '0909333444',
            'is_active' => false,
        ]);
    }

    // =========================================================================
    // 1. KIỂM THỬ LUỒNG CƠ BẢN (BASIC FLOW)
    // =========================================================================

    /**
     * Admin xem danh sách khách hàng, tìm kiếm theo tên/email/SĐT và lọc theo trạng thái active/locked
     */
    public function test_admin_can_view_search_and_filter_customers(): void
    {
        // Xem tất cả khách hàng (không hiển thị tài khoản nội bộ)
        $allResponse = $this->actingAs($this->admin)->get('/admin/customers');
        $allResponse->assertStatus(200);
        $allResponse->assertSee('Phạm Minh Hoạt Động');
        $allResponse->assertSee('Trần Văn Bị Khóa');
        $allResponse->assertDontSee('admin_cust@example.com');
        $allResponse->assertViewHas('statusCounts', function ($counts) {
            return $counts['all'] === 2 && $counts['active'] === 1 && $counts['locked'] === 1;
        });

        // Tìm kiếm theo SĐT
        $searchPhone = $this->actingAs($this->admin)->get('/admin/customers?q=0909111222');
        $searchPhone->assertStatus(200);
        $searchPhone->assertSee('Phạm Minh Hoạt Động');
        $searchPhone->assertDontSee('Trần Văn Bị Khóa');

        // Lọc theo trạng thái active
        $activeFilter = $this->actingAs($this->admin)->get('/admin/customers?status=active');
        $activeFilter->assertStatus(200);
        $activeFilter->assertSee('Phạm Minh Hoạt Động');
        $activeFilter->assertDontSee('Trần Văn Bị Khóa');

        // Lọc theo trạng thái locked
        $lockedFilter = $this->actingAs($this->admin)->get('/admin/customers?status=locked');
        $lockedFilter->assertStatus(200);
        $lockedFilter->assertSee('Trần Văn Bị Khóa');
        $lockedFilter->assertDontSee('Phạm Minh Hoạt Động');
    }

    /**
     * Admin xem chi tiết khách hàng kèm lịch sử đơn hàng
     */
    public function test_admin_can_view_customer_detail_with_order_history(): void
    {
        Order::create([
            'code'             => 'ORD-HIST-01',
            'user_id'          => $this->activeCustomer->id,
            'shipping_name'    => $this->activeCustomer->full_name,
            'shipping_phone'   => '0909111222',
            'shipping_address' => 'Hà Nội',
            'total_amount'     => 300000,
            'status'           => 'completed',
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/customers/{$this->activeCustomer->id}");
        $response->assertStatus(200);
        $response->assertSee('Phạm Minh Hoạt Động');
        $response->assertSee('ORD-HIST-01');
    }

    /**
     * Admin khóa và mở khóa tài khoản khách hàng; tài khoản bị khóa không thể đăng nhập
     */
    public function test_admin_can_lock_and_unlock_customer_and_locked_customer_cannot_login(): void
    {
        // Khóa tài khoản activeCustomer
        $lockResponse = $this->actingAs($this->admin)->patch("/admin/customers/{$this->activeCustomer->id}/lock");
        $lockResponse->assertSessionHas('success');
        $this->assertFalse($this->activeCustomer->fresh()->is_active);

        // Thử đăng nhập bằng tài khoản vừa bị khóa (trong phiên khách)
        auth()->logout();
        $loginAttempt = $this->post('/login', [
            'account'  => 'khach_active',
            'password' => 'customer_pass123',
        ]);
        $loginAttempt->assertSessionHasErrors(['account']);
        $this->assertGuest();

        // Thử gửi OTP quên mật khẩu cho tài khoản bị khóa -> bị chặn 403
        $otpAttempt = $this->postJson('/forgot-password/send-otp', [
            'email' => 'khach_active@example.com',
        ]);
        $otpAttempt->assertStatus(403);

        // Admin mở khóa lại tài khoản
        $unlockResponse = $this->actingAs($this->admin)->patch("/admin/customers/{$this->activeCustomer->id}/unlock");
        $unlockResponse->assertSessionHas('success');
        $this->assertTrue($this->activeCustomer->fresh()->is_active);

        // Sau khi mở khóa, khách hàng đăng nhập lại bình thường
        auth()->logout();
        $loginSuccess = $this->post('/login', [
            'account'  => 'khach_active',
            'password' => 'customer_pass123',
        ]);
        $loginSuccess->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($this->activeCustomer);
    }

    // =========================================================================
    // 2. KIỂM THỬ GIÁ TRỊ BIÊN & QUY TẮC NGHIỆP VỤ (BOUNDARY & BUSINESS RULES)
    // =========================================================================

    /**
     * Kiểm tra thao tác khóa lặp lại trên tài khoản đã khóa và mở khóa lặp lại trên tài khoản đang hoạt động
     */
    public function test_locking_already_locked_or_unlocking_already_active_customer_returns_error(): void
    {
        // Khóa tài khoản vốn đã bị khóa
        $lockAgain = $this->actingAs($this->admin)->patch("/admin/customers/{$this->lockedCustomer->id}/lock");
        $lockAgain->assertSessionHas('error');
        $this->assertFalse($this->lockedCustomer->fresh()->is_active);

        // Mở khóa tài khoản vốn đang hoạt động
        $unlockAgain = $this->actingAs($this->admin)->patch("/admin/customers/{$this->activeCustomer->id}/unlock");
        $unlockAgain->assertSessionHas('error');
        $this->assertTrue($this->activeCustomer->fresh()->is_active);
    }

    /**
     * Xem chi tiết khách hàng với ID của Admin hoặc Nhân viên phải trả về 404 Not Found
     */
    public function test_viewing_non_customer_in_customer_show_returns_404(): void
    {
        $this->actingAs($this->admin)->get("/admin/customers/{$this->admin->id}")->assertStatus(404);
        $this->actingAs($this->admin)->get("/admin/customers/{$this->warehouseStaff->id}")->assertStatus(404);
    }

    // =========================================================================
    // 3. KIỂM THỬ BẢO MẬT & PHÂN QUYỀN (SECURITY & RBAC)
    // =========================================================================

    /**
     * Không thể dùng endpoint khóa/mở khóa khách hàng để khóa tài khoản Admin hoặc Nhân viên (403 Forbidden)
     */
    public function test_cannot_lock_or_unlock_admin_or_staff_via_customer_endpoints(): void
    {
        $this->actingAs($this->admin)->patch("/admin/customers/{$this->admin->id}/lock")->assertStatus(403);
        $this->actingAs($this->admin)->patch("/admin/customers/{$this->warehouseStaff->id}/lock")->assertStatus(403);
        $this->actingAs($this->admin)->patch("/admin/customers/{$this->salesStaff->id}/unlock")->assertStatus(403);

        $this->assertTrue($this->admin->fresh()->is_active);
        $this->assertTrue($this->warehouseStaff->fresh()->is_active);
    }

    /**
     * Nhân viên kho (warehouse_staff) và Nhân viên bán hàng (sales_staff) KHÔNG được phép quản lý khách hàng
     */
    public function test_warehouse_and_sales_staff_cannot_access_customer_management(): void
    {
        foreach ([$this->warehouseStaff, $this->salesStaff, $this->activeCustomer] as $unauthorizedUser) {
            $this->actingAs($unauthorizedUser)->get('/admin/customers')->assertStatus(403);
            $this->actingAs($unauthorizedUser)->get("/admin/customers/{$this->activeCustomer->id}")->assertStatus(403);
            $this->actingAs($unauthorizedUser)->patch("/admin/customers/{$this->activeCustomer->id}/lock")->assertStatus(403);
            $this->actingAs($unauthorizedUser)->patch("/admin/customers/{$this->lockedCustomer->id}/unlock")->assertStatus(403);
        }

        $this->assertTrue($this->activeCustomer->fresh()->is_active);
        $this->assertFalse($this->lockedCustomer->fresh()->is_active);
    }
}
