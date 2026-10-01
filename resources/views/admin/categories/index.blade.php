@extends('layouts.admin')

@section('title', 'Quản lý loại sản phẩm')

@php $activeMenu = 'categories'; @endphp

@section('content')
{{--
    ================================================================
    [BACKEND ĐÃ CÓ] — Dành cho Frontend
    ================================================================
    Controller : App\Http\Controllers\Admin\CategoryController@index
    Route      : GET /admin/categories  (admin.categories.index)

    Dữ liệu truyền vào view:
      - $categories : Collection<Category>
          Mỗi Category có: id, name, slug, icon, products_count, created_at
          (products_count = số sản phẩm thuộc danh mục, dùng withCount)

    Flash messages:
      - session('success') : string — thông báo thành công
      - session('error')   : string — thông báo lỗi (vd: xóa danh mục có sản phẩm)

    Các action khác có sẵn:
      - Tạo mới : GET  /admin/categories/create  → admin.categories.create
      - Sửa     : GET  /admin/categories/{id}/edit → admin.categories.edit
      - Xóa     : DELETE /admin/categories/{id}   → admin.categories.destroy
                  (Lưu ý: cần form với method spoofing _method=DELETE)
    ================================================================
--}}

{{-- ===== KHU VỰC BACKEND: DANH SÁCH LOẠI SẢN PHẨM ===== --}}
<div style="border: 2px dashed #3b82f6; padding: 16px; margin: 16px;">
    <p><strong>[BACKEND DEMO]</strong> Trang quản lý loại sản phẩm — Frontend thay thế phần này bằng UI hoàn chỉnh.</p>

    {{-- Flash message --}}
    @if(session('success'))
        <p style="color: green;">✅ {{ session('success') }}</p>
    @endif
    @if(session('error'))
        <p style="color: red;">❌ {{ session('error') }}</p>
    @endif

    <h2>Danh sách loại sản phẩm ({{ $categories->count() }})</h2>

    <a href="{{ route('admin.categories.create') }}">+ Thêm loại sản phẩm mới</a>

    <table border="1" cellpadding="8" style="margin-top: 12px; width: 100%;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Icon</th>
                <th>Tên danh mục</th>
                <th>Slug</th>
                <th>Số sản phẩm</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>{{ $category->icon ?? '—' }}</td>
                    <td>{{ $category->name }}</td>
                    <td><code>{{ $category->slug }}</code></td>
                    <td>{{ $category->products_count }} sản phẩm</td>
                    <td>{{ $category->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('admin.categories.edit', $category) }}">Sửa</a>
                        |
                        {{-- Xóa cần form để gửi DELETE --}}
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                              style="display:inline;"
                              onsubmit="return confirm('Xác nhận xóa danh mục: {{ $category->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">Chưa có loại sản phẩm nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
{{-- ===== KẾT THÚC KHU VỰC BACKEND ===== --}}

@endsection
