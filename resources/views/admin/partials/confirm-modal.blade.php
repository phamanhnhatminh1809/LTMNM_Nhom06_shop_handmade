<!-- Modal Xác nhận dùng chung toàn khu vực Admin -->
<div class="modal-overlay"
     id="adminConfirmModal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="adminConfirmTitle">
    <div class="modal-box" style="max-width: 430px;">
        <div class="modal-confirm-icon is-primary" data-confirm-icon-wrap>
            <i class="bi bi-question-circle-fill" data-confirm-icon aria-hidden="true"></i>
        </div>
        <h3 class="modal-confirm-title" id="adminConfirmTitle" data-confirm-title>
            Xác nhận thao tác
        </h3>
        <p class="modal-confirm-desc" id="adminConfirmMessage" data-confirm-message>
            Bạn có chắc chắn muốn thực hiện thao tác này?
        </p>

        <div class="modal-confirm-actions">
            <button type="button" class="btn-cancel-modal" data-confirm-cancel>
                Hủy bỏ
            </button>
            <button type="button" class="btn-modal-save" data-confirm-accept>
                Xác nhận
            </button>
        </div>
    </div>
</div>
