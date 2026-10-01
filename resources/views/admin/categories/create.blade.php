@extends('layouts.admin')

@section('title', 'Thêm loại sản phẩm')

@php $activeMenu = 'categories'; @endphp

@section('content')
{{--
    ================================================================
    [BACKEND ĐÃ CÓ] — Dành cho Frontend
    ================================================================
    Controller : App\Http\Controllers\Admin\CategoryController@create + store
    Route form : POST /admin/categories  (admin.categories.store)

    Validation (store):
      - name  : required, string, max:100, unique (bảng categories)
      - icon  : nullable, string, max:50 (emoji hoặc class icon)

    Slug tự động sinh từ name, xử lý trùng lặp tự động.

    Sau khi lưu thành công → redirect về admin.categories.index
                              + flash session('success')

    Lỗi validation → $errors->all() hoặc $errors->get('fieldname')
    ================================================================
--}}

{{-- ===== KHU VỰC BACKEND: FORM THÊM LOẠI SẢN PHẨM ===== --}}
<div style="border: 2px dashed #3b82f6; padding: 16px; margin: 16px;">
    <p><strong>[BACKEND DEMO]</strong> Form thêm loại sản phẩm — Frontend thay thế phần này bằng UI hoàn chỉnh.</p>

    <h2>Thêm loại sản phẩm mới</h2>
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

    <form action="{{ route('admin.categories.store') }}" method="POST" style="margin-top: 12px;">
        @csrf

        <div style="margin-bottom: 10px;">
            <label for="name">Tên danh mục <span style="color:red">*</span></label><br>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   placeholder="VD: Gốm sứ thủ công" style="width: 300px;">
            {{-- Slug sẽ tự động sinh từ name trong backend --}}
        </div>

        <div style="margin-bottom: 10px;">
            <label for="icon">Icon (emoji hoặc class)</label><br>
            <input type="text" id="icon" name="icon" value="{{ old('icon') }}"
                   placeholder="VD: 🎨 hoặc bi-bag" style="width: 200px;">
        </div>

        <button type="submit">Lưu danh mục</button>
    </form>
</div>
{{-- ===== KẾT THÚC KHU VỰC BACKEND ===== --}}

@endsection
