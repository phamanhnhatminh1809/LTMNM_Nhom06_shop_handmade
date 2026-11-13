<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    protected Role $roleAdmin;
    protected Role $roleWarehouse;
    protected Role $roleSales;
    protected Role $roleCustomer;

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
            'username'  => 'admin_user',
            'email'     => 'admin@example.com',
            'password'  => Hash::make('password123'),
            'full_name' => 'Nguyễn Quản Trị',
            'is_active' => true,
        ]);

        $this->warehouseStaff = User::create([
            'role_id'   => $this->roleWarehouse->id,
            'username'  => 'staff_warehouse',
            'email'     => 'warehouse@example.com',
            'password'  => Hash::make('password123'),
            'full_name' => 'Trần Thủ Kho',
            'is_active' => true,
        ]);

        $this->salesStaff = User::create([
            'role_id'   => $this->roleSales->id,
            'username'  => 'staff_sales',
            'email'     => 'sales@example.com',
            'password'  => Hash::make('password123'),
            'full_name' => 'Lê Bán Hàng',
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'role_id'   => $this->roleCustomer->id,
            'username'  => 'customer_user',
            'email'     => 'customer@example.com',
            'password'  => Hash::make('password123'),
            'full_name' => 'Phạm Khách Hàng',
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_admin_profile(): void
    {
        $response = $this->get('/admin/profile');
        $response->assertRedirect('/login');
    }

    public function test_customer_cannot_access_admin_profile(): void
    {
        $response = $this->actingAs($this->customer)->get('/admin/profile');
        $response->assertStatus(403);
    }

    public function test_admin_and_staff_can_access_admin_profile(): void
    {
        // Admin
        $resAdmin = $this->actingAs($this->admin)->get('/admin/profile');
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('Nguyễn Quản Trị');
        $resAdmin->assertSee('admin_user');
        $resAdmin->assertSee('Quản trị viên');

        // Warehouse Staff
        $resWh = $this->actingAs($this->warehouseStaff)->get('/admin/profile');
        $resWh->assertStatus(200);
        $resWh->assertSee('Trần Thủ Kho');
        $resWh->assertSee('staff_warehouse');
        $resWh->assertSee('Nhân viên kho');

        // Sales Staff
        $resSales = $this->actingAs($this->salesStaff)->get('/admin/profile');
        $resSales->assertStatus(200);
        $resSales->assertSee('Lê Bán Hàng');
        $resSales->assertSee('staff_sales');
        $resSales->assertSee('Nhân viên bán hàng');
    }

    public function test_staff_can_update_profile_info(): void
    {
        $response = $this->actingAs($this->warehouseStaff)->put('/admin/profile', [
            'full_name' => 'Trần Văn Kho Mới',
            'email'     => 'warehouse_new@example.com',
            'phone'     => '0987654321',
        ]);

        $response->assertRedirect('/admin/profile');
        $response->assertSessionHas('success');

        $this->warehouseStaff->refresh();
        $this->assertEquals('Trần Văn Kho Mới', $this->warehouseStaff->full_name);
        $this->assertEquals('warehouse_new@example.com', $this->warehouseStaff->email);
        $this->assertEquals('0987654321', $this->warehouseStaff->phone);
    }

    public function test_staff_cannot_update_email_to_existing_duplicate(): void
    {
        $response = $this->actingAs($this->salesStaff)->put('/admin/profile', [
            'full_name' => 'Lê Bán Hàng',
            'email'     => 'admin@example.com', // Đã tồn tại của admin
            'phone'     => '0912345678',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_staff_can_change_password_with_valid_credentials(): void
    {
        $response = $this->actingAs($this->salesStaff)->put('/admin/profile/password', [
            'current_password'      => 'password123',
            'password'              => 'NewSecret@2026',
            'password_confirmation' => 'NewSecret@2026',
        ]);

        $response->assertRedirect('/admin/profile');
        $response->assertSessionHas('password_success');

        $this->salesStaff->refresh();
        $this->assertTrue(Hash::check('NewSecret@2026', $this->salesStaff->password));
    }

    public function test_staff_cannot_change_password_with_wrong_current_password(): void
    {
        $response = $this->actingAs($this->salesStaff)->put('/admin/profile/password', [
            'current_password'      => 'wrongpassword',
            'password'              => 'NewSecret@2026',
            'password_confirmation' => 'NewSecret@2026',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    public function test_staff_cannot_change_password_to_same_as_current_password(): void
    {
        $response = $this->actingAs($this->salesStaff)->put('/admin/profile/password', [
            'current_password'      => 'password123',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}
