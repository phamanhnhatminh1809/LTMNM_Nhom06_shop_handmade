<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Chia sẻ danh mục đã cache cho navbar và header để tránh query Category::all() lặp lại ở mỗi trang
        View::composer(['partials.navbar', 'partials.header'], function ($view) {
            $view->with('categories', Category::cachedAll());
        });

        // Chia sẻ số lượng thông báo chưa đọc theo vai trò cho chuông Admin (không cần query ở từng controller)
        View::composer('admin.partials.notification-bell', function ($view) {
            $count = Auth::check()
                ? app(AdminNotificationService::class)->unreadCountForUser(Auth::user())
                : 0;
            $view->with('adminUnreadNotificationsCount', $count);
        });
    }
}
