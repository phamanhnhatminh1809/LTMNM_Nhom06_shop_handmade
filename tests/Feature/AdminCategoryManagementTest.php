<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryManagementTest extends TestCase
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
            'username'  => 'admin_cat',
            'email'     => 'admin_cat@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Quản trị viên',
            'is_active' => true,
        ]);

        $this->warehouseStaff = User::create([
            'role_id'   => $roleWarehouse->id,
            'username'  => 'warehouse_cat',
            'email'     => 'warehouse_cat@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Nhân viên Kho',
            'is_active' => true,
        ]);

        $this->salesStaff = User::create([
            'role_id'   => $roleSales->id,
            'username'  => 'sales_cat',
            'email'     => 'sales_cat@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Nhân viên Bán hàng',
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'role_id'   => $roleCustomer->id,
            'username'  => 'customer_cat',
            'email'     => 'customer_cat@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Khách hàng',
            'is_active' => true,
        ]);
    }

    // =========================================================================
    // 1. KIỂM THỬ LUỒNG CƠ BẢN (BASIC FLOW)
    // =========================================================================

    /**
     * Admin và Nhân viên kho xem được danh sách loại sản phẩm kèm số lượng sản phẩm
     */
    public function test_admin_and_warehouse_staff_can_view_category_list(): void
    {
        $category = Category::create([
            'name' => 'Đồ len thủ công',
            'slug' => 'do-len-thu-cong',
            'icon' => '🧶',
        ]);

        Product::create([
            'code'           => 'SP-CAT-01',
            'category_id'    => $category->id,
            'name'           => 'Khăn len',
            'slug'           => 'khan-len',
            'price'          => 100000,
            'stock_quantity' => 5,
        ]);

        $adminResponse = $this->actingAs($this->admin)->get('/admin/categories');
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Đồ len thủ công');
        $adminResponse->assertSee('1 sản phẩm');

        $warehouseResponse = $this->actingAs($this->warehouseStaff)->get('/admin/categories');
        $warehouseResponse->assertStatus(200);
        $warehouseResponse->assertSee('Đồ len thủ công');
    }

    /**
     * Nhân viên kho xem được form tạo mới và tạo loại sản phẩm thành công (tự sinh slug)
     */
    public function test_warehouse_staff_can_create_category(): void
    {
        $createPage = $this->actingAs($this->warehouseStaff)->get('/admin/categories/create');
        $createPage->assertStatus(200);

        $response = $this->actingAs($this->warehouseStaff)->post('/admin/categories', [
            'name' => 'Gốm sứ nghệ thuật',
            'icon' => '🏺',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Gốm sứ nghệ thuật',
            'slug' => 'gom-su-nghe-thuat',
            'icon' => '🏺',
        ]);
    }

    /**
     * Nhân viên kho cập nhật loại sản phẩm thành công và tự động đổi slug
     */
    public function test_warehouse_staff_can_update_category(): void
    {
        $category = Category::create([
            'name' => 'Nến thơm cũ',
            'slug' => 'nen-thom-cu',
            'icon' => '🕯️',
        ]);

        $editPage = $this->actingAs($this->warehouseStaff)->get("/admin/categories/{$category->id}/edit");
        $editPage->assertStatus(200);

        $response = $this->actingAs($this->warehouseStaff)->put("/admin/categories/{$category->id}", [
            'name' => 'Nến thơm tinh dầu',
            'icon' => '✨',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');

        $category->refresh();
        $this->assertEquals('Nến thơm tinh dầu', $category->name);
        $this->assertEquals('nen-thom-tinh-dau', $category->slug);
        $this->assertEquals('✨', $category->icon);
    }

    /**
     * Xóa loại sản phẩm thành công khi không có sản phẩm nào thuộc danh mục
     */
    public function test_can_delete_empty_category(): void
    {
        $category = Category::create([
            'name' => 'Danh mục trống',
            'slug' => 'danh-muc-trong',
        ]);

        $response = $this->actingAs($this->warehouseStaff)->delete("/admin/categories/{$category->id}");

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    // =========================================================================
    // 2. KIỂM THỬ GIÁ TRỊ BIÊN & QUY TẮC NGHIỆP VỤ (BOUNDARY & BUSINESS RULES)
    // =========================================================================

    /**
     * Kiểm tra giá trị biên độ dài tên loại sản phẩm (max: 100) và icon (max: 50)
     */
    public function test_category_name_and_icon_boundary_lengths(): void
    {
        // Biên hợp lệ: name đúng 100 ký tự, icon đúng 50 ký tự
        $validName100 = str_repeat('a', 100);
        $validIcon50 = str_repeat('i', 50);

        $validResponse = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => $validName100,
            'icon' => $validIcon50,
        ]);
        $validResponse->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => $validName100]);

        // Biên không hợp lệ: name 101 ký tự, icon 51 ký tự
        $invalidName101 = str_repeat('b', 101);
        $invalidIcon51 = str_repeat('j', 51);

        $invalidResponse = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => $invalidName101,
            'icon' => $invalidIcon51,
        ]);
        $invalidResponse->assertSessionHasErrors(['name', 'icon']);

        // Biên rỗng: name để trống
        $emptyResponse = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => '',
        ]);
        $emptyResponse->assertSessionHasErrors(['name']);
    }

    /**
     * Không thể tạo hoặc cập nhật loại sản phẩm trùng tên, nhưng cho phép giữ nguyên tên cũ khi sửa
     */
    public function test_cannot_create_or_update_duplicate_category_name(): void
    {
        $cat1 = Category::create(['name' => 'Đồ len', 'slug' => 'do-len']);
        $cat2 = Category::create(['name' => 'Đồ gốm', 'slug' => 'do-gom']);

        // Tạo mới trùng tên
        $storeDup = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Đồ len',
        ]);
        $storeDup->assertSessionHasErrors(['name']);

        // Cập nhật cat2 trùng tên với cat1
        $updateDup = $this->actingAs($this->admin)->put("/admin/categories/{$cat2->id}", [
            'name' => 'Đồ len',
        ]);
        $updateDup->assertSessionHasErrors(['name']);

        // Cập nhật cat1 giữ nguyên tên chính nó -> hợp lệ
        $updateSame = $this->actingAs($this->admin)->put("/admin/categories/{$cat1->id}", [
            'name' => 'Đồ len',
            'icon' => '🧶',
        ]);
        $updateSame->assertRedirect(route('admin.categories.index'));
    }

    /**
     * Tự động thêm hậu tố -1 khi 2 tên danh mục khác nhau nhưng sinh ra cùng một slug
     */
    public function test_category_slug_collision_auto_increments_suffix(): void
    {
        Category::create(['name' => 'Đồ len', 'slug' => 'do-len']);

        $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Đồ - len',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Đồ - len',
            'slug' => 'do-len-1',
        ]);
    }

    /**
     * Chặn xóa loại sản phẩm khi đang có ít nhất 1 sản phẩm thuộc danh mục
     */
    public function test_cannot_delete_category_containing_products(): void
    {
        $category = Category::create([
            'name' => 'Danh mục có hàng',
            'slug' => 'danh-muc-co-hang',
        ]);

        Product::create([
            'code'           => 'SP-KEEP-01',
            'category_id'    => $category->id,
            'name'           => 'Sản phẩm đang bán',
            'slug'           => 'san-pham-dang-ban',
            'price'          => 150000,
            'stock_quantity' => 10,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    // =========================================================================
    // 3. KIỂM THỬ BẢO MẬT & PHÂN QUYỀN (SECURITY & RBAC)
    // =========================================================================

    /**
     * Nhân viên bán hàng (sales_staff) KHÔNG được phép thực hiện các chức năng quản lý loại sản phẩm của Nhân viên kho
     */
    public function test_sales_staff_cannot_access_or_modify_categories(): void
    {
        $category = Category::create([
            'name' => 'Danh mục kho',
            'slug' => 'danh-muc-kho',
        ]);

        $this->actingAs($this->salesStaff)->get('/admin/categories')->assertStatus(403);
        $this->actingAs($this->salesStaff)->get('/admin/categories/create')->assertStatus(403);
        $this->actingAs($this->salesStaff)->post('/admin/categories', ['name' => 'Hack'])->assertStatus(403);
        $this->actingAs($this->salesStaff)->get("/admin/categories/{$category->id}/edit")->assertStatus(403);
        $this->actingAs($this->salesStaff)->put("/admin/categories/{$category->id}", ['name' => 'Hack'])->assertStatus(403);
        $this->actingAs($this->salesStaff)->delete("/admin/categories/{$category->id}")->assertStatus(403);

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Danh mục kho']);
    }

    /**
     * Khách hàng (customer) và Khách vãng lai (guest) không được phép truy cập quản lý loại sản phẩm
     */
    public function test_guest_and_customer_cannot_access_categories_admin(): void
    {
        $this->get('/admin/categories')->assertRedirect('/login');
        $this->actingAs($this->customer)->get('/admin/categories')->assertStatus(403);
        $this->actingAs($this->customer)->post('/admin/categories', ['name' => 'Hack'])->assertStatus(403);
    }
}
