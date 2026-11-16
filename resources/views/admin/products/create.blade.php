@extends('layouts.admin')

@section('title', 'Thêm sản phẩm mới')

@php $activeMenu = 'products'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-bag-plus"></i>
        <span>Thêm sản phẩm mới</span>
    </div>
    <div class="top-bar-right">
        <a href="{{ route('admin.products.index') }}" class="btn-secondary-outline">
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
                <div>Vui lòng kiểm tra lại thông tin sản phẩm:</div>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="admin-form-grid">
        <!-- Cột trái: Thông tin chính của sản phẩm -->
        <div>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">
                        <i class="bi bi-box-seam"></i>
                        <span>Thông tin cơ bản và giá bán</span>
                    </h3>
                </div>
                <div class="admin-card-body">
                    <div class="modal-form-group" style="margin-bottom: 16px;">
                        <label class="modal-label" for="name">
                            Tên sản phẩm thủ công <span class="required-star">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="modal-input"
                               value="{{ old('name') }}"
                               placeholder="Ví dụ: Bình hoa gốm men mộc nghệ nhân..."
                               required>
                        @error('name')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-form-row-2" style="margin-bottom: 16px;">
                        <div class="modal-form-group">
                            <label class="modal-label" for="code">
                                Mã SKU <span class="required-star">*</span>
                            </label>
                            <input type="text"
                                   id="code"
                                   name="code"
                                   class="modal-input"
                                   style="font-family: var(--font-mono);"
                                   value="{{ old('code') }}"
                                   placeholder="VD: CRM-FLW-088"
                                   required>
                            @error('code')
                                <div class="form-error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-label" for="category_id">
                                Danh mục sản phẩm <span class="required-star">*</span>
                            </label>
                            @php
                                $categoryOptions = array_merge(
                                    [['value' => '', 'label' => '-- Chọn danh mục sản phẩm --']],
                                    $categories->map(fn ($cat) => [
                                        'value' => $cat->id,
                                        'label' => trim((string) $cat->name),
                                        'icon'  => $cat->display_icon,
                                    ])->all()
                                );
                            @endphp
                            <x-select id="category_id"
                                      name="category_id"
                                      :options="$categoryOptions"
                                      :value="old('category_id', '')"
                                      :required="true" />
                            @error('category_id')
                                <div class="form-error-text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-form-row-2" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); margin-bottom: 16px;">
                        <div class="modal-form-group">
                            <label class="modal-label" for="price">
                                Giá bán (VNĐ) <span class="required-star">*</span>
                            </label>
                            <input type="number"
                                   id="price"
                                   name="price"
                                   class="modal-input"
                                   value="{{ old('price') }}"
                                   min="0"
                                   step="1000"
                                   placeholder="380000"
                                   required>
                            @error('price')
                                <div class="form-error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-label" for="old_price">
                                Giá gốc (VNĐ)
                            </label>
                            <input type="number"
                                   id="old_price"
                                   name="old_price"
                                   class="modal-input"
                                   value="{{ old('old_price') }}"
                                   min="0"
                                   step="1000"
                                   placeholder="450000">
                            @error('old_price')
                                <div class="form-error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-label" for="stock_quantity">
                                Số lượng tồn kho <span class="required-star">*</span>
                            </label>
                            <input type="number"
                                   id="stock_quantity"
                                   name="stock_quantity"
                                   class="modal-input"
                                   value="{{ old('stock_quantity', 10) }}"
                                   min="0"
                                   required>
                            @error('stock_quantity')
                                <div class="form-error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-label" for="min_stock_threshold">
                                Ngưỡng cảnh báo tối thiểu
                            </label>
                            <input type="number"
                                   id="min_stock_threshold"
                                   name="min_stock_threshold"
                                   class="modal-input"
                                   value="{{ old('min_stock_threshold', 5) }}"
                                   min="0"
                                   max="10000"
                                   placeholder="5">
                            @error('min_stock_threshold')
                                <div class="form-error-text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-form-group" style="margin-bottom: 16px;">
                        <label class="modal-label" for="description">
                            Mô tả chi tiết sản phẩm
                        </label>
                        <textarea id="description"
                                  name="description"
                                  rows="5"
                                  class="preserve-textarea"
                                  style="min-height: 120px;"
                                  placeholder="Mô tả câu chuyện chế tác, chất liệu, kích thước và điểm đặc trưng của sản phẩm...">{{ trim((string) old('description')) }}</textarea>
                        @error('description')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="instruction_preserve">
                            Hướng dẫn sử dụng và bảo quản
                        </label>
                        <textarea id="instruction_preserve"
                                  name="instruction_preserve"
                                  rows="3"
                                  class="preserve-textarea"
                                  placeholder="Hướng dẫn vệ sinh, bảo quản sản phẩm thủ công bền đẹp theo thời gian...">{{ trim((string) old('instruction_preserve')) }}</textarea>
                        @error('instruction_preserve')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Hình ảnh & Trạng thái nổi bật -->
        <div>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">
                        <i class="bi bi-images"></i>
                        <span>Hình ảnh sản phẩm</span>
                    </h3>
                </div>
                <div class="admin-card-body">
                    <!-- Ảnh chính -->
                    <div class="modal-form-group" style="margin-bottom: 18px;">
                        <label class="modal-label" for="imageInput">
                            Ảnh đại diện chính
                        </label>
                        <input type="file"
                               id="imageInput"
                               name="image"
                               class="modal-input"
                               accept="image/*">
                        <div class="form-help-text">Định dạng JPG, PNG, WEBP, GIF (Tối đa 5MB).</div>
                        @error('image')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror

                        <div id="mainImagePreviewWrap" style="display: none; margin-top: 12px;">
                            <img id="mainImagePreview"
                                 src=""
                                 alt="Preview"
                                 style="width: 100%; max-height: 220px; object-fit: cover; border-radius: 10px; border: 1px solid #ebdcd7;">
                        </div>
                    </div>

                    <!-- Bộ sưu tập ảnh phụ -->
                    <div class="modal-form-group">
                        <label class="modal-label" for="galleryInput">
                            Bộ sưu tập ảnh phụ (Tối đa 5 ảnh)
                        </label>
                        <input type="file"
                               id="galleryInput"
                               name="gallery[]"
                               class="modal-input"
                               accept="image/*"
                               multiple>
                        <div class="form-help-text">Có thể giữ phím Ctrl / Shift để chọn nhiều ảnh cùng lúc.</div>
                        @error('gallery')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                        @error('gallery.*')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror

                        <div id="galleryPreviewGrid" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px;"></div>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">
                        <i class="bi bi-stars"></i>
                        <span>Thiết lập hiển thị</span>
                    </h3>
                </div>
                <div class="admin-card-body">
                    <input type="hidden" name="is_featured" value="0">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 14px; font-weight: 700; color: var(--color-on-surface);">
                        <input type="checkbox"
                               name="is_featured"
                               value="1"
                               style="width: 18px; height: 18px; accent-color: var(--color-primary);"
                               {{ old('is_featured') ? 'checked' : '' }}>
                        <span>Đánh dấu là Sản phẩm nổi bật trên Trang chủ</span>
                    </label>
                    <div class="form-help-text" style="margin-top: 6px;">
                        Sản phẩm nổi bật sẽ được gắn nhãn <strong>Nổi bật</strong> và ưu tiên giới thiệu tới khách hàng.
                    </div>

                    <div class="admin-form-actions">
                        <x-button variant="cancel" :href="route('admin.products.index')">
                            Hủy bỏ
                        </x-button>
                        <x-button variant="primary" type="submit" icon="bi-floppy">
                            <span>Lưu sản phẩm</span>
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
window.AdminApp.mount(() => {
    const imageInput = document.getElementById('imageInput');
    const mainPreviewWrap = document.getElementById('mainImagePreviewWrap');
    const mainPreviewImg = document.getElementById('mainImagePreview');
    const galleryInput = document.getElementById('galleryInput');
    const galleryGrid = document.getElementById('galleryPreviewGrid');

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    mainPreviewImg.src = e.target.result;
                    mainPreviewWrap.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                mainPreviewWrap.style.display = 'none';
            }
        });
    }

    if (galleryInput && galleryGrid) {
        galleryInput.addEventListener('change', function () {
            galleryGrid.innerHTML = '';
            const files = Array.from(this.files || []).slice(0, 5);
            files.forEach(file => {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'width: 68px; height: 68px; object-fit: cover; border-radius: 8px; border: 1px solid #ebdcd7;';
                    galleryGrid.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    }
});
</script>
@endpush
