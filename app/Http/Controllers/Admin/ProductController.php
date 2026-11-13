<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = trim($request->input('q', ''));
        $categorySlug = trim($request->input('category', ''));
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $stockStatus = trim($request->input('stock_status', ''));
        $sort = trim($request->input('sort', 'newest'));

        $query = Product::with('category');

        if ($keyword !== '') {
            $query->search($keyword);
        }

        if ($categorySlug !== '') {
            $query->filterCategory($categorySlug);
        }

        if ($minPrice !== null && $minPrice !== '' && is_numeric($minPrice) && (float) $minPrice >= 0) {
            $query->where('price', '>=', (float) $minPrice);
        }

        if ($maxPrice !== null && $maxPrice !== '' && is_numeric($maxPrice) && (float) $maxPrice >= 0) {
            $query->where('price', '<=', (float) $maxPrice);
        }

        match ($stockStatus) {
            'active' => $query->whereColumn('stock_quantity', '>', 'min_stock_threshold'),
            'low'    => $query->where('stock_quantity', '>', 0)
                              ->whereColumn('stock_quantity', '<=', 'min_stock_threshold'),
            'out'    => $query->where('stock_quantity', '<=', 0),
            default  => null,
        };

        match ($sort) {
            'oldest'       => $query->orderBy('created_at')->orderBy('id'),
            'best_selling' => $query->orderByDesc('sold')->orderByDesc('id'),
            'price_asc'    => $query->orderBy('price')->orderByDesc('id'),
            'price_desc'   => $query->orderByDesc('price')->orderByDesc('id'),
            default        => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        $products = $query->paginate(10)->withQueryString();
        $categories = Category::cachedAll();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Hiển thị form tạo sản phẩm mới.
     */
    public function create(): View
    {
        $categories = Category::cachedAll();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'code'                 => 'required|string|max:50|unique:products,code',
            'category_id'          => 'required|exists:categories,id',
            'price'                => 'required|numeric|min:0',
            'old_price'            => 'nullable|numeric|min:0',
            'stock_quantity'       => 'required|integer|min:0',
            'min_stock_threshold'  => 'nullable|integer|min:0|max:10000',
            'description'          => 'nullable|string',
            'instruction_preserve' => 'nullable|string',
            'is_featured'          => 'nullable|boolean',
            'image'                => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'gallery'              => 'nullable|array|max:5',
            'gallery.*'            => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);
        $validated['rating'] = 5.0;
        $validated['sold'] = 0;
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['min_stock_threshold'] = $request->filled('min_stock_threshold')
            ? (int) $validated['min_stock_threshold']
            : AdminNotificationService::DEFAULT_MIN_STOCK_THRESHOLD;

        // Xử lý upload ảnh đại diện chính
        if ($request->hasFile('image')) {
            $validated['image'] = $this->uploadProductImage($request->file('image'));
        } else {
            $validated['image'] = 'assets/images/products/tui-01.jpg'; // default placeholder
        }

        // Xử lý upload bộ sưu tập ảnh phụ (gallery)
        if ($request->hasFile('gallery')) {
            $galleryPaths = [];
            foreach ($request->file('gallery') as $file) {
                $galleryPaths[] = $this->uploadProductImage($file);
            }
            $validated['gallery'] = $galleryPaths;
        }

        $product = Product::create($validated);
        app(AdminNotificationService::class)->syncProductStockAlert($product);

        return redirect()->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    /**
     * Xem chi tiết sản phẩm (trong admin).
     */
    public function show(Product $product): View
    {
        $product->load('category');
        return view('admin.products.show', compact('product'));
    }

    /**
     * Hiển thị form chỉnh sửa sản phẩm.
     */
    public function edit(Product $product): View
    {
        $categories = Category::cachedAll();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Lưu thông tin sản phẩm đã chỉnh sửa.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'code'                 => 'required|string|max:50|unique:products,code,' . $product->id,
            'category_id'          => 'required|exists:categories,id',
            'price'                => 'required|numeric|min:0',
            'old_price'            => 'nullable|numeric|min:0',
            'stock_quantity'       => 'required|integer|min:0',
            'min_stock_threshold'  => 'nullable|integer|min:0|max:10000',
            'description'          => 'nullable|string',
            'instruction_preserve' => 'nullable|string',
            'is_featured'          => 'nullable|boolean',
            'image'                => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'gallery'              => 'nullable|array|max:5',
            'gallery.*'            => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        // Tái sinh slug nếu tên thay đổi
        if ($product->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);
        }

        $validated['is_featured'] = $request->boolean('is_featured');
        if ($request->filled('min_stock_threshold')) {
            $validated['min_stock_threshold'] = (int) $validated['min_stock_threshold'];
        } else {
            $validated['min_stock_threshold'] = $product->effectiveMinStockThreshold();
        }

        // Xử lý upload ảnh đại diện mới (nếu có chọn file mới)
        if ($request->hasFile('image')) {
            $validated['image'] = $this->uploadProductImage($request->file('image'));
        } else {
            unset($validated['image']);
        }

        // Xử lý upload bộ sưu tập ảnh mới (nếu có chọn file mới)
        if ($request->hasFile('gallery')) {
            $galleryPaths = [];
            foreach ($request->file('gallery') as $file) {
                $galleryPaths[] = $this->uploadProductImage($file);
            }
            $validated['gallery'] = $galleryPaths;
        } else {
            unset($validated['gallery']);
        }

        $product->update($validated);
        app(AdminNotificationService::class)->syncProductStockAlert($product->fresh());

        return redirect()->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm "' . $product->name . '" thành công!');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $productId = $product->id;
        $product->delete();
        app(AdminNotificationService::class)->resolveAlertsForDeletedProduct($productId);

        return redirect()->route('admin.products.index')
            ->with('success', 'Đã xóa sản phẩm thành công!');
    }

    /**
     * Lưu file ảnh sản phẩm vào thư mục public/uploads/products.
     */
    private function uploadProductImage(\Illuminate\Http\UploadedFile $file): string
    {
        $uploadDir = public_path('uploads/products');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        $file->move($uploadDir, $filename);

        return 'uploads/products/' . $filename;
    }
}
