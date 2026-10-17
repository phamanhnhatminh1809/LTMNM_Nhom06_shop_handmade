@extends('layouts.admin')

@section('title', 'Thêm loại sản phẩm')

@php $activeMenu = 'categories'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-tags"></i>
        <span>THÊM LOẠI SẢN PHẨM MỚI</span>
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
                <div>Vui lòng kiểm tra lại dữ liệu nhập vào:</div>
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
    <!-- Cột trái: Form nhập liệu -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">
                <i class="bi bi-plus-circle"></i>
                <span>Thông tin loại sản phẩm</span>
            </h3>
        </div>
        <div class="admin-card-body">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf

                <div class="modal-form-group" style="margin-bottom: 18px;">
                    <label class="modal-label" for="name">
                        Tên danh mục sản phẩm <span class="required-star">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           class="modal-input"
                           value="{{ old('name') }}"
                           placeholder="Ví dụ: Gốm sứ thủ công, Đồ len móc tay, Nến thơm..."
                           required
                           autofocus>
                    @error('name')
                        <div class="form-error-text">{{ $message }}</div>
                    @enderror
                </div>

                <div class="modal-form-group" style="margin-bottom: 18px;">
                    <label class="modal-label" for="slugPreview">
                        Mã định danh (Slug tự động)
                    </label>
                    <input type="text"
                           id="slugPreview"
                           class="modal-input"
                           style="font-family: var(--font-mono); background-color: #f9f3f0; color: var(--color-primary);"
                           placeholder="gom-su-thu-cong"
                           readonly>
                    <div class="form-help-text">
                        Slug sẽ được hệ thống tự động tạo và chống trùng lặp dựa trên tên danh mục.
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
                           value="{{ old('icon') }}"
                           placeholder="VD: 🏺, 🧶, 🕯️ hoặc bi-bag, bi-flower1">
                    @error('icon')
                        <div class="form-error-text">{{ $message }}</div>
                    @enderror

                    <div class="form-help-text" style="margin-top: 8px;">Chọn nhanh biểu tượng gợi ý:</div>
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
                            <button type="button" class="icon-chip-btn" data-icon="{{ $chip['val'] }}">
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
                        <span>Lưu danh mục</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cột phải: Xem trước & Hướng dẫn -->
    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-eye"></i>
                    <span>Xem trước hiển thị</span>
                </h3>
            </div>
            <div class="admin-card-body">
                <div style="display: flex; align-items: center; gap: 14px; padding: 14px; background: #fff8f6; border: 1px solid #ebdcd7; border-radius: 12px;">
                    <div class="metric-icon-box" id="previewIconBox" style="margin: 0; width: 46px; height: 46px; font-size: 20px;">
                        <i class="bi bi-tag"></i>
                    </div>
                    <div>
                        <div id="previewNameText" style="font-size: 15px; font-weight: 800; color: var(--color-on-surface);">
                            {{ old('name', 'Tên loại sản phẩm') }}
                        </div>
                        <div style="margin-top: 4px;">
                            <span class="mono-code" id="previewSlugBadge">slug-danh-muc</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-info-circle"></i>
                    <span>Quy tắc quản lý danh mục</span>
                </h3>
            </div>
            <div class="admin-card-body" style="font-size: 13px; color: var(--color-on-surface-variant); line-height: 1.6;">
                <p style="margin-bottom: 8px;">• <strong>Tên danh mục</strong> phải là duy nhất trong toàn hệ thống (tối đa 100 ký tự).</p>
                <p style="margin-bottom: 8px;">• <strong>Slug URL</strong> được sinh tự động để phục vụ bộ lọc sản phẩm thân thiện SEO.</p>
                <p>• Danh mục đã có sản phẩm sẽ được bảo vệ, không thể xóa nhầm cho tới khi chuyển hoặc xóa hết sản phẩm bên trong.</p>
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
    const previewNameText = document.getElementById('previewNameText');
    const previewSlugBadge = document.getElementById('previewSlugBadge');
    const previewIconBox = document.getElementById('previewIconBox');
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

    function updatePreview() {
        const nameVal = nameInput.value.trim();
        const slugVal = toSlug(nameVal) || 'slug-danh-muc';
        slugPreview.value = nameVal ? slugVal : '';
        previewNameText.textContent = nameVal || 'Tên loại sản phẩm';
        previewSlugBadge.textContent = slugVal;

        const iconVal = iconInput.value.trim();
        if (!iconVal) {
            previewIconBox.innerHTML = '<i class="bi bi-tag"></i>';
        } else if (iconVal.startsWith('bi-')) {
            previewIconBox.innerHTML = `<i class="bi ${iconVal}"></i>`;
        } else {
            previewIconBox.textContent = iconVal;
        }
    }

    if (nameInput) nameInput.addEventListener('input', updatePreview);
    if (iconInput) iconInput.addEventListener('input', updatePreview);

    iconChips.forEach(btn => {
        btn.addEventListener('click', function () {
            iconChips.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            iconInput.value = this.getAttribute('data-icon');
            updatePreview();
        });
    });

    updatePreview();
});
</script>
@endpush
