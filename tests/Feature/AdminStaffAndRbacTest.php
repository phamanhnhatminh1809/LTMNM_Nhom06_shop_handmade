<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminStaffAndRbacTest extends TestCase
{
    use RefreshDatabase;

    protected Role $roleAdmin;
    protected Role $roleCustomer;
    protected Role $roleWarehouse;
    protected Role $roleSales;

    protected User $admin;
    protected User $warehouseStaff;
    protected User $salesStaff;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleAdmin = Role::create(['id' => 1, 'name' => Role::ADMIN]);
        $this->roleCustomer = Role::create(['id' => 2, 'name' => Role::CUSTOMER]);
        $this->roleWarehouse = Role::create(['id' => 3, 'name' => Role::WAREHOUSE_STAFF]);
        $this->roleSales = Role::create(['id' => 4, 'name' => Role::SALES_STAFF]);

        $this->admin = User::create([
            'role_id'   => $this->roleAdmin->id,
            'username'  => 'admin_master',
            'email'     => 'admin_master@example.com',
            'password'  => Hash::make('password123'),
            'full_name' => 'Admin Toàn Quyền',
            'is_active' => true,
        ]);

        $this->warehouseStaff = User::create([
            'role_id'   => $this->roleWarehouse->id,
            'username'  => 'nv_kho',
            'email'     => 'nv_kho@example.com',
            'password'  => Hash::make('password123'),
            'full_name' => 'Nhân Viên Kho',
            'is_active' => true,
        ]);

        $this->salesStaff = User::create([
            'role_id'   => $this->roleSales->id,
            'username'  => 'nv_banhang',
            'email'     => 'nv_banhang@example.com',
            'password'  => Hash::make('password123'),
            'full_name' => 'Nhân Viên Bán Hàng',
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'role_id'   => $this->roleCustomer->id,
            'username'  => 'khach_hang',
            'email'     => 'khach_hang@example.com',
            'password'  => Hash::make('password123'),
            'full_name' => 'Khách Hàng Thường',
            'is_active' => true,
        ]);
    }

    // =========================================================================
    // 1. KIỂM THỬ LUỒNG CƠ BẢN (BASIC FLOW)
    // =========================================================================

    /**
     * Admin xem danh sách nhân sự, tìm kiếm và lọc theo từng nhóm quyền
     */
    public function test_admin_can_view_search_and_filter_staff_list(): void
    {
        // Mặc định role=staff_only: thấy Admin, NV Kho, NV Bán hàng; không thấy Khách hàng
        $defaultResponse = $this->actingAs($this->admin)->get('/admin/staff');
        $defaultResponse->assertStatus(200);
        $defaultResponse->assertSee('Admin Toàn Quyền');
        $defaultResponse->assertSee('Nhân Viên Kho');
        $defaultResponse->assertSee('Nhân Viên Bán Hàng');
        $defaultResponse->assertDontSee('khach_hang@example.com');
        $defaultResponse->assertViewHas('roleCounts', function ($counts) {
            return $counts['all'] === 4
                && $counts['staff_only'] === 3
                && $counts['admin'] === 1
                && $counts['warehouse_staff'] === 1
                && $counts['sales_staff'] === 1
                && $counts['customer'] === 1;
        });

        // Lọc theo role=warehouse_staff
        $warehouseFilter = $this->actingAs($this->admin)->get('/admin/staff?role=warehouse_staff');
        $warehouseFilter->assertStatus(200);
        $warehouseFilter->assertSee('Nhân Viên Kho');
        $warehouseFilter->assertDontSee('Nhân Viên Bán Hàng');

        // Lọc theo role=all (hiển thị cả khách hàng để cấp quyền)
        $allFilter = $this->actingAs($this->admin)->get('/admin/staff?role=all');
        $allFilter->assertStatus(200);
        $allFilter->assertSee('Khách Hàng Thường');
    }

    /**
     * Admin tạo mới tài khoản nhân viên kho và nhân viên bán hàng thành công
     */
    public function test_admin_can_create_new_staff_account(): void
    {
        $this->actingAs($this->admin)->get('/admin/staff/create')->assertStatus(200);

        $response = $this->actingAs($this->admin)->post('/admin/staff', [
            'full_name'             => 'Lê Hoàng Kho Mới',
            'username'              => 'kho_moi_01',
            'email'                 => 'kho_moi_01@example.com',
            'phone'                 => '0911222333',
            'role_id'               => $this->roleWarehouse->id,
            'password'              => 'staff_pass123',
            'password_confirmation' => 'staff_pass123',
        ]);

        $response->assertRedirect(route('admin.staff.index'));
        $response->assertSessionHas('success');

        $newStaff = User::where('username', 'kho_moi_01')->first();
        $this->assertNotNull($newStaff);
        $this->assertEquals($this->roleWarehouse->id, $newStaff->role_id);
        $this->assertTrue($newStaff->isWarehouseStaff());
        $this->assertTrue($newStaff->is_active);
    }

    /**
     * Admin cấp/đổi quyền trực tiếp cho tài khoản và khóa/mở khóa nhân viên
     */
    public function test_admin_can_update_user_role_and_toggle_staff_status(): void
    {
        // Đổi quyền từ Khách hàng lên Nhân viên bán hàng
        $roleResponse = $this->actingAs($this->admin)->patch("/admin/staff/{$this->customer->id}/role", [
            'role_id' => $this->roleSales->id,
        ]);
        $roleResponse->assertSessionHas('success');
        $this->assertEquals($this->roleSales->id, $this->customer->fresh()->role_id);

        // Khóa tài khoản nhân viên kho
        $lockResponse = $this->actingAs($this->admin)->patch("/admin/staff/{$this->warehouseStaff->id}/status");
        $lockResponse->assertSessionHas('success');
        $this->assertFalse($this->warehouseStaff->fresh()->is_active);

        // Mở khóa lại tài khoản nhân viên kho
        $unlockResponse = $this->actingAs($this->admin)->patch("/admin/staff/{$this->warehouseStaff->id}/status");
        $unlockResponse->assertSessionHas('success');
        $this->assertTrue($this->warehouseStaff->fresh()->is_active);
    }

    /**
     * Điều hướng thông minh sau đăng nhập theo từng vai trò (Admin, NV Kho, NV Bán hàng, Khách hàng)
     */
    public function test_login_redirects_each_role_to_appropriate_default_page(): void
    {
        // 1. Nhân viên kho -> /admin/products
        $resWarehouse = $this->post('/login', [
            'account'  => 'nv_kho',
            'password' => 'password123',
        ]);
        $resWarehouse->assertRedirect(route('admin.products.index'));
        auth()->logout();

        // 2. Nhân viên bán hàng -> /admin/orders
        $resSales = $this->post('/login', [
            'account'  => 'nv_banhang',
            'password' => 'password123',
        ]);
        $resSales->assertRedirect(route('admin.orders.index'));
        auth()->logout();

        // 3. Admin -> /admin/dashboard
        $resAdmin = $this->post('/login', [
            'account'  => 'admin_master',
            'password' => 'password123',
        ]);
        $resAdmin->assertRedirect(route('admin.dashboard'));
        auth()->logout();

        // 4. Khách hàng -> /
        $resCustomer = $this->post('/login', [
            'account'  => 'khach_hang',
            'password' => 'password123',
        ]);
        $resCustomer->assertRedirect(route('home'));
    }

    // =========================================================================
    // 2. KIỂM THỬ GIÁ TRỊ BIÊN & QUY TẮC NGHIỆP VỤ (BOUNDARY & BUSINESS RULES)
    // =========================================================================

    /**
     * Kiểm tra giá trị biên khi tạo tài khoản nhân viên (độ dài mật khẩu, username, full_name, trùng lặp)
     */
    public function test_create_staff_validation_boundaries(): void
    {
        // Biên không hợp lệ: mật khẩu 7 ký tự (< 8), username 51 ký tự (> 50), full_name 101 ký tự (> 100)
        $invalidResponse = $this->actingAs($this->admin)->post('/admin/staff', [
            'full_name'             => str_repeat('N', 101),
            'username'              => str_repeat('u', 51),
            'email'                 => 'valid_staff@example.com',
            'role_id'               => $this->roleWarehouse->id,
            'password'              => '1234567',
            'password_confirmation' => '1234567',
        ]);
        $invalidResponse->assertSessionHasErrors(['full_name', 'username', 'password']);

        // Biên hợp lệ: mật khẩu đúng 8 ký tự, username đúng 50 ký tự, full_name đúng 100 ký tự
        $validResponse = $this->actingAs($this->admin)->post('/admin/staff', [
            'full_name'             => str_repeat('N', 100),
            'username'              => str_repeat('u', 50),
            'email'                 => 'boundary_staff@example.com',
            'role_id'               => $this->roleSales->id,
            'password'              => '12345678',
            'password_confirmation' => '12345678',
        ]);
        $validResponse->assertRedirect(route('admin.staff.index'));
        $this->assertDatabaseHas('users', ['email' => 'boundary_staff@example.com']);

        // Trùng username và email đã tồn tại
        $dupResponse = $this->actingAs($this->admin)->post('/admin/staff', [
            'full_name'             => 'Nhân viên trùng',
            'username'              => 'nv_kho',
            'email'                 => 'nv_kho@example.com',
            'role_id'               => $this->roleWarehouse->id,
            'password'              => '12345678',
            'password_confirmation' => '12345678',
        ]);
        $dupResponse->assertSessionHasErrors(['username', 'email']);
    }

    /**
     * Chặn Admin tự hạ quyền hoặc tự khóa tài khoản đang đăng nhập của chính mình
     */
    public function test_admin_cannot_demote_or_lock_own_account(): void
    {
        // Tự hạ quyền xuống Nhân viên kho -> bị chặn
        $demoteResponse = $this->actingAs($this->admin)->patch("/admin/staff/{$this->admin->id}/role", [
            'role_id' => $this->roleWarehouse->id,
        ]);
        $demoteResponse->assertSessionHas('error');
        $this->assertEquals($this->roleAdmin->id, $this->admin->fresh()->role_id);

        // Tự khóa tài khoản đang đăng nhập -> bị chặn
        $lockSelfResponse = $this->actingAs($this->admin)->patch("/admin/staff/{$this->admin->id}/status");
        $lockSelfResponse->assertSessionHas('error');
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    // =========================================================================
    // 3. KIỂM THỬ BẢO MẬT & PHÂN QUYỀN CHÉO (CROSS-ROLE RBAC SECURITY)
    // =========================================================================

    /**
     * Kiểm tra ma trận phân quyền chéo giữa Nhân viên bán hàng và Nhân viên kho
     */
    public function test_cross_role_authorization_matrix_between_sales_and_warehouse_staff(): void
    {
        // Nhân viên bán hàng:
        // - Được vào Dashboard, Đơn hàng, Đánh giá (200)
        // - Bị chặn Sản phẩm, Loại sản phẩm (403)
        // - Bị chặn Khách hàng, Nhân viên (403)
        $this->actingAs($this->salesStaff)->get('/admin/dashboard')->assertStatus(200);
        $this->actingAs($this->salesStaff)->get('/admin/orders')->assertStatus(200);
        $this->actingAs($this->salesStaff)->get('/admin/reviews')->assertStatus(200);
        $this->actingAs($this->salesStaff)->get('/admin/products')->assertStatus(403);
        $this->actingAs($this->salesStaff)->get('/admin/categories')->assertStatus(403);
        $this->actingAs($this->salesStaff)->get('/admin/customers')->assertStatus(403);
        $this->actingAs($this->salesStaff)->get('/admin/staff')->assertStatus(403);

        // Nhân viên kho:
        // - Được vào Dashboard, Sản phẩm, Loại sản phẩm (200)
        // - Bị chặn Đơn hàng, Đánh giá (403)
        // - Bị chặn Khách hàng, Nhân viên (403)
        $this->actingAs($this->warehouseStaff)->get('/admin/dashboard')->assertStatus(200);
        $this->actingAs($this->warehouseStaff)->get('/admin/products')->assertStatus(200);
        $this->actingAs($this->warehouseStaff)->get('/admin/categories')->assertStatus(200);
        $this->actingAs($this->warehouseStaff)->get('/admin/orders')->assertStatus(403);
        $this->actingAs($this->warehouseStaff)->get('/admin/reviews')->assertStatus(403);
        $this->actingAs($this->warehouseStaff)->get('/admin/customers')->assertStatus(403);
        $this->actingAs($this->warehouseStaff)->get('/admin/staff')->assertStatus(403);

        // Nhân viên kho và Nhân viên bán hàng không thể tự leo thang đặc quyền qua API /admin/staff
        $this->actingAs($this->warehouseStaff)->patch("/admin/staff/{$this->warehouseStaff->id}/role", [
            'role_id' => $this->roleAdmin->id,
        ])->assertStatus(403);
        $this->actingAs($this->salesStaff)->post('/admin/staff', [
            'full_name'             => 'Hack Admin',
            'username'              => 'hack_admin',
            'email'                 => 'hack@example.com',
            'role_id'               => $this->roleAdmin->id,
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertStatus(403);
    }

    /**
     * Tài khoản nhân viên đang đăng nhập nếu bị khóa giữa chừng sẽ bị AdminMiddleware tự động đăng xuất ngay
     */
    public function test_locked_staff_during_active_session_is_immediately_logged_out(): void
    {
        // Đang đăng nhập với quyền Nhân viên kho nhưng bị Admin khóa trong CSDL
        $this->warehouseStaff->update(['is_active' => false]);

        $response = $this->actingAs($this->warehouseStaff)->get('/admin/products');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['account']);
        $this->assertGuest();
    }

    /**
     * Admin có thể xem trang sửa chi tiết, cập nhật thông tin và khóa tài khoản nhân viên
     */
    public function test_admin_can_edit_staff_account_and_toggle_lock(): void
    {
        $this->actingAs($this->admin)->get("/admin/staff/{$this->warehouseStaff->id}/edit")
            ->assertStatus(200)
            ->assertSee($this->warehouseStaff->full_name);

        $response = $this->actingAs($this->admin)->put("/admin/staff/{$this->warehouseStaff->id}", [
            'full_name' => 'Nhân Viên Kho Đã Sửa',
            'email'     => 'nv_kho_sua@example.com',
            'phone'     => '0988776655',
            'role_id'   => $this->roleSales->id, // Chuyển sang NV Bán hàng
            'is_active' => '0',                  // Khóa tài khoản
        ]);

        $response->assertRedirect(route('admin.staff.index'));
        $response->assertSessionHas('success');

        $updated = $this->warehouseStaff->fresh();
        $this->assertEquals('Nhân Viên Kho Đã Sửa', $updated->full_name);
        $this->assertEquals('nv_kho_sua@example.com', $updated->email);
        $this->assertEquals($this->roleSales->id, $updated->role_id);
        $this->assertFalse($updated->is_active);
    }

    /**
     * Admin có thể xóa tài khoản nhân viên nhưng không thể tự xóa chính mình
     */
    public function test_admin_can_delete_staff_and_cannot_delete_self(): void
    {
        // 1. Chặn tự xóa chính mình
        $selfDelete = $this->actingAs($this->admin)->delete("/admin/staff/{$this->admin->id}");
        $selfDelete->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);

        // 2. Xóa thành công tài khoản nhân viên kho
        $deleteStaff = $this->actingAs($this->admin)->delete("/admin/staff/{$this->warehouseStaff->id}");
        $deleteStaff->assertRedirect(route('admin.staff.index'));
        $deleteStaff->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $this->warehouseStaff->id]);
    }

    /**
     * Admin không thể tự hạ quyền hoặc tự khóa tài khoản của chính mình qua form update
     */
    public function test_admin_cannot_demote_or_lock_self_in_update(): void
    {
        // Tự hạ quyền
        $demote = $this->actingAs($this->admin)->put("/admin/staff/{$this->admin->id}", [
            'full_name' => $this->admin->full_name,
            'email'     => $this->admin->email,
            'role_id'   => $this->roleSales->id,
            'is_active' => '1',
        ]);
        $demote->assertSessionHas('error');
        $this->assertEquals($this->roleAdmin->id, $this->admin->fresh()->role_id);

        // Tự khóa
        $lock = $this->actingAs($this->admin)->put("/admin/staff/{$this->admin->id}", [
            'full_name' => $this->admin->full_name,
            'email'     => $this->admin->email,
            'role_id'   => $this->roleAdmin->id,
            'is_active' => '0',
        ]);
        $lock->assertSessionHas('error');
        $this->assertTrue($this->admin->fresh()->is_active);
    }
}
