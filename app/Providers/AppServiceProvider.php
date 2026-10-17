<?php

namespace App\Providers;

use App\Models\Category;
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
        // Chia sẻ danh mục cho partials.navbar với cache để tránh lặp lại query DB trên mỗi trang
        View::composer('partials.navbar', function ($view) {
            $categories = Cache::remember('global_categories', 3600, function () {
                return Category::all();
            });
            $view->with('categories', $categories);
        });
    }
}
