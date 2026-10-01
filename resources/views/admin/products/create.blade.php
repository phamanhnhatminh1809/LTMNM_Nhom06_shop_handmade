@extends('layouts.admin')

@section('title', 'Thêm sản phẩm')

@php $activeMenu = 'products'; @endphp

@section('content')
{{--
    ================================================================
    [BACKEND ĐÃ CÓ] — Dành cho Frontend
    ================================================================
    Controller : App\Http\Controllers\Admin\ProductController@create + store
    Route form : POST /admin/products  (admin.products.store)

    Dữ liệu truyền vào view:
      - $categories : Collection<Category> — danh sách để render <select>
          Mỗi Category có: id, name, slug

    Validation (store):
      - name             : required, string, max:255
      - code             : required, string, max:50, unique (bảng products)
      - category_id      : required, phải tồn tại trong bảng categories
      - price            : required, numeric, min:0
      - old_price        : nullable, numeric, min:0
      - stock_quantity   : required, integer, min:0
      - description      : nullable, string
      - instruction_preserve : nullable, string
      - is_featured      : nullable, boolean (checkbox)

    Backend tự sinh: slug, image (placeholder), rating=5.0, sold=0
    Sau khi lưu → redirect admin.products.index + flash success
    Lỗi validation → $errors->all() / $errors->get('field')
    ================================================================
--}}

{{-- ===== KHU VỰC BACKEND: FORM THÊM SẢN PHẨM ===== --}}
<div style="border: 2px dashed #3b82f6; padding: 16px; margin: 16px;">
    <p><strong>[BACKEND DEMO]</strong> Form thêm sản phẩm — Frontend thay thế bằng UI modal/page hoàn chỉnh.</p>

    <h2>Thêm sản phẩm mới</h2>
    <a href="{{ route('admin.products.index') }}">← Quay lại danh sách</a>

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

    <form action="{{ route('admin.products.store') }}" method="POST" style="margin-top: 12px;">
        @csrf

        <div style="margin-bottom: 8px;">
            <label>Tên sản phẩm <span style="color:red">*</span></label><br>
            <input type="text" name="name" value="{{ old('name') }}" style="width:350px;">
        </div>

        <div style="margin-bottom: 8px;">
            <label>Mã SKU <span style="color:red">*</span></label><br>
            <input type="text" name="code" value="{{ old('code') }}" placeholder="VD: SP-001" style="width:200px;">
        </div>

        <div style="margin-bottom: 8px;">
            <label>Danh mục <span style="color:red">*</span></label><br>
            <select name="category_id" style="width:200px;">
                <option value="">-- Chọn danh mục --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 8px;">
            <label>Giá bán (VNĐ) <span style="color:red">*</span></label><br>
            <input type="number" name="price" value="{{ old('price') }}" min="0" step="1000" style="width:150px;">
        </div>

        <div style="margin-bottom: 8px;">
            <label>Giá cũ (VNĐ)</label><br>
            <input type="number" name="old_price" value="{{ old('old_price') }}" min="0" step="1000" style="width:150px;">
        </div>

        <div style="margin-bottom: 8px;">
            <label>Tồn kho <span style="color:red">*</span></label><br>
            <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" style="width:100px;">
        </div>

        <div style="margin-bottom: 8px;">
            <label>Mô tả sản phẩm</label><br>
            <textarea name="description" rows="4" style="width:400px;">{{ old('description') }}</textarea>
        </div>

        <div style="margin-bottom: 8px;">
            <label>Hướng dẫn sử dụng & bảo quản</label><br>
            <textarea name="instruction_preserve" rows="3" style="width:400px;">{{ old('instruction_preserve') }}</textarea>
        </div>

        <div style="margin-bottom: 12px;">
            <label>
                <input type="hidden" name="is_featured" value="0">
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                Sản phẩm nổi bật
            </label>
        </div>

        <button type="submit">💾 Lưu sản phẩm</button>
    </form>
</div>
{{-- ===== KẾT THÚC KHU VỰC BACKEND ===== --}}

@endsection
