<!-- Modal Thêm Sản Phẩm Mới -->
<div class="product-modal-overlay" id="addProductModalOverlay">
    <div class="product-modal-card">
        <!-- Header Modal -->
        <div class="product-modal-header">
            <div class="modal-header-left">
                <div class="modal-title-row">
                    <i class="bi bi-plus-circle"></i>
                    <span>Thêm sản phẩm mới</span>
                </div>
                <p class="modal-subtitle">Nhập thông tin chi tiết, hình ảnh và biến thể sản phẩm thủ công vào hệ thống.</p>
            </div>
            <button type="button" class="btn-close-modal" id="btnCloseProductModal" title="Đóng">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Thân Modal -->
        <div class="product-modal-body">
            <!-- Cột trái: Thông tin cơ bản & Biến thể -->
            <div class="modal-col-left">
                <!-- Tên sản phẩm -->
                <div class="modal-form-group">
                    <label class="modal-label" for="productNameInput">
                        Tên sản phẩm <span class="required-star">*</span>
                    </label>
                    <input type="text" id="productNameInput" class="modal-input" placeholder="Nhập tên sản phẩm thủ công..." value="Bình hoa gốm men mộc nghệ nhân">
                </div>

                <!-- Mã SKU & Danh mục -->
                <div class="modal-form-row-2">
                    <div class="modal-form-group">
                        <label class="modal-label" for="productSkuInput">
                            Mã SKU <span class="required-star">*</span>
                        </label>
                        <input type="text" id="productSkuInput" class="modal-input" placeholder="e.g. CRM-FLW-088" value="CRM-FLW-088">
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label">
                            Danh mục <span class="required-star">*</span>
                        </label>
                        <!-- Custom Dropdown Danh mục đồng bộ -->
                        <div class="modal-select-wrapper" id="modalCategoryDropdown">
                            <button type="button" class="modal-select-btn" id="btnModalCategorySelect">
                                <span id="selectedModalCategoryText">Gốm sứ</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="modal-select-menu">
                                <div class="modal-select-item active" data-cat="Gốm sứ">Gốm sứ</div>
                                <div class="modal-select-item" data-cat="Đồ len">Đồ len</div>
                                <div class="modal-select-item" data-cat="Đồ da">Đồ da</div>
                                <div class="modal-select-divider"></div>
                                <div class="modal-select-add-new" id="btnOpenAddCategoryModal">
                                    <i class="bi bi-plus-circle"></i>
                                    <span>Thêm danh mục mới</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mô tả chi tiết sản phẩm kèm Toolbar mini -->
                <div class="modal-form-group">
                    <label class="modal-label" for="productDescriptionInput">
                        Mô tả chi tiết sản phẩm
                    </label>
                    <div class="editor-container">
                        <div class="editor-toolbar">
                            <button type="button" class="editor-btn" data-format="bold" title="In đậm (Bold)"><b>B</b></button>
                            <button type="button" class="editor-btn" data-format="italic" title="In nghiêng (Italic)"><i>I</i></button>
                            <button type="button" class="editor-btn" data-format="list" title="Danh sách (List)"><i class="bi bi-list-ul"></i></button>
                        </div>
                        <div id="productDescriptionInput" class="editor-content" contenteditable="true" placeholder="Nhập mô tả sản phẩm...">Bình hoa gốm thủ công được vuốt tay tỉ mỉ từ đất sét sông Hồng tuyển chọn, nung ở nhiệt độ 1280°C cho độ bền kết khối cao. Màu men mộc tro củi tự nhiên tạo nên hiệu ứng loang độc bản không trùng lặp trên từng sản phẩm.</div>
                    </div>
                </div>

                <!-- Bảng Biến thể & Tồn kho -->
                <div class="variant-section">
                    <div class="variant-header-row">
                        <div class="variant-title-box">
                            <span class="variant-title">Biến thể & Tồn kho</span>
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
                                    <th>KÍCH CỠ / MÀU</th>
                                    <th>SKU CON</th>
                                    <th>GIÁ BÁN</th>
                                    <th>TỒN KHO</th>
                                </tr>
                            </thead>
                            <tbody id="productVariantTableBody">
                                <tr>
                                    <td><span class="variant-name-text">Size M - Men tro</span></td>
                                    <td><span class="variant-sku-text">CRM-FLW-088-M</span></td>
                                    <td><span class="variant-price-text">380.000 đ</span></td>
                                    <td><span class="variant-stock-text">18</span></td>
                                </tr>
                                <tr>
                                    <td><span class="variant-name-text">Size L - Men mộc</span></td>
                                    <td><span class="variant-sku-text">CRM-FLW-088-L</span></td>
                                    <td><span class="variant-price-text">520.000 đ</span></td>
                                    <td><span class="variant-stock-text">8</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Hình ảnh, Trạng thái & Bảo quản -->
            <div class="modal-col-right">
                <!-- Hình ảnh sản phẩm -->
                <div class="image-upload-section">
                    <label class="modal-label">Hình ảnh sản phẩm (Tối đa 5 ảnh)</label>
                    <div class="image-thumbs-grid" id="productThumbsGrid">
                        <label class="btn-upload-thumb" id="btnAddImageThumb" for="productImageFileInput">
                            <i class="bi bi-image"></i>
                            <span>Thêm ảnh</span>
                            <input type="file" id="productImageFileInput" accept="image/*" multiple style="display: none;">
                        </label>
                    </div>
                </div>

                <!-- Trạng thái đăng bán -->
                <div class="modal-form-group">
                    <label class="modal-label">Trạng thái đăng bán</label>
                    <div class="status-radio-grid">
                        <label class="status-radio-card">
                            <input type="radio" name="product_publish_status" value="published" checked>
                            <span>Đang bán</span>
                        </label>
                        <label class="status-radio-card">
                            <input type="radio" name="product_publish_status" value="draft">
                            <span>Bản nháp</span>
                        </label>
                        <label class="status-radio-card">
                            <input type="radio" name="product_publish_status" value="archived">
                            <span>Lưu trữ</span>
                        </label>
                    </div>
                </div>

                <!-- Hướng dẫn sử dụng & Bảo quản -->
                <div class="modal-form-group">
                    <label class="modal-label" for="productPreserveInput">
                        Hướng dẫn sử dụng & Bảo quản
                    </label>
                    <textarea id="productPreserveInput" class="preserve-textarea" placeholder="Nhập hướng dẫn bảo quản...">Vệ sinh bằng nước ấm hoặc khăn mềm, tránh chất tẩy rửa có tính mài mòn cao.</textarea>
                </div>
            </div>
        </div>

        <!-- Footer Modal: Nút Hủy bỏ & Lưu sản phẩm -->
        <div class="product-modal-footer">
            <div class="modal-footer-actions">
                <button type="button" class="btn-modal-cancel" id="btnCancelProductModal">Hủy bỏ</button>
                <button type="button" class="btn-modal-save" id="btnSaveProduct">
                    <i class="bi bi-floppy"></i>
                    <span>Lưu sản phẩm</span>
                </button>
            </div>
        </div>
    </div>
</div>