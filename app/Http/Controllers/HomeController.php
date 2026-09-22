<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::all();
        $featured = Product::featured()->with('category')->take(8)->get();
        $newest = Product::latest()->with('category')->take(8)->get();

        $banners = [
            [
                'image' => 'https://images.unsplash.com/photo-1591561954557-26941169b49e?w=1600&h=800&fit=crop',
                'title' => 'Đồ thủ công len xinh',
                'subtitle' => 'Mỗi sản phẩm là một tác phẩm nhỏ được đan tay tỉ mỉ',
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
                'image' => 'https://images.unsplash.com/photo-1615486364267-3f7a0e9c62e8?w=1600&h=800&fit=crop',
                'title' => 'Móc khóa dễ thương',
                'subtitle' => 'Làm quà tặng bạn bè, người thân cực xinh',
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
}
