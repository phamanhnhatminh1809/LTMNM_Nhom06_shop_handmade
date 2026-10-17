<!-- Modal Thêm danh mục mới (Gửi form lưu trực tiếp vào CSDL) -->
<div class="category-modal-overlay" id="addCategoryModalOverlay">
    <form action="{{ route('admin.categories.store') }}" method="POST" class="category-modal-card" id="categoryModalCard">
        @csrf
        <!-- Header Modal -->
        <div class="category-modal-header">
            <div class="category-modal-title-wrap">
                <i class="bi bi-plus-circle category-modal-header-icon"></i>
                <div>
                    <h3 class="category-modal-title">Thêm danh mục mới</h3>
                    <p class="category-modal-subtitle">Tạo loại sản phẩm mới và lưu trực tiếp vào cơ sở dữ liệu</p>
                </div>
            </div>
            <button type="button" class="btn-close-category-modal" id="btnCloseCategoryModal" title="Đóng">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Body Modal -->
        <div class="category-modal-body">
            <div class="category-info-notice">
                <i class="bi bi-info-circle"></i>
                <span>Danh mục sau khi tạo sẽ được lưu vào hệ thống và tự động sinh mã Slug chuẩn SEO.</span>
            </div>

            <!-- Tên danh mục -->
            <div class="modal-form-group">
                <label class="modal-label" for="categoryNameInput">
                    Tên danh mục <span class="required-star">*</span>
                </label>
                <input type="text"
                       id="categoryNameInput"
                       name="name"
                       class="modal-input"
                       placeholder="Ví dụ: Nến thơm & Tinh dầu thủ công"
                       required>
            </div>

            <!-- Mã danh mục (Slug preview) -->
            <div class="modal-form-group">
                <label class="modal-label" for="categorySlugInput">
                    Mã danh mục (Slug tự động)
                </label>
                <input type="text"
                       id="categorySlugInput"
                       class="modal-input"
                       style="font-family: var(--font-mono); background-color: #f9f3f0;"
                       placeholder="nen-thom-tinh-dau"
                       readonly>
            </div>

            <!-- Biểu tượng (Icon) -->
            <div class="modal-form-group">
                <label class="modal-label" for="categoryIconInput">
                    Biểu tượng (Emoji hoặc Bootstrap Icon)
                </label>
                <input type="text"
                       id="categoryIconInput"
                       name="icon"
                       class="modal-input"
                       placeholder="VD: 🕯️, 🏺, 🧶 hoặc bi-tag">
            </div>
        </div>

        <!-- Footer Modal -->
        <div class="category-modal-footer">
            <button type="button" class="btn-cancel-category" id="btnCancelCategoryModal">Hủy bỏ</button>
            <button type="submit" class="btn-submit-category" id="btnSubmitCategory">
                <i class="bi bi-check2-circle"></i>
                <span>Lưu danh mục</span>
            </button>
        </div>
    </form>
</div>