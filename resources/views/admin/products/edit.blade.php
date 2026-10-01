@extends('layouts.admin')

@section('title', 'Sửa sản phẩm')

@php $activeMenu = 'products'; @endphp

@section('content')
{{--
    ================================================================
    [BACKEND ĐÃ CÓ] — Dành cho Frontend
    ================================================================
    Controller : App\Http\Controllers\Admin\ProductController@edit + update
    Route form : PUT/PATCH /admin/products/{product}  (admin.products.update)
                 Cần @method('PUT') trong form.

    Dữ liệu truyền vào view:
      - $product    : Product — sản phẩm đang sửa (đã load 'category')
      - $categories : Collection<Category> — danh sách danh mục

    Validation (update): giống store, nhưng unique code bỏ qua id hiện tại.
    Slug tự cập nhật nếu name thay đổi.
    Sau khi lưu → redirect admin.products.index + flash success
    ================================================================
--}}

{{-- ===== KHU VỰC BACKEND: FORM SỬA SẢN PHẨM ===== --}}
<div style="border: 2px dashed #f59e0b; padding: 16px; margin: 16px;">
    <p><strong>[BACKEND DEMO]</strong> Form sửa sản phẩm — Frontend thay thế bằng UI modal/page hoàn chỉnh.</p>

    <h2>Sửa sản phẩm: <em>{{ $product->name }}</em></h2>
    <a href="{{ route('admin.products.index') }}">← Quay lại danh sách</a>
    |
    <a href="{{ route('admin.products.show', $product) }}">Xem chi tiết</a>

    @if($errors->any())
        <div style="color: red; margin: 8px 0;">
            <strong>Lỗi validation:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.update', $product) }}" method="POST" style="margin-top: 12px;">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 8px;">
            <label>Tên sản phẩm <span style="color:red">*</span></label><br>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" style="width:350px;">
        </div>

        <div style="margin-bottom: 8px;">
            <label>Mã SKU <span style="color:red">*</span></label><br>
            <input type="text" name="code" value="{{ old('code', $product->code) }}" style="width:200px;">
        </div>

        <div style="margin-bottom: 8px;">
            <label>Danh mục <span style="color:red">*</span></label><br>
            <select name="category_id" style="width:200px;">
                <option value="">-- Chọn danh mục --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}"
                        {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 8px;">
            <label>Giá bán (VNĐ) <span style="color:red">*</span></label><br>
            <input type="number" name="price" value="{{ old('price', $product->price) }}" min="0" step="1000" style="width:150px;">
        </div>

        <div style="margin-bottom: 8px;">
            <label>Giá cũ (VNĐ)</label><br>
            <input type="number" name="old_price" value="{{ old('old_price', $product->old_price) }}" min="0" step="1000" style="width:150px;">
        </div>

        <div style="margin-bottom: 8px;">
            <label>Tồn kho <span style="color:red">*</span></label><br>
            <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0" style="width:100px;">
        </div>

        <div style="margin-bottom: 8px;">
            <label>Mô tả sản phẩm</label><br>
            <textarea name="description" rows="4" style="width:400px;">{{ old('description', $product->description) }}</textarea>
        </div>

        <div style="margin-bottom: 8px;">
            <label>Hướng dẫn sử dụng & bảo quản</label><br>
            <textarea name="instruction_preserve" rows="3" style="width:400px;">{{ old('instruction_preserve', $product->instruction_preserve) }}</textarea>
        </div>

        <div style="margin-bottom: 12px;">
            <label>
                <input type="hidden" name="is_featured" value="0">
                <input type="checkbox" name="is_featured" value="1"
                    {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                Sản phẩm nổi bật
            </label>
        </div>

        <button type="submit">💾 Cập nhật sản phẩm</button>
    </form>
</div>
{{-- ===== KẾT THÚC KHU VỰC BACKEND ===== --}}

@endsection
