@extends('layouts.admin')

@section('title', 'Chi tiết sản phẩm')

@php $activeMenu = 'products'; @endphp

@section('content')
{{--
    ================================================================
    [BACKEND ĐÃ CÓ] — Dành cho Frontend
    ================================================================
    Controller : App\Http\Controllers\Admin\ProductController@show
    Route      : GET /admin/products/{product}  (admin.products.show)

    Dữ liệu truyền vào view:
      - $product : Product — object sản phẩm (đã load quan hệ 'category')
          Có: id, code, name, slug, description, instruction_preserve,
              image, gallery (array), price, old_price, stock_quantity,
              rating, sold, is_featured, category (Category object)

    Từ đây có thể điều hướng sang:
      - Sửa  : route('admin.products.edit', $product)
      - Xóa  : form DELETE route('admin.products.destroy', $product)
      - Back  : route('admin.products.index')
    ================================================================
--}}

{{-- ===== KHU VỰC BACKEND: CHI TIẾT SẢN PHẨM ===== --}}
<div style="border: 2px dashed #3b82f6; padding: 16px; margin: 16px;">
    <p><strong>[BACKEND DEMO]</strong> Trang chi tiết sản phẩm — Frontend thay thế bằng UI hoàn chỉnh.</p>

    <h2>Chi tiết sản phẩm: {{ $product->name }}</h2>
    <a href="{{ route('admin.products.index') }}">← Quay lại danh sách</a>
    |
    <a href="{{ route('admin.products.edit', $product) }}">✏️ Sửa sản phẩm này</a>

    <table border="1" cellpadding="8" style="margin-top: 12px;">
        <tr><th>Mã SKU</th><td>{{ $product->code }}</td></tr>
        <tr><th>Tên sản phẩm</th><td>{{ $product->name }}</td></tr>
        <tr><th>Slug</th><td><code>{{ $product->slug }}</code></td></tr>
        <tr><th>Danh mục</th><td>{{ $product->category->name ?? '—' }}</td></tr>
        <tr><th>Giá bán</th><td>{{ number_format($product->price, 0, ',', '.') }}đ</td></tr>
        <tr><th>Giá cũ</th><td>{{ $product->old_price ? number_format($product->old_price, 0, ',', '.') . 'đ' : '—' }}</td></tr>
        <tr><th>Tồn kho</th><td>{{ $product->stock_quantity }}</td></tr>
        <tr><th>Đã bán</th><td>{{ $product->sold }}</td></tr>
        <tr><th>Đánh giá</th><td>⭐ {{ $product->rating }}</td></tr>
        <tr><th>Nổi bật</th><td>{{ $product->is_featured ? '✅ Có' : '❌ Không' }}</td></tr>
        <tr><th>Mô tả</th><td>{{ $product->description ?? '—' }}</td></tr>
        <tr><th>Hướng dẫn bảo quản</th><td>{{ $product->instruction_preserve ?? '—' }}</td></tr>
        <tr><th>Hình ảnh chính</th><td>
            @if($product->image)
                <img src="{{ asset(ltrim($product->image, '/')) }}" alt="{{ $product->name }}" style="max-width:150px;" onerror="this.style.display='none'">
            @else
                (Chưa có ảnh)
            @endif
        </td></tr>
        <tr><th>Ngày tạo</th><td>{{ $product->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><th>Cập nhật lần cuối</th><td>{{ $product->updated_at->format('d/m/Y H:i') }}</td></tr>
    </table>
</div>
{{-- ===== KẾT THÚC KHU VỰC BACKEND ===== --}}

@endsection
