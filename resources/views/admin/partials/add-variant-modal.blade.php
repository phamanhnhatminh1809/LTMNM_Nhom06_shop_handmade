<!-- Modal Thêm biến thể sản phẩm -->
<div class="variant-modal-overlay" id="addVariantModalOverlay">
    <div class="variant-modal-card" id="variantModalCard">
        <!-- Header Modal -->
        <div class="variant-modal-header">
            <div class="variant-modal-title-wrap">
                <i class="bi bi-sliders2 variant-modal-header-icon"></i>
                <div>
                    <h3 class="variant-modal-title">Thêm biến thể sản phẩm</h3>
                    <p class="variant-modal-subtitle">Cấu hình thuộc tính, SKU riêng, giá bán và tồn kho cho biến thể này</p>
                </div>
            </div>
            <button type="button" class="btn-close-variant-modal" id="btnCloseVariantModal" title="Đóng">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Body Modal -->
        <div class="variant-modal-body">
            <!-- Banner Thông tin sản phẩm gốc -->
            <div class="variant-info-banner">
                <div class="variant-info-left">
                    <i class="bi bi-info-circle"></i>
                    <span><strong>Sản phẩm gốc:</strong> <span id="variantBaseProductName">Bình hoa gốm men mộc nghệ nhân (CRM-FLW-088)</span></span>
                </div>
                <span class="badge-variant-category" id="variantBaseCategoryBadge">Gốm sứ thủ công</span>
            </div>

            <!-- Phân loại thuộc tính -->
            <div class="variant-section-card">
                <div class="variant-card-title">
                    <i class="bi bi-diagram-3"></i>
                    <span>Phân loại thuộc tính</span>
                </div>

                <!-- Chọn kích cỡ -->
                <div class="modal-form-group">
                    <label class="modal-label">
                        Kích cỡ <span class="required-star">*</span>
                    </label>
                    <div class="size-chips-wrapper">
                        <button type="button" class="size-chip-btn" data-size="Size S">
                            <i class="bi bi-check2 size-chip-check-icon hidden"></i>
                            <span>Size S</span>
                        </button>
                        <button type="button" class="size-chip-btn" data-size="Size M">
                            <i class="bi bi-check2 size-chip-check-icon hidden"></i>
                            <span>Size M</span>
                        </button>
                        <button type="button" class="size-chip-btn" data-size="Size L">
                            <i class="bi bi-check2 size-chip-check-icon hidden"></i>
                            <span>Size L</span>
                        </button>
                        <button type="button" class="size-chip-btn active" data-size="Size XL">
                            <i class="bi bi-check2 size-chip-check-icon"></i>
                            <span>Size XL</span>
                        </button>
                        <input type="text" id="variantOtherSizeInput" class="size-other-input" placeholder="Kích cỡ khác...">
                    </div>
                </div>

                <!-- Hàng: Màu sắc / Kiểu dáng & Mã SKU biến thể -->
                <div class="modal-form-row-2">
                    <div class="modal-form-group">
                        <label class="modal-label">
                            Màu sắc / Kiểu dáng <span class="required-star">*</span>
                        </label>
                        <!-- Custom Dropdown Màu sắc / Kiểu dáng -->
                        <div class="modal-select-wrapper" id="variantColorDropdownWrapper">
                            <button type="button" class="modal-select-btn" id="btnVariantColorSelect">
                                <span id="selectedVariantColorText">Men hỏa biến lam</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="modal-select-menu">
                                <div class="modal-select-item active" data-val="lam" data-code="LAM">Men hỏa biến lam</div>
                                <div class="modal-select-item" data-val="tro" data-code="TRO">Men tro củi</div>
                                <div class="modal-select-item" data-val="moc" data-code="MOC">Men mộc tự nhiên</div>
                                <div class="modal-select-item" data-val="nau" data-code="NAU">Men nâu đất</div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="variantSkuInput">
                            Mã SKU biến thể <span class="required-star">*</span>
                        </label>
                        <input type="text" id="variantSkuInput" class="modal-input" value="CRM-FLW-088-XL-LAM">
                    </div>
                </div>

                <!-- Tên hiển thị đầy đủ của biến thể -->
                <div class="modal-form-group">
                    <label class="modal-label" for="variantDisplayNameInput">
                        Tên hiển thị đầy đủ của biến thể
                    </label>
                    <input type="text" id="variantDisplayNameInput" class="modal-input" value="Size XL - Men hỏa biến lam">
                </div>
            </div>

            <!-- Giá bán và tồn kho -->
            <div class="variant-section-card">
                <div class="variant-card-title">
                    <i class="bi bi-tag"></i>
                    <span>Giá bán và tồn kho</span>
                </div>

                <div class="modal-form-row-2" style="grid-template-columns: 1fr 1fr 1fr;">
                    <!-- Giá bán lẻ -->
                    <div class="modal-form-group">
                        <label class="modal-label" for="variantRetailPriceInput">
                            Giá bán lẻ <span class="required-star">*</span>
                        </label>
                        <div class="input-with-suffix">
                            <input type="text" id="variantRetailPriceInput" class="modal-input" style="color: #ba1a1a; font-weight: 800;" value="680.000">
                            <span class="input-suffix-text">đ</span>
                        </div>
                    </div>

                    <!-- Giá vốn -->
                    <div class="modal-form-group">
                        <label class="modal-label" for="variantCostPriceInput">
                            Giá vốn
                        </label>
                        <div class="input-with-suffix">
                            <input type="text" id="variantCostPriceInput" class="modal-input" value="350.000">
                            <span class="input-suffix-text">đ</span>
                        </div>
                    </div>

                    <!-- Số lượng tồn kho -->
                    <div class="modal-form-group">
                        <label class="modal-label" for="variantStockQuantityInput">
                            Số lượng tồn kho <span class="required-star">*</span>
                        </label>
                        <div class="input-with-suffix">
                            <input type="number" id="variantStockQuantityInput" class="modal-input" min="0" value="15">
                            <span class="input-suffix-text">chiếc</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Modal -->
        <div class="variant-modal-footer">
            <button type="button" class="btn-cancel-variant" id="btnCancelVariantModal">Hủy bỏ</button>
            <button type="button" class="btn-submit-variant" id="btnSubmitVariant">
                <i class="bi bi-plus-circle"></i>
                <span>Thêm biến thể</span>
            </button>
        </div>
    </div>
</div>