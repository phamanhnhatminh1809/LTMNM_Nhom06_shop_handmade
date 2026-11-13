@extends('layouts.admin')

@section('title', 'Sửa danh mục — ' . $category->name)

@php $activeMenu = 'categories'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-pencil-square"></i>
        <span>CHỈNH SỬA DANH MỤC</span>
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
                        Biểu tượng danh mục (Mã Bootstrap Icon)
                    </label>
                    <input type="text"
                           id="icon"
                           name="icon"
                           class="modal-input"
                           value="{{ old('icon', $category->display_icon) }}"
                           placeholder="VD: bi-bag-heart, bi-gem, bi-flower1">
                    @error('icon')
                        <div class="form-error-text">{{ $message }}</div>
                    @enderror

                    <div class="form-help-text" style="margin-top: 10px; font-weight: 700; color: var(--color-on-surface);">
                        Chọn nhanh biểu tượng chuẩn hóa theo ngành hàng:
                    </div>
                    <div class="icon-chip-picker" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;">
                        @foreach([
                            // Túi xách & Balo
                            ['val' => 'bi-bag-heart', 'label' => 'Túi xách'],
                            ['val' => 'bi-handbag', 'label' => 'Túi xách tay'],
                            ['val' => 'bi-backpack', 'label' => 'Balo'],
                            ['val' => 'bi-wallet2', 'label' => 'Ví mini'],
                            // Móc khóa & Phụ kiện
                            ['val' => 'bi-key', 'label' => 'Móc khóa'],
                            ['val' => 'bi-tag', 'label' => 'Thẻ tag'],
                            ['val' => 'bi-bookmark-star', 'label' => 'Bookmark'],
                            ['val' => 'bi-pin-angle', 'label' => 'Ghim cài'],
                            // Trang sức & Đá quý
                            ['val' => 'bi-gem', 'label' => 'Trang sức'],
                            ['val' => 'bi-stars', 'label' => 'Lấp lánh'],
                            ['val' => 'bi-sparkles', 'label' => 'Phụ kiện'],
                            // Đồ len & Đan móc
                            ['val' => 'bi-snow', 'label' => 'Khăn len'],
                            ['val' => 'bi-palette', 'label' => 'Màu sắc'],
                            ['val' => 'bi-brush', 'label' => 'Thủ công'],
                            // Gấu bông & Đồ chơi
                            ['val' => 'bi-heart', 'label' => 'Gấu bông'],
                            ['val' => 'bi-balloon', 'label' => 'Bong bóng'],
                            ['val' => 'bi-emoji-smile', 'label' => 'Quà tặng'],
                            // Hoa & Cây cảnh
                            ['val' => 'bi-flower1', 'label' => 'Hoa len'],
                            ['val' => 'bi-flower2', 'label' => 'Bó hoa'],
                            ['val' => 'bi-tree', 'label' => 'Mây tre'],
                            ['val' => 'bi-sun', 'label' => 'Decor'],
                            // Nến thơm & Tinh dầu
                            ['val' => 'bi-fire', 'label' => 'Nến thơm'],
                            ['val' => 'bi-droplet', 'label' => 'Tinh dầu'],
                            ['val' => 'bi-brightness-high', 'label' => 'Hương thơm'],
                            // Gốm sứ & Đồ nhà
                            ['val' => 'bi-cup-hot', 'label' => 'Gốm sứ'],
                            ['val' => 'bi-lamp', 'label' => 'Đèn ngủ'],
                            ['val' => 'bi-house-heart', 'label' => 'Gia đình'],
                            // May mặc & Khác
                            ['val' => 'bi-scissors', 'label' => 'May vá'],
                            ['val' => 'bi-gift', 'label' => 'Hộp quà'],
                            ['val' => 'bi-award', 'label' => 'Cao cấp'],
                            ['val' => 'bi-patch-check', 'label' => 'Chính hãng'],
                        ] as $chip)
                            <button type="button"
                                    class="icon-chip-btn {{ old('icon', $category->display_icon) === $chip['val'] ? 'active' : '' }}"
                                    data-icon="{{ $chip['val'] }}"
                                    style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 13px;">
                                <i class="bi {{ $chip['val'] }}"></i>
                                <span>{{ $chip['label'] }}</span>
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

    <!-- Cột phải: Thông tin chi tiết danh mục & Xem trước -->
    <div>
        <div class="admin-card" style="margin-bottom: 20px;">
            <div class="admin-card-header">
                <h3 class="admin-card-title">
                    <i class="bi bi-eye"></i>
                    <span>Xem trước hiển thị</span>
                </h3>
            </div>
            <div class="admin-card-body">
                <div style="display: flex; align-items: center; gap: 14px; padding: 14px; background: #fff8f6; border: 1px solid #ebdcd7; border-radius: 12px;">
                    <div class="metric-icon-box" id="previewIconBox" style="margin: 0; width: 46px; height: 46px; font-size: 20px; background: #fff5f2; color: var(--color-primary);">
                        <i class="bi {{ $category->display_icon }}"></i>
                    </div>
                    <div>
                        <div id="previewNameText" style="font-size: 15px; font-weight: 800; color: var(--color-on-surface);">
                            {{ old('name', $category->name) }}
                        </div>
                        <div style="margin-top: 4px;">
                            <span class="mono-code" id="previewSlugBadge">{{ $category->slug }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                        <span class="info-kv-label">Số sản phẩm</span>
                        <span class="info-kv-value"><strong>{{ $category->products()->count() }} sản phẩm</strong></span>
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
        const nameVal = nameInput ? nameInput.value.trim() : '';
        const slugVal = toSlug(nameVal) || '{{ $category->slug }}';
        if (slugPreview) slugPreview.value = nameVal ? slugVal : '';
        if (previewNameText) previewNameText.textContent = nameVal || '{{ $category->name }}';
        if (previewSlugBadge) previewSlugBadge.textContent = slugVal;

        const iconVal = iconInput ? iconInput.value.trim() : '';
        if (!iconVal) {
            previewIconBox.innerHTML = '<i class="bi bi-tag"></i>';
        } else if (iconVal.startsWith('bi-')) {
            previewIconBox.innerHTML = `<i class="bi ${iconVal}"></i>`;
        } else {
            previewIconBox.innerHTML = `<i class="bi bi-${iconVal}"></i>`;
        }
    }

    if (nameInput) nameInput.addEventListener('input', updatePreview);
    if (iconInput) iconInput.addEventListener('input', updatePreview);

    iconChips.forEach(btn => {
        btn.addEventListener('click', function () {
            iconChips.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            if (iconInput) {
                iconInput.value = this.getAttribute('data-icon');
            }
            updatePreview();
        });
    });

    updatePreview();
});
</script>
@endpush
