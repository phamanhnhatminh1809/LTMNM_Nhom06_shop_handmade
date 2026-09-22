<div class="modal-backdrop-custom" id="modal-forgot-pwd">
    <div class="modal-box-custom">
        <button type="button" class="modal-close-btn" id="btn-close-forgot">&times;</button>

        <div class="modal-steps-indicator">
            <div class="step-dot active" data-step="1"><span>1</span> Email</div>
            <div class="step-line"></div>
            <div class="step-dot" data-step="2"><span>2</span> Mã OTP</div>
            <div class="step-line"></div>
            <div class="step-dot" data-step="3"><span>3</span> Mật khẩu mới</div>
        </div>

        <div class="forgot-step-content" id="forgot-step-1">
            <div class="modal-icon-circle">
                <i class="bi bi-envelope-at-fill"></i>
            </div>
            <h3 class="modal-title">Khôi phục mật khẩu</h3>
            <p class="modal-desc">Nhập địa chỉ email đăng ký tài khoản. Chúng tôi sẽ gửi mã OTP xác thực (gồm 6 chữ số) đến hộp thư của bạn.</p>
            <form id="forgot-form-step-1">
                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="forgot-email" class="form-label">EMAIL TÀI KHOẢN <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <span class="input-icon-left">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" id="forgot-email" class="form-control has-icon-left" placeholder="nguyenvanan@gmail.com" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit" style="width: 100%;">
                    <span>Gửi mã xác thực OTP</span>
                    <i class="bi bi-arrow-right" style="margin-left: 6px;"></i>
                </button>
            </form>
        </div>

        <div class="forgot-step-content" id="forgot-step-2" style="display: none;">
            <div class="modal-icon-circle">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h3 class="modal-title">Xác thực mã OTP</h3>
            <p class="modal-desc">Mã xác thực đã gửi đến <strong id="display-target-email" style="color: var(--color-on-surface);">email của bạn</strong>. Vui lòng nhập mã để tiếp tục.</p>

            <form id="forgot-form-step-2">
                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label">MÃ XÁC THỰC 6 CHỮ SỐ <span class="required">*</span></label>
                    <div class="otp-inputs-wrapper">
                        <input type="text" maxlength="1" class="otp-digit" required pattern="[0-9]" inputmode="numeric">
                        <input type="text" maxlength="1" class="otp-digit" required pattern="[0-9]" inputmode="numeric">
                        <input type="text" maxlength="1" class="otp-digit" required pattern="[0-9]" inputmode="numeric">
                        <input type="text" maxlength="1" class="otp-digit" required pattern="[0-9]" inputmode="numeric">
                        <input type="text" maxlength="1" class="otp-digit" required pattern="[0-9]" inputmode="numeric">
                        <input type="text" maxlength="1" class="otp-digit" required pattern="[0-9]" inputmode="numeric">
                    </div>
                    <div class="otp-resend-row">
                        <span>Chưa nhận được mã?</span>
                        <button type="button" class="btn-resend-otp" id="btn-resend-otp">Gửi lại mã (<span id="resend-countdown">60</span>s)</button>
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn-outline-custom" id="btn-back-step-1" style="flex: 1;">
                        Quay lại
                    </button>
                    <button type="submit" class="btn-submit" style="flex: 2;">
                        <span>Xác nhận mã</span>
                        <i class="bi bi-check-circle" style="margin-left: 6px;"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="forgot-step-content" id="forgot-step-3" style="display: none;">
            <div class="modal-icon-circle">
                <i class="bi bi-lock-fill"></i>
            </div>
            <h3 class="modal-title">Đặt lại mật khẩu mới</h3>
            <p class="modal-desc">Mã xác thực thành công! Hãy tạo mật khẩu mới cho tài khoản của bạn.</p>

            <form id="forgot-form-step-3">
                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="new-password" class="form-label">MẬT KHẨU MỚI <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <span class="input-icon-left">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" id="new-password" class="form-control has-icon-left has-icon-right" placeholder="Tối thiểu 8 ký tự" minlength="8" required>
                        <button type="button" class="btn-toggle-pwd" data-target="new-password" title="Hiện/Ẩn mật khẩu">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="confirm-new-password" class="form-label">XÁC NHẬN MẬT KHẨU MỚI <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <span class="input-icon-left">
                            <i class="bi bi-shield-check"></i>
                        </span>
                        <input type="password" id="confirm-new-password" class="form-control has-icon-left has-icon-right" placeholder="Nhập lại mật khẩu mới" minlength="8" required>
                        <button type="button" class="btn-toggle-pwd" data-target="confirm-new-password" title="Hiện/Ẩn mật khẩu">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" style="width: 100%;">
                    <span>Lưu mật khẩu & Đăng nhập</span>
                    <i class="bi bi-box-arrow-in-right" style="margin-left: 3px;"></i>
                </button>
            </form>
        </div>

        <div class="forgot-step-content" id="forgot-step-success" style="display: none; text-align: center;">
            <div class="modal-icon-circle" style="margin: 0 auto 16px;">
                <i class="bi bi-check-lg" style="font-size: 28px; font-weight: bold;"></i>
            </div>
            <h3 class="modal-title">Đổi mật khẩu thành công!</h3>
            <p class="modal-desc">Mật khẩu mới của bạn đã được cập nhật vào hệ thống. Bạn có thể đăng nhập ngay bây giờ.</p>
            <button type="button" class="btn-submit" style="width: 100%;" id="btn-finish-forgot">
                Quay lại đăng nhập
            </button>
        </div>
    </div>
</div>
