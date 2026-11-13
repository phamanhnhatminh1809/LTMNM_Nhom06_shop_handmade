<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
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

// Đặt hàng & Thanh toán (Checkout COD / PayOS QR)
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/payos/return', [CheckoutController::class, 'payosReturn'])->name('checkout.payos.return');
Route::get('/checkout/payos/cancel', [CheckoutController::class, 'payosCancel'])->name('checkout.payos.cancel');
Route::post('/checkout/payos/webhook', [CheckoutController::class, 'payosWebhook'])->name('checkout.payos.webhook');
Route::post('/checkout/payos/{order}/verify', [CheckoutController::class, 'payosVerify'])->name('checkout.payos.verify');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
// ==========================================
// 2. AUTHENTICATION & PASSWORD RESET (OTP)
// ==========================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register/send-otp', [AuthController::class, 'sendRegisterOtp'])
    ->middleware('throttle:10,1')
    ->name('register.sendOtp');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Quên mật khẩu qua OTP email
Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])
    ->middleware('throttle:10,1')
    ->name('password.sendOtp');
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])
    ->middleware('throttle:10,1')
    ->name('password.verifyOtp');
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])
    ->middleware('throttle:10,1')
    ->name('password.resetWithOtp');

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
    Route::post('/orders/{order}/reorder', [OrderController::class, 'reorder'])->name('orders.reorder');
    // Đánh giá sản phẩm sau khi đơn hàng hoàn thành
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});

// ==========================================
// 4. ADMIN MANAGEMENT ROUTES (CÓ PHÂN QUYỀN ROLE)
// ==========================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard (Admin, Nhân viên kho, Nhân viên bán hàng đều vào được khu vực quản trị)
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    // Hồ sơ cá nhân của Quản trị viên và Nhân viên (đổi tên, email, số điện thoại, mật khẩu)
    Route::get('profile', [AdminProfileController::class, 'edit'])->name('profile');
    Route::put('profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [AdminProfileController::class, 'changePassword'])->name('profile.password');

    // Hệ thống thông báo nội bộ Admin & Nhân viên (lọc theo quyền trong NotificationController)
    Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [AdminNotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // --- PHÂN HỆ KHO: Chỉ Admin & Nhân viên kho (warehouse_staff) ---
    Route::middleware('admin:admin,warehouse_staff')->group(function () {
        // Quản lý loại sản phẩm (Category) — CRUD đầy đủ
        Route::resource('categories', AdminCategoryController::class);

        // Quản lý sản phẩm — CRUD đầy đủ + Upload ảnh
        Route::resource('products', AdminProductController::class);
    });

    // --- PHÂN HỆ BÁN HÀNG: Chỉ Admin & Nhân viên bán hàng (sales_staff) ---
    Route::middleware('admin:admin,sales_staff')->group(function () {
        // Quản lý đơn hàng — xem danh sách, xem chi tiết, cập nhật trạng thái
        Route::resource('orders', AdminOrderController::class)->only(['index', 'show']);
        Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])
            ->name('orders.updateStatus');

        // Quản lý đánh giá sản phẩm — xem danh sách, lọc theo sản phẩm/số sao, xóa đánh giá
        Route::resource('reviews', AdminReviewController::class)->only(['index', 'destroy']);
    });

    // --- PHÂN HỆ TOÀN QUYỀN: Chỉ Admin (admin) ---
    Route::middleware('admin:admin')->group(function () {
        // Quản lý khách hàng — xem danh sách, xem chi tiết, khóa/mở khóa
        Route::resource('customers', AdminCustomerController::class)->only(['index', 'show']);
        Route::patch('customers/{customer}/lock', [AdminCustomerController::class, 'lock'])
            ->name('customers.lock');
        Route::patch('customers/{customer}/unlock', [AdminCustomerController::class, 'unlock'])
            ->name('customers.unlock');

        // Quản lý nhân viên (Admin, Nhân viên kho, Nhân viên bán hàng)
        Route::resource('staff', AdminStaffController::class)
            ->parameters(['staff' => 'user'])
            ->except(['show']);
        Route::patch('staff/{user}/role', [AdminStaffController::class, 'updateRole'])
            ->name('staff.updateRole');
        Route::patch('staff/{user}/status', [AdminStaffController::class, 'toggleStatus'])
            ->name('staff.toggleStatus');
    });
});

// ==========================================
// 5. BACKWARD COMPATIBILITY (?route=...)
// ==========================================
Route::get('/index.php', [HomeController::class, 'legacyRedirect']);
