<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = trim($request->input('q', ''));
        $categorySlug = trim($request->input('category', ''));
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $sort = $request->input('sort', 'default');

        $query = Product::with('category')->withCount('reviews');

        if ($keyword !== '') {
            $query->search($keyword);
        }

        if ($categorySlug !== '') {
            $query->filterCategory($categorySlug);
        }

        if ($minPrice !== null && is_numeric($minPrice)) {
            $query->where('price', '>=', (float) $minPrice);
        }

        if ($maxPrice !== null && is_numeric($maxPrice)) {
            $query->where('price', '<=', (float) $maxPrice);
        }

        $minRating = $request->input('min_rating');
        if ($minRating !== null && is_numeric($minRating)) {
            $query->where('rating', '>=', (float) $minRating);
        }

        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc')->orderByDesc('id');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc')->orderByDesc('id');
                break;
            case 'latest':
                $query->orderByDesc('created_at')->orderByDesc('id');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
                break;
            case 'bestseller':
                $query->orderBy('sold', 'desc')->orderByDesc('id');
                break;
            default:
                $query->latest('id');
                break;
        }

        $products = $query->paginate(12)->withQueryString();
        $total = $products->total();
        $categories = Category::cachedAll();
        $currentCategory = $categorySlug !== '' ? $categories->firstWhere('slug', $categorySlug) : null;

        return view('products.index', compact(
            'products',
            'categories',
            'currentCategory',
            'categorySlug',
            'keyword',
            'total'
        ));
    }

    public function show(string|int $id): View
    {
        $product = is_numeric($id)
            ? Product::with(['category', 'reviews.user'])->withCount('reviews')->findOrFail((int) $id)
            : Product::with(['category', 'reviews.user'])->withCount('reviews')->where('slug', $id)->firstOrFail();

        $related = Product::withCount('reviews')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'related'));
    }
}
