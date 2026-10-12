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

        $query = Product::with('category');

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
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'latest':
                $query->latest();
                break;
            case 'bestseller':
                $query->orderBy('sold', 'desc');
                break;
            default:
                $query->latest('id');
                break;
        }

        $total = (clone $query)->count();
        $products = $query->paginate(12)->withQueryString();
        $categories = Category::all();
        $currentCategory = $categorySlug !== '' ? Category::where('slug', $categorySlug)->first() : null;

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
            ? Product::with(['category', 'reviews.user'])->findOrFail((int) $id)
            : Product::with(['category', 'reviews.user'])->where('slug', $id)->firstOrFail();

        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'related'));
    }
}
