<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::cachedAll();
        $featured = Product::featured()->with('category')->withCount('reviews')->take(8)->get();
        $newest = Product::latest()->with('category')->withCount('reviews')->take(8)->get();

        $banners = [
            [
                'image' => 'https://images.unsplash.com/photo-1591561954557-26941169b49e?w=1600&h=800&fit=crop',
                'title' => 'Đồ thủ công đan len',
                'subtitle' => 'Sản phẩm thủ công được đan tay truyền thống',
                'cta' => 'Khám phá ngay',
                'link' => route('products.index'),
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?w=1600&h=800&fit=crop',
                'title' => 'Khăn len mùa đông',
                'subtitle' => 'Giữ ấm cho bạn trong những ngày se lạnh',
                'cta' => 'Xem khăn len',
                'link' => route('products.index', ['category' => 'khan-len']),
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=1600&h=800&fit=crop',
                'title' => 'Móc khóa dễ thương',
                'subtitle' => 'Phù hợp làm quà tặng bạn bè và người thân',
                'cta' => 'Xem móc khóa',
                'link' => route('products.index', ['category' => 'moc-khoa']),
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1562040506-a9b32cb51b94?w=1600&h=800&fit=crop',
                'title' => 'Gấu bông handmade',
                'subtitle' => 'Mềm mịn, an toàn, đáng yêu cho bé',
                'cta' => 'Xem gấu bông',
                'link' => route('products.index', ['category' => 'gau-bong']),
            ],
        ];

        return view('home', compact('categories', 'featured', 'newest', 'banners'));
    }

    /**
     * Điều hướng tương thích ngược cho ?route=... (không dùng Closure để bật route:cache).
     */
    public function legacyRedirect(Request $request): RedirectResponse
    {
        $route = $request->input('route', 'home');

        return match ($route) {
            'products'       => redirect()->route('products.index', $request->except('route')),
            'product-detail' => redirect()->route('products.show', $request->input('id', 1)),
            'login'          => redirect()->route('login'),
            'register'       => redirect()->route('register'),
            'about'          => redirect()->route('about'),
            'contact'        => redirect()->route('contact'),
            default          => redirect()->route('home'),
        };
    }
}
