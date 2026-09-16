<?php

declare(strict_types=1);

use App\Features\Products\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Product API Routes
|--------------------------------------------------------------------------
|
| Prefix    : /api (đã cấu hình trong bootstrap/app.php)
| Middleware: throttle:60,1 (đã cấu hình trong bootstrap/app.php)
| Auth      : Public — không yêu cầu đăng nhập
|
*/

Route::get('/products', [ProductController::class, 'index']);
