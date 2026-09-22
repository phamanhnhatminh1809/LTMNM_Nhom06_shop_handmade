<!-- Modal Thêm danh mục mới -->
<div class="category-modal-overlay" id="addCategoryModalOverlay">
    <div class="category-modal-card" id="categoryModalCard">
        <!-- Header Modal -->
        <div class="category-modal-header">
            <div class="category-modal-title-wrap">
                <i class="bi bi-plus-circle category-modal-header-icon"></i>
                <div>
                    <h3 class="category-modal-title">Thêm danh mục mới</h3>
                    <p class="category-modal-subtitle">Tạo danh mục sản phẩm vào hệ thống</p>
                </div>
            </div>
            <button type="button" class="btn-close-category-modal" id="btnCloseCategoryModal" title="Đóng">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Body Modal -->
        <div class="category-modal-body">
            <!-- Banner nhắc nhở giữ nguyên dữ liệu sản phẩm -->
            <div class="category-info-notice">
                <i class="bi bi-info-circle"></i>
                <span>Dữ liệu sản phẩm đang nhập được giữ nguyên vẹn và sẽ tự động chọn danh mục này.</span>
            </div>

            <!-- Tên danh mục -->
            <div class="modal-form-group">
                <label class="modal-label" for="categoryNameInput">
                    Tên danh mục <span class="required-star">*</span>
                </label>
                <input type="text" id="categoryNameInput" class="modal-input" placeholder="Ví dụ: Nến thơm & Tinh dầu thủ công" value="Nến thơm & Tinh dầu thủ công">
            </div>

            <!-- Mã danh mục (Slug) -->
            <div class="modal-form-group">
                <label class="modal-label" for="categorySlugInput">
                    Mã danh mục (Slug)
                </label>
                <input type="text" id="categorySlugInput" class="modal-input" placeholder="nen-thom-tinh-dau" value="nen-thom-tinh-dau">
            </div>

            <!-- Mô tả ngắn -->
            <div class="modal-form-group">
                <label class="modal-label" for="categoryDescriptionInput">
                    Mô tả ngắn
                </label>
                <textarea id="categoryDescriptionInput" class="category-textarea" placeholder="Mô tả về nhóm danh mục sản phẩm này...">Các dòng nến sáp đậu nành đổ khuôn tay, tinh dầu thiên nhiên thơm dịu và phụ kiện đốt nến trang trí.</textarea>
            </div>
        </div>

        <!-- Footer Modal -->
        <div class="category-modal-footer">
            <button type="button" class="btn-cancel-category" id="btnCancelCategoryModal">Hủy bỏ</button>
            <button type="button" class="btn-submit-category" id="btnSubmitCategory">
                <i class="bi bi-check2-circle"></i>
                <span>Tạo danh mục & Chọn ngay</span>
            </button>
        </div>
    </div>
</div>