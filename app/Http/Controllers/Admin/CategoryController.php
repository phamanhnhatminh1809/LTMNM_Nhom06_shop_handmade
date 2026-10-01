<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Hiển thị danh sách loại sản phẩm.
     */
    public function index(): View
    {
        $categories = Category::withCount('products')->latest('id')->get();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Hiển thị form tạo loại sản phẩm mới.
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * Lưu loại sản phẩm mới vào CSDL.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'icon' => 'nullable|string|max:50',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Đảm bảo slug không bị trùng
        $slugBase = $validated['slug'];
        $count = 1;
        while (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $slugBase . '-' . $count;
            $count++;
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Thêm loại sản phẩm "' . $validated['name'] . '" thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa loại sản phẩm.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Cập nhật loại sản phẩm.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'icon' => 'nullable|string|max:50',
        ]);

        // Tự động cập nhật slug khi tên thay đổi
        if ($category->name !== $validated['name']) {
            $slugBase = Str::slug($validated['name']);
            $slug = $slugBase;
            $count = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $slugBase . '-' . $count;
                $count++;
            }
            $validated['slug'] = $slug;
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Cập nhật loại sản phẩm "' . $category->name . '" thành công!');
    }

    /**
     * Xóa loại sản phẩm.
     * Chỉ xóa được nếu không còn sản phẩm thuộc danh mục này.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Không thể xóa! Danh mục "' . $category->name . '" đang có sản phẩm thuộc về.');
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Đã xóa loại sản phẩm "' . $name . '" thành công!');
    }
}
