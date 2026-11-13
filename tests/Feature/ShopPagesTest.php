<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['id' => 1, 'name' => 'admin']);
        $roleCustomer = Role::create(['id' => 2, 'name' => 'customer']);

        User::create([
            'role_id' => $roleAdmin->id,
            'username' => 'admin',
            'email' => 'admin@shophandmade.com',
            'password' => bcrypt('password123'),
            'full_name' => 'Admin User',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Đồ len',
            'slug' => 'do-len',
            'icon' => '🧶',
        ]);

        Product::create([
            'code' => 'SP001',
            'category_id' => $category->id,
            'name' => 'Khăn len handmade',
            'slug' => 'khan-len-handmade',
            'price' => 150000,
            'stock_quantity' => 10,
            'image' => 'images/products/tui-01.jpg',
            'is_featured' => true,
        ]);
    }

    public function test_homepage_is_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Handmade');
        $response->assertSee('Khăn len handmade');
    }

    public function test_products_page_is_accessible(): void
    {
        $response = $this->get('/products');
        $response->assertStatus(200);
        $response->assertSee('Danh sách sản phẩm');
    }

    public function test_product_detail_page_is_accessible(): void
    {
        $product = Product::first();
        $response = $this->get('/products/' . $product->id);
        $response->assertStatus(200);
        $response->assertSee($product->name);
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Đăng nhập');
    }

    public function test_register_page_is_accessible(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Tạo tài khoản');
    }

    public function test_admin_dashboard_is_accessible(): void
    {
        $admin = User::where('username', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Tổng quan hệ thống');
    }

    public function test_admin_products_is_accessible(): void
    {
        $admin = User::where('username', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/products');
        $response->assertStatus(200);
        $response->assertSee('Quản lý sản phẩm');
    }

    public function test_legacy_route_redirects(): void
    {
        $response = $this->get('/index.php?route=products');
        $response->assertRedirect('/products');
    }

    public function test_about_page_is_accessible(): void
    {
        $response = $this->get('/about');
        $response->assertStatus(200);
        $response->assertSee('Giới thiệu về Shop Handmade');
        $response->assertSee(route('contact'));
    }

    public function test_contact_page_is_accessible(): void
    {
        $response = $this->get('/contact');
        $response->assertStatus(200);
        $response->assertSee('Liên hệ với chúng tôi');
    }
}
