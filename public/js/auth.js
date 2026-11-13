/**
 * Xử lý các tương tác phía người dùng cho trang Đăng nhập / Đăng ký / Quên mật khẩu
 * Tác giả: Nhóm 06 - LTMNM
 */

document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // 1. HÀM TIỆN ÍCH CHUNG
    // ==========================================

    /**
     * Lấy CSRF Token từ thẻ meta trong HTML
     */
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    /**
     * Kiểm tra định dạng email cơ bản
     */
    function checkEmailFormat(emailStr) {
        if (!emailStr) return { valid: false, message: 'Vui lòng nhập địa chỉ email.' };
        
        const baseRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!baseRegex.test(emailStr)) {
            return { valid: false, message: 'Địa chỉ email không đúng định dạng (ví dụ: name@gmail.com).' };
        }

        const parts = emailStr.split('@');
        const domain = parts[1].toLowerCase();

        if (domain === 'gmail.co') return { valid: false, message: 'Địa chỉ email không hợp lệ. Phải đúng định dạng @gmail.com!' };
        if (domain === 'gmai.com' || domain === 'gamil.com' || domain === 'gmial.com') return { valid: false, message: 'Địa chỉ email bị sai tên miền Gmail.' };
        
        return { valid: true };
    }

    /**
     * Hiển thị thông báo lỗi dưới ô input
     */
    function showFieldError(inputId, errorSpanId, message) {
        const input = document.getElementById(inputId);
        const span = document.getElementById(errorSpanId);
        if (input) input.classList.add('is-invalid');
        if (span) {
            span.innerHTML = `<i class="bi bi-exclamation-circle-fill"></i> ${message}`;
            span.style.display = 'flex';
        }
    }

    /**
     * Xóa thông báo lỗi dưới ô input
     */
    function clearFieldError(inputId, errorSpanId) {
        const input = document.getElementById(inputId);
        const span = document.getElementById(errorSpanId);
        if (input) input.classList.remove('is-invalid');
        if (span) {
            span.innerHTML = '';
            span.style.display = 'none';
        }
    }

    // ==========================================
    // 2. XỬ LÝ ẨN/HIỆN MẬT KHẨU
    // ==========================================
    function setupPasswordToggles() {
        const toggleButtons = document.querySelectorAll('.btn-toggle-pwd');
        toggleButtons.forEach(button => {
            if (button.dataset.toggleInitialized) return;
            button.dataset.toggleInitialized = 'true';

            button.addEventListener('click', () => {
                const inputId = button.getAttribute('data-target');
                const input = document.getElementById(inputId);
                if (!input) return;

                const isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');

                const icon = button.querySelector('i');
                if (icon) {
                    icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
                }
            });
        });
    }
    setupPasswordToggles();

    // ==========================================
    // 3. XỬ LÝ MODAL QUÊN MẬT KHẨU (OTP)
    // ==========================================
    const btnOpenForgot = document.getElementById('btn-open-forgot');
    const btnCloseForgot = document.getElementById('btn-close-forgot');
    const modalForgot = document.getElementById('modal-forgot-pwd');

    const step1 = document.getElementById('forgot-step-1');
    const step2 = document.getElementById('forgot-step-2');
    const step3 = document.getElementById('forgot-step-3');
    const stepSuccess = document.getElementById('forgot-step-success');

    const formStep1 = document.getElementById('forgot-form-step-1');
    const formStep2 = document.getElementById('forgot-form-step-2');
    const formStep3 = document.getElementById('forgot-form-step-3');

    const btnBackStep1 = document.getElementById('btn-back-step-1');
    const btnResendOtp = document.getElementById('btn-resend-otp');
    const btnFinishForgot = document.getElementById('btn-finish-forgot');
    const displayTargetEmail = document.getElementById('display-target-email');
    const otpDigits = document.querySelectorAll('.otp-digit');
    const stepDots = document.querySelectorAll('.step-dot');

    let countdownTimer = null;
    let savedEmail = '';
    let savedOtp = '';

    function switchStep(targetStep) {
        if (step1) step1.style.display = 'none';
        if (step2) step2.style.display = 'none';
        if (step3) step3.style.display = 'none';
        if (stepSuccess) stepSuccess.style.display = 'none';

        stepDots.forEach(dot => {
            const stepNum = parseInt(dot.getAttribute('data-step'), 10);
            dot.classList.remove('active', 'completed');
            if (stepNum === targetStep) dot.classList.add('active');
            else if (stepNum < targetStep) dot.classList.add('completed');
        });

        if (targetStep === 1 && step1) step1.style.display = 'block';
        else if (targetStep === 2 && step2) {
            step2.style.display = 'block';
            if (otpDigits[0]) otpDigits[0].focus();
        }
        else if (targetStep === 3 && step3) {
            step3.style.display = 'block';
            setupPasswordToggles();
        }
        else if (targetStep === 4 && stepSuccess) stepSuccess.style.display = 'block';
    }

    function startResendCountdown(duration = 60) {
        if (!btnResendOtp) return;
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
        let timeLeft = duration;
        btnResendOtp.disabled = true;
        btnResendOtp.innerHTML = `Gửi lại mã (<span id="resend-countdown">${timeLeft}</span>s)`;

        countdownTimer = setInterval(() => {
            timeLeft -= 1;
            const spanEl = document.getElementById('resend-countdown');
            if (spanEl) spanEl.textContent = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(countdownTimer);
                countdownTimer = null;
                btnResendOtp.disabled = false;
                btnResendOtp.textContent = 'Gửi lại mã OTP';
            }
        }, 1000);
    }

    // Tự động nhảy ô khi gõ OTP
    otpDigits.forEach((digitInput, index) => {
        digitInput.addEventListener('input', (e) => {
            const val = e.target.value.replace(/\D/g, '').slice(-1);
            e.target.value = val;

            if (val.length > 0 && index < otpDigits.length - 1) {
                otpDigits[index + 1].focus();
            }
        });

        digitInput.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                otpDigits[index - 1].focus();
            }
        });

        digitInput.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim();
            if (/^\d{6}$/.test(pasteData)) {
                pasteData.split('').forEach((char, i) => {
                    if (otpDigits[i]) otpDigits[i].value = char;
                });
                otpDigits[otpDigits.length - 1].focus();
            }
        });
    });

    function resetForgotPasswordModal() {
        if (formStep1) formStep1.reset();
        if (formStep2) formStep2.reset();
        if (formStep3) formStep3.reset();
        otpDigits.forEach(d => d.value = '');
        savedEmail = '';
        if (displayTargetEmail) displayTargetEmail.textContent = '';
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
        if (btnResendOtp) {
            btnResendOtp.disabled = true;
            btnResendOtp.innerHTML = 'Gửi lại mã (<span id="resend-countdown">60</span>s)';
        }
        switchStep(1);
    }

    if (btnOpenForgot && modalForgot) {
        btnOpenForgot.addEventListener('click', () => {
            resetForgotPasswordModal();
            modalForgot.classList.add('show');
            const emailInput = document.getElementById('forgot-email');
            if (emailInput) setTimeout(() => emailInput.focus(), 150);
        });
    }

    function closeModal() {
        if (modalForgot) {
            modalForgot.classList.remove('show');
            resetForgotPasswordModal();
        }
    }

    if (btnCloseForgot) btnCloseForgot.addEventListener('click', closeModal);
    if (btnFinishForgot) btnFinishForgot.addEventListener('click', closeModal);

    const modalAlertBox = document.getElementById('forgot-modal-alert');
    function showModalAlert(message, type = 'danger') {
        if (!modalAlertBox) return;
        modalAlertBox.className = `modal-alert-box alert-${type}`;
        modalAlertBox.innerHTML = `<i class="bi ${type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill'}"></i><span>${message}</span>`;
        modalAlertBox.style.display = 'flex';
    }
    function clearModalAlert() {
        if (modalAlertBox) {
            modalAlertBox.style.display = 'none';
            modalAlertBox.innerHTML = '';
        }
    }

    // B1: Gửi OTP Quên mật khẩu
    if (formStep1) {
        formStep1.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearModalAlert();
            const emailInput = document.getElementById('forgot-email');
            savedEmail = emailInput ? emailInput.value.trim() : '';

            const check = checkEmailFormat(savedEmail);
            if (!check.valid) {
                showModalAlert(check.message);
                return;
            }

            const submitBtn = formStep1.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="auth-spinner"></span><span>Đang gửi...</span>';
            }

            try {
                const response = await fetch('/forgot-password/send-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email: savedEmail }),
                });
                const data = await response.json();

                if (response.ok && data.success) {
                    if (displayTargetEmail) displayTargetEmail.textContent = savedEmail;
                    otpDigits.forEach(d => d.value = '');
                    switchStep(2);
                    startResendCountdown();
                    showModalAlert(data.message || 'Mã xác thực OTP đã được gửi đến email của bạn.', 'success');
                } else {
                    showModalAlert(data.message || 'Không thể gửi mã OTP.');
                }
            } catch (err) {
                showModalAlert('Có lỗi kết nối đến máy chủ.');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    if (btnBackStep1) {
        btnBackStep1.addEventListener('click', () => {
            clearModalAlert();
            switchStep(1);
        });
    }

    if (btnResendOtp) {
        btnResendOtp.addEventListener('click', async () => {
            if (!savedEmail) return;
            clearModalAlert();
            btnResendOtp.disabled = true;
            btnResendOtp.textContent = 'Đang gửi...';

            try {
                const response = await fetch('/forgot-password/send-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email: savedEmail }),
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    showModalAlert(data.message || 'Mã OTP mới đã được gửi đến email của bạn!', 'success');
                    startResendCountdown(60);
                } else {
                    showModalAlert(data.message || 'Gửi lại mã thất bại!');
                    btnResendOtp.disabled = false;
                    btnResendOtp.textContent = 'Gửi lại mã OTP';
                }
            } catch (err) {
                showModalAlert('Có lỗi kết nối!');
                btnResendOtp.disabled = false;
                btnResendOtp.textContent = 'Gửi lại mã OTP';
            }
        });
    }

    if (formStep2) {
        formStep2.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearModalAlert();
            let otpVal = '';
            otpDigits.forEach(d => otpVal += d.value);

            if (otpVal.length < 6) {
                showModalAlert('Vui lòng nhập đủ 6 chữ số OTP.');
                return;
            }

            const submitBtn = formStep2.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="auth-spinner"></span><span>Đang xác thực...</span>';
            }

            try {
                const response = await fetch('/forgot-password/verify-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email: savedEmail, otp: otpVal }),
                });
                const data = await response.json();

                if (response.ok && data.success) {
                    savedOtp = otpVal;
                    switchStep(3);
                } else {
                    showModalAlert(data.message || 'Mã OTP không chính xác.');
                }
            } catch (err) {
                showModalAlert('Có lỗi xảy ra khi xác thực OTP!');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    if (formStep3) {
        formStep3.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearModalAlert();
            const newPwdEl = document.getElementById('new-password');
            const confirmNewPwdEl = document.getElementById('confirm-new-password');
            const newPwd = newPwdEl ? newPwdEl.value : '';
            const confirmNewPwd = confirmNewPwdEl ? confirmNewPwdEl.value : '';

            if (newPwd.length < 8 || !/[^a-zA-Z0-9]/.test(newPwd)) {
                showModalAlert('Mật khẩu mới phải từ 8 ký tự trở lên và chứa ít nhất 1 ký tự đặc biệt.');
                return;
            }
            if (newPwd !== confirmNewPwd) {
                showModalAlert('Mật khẩu xác nhận không khớp.');
                return;
            }

            const submitBtn = formStep3.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="auth-spinner"></span><span>Đang đổi...</span>';
            }

            try {
                const response = await fetch('/forgot-password/reset', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        email: savedEmail,
                        otp: savedOtp,
                        password: newPwd,
                        password_confirmation: confirmNewPwd,
                    }),
                });
                const data = await response.json();

                if (response.ok && data.success) {
                    switchStep(4);
                } else {
                    showModalAlert(data.message || 'Không thể đặt lại mật khẩu!');
                }
            } catch (err) {
                showModalAlert('Có lỗi xảy ra khi cập nhật mật khẩu!');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    // ==========================================
    // 4. XỬ LÝ GỬI OTP ĐĂNG KÝ (KHÔNG DÙNG ALERT)
    // ==========================================
    const btnSendRegOtp = document.getElementById('btn-send-reg-otp');
    const regOtpFeedback = document.getElementById('register-otp-feedback');
    let regOtpCountdownTimer = null;

    function showRegOtpFeedback(message, type = 'success') {
        if (!regOtpFeedback) return;
        const isError = type === 'error';
        regOtpFeedback.style.display = 'flex';
        regOtpFeedback.style.background = isError ? '#fef2f2' : '#ecfdf5';
        regOtpFeedback.style.color = isError ? '#991b1b' : '#065f46';
        regOtpFeedback.style.border = `1px solid ${isError ? '#fecaca' : '#a7f3d0'}`;
        regOtpFeedback.innerHTML = `<i class="bi ${isError ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill'}"></i><span>${message}</span>`;
    }

    function startRegOtpCountdown(duration = 60) {
        if (!btnSendRegOtp) return;
        if (regOtpCountdownTimer) {
            clearInterval(regOtpCountdownTimer);
            regOtpCountdownTimer = null;
        }
        let left = duration;
        btnSendRegOtp.disabled = true;
        btnSendRegOtp.textContent = `Gửi lại (${left}s)`;

        regOtpCountdownTimer = setInterval(() => {
            left -= 1;
            if (left <= 0) {
                clearInterval(regOtpCountdownTimer);
                regOtpCountdownTimer = null;
                btnSendRegOtp.disabled = false;
                btnSendRegOtp.textContent = 'Gửi mã OTP';
            } else {
                btnSendRegOtp.textContent = `Gửi lại (${left}s)`;
            }
        }, 1000);
    }

    if (btnSendRegOtp) {
        let isSendingOtp = false;

        btnSendRegOtp.addEventListener('click', async function () {
            if (isSendingOtp || this.disabled) return;

            const emailInput = document.getElementById('reg-email');
            const fullNameInput = document.getElementById('reg-fullname');
            const email = emailInput ? emailInput.value.trim() : '';
            const emailCheck = checkEmailFormat(email);

            if (!emailCheck.valid) {
                showFieldError('reg-email', 'error-reg-email', emailCheck.message);
                showRegOtpFeedback(emailCheck.message, 'error');
                if (emailInput) emailInput.focus();
                return;
            }
            clearFieldError('reg-email', 'error-reg-email');

            isSendingOtp = true;
            this.disabled = true;
            this.innerHTML = '<span class="auth-spinner"></span> Đang gửi...';

            try {
                const response = await fetch('/register/send-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        full_name: fullNameInput ? fullNameInput.value.trim() : '',
                    }),
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showRegOtpFeedback(data.message || 'Mã OTP đã được gửi đến email của bạn.', 'success');
                    startRegOtpCountdown(60);
                    const regOtpInput = document.getElementById('register-otp');
                    if (regOtpInput) regOtpInput.focus();
                } else {
                    const errMsg = data.message || 'Không thể gửi mã OTP lúc này.';
                    showRegOtpFeedback(errMsg, 'error');
                    this.disabled = false;
                    this.textContent = 'Gửi mã OTP';
                }
            } catch (err) {
                showRegOtpFeedback('Lỗi kết nối máy chủ. Vui lòng thử lại sau.', 'error');
                this.disabled = false;
                this.textContent = 'Gửi mã OTP';
            } finally {
                isSendingOtp = false;
            }
        });
    }

    // ==========================================
    // 4B. MODAL ĐIỀU KHOẢN & CHÍNH SÁCH BẢO MẬT
    // ==========================================
    const modalTerms = document.getElementById('modal-terms-policy');
    const btnOpenTerms = document.getElementById('btn-open-terms');
    const btnOpenPrivacy = document.getElementById('btn-open-privacy');
    const btnCloseTerms = document.getElementById('btn-close-terms');
    const btnCloseTermsFooter = document.getElementById('btn-close-terms-footer');
    const btnAgreeTerms = document.getElementById('btn-agree-terms');
    const acceptTermsCheckbox = document.getElementById('accept-terms');

    function openTermsModal() {
        if (modalTerms) modalTerms.classList.add('show');
    }
    function closeTermsModal() {
        if (modalTerms) modalTerms.classList.remove('show');
    }

    if (btnOpenTerms) btnOpenTerms.addEventListener('click', openTermsModal);
    if (btnOpenPrivacy) btnOpenPrivacy.addEventListener('click', openTermsModal);
    if (btnCloseTerms) btnCloseTerms.addEventListener('click', closeTermsModal);
    if (btnCloseTermsFooter) btnCloseTermsFooter.addEventListener('click', closeTermsModal);
    if (btnAgreeTerms) {
        btnAgreeTerms.addEventListener('click', () => {
            if (acceptTermsCheckbox) {
                acceptTermsCheckbox.checked = true;
                clearFieldError('accept-terms', 'error-accept-terms');
            }
            closeTermsModal();
        });
    }
    if (modalTerms) {
        modalTerms.addEventListener('click', (e) => {
            if (e.target === modalTerms) closeTermsModal();
        });
    }

    // ==========================================
    // 4C. CLIENT-SIDE VALIDATION ĐĂNG NHẬP / ĐĂNG KÝ
    // ==========================================
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            let hasError = false;
            const accountEl = document.getElementById('login-account');
            const pwdEl = document.getElementById('login-password');

            clearFieldError('login-account', 'error-login-account');
            clearFieldError('login-password', 'error-login-password');

            if (!accountEl || accountEl.value.trim() === '') {
                showFieldError('login-account', 'error-login-account', 'Vui lòng nhập email hoặc tên tài khoản.');
                hasError = true;
            }
            if (!pwdEl || pwdEl.value === '') {
                showFieldError('login-password', 'error-login-password', 'Vui lòng nhập mật khẩu.');
                hasError = true;
            }
            if (hasError) e.preventDefault();
        });
    }

    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            let hasError = false;
            const fullName = document.getElementById('reg-fullname')?.value.trim() || '';
            const username = document.getElementById('reg-username')?.value.trim() || '';
            const email = document.getElementById('reg-email')?.value.trim() || '';
            const password = document.getElementById('reg-password')?.value || '';
            const confirmPassword = document.getElementById('reg-confirm-password')?.value || '';
            const otp = document.getElementById('register-otp')?.value.trim() || '';
            const accepted = acceptTermsCheckbox ? acceptTermsCheckbox.checked : true;

            clearFieldError('reg-fullname', 'error-reg-fullname');
            clearFieldError('reg-username', 'error-reg-username');
            clearFieldError('reg-email', 'error-reg-email');
            clearFieldError('reg-password', 'error-reg-password');
            clearFieldError('reg-confirm-password', 'error-reg-confirm-password');
            clearFieldError('register-otp', 'error-register-otp');
            clearFieldError('accept-terms', 'error-accept-terms');

            if (!fullName) {
                showFieldError('reg-fullname', 'error-reg-fullname', 'Vui lòng nhập họ và tên.');
                hasError = true;
            }
            if (!username) {
                showFieldError('reg-username', 'error-reg-username', 'Vui lòng nhập tên tài khoản.');
                hasError = true;
            }
            const emailCheck = checkEmailFormat(email);
            if (!emailCheck.valid) {
                showFieldError('reg-email', 'error-reg-email', emailCheck.message);
                hasError = true;
            }
            if (password.length < 8 || !/[^a-zA-Z0-9]/.test(password)) {
                showFieldError('reg-password', 'error-reg-password', 'Mật khẩu phải từ 8 ký tự và có ít nhất 1 ký tự đặc biệt.');
                hasError = true;
            }
            if (confirmPassword !== password) {
                showFieldError('reg-confirm-password', 'error-reg-confirm-password', 'Xác nhận mật khẩu không khớp.');
                hasError = true;
            }
            if (!/^\d{6}$/.test(otp)) {
                showFieldError('register-otp', 'error-register-otp', 'Vui lòng nhập đủ 6 chữ số OTP gửi về email.');
                hasError = true;
            }
            if (!accepted) {
                showFieldError('accept-terms', 'error-accept-terms', 'Bạn cần đồng ý với điều khoản sử dụng và chính sách bảo mật.');
                hasError = true;
            }

            if (hasError) e.preventDefault();
        });
    }

    // ==========================================
    // 5. CHUYỂN TAB ĐĂNG NHẬP/ĐĂNG KÝ
    // ==========================================
    const tabBtnLogin = document.getElementById('tab-btn-login');
    const tabBtnRegister = document.getElementById('tab-btn-register');
    const slideLogin = document.getElementById('slide-login');
    const slideRegister = document.getElementById('slide-register');
    const bannerSlideLogin = document.getElementById('banner-slide-login');
    const bannerSlideRegister = document.getElementById('banner-slide-register');
    const switchLinksToRegister = document.querySelectorAll('.link-switch-to-register, a[href*="register"]');
    const switchLinksToLogin = document.querySelectorAll('.link-switch-to-login, a[href*="login"]');

    function switchToTab(tabName) {
        if (!slideLogin || !slideRegister) return;

        if (tabName === 'register') {
            if (tabBtnLogin) tabBtnLogin.classList.remove('active');
            if (tabBtnRegister) tabBtnRegister.classList.add('active');
            if (bannerSlideLogin) bannerSlideLogin.classList.remove('active');
            if (bannerSlideRegister) bannerSlideRegister.classList.add('active');
            slideLogin.classList.remove('active');
            slideLogin.classList.add('slide-out-left');
            slideRegister.classList.remove('slide-out-right', 'slide-out-left');
            slideRegister.classList.add('active');
            window.history.replaceState(null, 'Tạo tài khoản mới', '/register');
        } else {
            if (tabBtnRegister) tabBtnRegister.classList.remove('active');
            if (tabBtnLogin) tabBtnLogin.classList.add('active');
            if (bannerSlideRegister) bannerSlideRegister.classList.remove('active');
            if (bannerSlideLogin) bannerSlideLogin.classList.add('active');
            slideRegister.classList.remove('active');
            slideRegister.classList.add('slide-out-right');
            slideLogin.classList.remove('slide-out-left', 'slide-out-right');
            slideLogin.classList.add('active');
            window.history.replaceState(null, 'Đăng nhập tài khoản', '/login');
        }
    }

    if (tabBtnLogin) tabBtnLogin.addEventListener('click', (e) => { e.preventDefault(); switchToTab('login'); });
    if (tabBtnRegister) tabBtnRegister.addEventListener('click', (e) => { e.preventDefault(); switchToTab('register'); });
    switchLinksToRegister.forEach(link => link.addEventListener('click', (e) => { e.preventDefault(); switchToTab('register'); }));
    switchLinksToLogin.forEach(link => link.addEventListener('click', (e) => { e.preventDefault(); switchToTab('login'); }));

    if (window.location.pathname.includes('register')) {
        switchToTab('register');
    } else {
        switchToTab('login');
    }
});