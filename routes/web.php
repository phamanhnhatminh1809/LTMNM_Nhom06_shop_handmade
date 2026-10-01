<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. PUBLIC / CUSTOMER ROUTES
// ==========================================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::view('/about', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');

// ==========================================
// 2. AUTHENTICATION ROUTES
// ==========================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==========================================
// 3. ADMIN MANAGEMENT ROUTES
// ==========================================
Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    // Quản lý loại sản phẩm (Category) — CRUD đầy đủ
    Route::resource('categories', AdminCategoryController::class);

    // Quản lý sản phẩm — CRUD đầy đủ (có show + edit + update)
    Route::resource('products', AdminProductController::class);

    // Quản lý đơn hàng — xem danh sách, xem chi tiết, cập nhật trạng thái
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show']);
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])
        ->name('orders.updateStatus');

    // Quản lý khách hàng — xem danh sách, xem chi tiết, khóa/mở khóa
    Route::resource('customers', AdminCustomerController::class)->only(['index', 'show']);
    Route::patch('customers/{customer}/lock', [AdminCustomerController::class, 'lock'])
        ->name('customers.lock');
    Route::patch('customers/{customer}/unlock', [AdminCustomerController::class, 'unlock'])
        ->name('customers.unlock');
});


// ==========================================
// 4. BACKWARD COMPATIBILITY (?route=...)
// ==========================================
Route::get('/index.php', function () {
    $route = request('route', 'home');
    return match ($route) {
        'products'       => redirect()->route('products.index', request()->except('route')),
        'product-detail' => redirect()->route('products.show', request('id', 1)),
        'login'          => redirect()->route('login'),
        'register'       => redirect()->route('register'),
        'about'          => redirect()->route('about'),
        'contact'        => redirect()->route('contact'),
        default          => redirect()->route('home'),
    };
});
