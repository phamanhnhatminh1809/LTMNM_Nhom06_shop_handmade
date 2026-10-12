<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. PUBLIC / CUSTOMER ROUTES
// ==========================================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::view('/about', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');

// Giỏ hàng (Cart)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::put('/cart/{cartDetail}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{cartDetail}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Đặt hàng & Thanh toán (Checkout COD)
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

// ==========================================
// 2. AUTHENTICATION & PASSWORD RESET (OTP)
// ==========================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Quên mật khẩu qua OTP email
Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->name('password.sendOtp');
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verifyOtp');
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.resetWithOtp');

// ==========================================
// 3. USER PROFILE, ORDERS & AUTH ACTIONS
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');

    // Theo dõi & Quản lý đơn hàng khách hàng
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Đánh giá sản phẩm sau khi đơn hàng hoàn thành
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

// ==========================================
// 3. ADMIN MANAGEMENT ROUTES
// ==========================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
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
