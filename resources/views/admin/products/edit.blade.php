@extends('layouts.admin')

@section('title', 'Chỉnh sửa sản phẩm — ' . $product->name)

@php $activeMenu = 'products'; @endphp

@section('content')
<!-- Top Bar -->
<div class="admin-top-bar">
    <div class="page-main-title">
        <i class="bi bi-pencil-square"></i>
        <span>Chỉnh sửa sản phẩm</span>
    </div>
    <div class="top-bar-right">
        <a href="{{ route('admin.products.show', $product) }}" class="btn-secondary-outline">
            <i class="bi bi-info-circle"></i>
            <span>Xem chi tiết</span>
        </a>
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
                <div>Vui lòng kiểm tra lại thông tin cập nhật:</div>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="admin-form-grid">
        <!-- Cột trái: Thông tin sản phẩm -->
        <div>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">
                        <i class="bi bi-box-seam"></i>
                        <span>Thông tin sản phẩm: {{ $product->name }}</span>
                    </h3>
                    <span class="mono-code">{{ $product->code }}</span>
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
                               value="{{ old('name', $product->name) }}"
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
                                   value="{{ old('code', $product->code) }}"
                                   required>
                            @error('code')
                                <div class="form-error-text">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="modal-form-group">
                            <label class="modal-label" for="category_id">
                                Danh mục sản phẩm <span class="required-star">*</span>
                            </label>
                            <select id="category_id" name="category_id" class="modal-input" required>
                                <option value="">-- Chọn danh mục sản phẩm --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
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
                                   value="{{ old('price', $product->price) }}"
                                   min="0"
                                   step="1000"
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
                                   value="{{ old('old_price', $product->old_price) }}"
                                   min="0"
                                   step="1000">
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
                                   value="{{ old('stock_quantity', $product->stock_quantity) }}"
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
                                   value="{{ old('min_stock_threshold', $product->effectiveMinStockThreshold()) }}"
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
                                  style="min-height: 120px;">{{ old('description', $product->description) }}</textarea>
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
                                  class="preserve-textarea">{{ old('instruction_preserve', $product->instruction_preserve) }}</textarea>
                        @error('instruction_preserve')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Hình ảnh hiện tại & Cập nhật ảnh -->
        <div>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">
                        <i class="bi bi-images"></i>
                        <span>Hình ảnh sản phẩm</span>
                    </h3>
                </div>
                <div class="admin-card-body">
                    @php
                        $currentMainImg = !empty($product->image)
                            ? (str_starts_with($product->image, 'http') ? $product->image : asset(ltrim($product->image, '/')))
                            : null;
                    @endphp

                    <div class="modal-form-group" style="margin-bottom: 18px;">
                        <label class="modal-label" for="imageInput">
                            Ảnh đại diện chính (Để trống nếu giữ nguyên ảnh cũ)
                        </label>
                        @if($currentMainImg)
                            <div style="margin-bottom: 10px;">
                                <img id="mainImagePreview"
                                     src="{{ $currentMainImg }}"
                                     alt="{{ $product->name }}"
                                     style="width: 100%; max-height: 210px; object-fit: cover; border-radius: 10px; border: 1px solid #ebdcd7;"
                                     onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder.svg') }}';">
                            </div>
                        @endif
                        <input type="file"
                               id="imageInput"
                               name="image"
                               class="modal-input"
                               accept="image/*">
                        @error('image')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="galleryInput">
                            Bộ sưu tập ảnh phụ (Tối đa 5 ảnh)
                        </label>
                        @if(!empty($product->gallery) && is_array($product->gallery))
                            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px;" id="galleryPreviewGrid">
                                @foreach($product->gallery as $galImg)
                                    @php
                                        $galUrl = str_starts_with($galImg, 'http') ? $galImg : asset(ltrim($galImg, '/'));
                                    @endphp
                                    <img src="{{ $galUrl }}"
                                         alt="Gallery"
                                         style="width: 64px; height: 64px; object-fit: cover; border-radius: 8px; border: 1px solid #ebdcd7;"
                                         onerror="this.style.display='none'">
                                @endforeach
                            </div>
                        @else
                            <div id="galleryPreviewGrid" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px;"></div>
                        @endif

                        <input type="file"
                               id="galleryInput"
                               name="gallery[]"
                               class="modal-input"
                               accept="image/*"
                               multiple>
                        <div class="form-help-text">Chọn ảnh mới nếu muốn thay thế bộ sưu tập ảnh phụ.</div>
                        @error('gallery')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                        @error('gallery.*')
                            <div class="form-error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h3 class="admin-card-title">
                        <i class="bi bi-stars"></i>
                        <span>Thiết lập và lưu thay đổi</span>
                    </h3>
                </div>
                <div class="admin-card-body">
                    <input type="hidden" name="is_featured" value="0">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 14px; font-weight: 700; color: var(--color-on-surface);">
                        <input type="checkbox"
                               name="is_featured"
                               value="1"
                               style="width: 18px; height: 18px; accent-color: var(--color-primary);"
                               {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                        <span>Sản phẩm nổi bật</span>
                    </label>

                    <div class="info-kv-list" style="margin-top: 14px; padding-top: 14px; border-top: 1px solid #f4eae6;">
                        <div class="info-kv-row">
                            <span class="info-kv-label">Đã bán</span>
                            <span class="info-kv-value">{{ $product->sold }} sản phẩm</span>
                        </div>
                        <div class="info-kv-row">
                            <span class="info-kv-label">Điểm đánh giá</span>
                            <span class="info-kv-value">⭐ {{ $product->rating }} / 5.0</span>
                        </div>
                    </div>

                    <div class="admin-form-actions">
                        <a href="{{ route('admin.products.index') }}" class="btn-modal-cancel" style="text-decoration: none;">
                            Hủy bỏ
                        </a>
                        <button type="submit" class="btn-modal-save">
                            <i class="bi bi-floppy"></i>
                            <span>Cập nhật sản phẩm</span>
                        </button>
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
    const mainPreviewImg = document.getElementById('mainImagePreview');
    const galleryInput = document.getElementById('galleryInput');
    const galleryGrid = document.getElementById('galleryPreviewGrid');

    if (imageInput && mainPreviewImg) {
        imageInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    mainPreviewImg.src = e.target.result;
                };
                reader.readAsDataURL(file);
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
                    img.style.cssText = 'width: 64px; height: 64px; object-fit: cover; border-radius: 8px; border: 1px solid #ebdcd7;';
                    galleryGrid.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    }
});
</script>
@endpush
