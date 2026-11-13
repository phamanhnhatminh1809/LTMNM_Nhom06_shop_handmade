<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $warehouseStaff;
    protected User $salesStaff;
    protected User $customer;
    protected Category $categoryLen;
    protected Category $categoryGom;
    protected array $createdFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['id' => 1, 'name' => Role::ADMIN]);
        $roleCustomer = Role::create(['id' => 2, 'name' => Role::CUSTOMER]);
        $roleWarehouse = Role::create(['id' => 3, 'name' => Role::WAREHOUSE_STAFF]);
        $roleSales = Role::create(['id' => 4, 'name' => Role::SALES_STAFF]);

        $this->admin = User::create([
            'role_id'   => $roleAdmin->id,
            'username'  => 'admin_prod',
            'email'     => 'admin_prod@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Quản trị viên',
            'is_active' => true,
        ]);

        $this->warehouseStaff = User::create([
            'role_id'   => $roleWarehouse->id,
            'username'  => 'warehouse_prod',
            'email'     => 'warehouse_prod@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Nhân viên Kho',
            'is_active' => true,
        ]);

        $this->salesStaff = User::create([
            'role_id'   => $roleSales->id,
            'username'  => 'sales_prod',
            'email'     => 'sales_prod@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Nhân viên Bán hàng',
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'role_id'   => $roleCustomer->id,
            'username'  => 'customer_prod',
            'email'     => 'customer_prod@example.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Khách hàng',
            'is_active' => true,
        ]);

        $this->categoryLen = Category::create([
            'name' => 'Đồ len',
            'slug' => 'do-len',
        ]);

        $this->categoryGom = Category::create([
            'name' => 'Đồ gốm',
            'slug' => 'do-gom',
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $relativePath) {
            $fullPath = public_path($relativePath);
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        parent::tearDown();
    }

    // =========================================================================
    // 1. KIỂM THỬ LUỒNG CƠ BẢN (BASIC FLOW)
    // =========================================================================

    /**
     * Xem danh sách sản phẩm, tìm kiếm theo từ khóa và lọc theo danh mục trong trang Admin
     */
    public function test_warehouse_staff_can_view_search_and_filter_products(): void
    {
        Product::create([
            'code'           => 'SP-LEN-01',
            'category_id'    => $this->categoryLen->id,
            'name'           => 'Túi len móc hoa cúc',
            'slug'           => 'tui-len-moc-hoa-cuc',
            'price'          => 220000,
            'stock_quantity' => 12,
        ]);

        Product::create([
            'code'           => 'SP-GOM-01',
            'category_id'    => $this->categoryGom->id,
            'name'           => 'Bình gốm Bát Tràng',
            'slug'           => 'binh-gom-bat-trang',
            'price'          => 350000,
            'stock_quantity' => 5,
        ]);

        // Xem toàn bộ
        $allResponse = $this->actingAs($this->warehouseStaff)->get('/admin/products');
        $allResponse->assertStatus(200);
        $allResponse->assertSee('Túi len móc hoa cúc');
        $allResponse->assertSee('Bình gốm Bát Tràng');

        // Tìm kiếm theo từ khóa
        $searchResponse = $this->actingAs($this->warehouseStaff)->get('/admin/products?q=hoa+cúc');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Túi len móc hoa cúc');
        $searchResponse->assertDontSee('Bình gốm Bát Tràng');

        // Lọc theo danh mục
        $filterResponse = $this->actingAs($this->warehouseStaff)->get('/admin/products?category=do-gom');
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Bình gốm Bát Tràng');
        $filterResponse->assertDontSee('Túi len móc hoa cúc');
    }

    /**
     * Thêm sản phẩm mới thành công kèm upload ảnh chính và bộ sưu tập ảnh phụ (gallery)
     */
    public function test_warehouse_staff_can_create_product_with_image_and_gallery(): void
    {
        $this->actingAs($this->warehouseStaff)->get('/admin/products/create')->assertStatus(200);

        $mainImage = UploadedFile::fake()->image('main_product.jpg', 400, 400);
        $gallery1 = UploadedFile::fake()->image('sub_1.png', 300, 300);
        $gallery2 = UploadedFile::fake()->image('sub_2.jpg', 300, 300);

        $response = $this->actingAs($this->warehouseStaff)->post('/admin/products', [
            'name'                 => 'Gấu bông len Amigurumi',
            'code'                 => 'SP-NEW-01',
            'category_id'          => $this->categoryLen->id,
            'price'                => 180000,
            'old_price'            => 220000,
            'stock_quantity'       => 25,
            'description'          => 'Gấu bông đan tay 100% sợi cotton milk',
            'instruction_preserve' => 'Giặt tay nhẹ nhàng bằng nước lạnh',
            'is_featured'          => '1',
            'image'                => $mainImage,
            'gallery'              => [$gallery1, $gallery2],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        $product = Product::where('code', 'SP-NEW-01')->first();
        $this->assertNotNull($product);
        $this->assertEquals('Gấu bông len Amigurumi', $product->name);
        $this->assertEquals(180000, $product->price);
        $this->assertEquals(25, $product->stock_quantity);
        $this->assertTrue($product->is_featured);
        $this->assertStringStartsWith('uploads/products/', $product->image);
        $this->assertCount(2, $product->gallery);

        // Ghi nhận file đã tạo để dọn dẹp sau khi chạy test
        $this->createdFiles[] = $product->image;
        foreach ($product->gallery as $path) {
            $this->createdFiles[] = $path;
        }

        $this->assertFileExists(public_path($product->image));
    }

    /**
     * Xem chi tiết, chỉnh sửa và xóa sản phẩm thành công
     */
    public function test_warehouse_staff_can_show_update_and_delete_product(): void
    {
        $product = Product::create([
            'code'           => 'SP-EDIT-01',
            'category_id'    => $this->categoryLen->id,
            'name'           => 'Sản phẩm ban đầu',
            'slug'           => 'san-pham-ban-dau-abcde',
            'price'          => 100000,
            'stock_quantity' => 10,
            'image'          => 'assets/images/products/tui-01.jpg',
        ]);

        // Xem chi tiết
        $this->actingAs($this->warehouseStaff)
            ->get("/admin/products/{$product->id}")
            ->assertStatus(200)
            ->assertSee('Sản phẩm ban đầu');

        // Xem form sửa
        $this->actingAs($this->warehouseStaff)
            ->get("/admin/products/{$product->id}/edit")
            ->assertStatus(200)
            ->assertSee('Sản phẩm ban đầu');

        // Cập nhật không upload ảnh mới -> giữ nguyên ảnh cũ
        $updateResponse = $this->actingAs($this->warehouseStaff)->put("/admin/products/{$product->id}", [
            'name'           => 'Sản phẩm đã cập nhật',
            'code'           => 'SP-EDIT-01',
            'category_id'    => $this->categoryGom->id,
            'price'          => 150000,
            'stock_quantity' => 30,
            'is_featured'    => '1',
        ]);

        $updateResponse->assertRedirect(route('admin.products.index'));
        $product->refresh();
        $this->assertEquals('Sản phẩm đã cập nhật', $product->name);
        $this->assertEquals($this->categoryGom->id, $product->category_id);
        $this->assertEquals(150000, $product->price);
        $this->assertEquals(30, $product->stock_quantity);
        $this->assertEquals('assets/images/products/tui-01.jpg', $product->image);

        // Xóa sản phẩm
        $deleteResponse = $this->actingAs($this->warehouseStaff)->delete("/admin/products/{$product->id}");
        $deleteResponse->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    // =========================================================================
    // 2. KIỂM THỬ GIÁ TRỊ BIÊN & QUY TẮC NGHIỆP VỤ (BOUNDARY & BUSINESS RULES)
    // =========================================================================

    /**
     * Kiểm tra giá trị biên cho giá bán (price >= 0) và tồn kho (stock_quantity >= 0, số nguyên)
     */
    public function test_product_price_and_stock_quantity_boundaries(): void
    {
        // Biên dưới hợp lệ: price = 0, old_price = 0, stock_quantity = 0
        $validZeroResponse = $this->actingAs($this->admin)->post('/admin/products', [
            'name'           => 'Sản phẩm tặng kèm 0đ',
            'code'           => 'SP-ZERO-01',
            'category_id'    => $this->categoryLen->id,
            'price'          => 0,
            'old_price'      => 0,
            'stock_quantity' => 0,
        ]);
        $validZeroResponse->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'code'           => 'SP-ZERO-01',
            'price'          => 0,
            'stock_quantity' => 0,
        ]);

        // Biên âm không hợp lệ: price = -1, old_price = -1, stock_quantity = -1
        $negativeResponse = $this->actingAs($this->admin)->post('/admin/products', [
            'name'           => 'Sản phẩm giá âm',
            'code'           => 'SP-NEG-01',
            'category_id'    => $this->categoryLen->id,
            'price'          => -1,
            'old_price'      => -1,
            'stock_quantity' => -1,
        ]);
        $negativeResponse->assertSessionHasErrors(['price', 'old_price', 'stock_quantity']);
    }

    /**
     * Kiểm tra biên độ dài tên sản phẩm (max: 255), mã sản phẩm (max: 50) và tính duy nhất của mã code
     */
    public function test_product_name_code_length_and_uniqueness_boundaries(): void
    {
        // Biên hợp lệ: name đúng 255 ký tự, code đúng 50 ký tự
        $name255 = str_repeat('P', 255);
        $code50 = str_repeat('C', 50);

        $validBoundary = $this->actingAs($this->admin)->post('/admin/products', [
            'name'           => $name255,
            'code'           => $code50,
            'category_id'    => $this->categoryLen->id,
            'price'          => 100000,
            'stock_quantity' => 5,
        ]);
        $validBoundary->assertRedirect(route('admin.products.index'));

        // Biên vượt quá giới hạn: name 256 ký tự, code 51 ký tự
        $invalidBoundary = $this->actingAs($this->admin)->post('/admin/products', [
            'name'           => str_repeat('P', 256),
            'code'           => str_repeat('C', 51),
            'category_id'    => $this->categoryLen->id,
            'price'          => 100000,
            'stock_quantity' => 5,
        ]);
        $invalidBoundary->assertSessionHasErrors(['name', 'code']);

        // Trùng mã code đã tồn tại
        $duplicateCode = $this->actingAs($this->admin)->post('/admin/products', [
            'name'           => 'Sản phẩm trùng mã',
            'code'           => $code50,
            'category_id'    => $this->categoryLen->id,
            'price'          => 100000,
            'stock_quantity' => 5,
        ]);
        $duplicateCode->assertSessionHasErrors(['code']);
    }

    /**
     * Kiểm tra biên số lượng ảnh phụ gallery (tối đa 5 ảnh) và dung lượng file ảnh (tối đa 5120 KB)
     */
    public function test_product_gallery_count_and_file_size_boundaries(): void
    {
        // Vượt quá 5 ảnh trong gallery (6 ảnh) -> báo lỗi validation
        $sixImages = [];
        for ($i = 1; $i <= 6; $i++) {
            $sixImages[] = UploadedFile::fake()->image("img_{$i}.jpg");
        }

        $tooManyGalleryResponse = $this->actingAs($this->admin)->post('/admin/products', [
            'name'           => 'Sản phẩm quá nhiều ảnh',
            'code'           => 'SP-GAL-6',
            'category_id'    => $this->categoryLen->id,
            'price'          => 100000,
            'stock_quantity' => 5,
            'gallery'        => $sixImages,
        ]);
        $tooManyGalleryResponse->assertSessionHasErrors(['gallery']);

        // Ảnh vượt quá 5MB (5121 KB) và sai định dạng (.pdf)
        $oversizedImage = UploadedFile::fake()->create('huge.jpg', 5121, 'image/jpeg');
        $invalidFileResponse = $this->actingAs($this->admin)->post('/admin/products', [
            'name'           => 'Sản phẩm ảnh quá nặng',
            'code'           => 'SP-HUGE',
            'category_id'    => $this->categoryLen->id,
            'price'          => 100000,
            'stock_quantity' => 5,
            'image'          => $oversizedImage,
        ]);
        $invalidFileResponse->assertSessionHasErrors(['image']);
    }

    // =========================================================================
    // 3. KIỂM THỬ BẢO MẬT & PHÂN QUYỀN (SECURITY & RBAC)
    // =========================================================================

    /**
     * Nhân viên bán hàng (sales_staff) KHÔNG được phép thực hiện bất kỳ chức năng quản lý sản phẩm nào của Nhân viên kho
     */
    public function test_sales_staff_cannot_access_or_manage_products(): void
    {
        $product = Product::create([
            'code'           => 'SP-SEC-01',
            'category_id'    => $this->categoryLen->id,
            'name'           => 'Sản phẩm bảo mật',
            'slug'           => 'san-pham-bao-mat',
            'price'          => 100000,
            'stock_quantity' => 10,
        ]);

        $this->actingAs($this->salesStaff)->get('/admin/products')->assertStatus(403);
        $this->actingAs($this->salesStaff)->get('/admin/products/create')->assertStatus(403);
        $this->actingAs($this->salesStaff)->post('/admin/products', [
            'name'           => 'Hack SP',
            'code'           => 'SP-HACK',
            'category_id'    => $this->categoryLen->id,
            'price'          => 10000,
            'stock_quantity' => 1,
        ])->assertStatus(403);
        $this->actingAs($this->salesStaff)->get("/admin/products/{$product->id}")->assertStatus(403);
        $this->actingAs($this->salesStaff)->get("/admin/products/{$product->id}/edit")->assertStatus(403);
        $this->actingAs($this->salesStaff)->put("/admin/products/{$product->id}", [
            'name' => 'Hack Edit',
        ])->assertStatus(403);
        $this->actingAs($this->salesStaff)->delete("/admin/products/{$product->id}")->assertStatus(403);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Sản phẩm bảo mật']);
    }

    /**
     * Lọc nâng cao sản phẩm theo khoảng giá, trạng thái kho và sắp xếp trên Admin
     */
    public function test_warehouse_staff_can_use_advanced_filters_and_sorting_on_products(): void
    {
        $prodActiveCheap = Product::create([
            'code'           => 'SP-ADV-01',
            'category_id'    => $this->categoryLen->id,
            'name'           => 'Móc khóa len mini',
            'slug'           => 'moc-khoa-len-mini',
            'price'          => 80000,
            'stock_quantity' => 15,
            'sold'           => 5,
        ]);

        $prodLowMid = Product::create([
            'code'           => 'SP-ADV-02',
            'category_id'    => $this->categoryGom->id,
            'name'           => 'Ly gốm men hỏa biến',
            'slug'           => 'ly-gom-men-hoa-bien',
            'price'          => 250000,
            'stock_quantity' => 3,
            'sold'           => 40,
        ]);

        $prodOutExpensive = Product::create([
            'code'           => 'SP-ADV-03',
            'category_id'    => $this->categoryGom->id,
            'name'           => 'Bình gốm nghệ nhân lớn',
            'slug'           => 'binh-gom-nghe-nhan-lon',
            'price'          => 600000,
            'stock_quantity' => 0,
            'sold'           => 12,
        ]);

        // 1. Lọc theo khoảng giá (min_price = 100000, max_price = 300000)
        $priceFilter = $this->actingAs($this->warehouseStaff)->get('/admin/products?min_price=100000&max_price=300000');
        $priceFilter->assertStatus(200);
        $priceFilter->assertSee('Ly gốm men hỏa biến');
        $priceFilter->assertDontSee('Móc khóa len mini');
        $priceFilter->assertDontSee('Bình gốm nghệ nhân lớn');

        // 2. Lọc theo trạng thái kho: low (1 - 5) và out (0)
        $lowFilter = $this->actingAs($this->warehouseStaff)->get('/admin/products?stock_status=low');
        $lowFilter->assertStatus(200);
        $lowFilter->assertSee('Ly gốm men hỏa biến');
        $lowFilter->assertDontSee('Móc khóa len mini');
        $lowFilter->assertDontSee('Bình gốm nghệ nhân lớn');

        $outFilter = $this->actingAs($this->warehouseStaff)->get('/admin/products?stock_status=out');
        $outFilter->assertStatus(200);
        $outFilter->assertSee('Bình gốm nghệ nhân lớn');
        $outFilter->assertDontSee('Ly gốm men hỏa biến');

        // 3. Sắp xếp theo bán chạy nhất và giá tăng dần
        $bestSelling = $this->actingAs($this->warehouseStaff)->get('/admin/products?sort=best_selling');
        $bestSelling->assertStatus(200);
        $bestSelling->assertViewHas('products', fn ($paginator) => $paginator->first()->id === $prodLowMid->id);

        $priceAsc = $this->actingAs($this->warehouseStaff)->get('/admin/products?sort=price_asc');
        $priceAsc->assertStatus(200);
        $priceAsc->assertViewHas('products', fn ($paginator) => $paginator->first()->id === $prodActiveCheap->id);

        $priceDesc = $this->actingAs($this->warehouseStaff)->get('/admin/products?sort=price_desc');
        $priceDesc->assertStatus(200);
        $priceDesc->assertViewHas('products', fn ($paginator) => $paginator->first()->id === $prodOutExpensive->id);
    }

    /**
     * Khách hàng (customer) và Khách vãng lai (guest) không được truy cập quản lý sản phẩm
     */
    public function test_guest_and_customer_cannot_access_admin_products(): void
    {
        $this->get('/admin/products')->assertRedirect('/login');
        $this->actingAs($this->customer)->get('/admin/products')->assertStatus(403);
        $this->actingAs($this->customer)->post('/admin/products', [])->assertStatus(403);
    }
}
