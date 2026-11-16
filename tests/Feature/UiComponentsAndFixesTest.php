<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiComponentsAndFixesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $salesStaff;
    private User $customer;
    private Category $category;
    private Product $inStockProduct;
    private Product $outOfStockProduct;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['id' => 1, 'name' => Role::ADMIN]);
        Role::create(['id' => 2, 'name' => Role::CUSTOMER]);
        Role::create(['id' => 3, 'name' => Role::WAREHOUSE_STAFF]);
        Role::create(['id' => 4, 'name' => Role::SALES_STAFF]);

        $this->admin = User::create([
            'role_id'   => 1,
            'username'  => 'admin_ui',
            'email'     => 'admin_ui@shophandmade.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Quản trị UI',
            'is_active' => true,
        ]);

        $this->salesStaff = User::create([
            'role_id'   => 4,
            'username'  => 'sales_ui',
            'email'     => 'sales_ui@shophandmade.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Thu Ngân UI',
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'role_id'   => 2,
            'username'  => 'cust_ui',
            'email'     => 'cust_ui@shophandmade.com',
            'password'  => bcrypt('password123'),
            'full_name' => 'Khách UI',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Gốm sứ',
            'slug' => 'gom-su',
            'icon' => 'bi-cup-hot',
        ]);

        $this->inStockProduct = Product::create([
            'code'                 => 'SPUI01',
            'category_id'          => $this->category->id,
            'name'                 => 'Bình gốm men mộc',
            'slug'                 => 'binh-gom-men-moc',
            'price'                => 180000,
            'stock_quantity'       => 12,
            'description'          => "  \nMô tả có khoảng trắng thừa ở đầu và cuối.\n  ",
            'instruction_preserve' => "\n  Lau nhẹ bằng khăn ẩm.  \n",
            'image'                => 'assets/images/placeholder.svg',
            'is_featured'          => true,
        ]);

        $this->outOfStockProduct = Product::create([
            'code'           => 'SPUI02',
            'category_id'    => $this->category->id,
            'name'           => 'Ly gốm hết hàng',
            'slug'           => 'ly-gom-het-hang',
            'price'          => 120000,
            'stock_quantity' => 0,
            'image'          => 'assets/images/placeholder.svg',
            'is_featured'    => false,
        ]);
    }

    public function test_homepage_and_products_cards_display_quick_add_to_cart_button(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Thêm vào giỏ');
        $response->assertSee('data-quick-add-cart', false);
        $response->assertSee('Chi tiết');

        $catalogResponse = $this->get('/products');
        $catalogResponse->assertOk();
        $catalogResponse->assertSee('Thêm vào giỏ');
        $catalogResponse->assertSee('Hết hàng');
    }

    public function test_cart_uses_auto_dismiss_toast_without_stuck_inline_banner_and_supports_ajax_update(): void
    {
        $this->actingAs($this->customer)->post('/cart', [
            'product_id' => $this->inStockProduct->id,
            'quantity'   => 1,
        ]);

        $cart = Cart::getActiveCart();
        $detail = $cart->details()->firstOrFail();

        $ajaxResponse = $this->actingAs($this->customer)
            ->putJson("/cart/{$detail->id}", ['quantity' => 3]);

        $ajaxResponse->assertOk()
            ->assertJson([
                'success'        => true,
                'item_quantity'  => 3,
                'total_quantity' => 3,
                'item_subtotal'  => 540000,
                'total_amount'   => 540000,
            ]);

        $pageWithFlash = $this->actingAs($this->customer)
            ->withSession(['success' => 'Cập nhật số lượng thành công.'])
            ->get('/cart');

        $pageWithFlash->assertOk();
        $pageWithFlash->assertSee('data-flash-success="Cập nhật số lượng thành công."', false);
        // Inline stuck green flash banner outside #shopToastContainer must not be rendered on /cart
        $html = $pageWithFlash->getContent();
        $this->assertSame(1, substr_count($html, 'Cập nhật số lượng thành công.'));
    }

    public function test_shared_select_button_and_alert_components_render_across_admin_and_shop(): void
    {
        $staffEdit = $this->actingAs($this->admin)->get("/admin/staff/{$this->salesStaff->id}/edit");
        $staffEdit->assertOk()
            ->assertSee('data-ui-select', false)
            ->assertSee('ui-select__trigger', false)
            ->assertSee('btn-modal-save', false)
            ->assertSee('btn-modal-cancel', false);

        $staffCreate = $this->actingAs($this->admin)->get('/admin/staff/create');
        $staffCreate->assertOk()
            ->assertSee('data-ui-select', false)
            ->assertSee('btn-modal-save', false);

        $productCreate = $this->actingAs($this->admin)->get('/admin/products/create');
        $productCreate->assertOk()
            ->assertSee('data-ui-select', false)
            ->assertSee('btn-modal-save', false);

        $reviewsPage = $this->actingAs($this->admin)->get('/admin/reviews');
        $reviewsPage->assertOk()
            ->assertSee('data-ui-select', false)
            ->assertSee('data-searchable="true"', false);

        $profilePage = $this->actingAs($this->admin)
            ->withSession(['success' => 'Đã cập nhật hồ sơ.'])
            ->get('/admin/profile');
        $profilePage->assertOk()
            ->assertSee('data-ui-alert', false)
            ->assertSee('data-auto-dismiss="4000"', false)
            ->assertSee('btn-modal-save', false)
            ->assertSee('btn-modal-cancel', false);

        $shopCatalog = $this->get('/products');
        $shopCatalog->assertOk()
            ->assertSee('data-ui-select', false)
            ->assertSee('ui-select--shop', false);
    }

    public function test_admin_product_show_trims_description_and_preserve_guide_without_leading_blank_line(): void
    {
        $response = $this->actingAs($this->admin)->get("/admin/products/{$this->inStockProduct->id}");
        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString(
            'white-space: pre-line; background: #fff8f6; padding: 14px 16px; border-radius: 10px; border: 1px solid #f2e2dc;">Mô tả có khoảng trắng thừa ở đầu và cuối.</div>',
            $content
        );
        $this->assertStringContainsString(
            'white-space: pre-line; background: #fff8f6; padding: 14px 16px; border-radius: 10px; border: 1px solid #f2e2dc;">Lau nhẹ bằng khăn ẩm.</div>',
            $content
        );
    }

    public function test_admin_filter_bars_and_confirm_modals_render_without_native_confirm(): void
    {
        $pendingOrder = Order::create([
            'code'             => 'ORD-UI-FIX2',
            'user_id'          => $this->customer->id,
            'shipping_name'    => 'Khách UI Test',
            'shipping_phone'   => '0909888777',
            'shipping_address' => '123 Nguyễn Trãi, Hà Nội',
            'total_amount'     => 250000,
            'payment_method'   => 'cod',
            'status'           => 'pending',
        ]);

        foreach (['/admin/customers', '/admin/staff', '/admin/orders', '/admin/reviews'] as $path) {
            $page = $this->actingAs($this->admin)->get($path);
            $page->assertOk()
                ->assertSee('product-filter-bar is-inline', false)
                ->assertSee('id="adminConfirmModal"', false)
                ->assertDontSee('return confirm(', false);
        }

        $customersPage = $this->actingAs($this->admin)->get('/admin/customers');
        $customersPage->assertOk()
            ->assertSee('admin-toolbar-search', false)
            ->assertSee('data-confirm', false);

        $ordersPage = $this->actingAs($this->admin)->get('/admin/orders');
        $ordersPage->assertOk()
            ->assertSee('admin-toolbar-search', false)
            ->assertSee('data-confirm-variant="primary"', false);

        $orderDetailPage = $this->actingAs($this->admin)->get("/admin/orders/{$pendingOrder->id}");
        $orderDetailPage->assertOk()
            ->assertSee('id="adminConfirmModal"', false)
            ->assertSee('data-confirm', false)
            ->assertSee('data-confirm-variant="primary"', false)
            ->assertDontSee('return confirm(', false);

        $categoriesPage = $this->actingAs($this->admin)->get('/admin/categories');
        $categoriesPage->assertOk()
            ->assertSee('id="adminConfirmModal"', false)
            ->assertSee('data-confirm', false)
            ->assertDontSee('return confirm(', false);
    }

    public function test_payos_logo_png_exists_and_is_square_512x512(): void
    {
        $logoPath = public_path('assets/images/payos-logo.png');
        $this->assertFileExists($logoPath);

        $dimensions = getimagesize($logoPath);
        $this->assertNotFalse($dimensions);
        $this->assertSame(512, $dimensions[0]);
        $this->assertSame(512, $dimensions[1]);
        $this->assertSame(IMAGETYPE_PNG, $dimensions[2]);
    }
}
