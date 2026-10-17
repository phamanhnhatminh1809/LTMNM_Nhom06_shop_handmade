@extends('layouts.admin')

@section('title', 'Sửa loại sản phẩm — ' . $category->name)

@php $activeMenu = 'categories'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-pencil-square"></i>
        <span>CHỈNH SỬA LOẠI SẢN PHẨM</span>
    </div>
    <div class="top-bar-right">
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary-outline">
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại danh sách</span>
        </a>
    </div>
</div>

@if($errors->any())
    <div class="admin-alert error">
        <div class="admin-alert-content">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <div>
                <div>Vui lòng kiểm tra lại dữ liệu cập nhật:</div>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<div class="admin-form-grid">
    <!-- Cột trái: Form cập nhật -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="bi bi-tag"></i>
                <span>Cập nhật danh mục: {{ $category->name }}</span>
            </h3>
            <span class="mono-code">#{{ $category->id }}</span>
        </div>
        <div class="admin-card-body">
            <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-form-group" style="margin-bottom: 18px;">
                    <label class="modal-label" for="name">
                        Tên danh mục sản phẩm <span class="required-star">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           class="modal-input"
                           value="{{ old('name', $category->name) }}"
                           required>
                    @error('name')
                        <div class="form-error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="modal-form-group" style="margin-bottom: 18px;">
                    <label class="modal-label" for="slugPreview">
                        Slug hiện tại / Dự kiến cập nhật
                    </label>
                    <input type="text"
                           id="slugPreview"
                           class="modal-input"
                           style="font-family: var(--font-mono); background-color: #f9f3f0; color: var(--color-primary);"
                           value="{{ $category->slug }}"
                           readonly>
                    <div class="form-help-text">
                        Nếu bạn đổi tên danh mục, hệ thống sẽ tự động cập nhật lại mã slug tương ứng.
                    </div>
                </div>

                <div class="modal-form-group" style="margin-bottom: 18px;">
                    <label class="modal-label" for="icon">
                        Biểu tượng (Emoji hoặc mã Bootstrap Icon)
                    </label>
                    <input type="text"
                           id="icon"
                           name="icon"
                           class="modal-input"
                           value="{{ old('icon', $category->icon) }}"
                           placeholder="VD: 🏺, 🧶, 🕯️ hoặc bi-bag">
                    @error('icon')
                        <div class="form-error-text">{{ $message }}</div>
                    @enderror

                    <div class="form-help-text" style="margin-top: 8px;">Chọn nhanh biểu tượng:</div>
                    <div class="icon-chip-picker">
                        @foreach([
                            ['val' => '🏺', 'label' => '🏺 Gốm sứ'],
                            ['val' => '🧶', 'label' => '🧶 Đồ len'],
                            ['val' => '🕯️', 'label' => '🕯️ Nến thơm'],
                            ['val' => '👜', 'label' => '👜 Túi & Phụ kiện'],
                            ['val' => '💐', 'label' => '💐 Hoa thủ công'],
                            ['val' => '🌿', 'label' => '🌿 Mây tre đan'],
                            ['val' => '🎨', 'label' => '🎨 Tranh & Decor'],
                            ['val' => 'bi-bag-heart', 'label' => 'bi-bag-heart'],
                            ['val' => 'bi-flower1', 'label' => 'bi-flower1'],
                        ] as $chip)
                            <button type="button"
                                    class="icon-chip-btn {{ old('icon', $category->icon) === $chip['val'] ? 'active' : '' }}"
                                    data-icon="{{ $chip['val'] }}">
                                {{ $chip['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="admin-form-actions">
                    <a href="{{ route('admin.categories.index') }}" class="btn-modal-cancel" style="text-decoration: none;">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="btn-modal-save">
                        <i class="bi bi-floppy"></i>
                        <span>Cập nhật danh mục</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cột phải: Thông tin chi tiết danh mục -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-info-circle"></i>
                    <span>Thông tin danh mục</span>
                </h3>
            </div>
            <div class="admin-card-body">
                <div class="info-kv-list">
                    <div class="info-kv-row">
                        <span class="info-kv-label">Mã ID</span>
                        <span class="info-kv-value"><span class="mono-code">#{{ $category->id }}</span></span>
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">Slug gốc</span>
                        <span class="info-kv-value"><span class="mono-code">{{ $category->slug }}</span></span>
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">Ngày tạo</span>
                        <span class="info-kv-value">{{ $category->created_at ? $category->created_at->format('d/m/Y H:i') : '—' }}</span>
                    </div>
                    <div class="info-kv-row">
                        <span class="info-kv-label">Cập nhật gần nhất</span>
                        <span class="info-kv-value">{{ $category->updated_at ? $category->updated_at->format('d/m/Y H:i') : '—' }}</span>
                    </div>
                </div>

                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #f4eae6;">
                    <a href="{{ route('admin.products.index', ['category' => $category->slug]) }}"
                       class="btn-secondary-outline"
                       style="width: 100%; justify-content: center;">
                        <i class="bi bi-box-seam"></i>
                        <span>Xem sản phẩm thuộc danh mục này</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const nameInput = document.getElementById('name');
    const slugPreview = document.getElementById('slugPreview');
    const iconInput = document.getElementById('icon');
    const iconChips = document.querySelectorAll('.icon-chip-btn');

    function toSlug(str) {
        return str
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[đĐ]/g, 'd')
            .replace(/[^0-9a-z-\s]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    if (nameInput && slugPreview) {
        nameInput.addEventListener('input', function () {
            slugPreview.value = toSlug(this.value.trim());
        });
    }

    iconChips.forEach(btn => {
        btn.addEventListener('click', function () {
            iconChips.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            iconInput.value = this.getAttribute('data-icon');
        });
    });
});
</script>
@endpush
