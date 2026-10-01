@extends('layouts.admin')

@section('title', 'Sửa loại sản phẩm')

@php $activeMenu = 'categories'; @endphp

@section('content')
{{--
    ================================================================
    [BACKEND ĐÃ CÓ] — Dành cho Frontend
    ================================================================
    Controller : App\Http\Controllers\Admin\CategoryController@edit + update
    Route form : PUT/PATCH /admin/categories/{category}  (admin.categories.update)
                 Cần @method('PUT') trong form.

    Dữ liệu truyền vào view:
      - $category : Category — object cần sửa
          Có: id, name, slug, icon, created_at

    Validation (update):
      - name : required, string, max:100, unique (bỏ qua id hiện tại)
      - icon : nullable, string, max:50

    Nếu tên thay đổi → slug tự động cập nhật.
    Sau khi lưu → redirect về admin.categories.index + flash success.
    ================================================================
--}}

{{-- ===== KHU VỰC BACKEND: FORM SỬA LOẠI SẢN PHẨM ===== --}}
<div style="border: 2px dashed #f59e0b; padding: 16px; margin: 16px;">
    <p><strong>[BACKEND DEMO]</strong> Form sửa loại sản phẩm — Frontend thay thế phần này bằng UI hoàn chỉnh.</p>

    <h2>Sửa loại sản phẩm: <em>{{ $category->name }}</em></h2>
    <a href="{{ route('admin.categories.index') }}">← Quay lại danh sách</a>

    @if($errors->any())
        <div style="color: red; margin: 8px 0;">
            <strong>Lỗi:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.categories.update', $category) }}" method="POST" style="margin-top: 12px;">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 10px;">
            <label for="name">Tên danh mục <span style="color:red">*</span></label><br>
            <input type="text" id="name" name="name"
                   value="{{ old('name', $category->name) }}"
                   style="width: 300px;">
            <small>Slug hiện tại: <code>{{ $category->slug }}</code> (tự cập nhật nếu tên thay đổi)</small>
        </div>

        <div style="margin-bottom: 10px;">
            <label for="icon">Icon (emoji hoặc class)</label><br>
            <input type="text" id="icon" name="icon"
                   value="{{ old('icon', $category->icon) }}"
                   style="width: 200px;">
        </div>

        <button type="submit">Cập nhật danh mục</button>
    </form>
</div>
{{-- ===== KẾT THÚC KHU VỰC BACKEND ===== --}}

@endsection
