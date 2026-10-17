<!-- Modal Xác nhận Xóa Sản Phẩm -->
<div class="delete-modal-overlay" id="deleteProductModalOverlay">
    <div class="delete-modal-card">
        <!-- Header Modal -->
        <div class="delete-modal-header">
            <div class="delete-modal-icon-wrap">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="delete-modal-title-box">
                <h3 class="delete-modal-title">Xác nhận xóa sản phẩm</h3>
                <p class="delete-modal-subtitle">Thao tác này sẽ gỡ bỏ sản phẩm khỏi hệ thống và không thể hoàn tác.</p>
            </div>
            <button type="button" class="btn-close-modal" id="btnCloseDeleteProductModal" title="Đóng">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Body: Chi tiết sản phẩm sẽ bị xóa -->
        <div class="delete-modal-body">
            <div class="delete-product-preview-card">
                <img id="deleteProductImg" src="{{ asset('images/logo.png') }}" alt="Product" class="delete-product-thumb" onerror="this.src='{{ asset('images/logo.png') }}'">
                <div class="delete-product-info">
                    <div class="delete-product-name" id="deleteProductName">Tên sản phẩm</div>
                    <div class="delete-product-meta">
                        <span class="mono-code" id="deleteProductSku">SKU</span>
                        <span class="delete-product-cat" id="deleteProductCategory">Danh mục</span>
                    </div>
                </div>
            </div>

            <div class="delete-warning-note">
                <i class="bi bi-shield-exclamation"></i>
                <span>Bạn có chắc chắn muốn xóa vĩnh viễn sản phẩm này khỏi kho hàng? Thông tin sản phẩm, hình ảnh và tồn kho sẽ bị xóa.</span>
            </div>
        </div>

        <!-- Footer: Nút Hủy & Xác nhận Xóa -->
        <div class="delete-modal-footer">
            <button type="button" class="btn-modal-cancel" id="btnCancelDeleteProductModal">Hủy bỏ</button>
            <form id="deleteProductForm" action="" method="POST" style="display: inline; margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-modal-confirm-delete" id="btnConfirmDeleteProduct">
                    <i class="bi bi-trash-fill"></i>
                    <span>Xác nhận xóa</span>
                </button>
            </form>
        </div>
    </div>
</div>
