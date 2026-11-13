<!-- Modal Thêm Sản Phẩm Mới (Gửi form POST thực tế vào admin.products.store) -->
<div class="product-modal-overlay" id="addProductModalOverlay">
    <form action="{{ route('admin.products.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="product-modal-card"
          id="addProductModalForm">
        @csrf
        <!-- Header Modal -->
        <div class="product-modal-header">
            <div class="modal-header-left">
                <div class="modal-title-row">
                    <i class="bi bi-plus-circle"></i>
                    <span>Thêm sản phẩm mới</span>
                </div>
                <p class="modal-subtitle">Nhập thông tin chi tiết, hình ảnh và tồn kho sản phẩm thủ công vào hệ thống.</p>
            </div>
            <button type="button" class="btn-close-modal" id="btnCloseProductModal" title="Đóng">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Thân Modal -->
        <div class="product-modal-body">
            <!-- Cột trái: Thông tin cơ bản & Giá / Tồn kho -->
            <div class="modal-col-left">
                <!-- Tên sản phẩm -->
                <div class="modal-form-group">
                    <label class="modal-label" for="productNameInput">
                        Tên sản phẩm <span class="required-star">*</span>
                    </label>
                    <input type="text"
                           id="productNameInput"
                           name="name"
                           class="modal-input"
                           placeholder="Nhập tên sản phẩm thủ công..."
                           value="{{ old('name') }}"
                           required>
                </div>

                <!-- Mã SKU & Danh mục -->
                <div class="modal-form-row-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="productSkuInput">
                            Mã SKU <span class="required-star">*</span>
                        </label>
                        <input type="text"
                               id="productSkuInput"
                               name="code"
                               class="modal-input"
                               style="font-family: var(--font-mono);"
                               placeholder="VD: CRM-FLW-088"
                               value="{{ old('code') }}"
                               required>
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label" for="modalCategorySelectNative">
                            Danh mục <span class="required-star">*</span>
                        </label>
                        @php
                            $firstCat = isset($categories) && count($categories) > 0 ? $categories->first() : null;
                        @endphp
                        <input type="hidden" name="category_id" id="modalCategoryIdInput" value="{{ old('category_id', $firstCat?->id) }}">
                        <div class="modal-select-wrapper" id="modalCategoryDropdown">
                            <button type="button" class="modal-select-btn" id="btnModalCategorySelect">
                                <span id="selectedModalCategoryText">{{ $firstCat?->name ?? 'Chọn danh mục' }}</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="modal-select-menu">
                                @if(isset($categories) && count($categories) > 0)
                                    @foreach($categories as $idx => $cat)
                                        <div class="modal-select-item {{ $idx === 0 ? 'active' : '' }}"
                                             data-cat="{{ $cat->name }}"
                                             data-cat-id="{{ $cat->id }}">
                                            {{ $cat->name }}
                                        </div>
                                    @endforeach
                                @endif
                                <div class="modal-select-divider"></div>
                                <div class="modal-select-add-new" id="btnOpenAddCategoryModal">
                                    <i class="bi bi-plus-circle"></i>
                                    <span>Thêm danh mục mới</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Giá bán, Giá cũ, Tồn kho & Ngưỡng cảnh báo -->
                <div class="modal-form-row-2" style="grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));">
                    <div class="modal-form-group">
                        <label class="modal-label" for="productPriceInput">
                            Giá bán (VNĐ) <span class="required-star">*</span>
                        </label>
                        <input type="number"
                               id="productPriceInput"
                               name="price"
                               class="modal-input"
                               min="0"
                               step="1000"
                               placeholder="350000"
                               value="{{ old('price', 350000) }}"
                               required>
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label" for="productOldPriceInput">
                            Giá gốc (VNĐ)
                        </label>
                        <input type="number"
                               id="productOldPriceInput"
                               name="old_price"
                               class="modal-input"
                               min="0"
                               step="1000"
                               placeholder="420000"
                               value="{{ old('old_price') }}">
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label" for="productStockInput">
                            Tồn kho <span class="required-star">*</span>
                        </label>
                        <input type="number"
                               id="productStockInput"
                               name="stock_quantity"
                               class="modal-input"
                               min="0"
                               placeholder="20"
                               value="{{ old('stock_quantity', 20) }}"
                               required>
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label" for="productMinThresholdInput">
                            Ngưỡng tối thiểu
                        </label>
                        <input type="number"
                               id="productMinThresholdInput"
                               name="min_stock_threshold"
                               class="modal-input"
                               min="0"
                               max="10000"
                               placeholder="5"
                               value="{{ old('min_stock_threshold', 5) }}">
                    </div>
                </div>

                <!-- Mô tả chi tiết sản phẩm kèm Toolbar mini -->
                <div class="modal-form-group">
                    <label class="modal-label" for="productDescriptionInput">
                        Mô tả chi tiết sản phẩm
                    </label>
                    <input type="hidden" name="description" id="productDescriptionHidden" value="{{ old('description') }}">
                    <div class="editor-container">
                        <div class="editor-toolbar">
                            <button type="button" class="editor-btn" data-format="bold" title="In đậm"><b>B</b></button>
                            <button type="button" class="editor-btn" data-format="italic" title="In nghiêng"><i>I</i></button>
                            <button type="button" class="editor-btn" data-format="list" title="Danh sách"><i class="bi bi-list-ul"></i></button>
                        </div>
                        <div id="productDescriptionInput" class="editor-content" contenteditable="true" placeholder="Nhập mô tả sản phẩm...">{{ old('description', 'Sản phẩm thủ công được hoàn thiện từ nguyên liệu tự nhiên.') }}</div>
                    </div>
                </div>

                <!-- Bảng Biến thể và Tồn kho -->
                <div class="variant-section">
                    <div class="variant-header-row">
                        <div class="variant-title-box">
                            <span class="variant-title">Biến thể và tồn kho</span>
                            <span class="variant-subtitle">Cấu hình kích cỡ, màu sắc và giá bán</span>
                        </div>
                        <button type="button" class="btn-add-variant" id="btnAddVariant">
                            <i class="bi bi-plus-lg"></i>
                            <span>Thêm biến thể</span>
                        </button>
                    </div>

                    <div class="variant-table-card">
                        <table class="variant-table">
                            <thead>
                                <tr>
                                    <th>Kích cỡ / Màu</th>
                                    <th>SKU con</th>
                                    <th>Giá bán</th>
                                    <th>Tồn kho</th>
                                </tr>
                            </thead>
                            <tbody id="productVariantTableBody">
                                <tr>
                                    <td><span class="variant-name-text">Bản tiêu chuẩn</span></td>
                                    <td><span class="variant-sku-text">STD-01</span></td>
                                    <td><span class="variant-price-text">350.000 đ</span></td>
                                    <td><span class="variant-stock-text">20</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Hình ảnh, Trạng thái & Bảo quản -->
            <div class="modal-col-right">
                <!-- Ảnh đại diện chính -->
                <div class="modal-form-group">
                    <label class="modal-label" for="productMainImageInput">
                        Ảnh đại diện chính
                    </label>
                    <input type="file"
                           id="productMainImageInput"
                           name="image"
                           class="modal-input"
                           accept="image/*">
                </div>

                <!-- Hình ảnh phụ (Gallery) -->
                <div class="image-upload-section">
                    <label class="modal-label">Bộ sưu tập ảnh phụ (Tối đa 5 ảnh)</label>
                    <div class="image-thumbs-grid" id="productThumbsGrid">
                        <label class="btn-upload-thumb" id="btnAddImageThumb" for="productImageFileInput">
                            <i class="bi bi-image"></i>
                            <span>Thêm ảnh</span>
                            <input type="file" id="productImageFileInput" name="gallery[]" accept="image/*" multiple style="display: none;">
                        </label>
                    </div>
                </div>

                <!-- Sản phẩm nổi bật -->
                <div class="modal-form-group">
                    <label class="modal-label">Hiển thị nổi bật</label>
                    <div class="status-radio-grid" style="grid-template-columns: 1fr 1fr;">
                        <label class="status-radio-card">
                            <input type="radio" name="is_featured" value="1">
                            <span>⭐ Nổi bật</span>
                        </label>
                        <label class="status-radio-card">
                            <input type="radio" name="is_featured" value="0" checked>
                            <span>Thông thường</span>
                        </label>
                    </div>
                </div>

                <!-- Hướng dẫn sử dụng và bảo quản -->
                <div class="modal-form-group">
                    <label class="modal-label" for="productPreserveInput">
                        Hướng dẫn sử dụng và bảo quản
                    </label>
                    <textarea id="productPreserveInput"
                              name="instruction_preserve"
                              class="preserve-textarea"
                              placeholder="Nhập hướng dẫn bảo quản...">{{ old('instruction_preserve', 'Vệ sinh bằng nước ấm hoặc khăn mềm, tránh chất tẩy rửa có tính mài mòn cao.') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Footer Modal: Nút Hủy bỏ & Lưu sản phẩm -->
        <div class="product-modal-footer">
            <div class="modal-footer-actions">
                <button type="button" class="btn-modal-cancel" id="btnCancelProductModal">Hủy bỏ</button>
                <button type="submit" class="btn-modal-save" id="btnSaveProduct">
                    <i class="bi bi-floppy"></i>
                    <span>Lưu sản phẩm</span>
                </button>
            </div>
        </div>
    </form>
</div>