document.addEventListener('DOMContentLoaded', () => {
    // Xử lý nút ẩn / hiện mật khẩu
    function setupPasswordToggles() {
        const toggleButtons = document.querySelectorAll('.btn-toggle-pwd');
        toggleButtons.forEach(button => {
            // Tránh gán trùng lặp event listener
            if (button.dataset.toggleInitialized) return;
            button.dataset.toggleInitialized = 'true';

            button.addEventListener('click', () => {
                const inputId = button.getAttribute('data-target');
                const input = document.getElementById(inputId);

                if (!input) return;

                const isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');

                // Đổi icon con mắt
                const icon = button.querySelector('i');
                if (icon) {
                    icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
                }
            });
        });
    }
    setupPasswordToggles();

    // Xử lý Modal Quên mật khẩu
    const btnOpenForgot = document.getElementById('btn-open-forgot');
    const btnCloseForgot = document.getElementById('btn-close-forgot');
    const modalForgot = document.getElementById('modal-forgot-pwd');

    // Các bước
    const step1 = document.getElementById('forgot-step-1');
    const step2 = document.getElementById('forgot-step-2');
    const step3 = document.getElementById('forgot-step-3');
    const stepSuccess = document.getElementById('forgot-step-success');

    // Forms
    const formStep1 = document.getElementById('forgot-form-step-1');
    const formStep2 = document.getElementById('forgot-form-step-2');
    const formStep3 = document.getElementById('forgot-form-step-3');

    // Nút điều hướng & OTP
    const btnBackStep1 = document.getElementById('btn-back-step-1');
    const btnResendOtp = document.getElementById('btn-resend-otp');
    const btnFinishForgot = document.getElementById('btn-finish-forgot');
    const resendCountdown = document.getElementById('resend-countdown');
    const displayTargetEmail = document.getElementById('display-target-email');
    const otpDigits = document.querySelectorAll('.otp-digit');
    const stepDots = document.querySelectorAll('.step-dot');

    let countdownTimer = null;
    let savedEmail = '';

    function switchStep(targetStep) {
        if (step1) step1.style.display = 'none';
        if (step2) step2.style.display = 'none';
        if (step3) step3.style.display = 'none';
        if (stepSuccess) stepSuccess.style.display = 'none';

        stepDots.forEach(dot => {
            const stepNum = parseInt(dot.getAttribute('data-step'), 10);
            dot.classList.remove('active', 'completed');
            if (stepNum === targetStep) {
                dot.classList.add('active');
            } else if (stepNum < targetStep) {
                dot.classList.add('completed');
            }
        });

        if (targetStep === 1 && step1) {
            step1.style.display = 'block';
        } else if (targetStep === 2 && step2) {
            step2.style.display = 'block';
            if (otpDigits[0]) otpDigits[0].focus();
        } else if (targetStep === 3 && step3) {
            step3.style.display = 'block';
            setupPasswordToggles();
        } else if (targetStep === 4 && stepSuccess) {
            stepSuccess.style.display = 'block';
        }
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
            if (spanEl) {
                spanEl.textContent = timeLeft;
            } else {
                btnResendOtp.innerHTML = `Gửi lại mã (<span id="resend-countdown">${timeLeft}</span>s)`;
            }

            if (timeLeft <= 0) {
                clearInterval(countdownTimer);
                countdownTimer = null;
                btnResendOtp.disabled = false;
                btnResendOtp.textContent = 'Gửi lại mã OTP';
            }
        }, 1000);
    }

    // Tự động nhảy ô khi gõ 6 số OTP
    otpDigits.forEach((digitInput, index) => {
        digitInput.addEventListener('input', (e) => {
            const val = e.target.value;
            // Chỉ giữ 1 ký tự số
            if (val.length > 0) {
                if (index < otpDigits.length - 1) {
                    otpDigits[index + 1].focus();
                }
            }
        });

        digitInput.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                otpDigits[index - 1].focus();
            }
        });

        // Cho phép dán cả mã 6 chữ số
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

    // Hàm reset form Quên mật khẩu về trạng thái ban đầu
    function resetForgotPasswordModal() {
        // Reset dữ liệu các form
        if (formStep1) formStep1.reset();
        if (formStep2) formStep2.reset();
        if (formStep3) formStep3.reset();

        // Xóa trắng toàn bộ ô OTP
        otpDigits.forEach(d => d.value = '');

        // Xóa biến tạm
        savedEmail = '';
        if (displayTargetEmail) displayTargetEmail.textContent = '';

        // Hủy bộ đếm countdown
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
        if (btnResendOtp) {
            btnResendOtp.disabled = true;
            btnResendOtp.innerHTML = 'Gửi lại mã (<span id="resend-countdown">60</span>s)';
        }

        // Đưa về Bước 1
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

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    let savedOtp = '';

    // Hàm hiển thị thông báo lỗi / thành công trong Modal Quên mật khẩu
    const modalAlertBox = document.getElementById('forgot-modal-alert');
    function showModalAlert(message, type = 'danger') {
        if (!modalAlertBox) return;
        modalAlertBox.className = `modal-alert-box alert-${type}`;
        modalAlertBox.innerHTML = `
            <i class="bi ${type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill'}"></i>
            <span>${message}</span>
        `;
        modalAlertBox.style.display = 'flex';
    }

    function clearModalAlert() {
        if (modalAlertBox) {
            modalAlertBox.style.display = 'none';
            modalAlertBox.innerHTML = '';
        }
    }

    // Tiện ích hiển thị / xóa lỗi từng trường trong Modal Quên mật khẩu
    function showModalFieldError(inputId, errorSpanId, message) {
        const input = document.getElementById(inputId);
        const span = document.getElementById(errorSpanId);
        if (input) input.classList.add('is-invalid');
        if (span) {
            span.innerHTML = `<i class="bi bi-exclamation-circle-fill"></i> ${message}`;
            span.style.display = 'flex';
        }
    }

    function clearModalFieldError(inputId, errorSpanId) {
        const input = document.getElementById(inputId);
        const span = document.getElementById(errorSpanId);
        if (input) input.classList.remove('is-invalid');
        if (span) {
            span.innerHTML = '';
            span.style.display = 'none';
        }
    }

    // Tự động xóa lỗi khi người dùng nhập liệu trong Modal Quên mật khẩu
    // Hàm kiểm tra định dạng email
    function checkEmailFormat(emailStr) {
        if (!emailStr) return { valid: false, message: 'Vui lòng nhập địa chỉ email.' };

        // Kiểm tra cấu trúc email cơ bản
        const baseRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!baseRegex.test(emailStr)) {
            return { valid: false, message: 'Địa chỉ email không đúng định dạng (ví dụ: name@gmail.com).' };
        }

        const parts = emailStr.split('@');
        const domain = parts[1].toLowerCase();

        // Bắt lỗi gõ thiếu chữ
        if (domain === 'gmail.co') {
            return { valid: false, message: 'Địa chỉ email không hợp lệ. Phải đúng định dạng @gmail.com!' };
        }
        if (domain === 'gmai.com' || domain === 'gamil.com' || domain === 'gmial.com') {
            return { valid: false, message: 'Địa chỉ email bị sai tên miền Gmail (ví dụ: @gmail.com).' };
        }
        if (domain === 'yahoo.co' || domain === 'yaho.com') {
            return { valid: false, message: 'Địa chỉ email không hợp lệ. Có phải bạn muốn nhập @yahoo.com không?' };
        }
        if (domain === 'hotmail.co') {
            return { valid: false, message: 'Địa chỉ email không hợp lệ. Có phải bạn muốn nhập @hotmail.com không?' };
        }

        return { valid: true };
    }

    const forgotEmailInput = document.getElementById('forgot-email');
    if (forgotEmailInput) {
        forgotEmailInput.addEventListener('input', () => {
            clearModalFieldError('forgot-email', 'error-forgot-email');
            clearModalAlert();
        });

        // Báo lỗi ngay khi rời chuột khỏi ô email trong Modal Quên mật khẩu
        forgotEmailInput.addEventListener('blur', () => {
            const val = forgotEmailInput.value.trim();
            if (val) {
                const check = checkEmailFormat(val);
                if (!check.valid) {
                    showModalFieldError('forgot-email', 'error-forgot-email', check.message);
                } else {
                    clearModalFieldError('forgot-email', 'error-forgot-email');
                }
            }
        });
    }

    otpDigits.forEach(d => {
        d.addEventListener('input', () => {
            otpDigits.forEach(el => el.classList.remove('is-invalid'));
            clearModalFieldError('', 'error-forgot-otp');
            clearModalAlert();
        });
    });

    const newPwdInput = document.getElementById('new-password');
    if (newPwdInput) {
        newPwdInput.addEventListener('input', () => {
            clearModalFieldError('new-password', 'error-new-password');
            clearModalAlert();
        });
    }

    const confirmNewPwdInput = document.getElementById('confirm-new-password');
    if (confirmNewPwdInput) {
        confirmNewPwdInput.addEventListener('input', () => {
            clearModalFieldError('confirm-new-password', 'error-confirm-new-password');
            clearModalAlert();
        });
    }

    // B1: Nhập email -> gửi OTP qua backend
    if (formStep1) {
        formStep1.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearModalAlert();
            clearModalFieldError('forgot-email', 'error-forgot-email');

            const emailInput = document.getElementById('forgot-email');
            savedEmail = emailInput ? emailInput.value.trim() : '';

            // Kiểm tra rỗng và định dạng email
            const check = checkEmailFormat(savedEmail);
            if (!check.valid) {
                showModalFieldError('forgot-email', 'error-forgot-email', check.message);
                emailInput?.focus();
                return;
            }

            const submitBtn = formStep1.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="auth-spinner"></span><span>Đang gửi mã OTP...</span>';
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

                    if (data.dev_otp) {
                        console.log('%c[Shop Handmade] MÃ OTP KIỂM THỬ: ' + data.dev_otp, 'font-size: 16px; font-weight: bold; color: #e76f51;');
                    }
                } else {
                    showModalAlert(data.message || 'Không thể gửi mã OTP. Vui lòng kiểm tra lại email!');
                }
            } catch (err) {
                console.error(err);
                showModalAlert('Có lỗi kết nối đến máy chủ. Vui lòng thử lại sau!');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    // Nút quay lại B1
    if (btnBackStep1) {
        btnBackStep1.addEventListener('click', () => {
            clearModalAlert();
            clearModalFieldError('forgot-email', 'error-forgot-email');
            clearModalFieldError('', 'error-forgot-otp');
            switchStep(1);
        });
    }

    // Nút gửi lại mã OTP
    if (btnResendOtp) {
        btnResendOtp.addEventListener('click', async () => {
            if (!savedEmail) return;
            clearModalAlert();
            clearModalFieldError('', 'error-forgot-otp');
            btnResendOtp.disabled = true;
            btnResendOtp.textContent = 'Đang gửi mã mới...';

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
                    showModalAlert('Mã OTP mới đã được gửi lại vào email của bạn!', 'success');
                    startResendCountdown(60);
                    if (data.dev_otp) {
                        console.log('%c[Shop Handmade] MÃ OTP MỚI: ' + data.dev_otp, 'font-size: 16px; font-weight: bold; color: #e76f51;');
                    }
                } else {
                    showModalAlert(data.message || 'Gửi lại mã OTP thất bại!');
                    btnResendOtp.disabled = false;
                    btnResendOtp.textContent = 'Gửi lại mã OTP';
                }
            } catch (err) {
                console.error(err);
                showModalAlert('Có lỗi kết nối khi gửi lại mã OTP!');
                btnResendOtp.disabled = false;
                btnResendOtp.textContent = 'Gửi lại mã OTP';
            }
        });
    }

    // B2: Nhập OTP -> Gọi API xác thực OTP
    if (formStep2) {
        formStep2.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearModalAlert();
            clearModalFieldError('', 'error-forgot-otp');

            let otpVal = '';
            otpDigits.forEach(d => otpVal += d.value);

            if (otpVal.length < 6) {
                otpDigits.forEach(d => {
                    if (!d.value) d.classList.add('is-invalid');
                });
                showModalFieldError('', 'error-forgot-otp', 'Vui lòng nhập đầy đủ 6 chữ số mã xác thực OTP.');
                const firstEmpty = Array.from(otpDigits).find(d => !d.value);
                if (firstEmpty) firstEmpty.focus();
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
                    otpDigits.forEach(d => d.classList.add('is-invalid'));
                    showModalFieldError('', 'error-forgot-otp', data.message || 'Mã OTP không chính xác hoặc đã hết hạn.');
                }
            } catch (err) {
                console.error(err);
                showModalAlert('Có lỗi xảy ra khi xác thực OTP!');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    // B3: Đổi mật khẩu mới qua API
    if (formStep3) {
        formStep3.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearModalAlert();
            clearModalFieldError('new-password', 'error-new-password');
            clearModalFieldError('confirm-new-password', 'error-confirm-new-password');

            const newPwdEl = document.getElementById('new-password');
            const confirmNewPwdEl = document.getElementById('confirm-new-password');
            const newPwd = newPwdEl ? newPwdEl.value : '';
            const confirmNewPwd = confirmNewPwdEl ? confirmNewPwdEl.value : '';

            let isValid = true;

            const specialCharRegex = /[^a-zA-Z0-9]/;

            if (!newPwd) {
                showModalFieldError('new-password', 'error-new-password', 'Vui lòng nhập mật khẩu mới.');
                isValid = false;
            } else if (newPwd.length < 8) {
                showModalFieldError('new-password', 'error-new-password', 'Mật khẩu mới phải có tối thiểu 8 ký tự.');
                isValid = false;
            } else if (!specialCharRegex.test(newPwd)) {
                showModalFieldError('new-password', 'error-new-password', 'Mật khẩu mới phải chứa ít nhất 1 ký tự đặc biệt (ví dụ: @, #, $, %, !...).');
                isValid = false;
            }

            if (!confirmNewPwd) {
                showModalFieldError('confirm-new-password', 'error-confirm-new-password', 'Vui lòng xác nhận mật khẩu mới.');
                isValid = false;
            } else if (newPwd && newPwd !== confirmNewPwd) {
                showModalFieldError('confirm-new-password', 'error-confirm-new-password', 'Mật khẩu xác nhận không khớp.');
                isValid = false;
            }

            if (!isValid) {
                const firstInvalid = formStep3.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.focus();
                return;
            }

            const submitBtn = formStep3.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="auth-spinner"></span><span>Đang đổi mật khẩu...</span>';
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
                console.error(err);
                showModalAlert('Có lỗi xảy ra khi cập nhật mật khẩu mới!');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    // Xử lý Modal Xem Điều Khoản & Chính Sách Bảo Mật
    const modalTerms = document.getElementById('modal-terms-policy');
    const btnOpenTerms = document.getElementById('btn-open-terms');
    const btnOpenPrivacy = document.getElementById('btn-open-privacy');
    const btnCloseTerms = document.getElementById('btn-close-terms');
    const btnCloseTermsFooter = document.getElementById('btn-close-terms-footer');
    const btnAgreeTerms = document.getElementById('btn-agree-terms');
    const acceptTermsCheckbox = document.getElementById('accept-terms');

    function openTermsModal() {
        if (modalTerms) {
            modalTerms.classList.add('show');
        }
    }

    function closeTermsModal() {
        if (modalTerms) {
            modalTerms.classList.remove('show');
        }
    }

    if (btnOpenTerms) btnOpenTerms.addEventListener('click', openTermsModal);
    if (btnOpenPrivacy) btnOpenPrivacy.addEventListener('click', openTermsModal);
    if (btnCloseTerms) btnCloseTerms.addEventListener('click', closeTermsModal);
    if (btnCloseTermsFooter) btnCloseTermsFooter.addEventListener('click', closeTermsModal);

    if (modalTerms) {
        modalTerms.addEventListener('click', (e) => {
            if (e.target === modalTerms) closeTermsModal();
        });
    }

    if (btnAgreeTerms) {
        btnAgreeTerms.addEventListener('click', () => {
            if (acceptTermsCheckbox) {
                acceptTermsCheckbox.checked = true;
            }
            closeTermsModal();
        });
    }

    // Hiệu ứng Spinner & Kiểm tra Client-side form Đăng nhập
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        function showLoginError(inputId, errorSpanId, message) {
            const input = document.getElementById(inputId);
            const span = document.getElementById(errorSpanId);
            if (input) input.classList.add('is-invalid');
            if (span) {
                span.innerHTML = `<i class="bi bi-exclamation-circle-fill"></i> ${message}`;
                span.style.display = 'flex';
            }
        }

        function clearLoginError(inputId, errorSpanId) {
            const input = document.getElementById(inputId);
            const span = document.getElementById(errorSpanId);
            if (input) input.classList.remove('is-invalid');
            if (span) {
                span.innerHTML = '';
                span.style.display = 'none';
            }
        }

        // Tự động xóa lỗi khi người dùng gõ phím ở form đăng nhập
        const loginAccountInput = document.getElementById('login-account');
        const loginPasswordInput = document.getElementById('login-password');

        if (loginAccountInput) {
            loginAccountInput.addEventListener('input', () => {
                clearLoginError('login-account', 'error-login-account');
            });

            // Khi người dùng bấm chuột ra ngoài (blur), nếu có gõ ký tự '@' mà email sai định dạng thì báo ngay
            loginAccountInput.addEventListener('blur', () => {
                const val = loginAccountInput.value.trim();
                if (val && val.includes('@')) {
                    const check = checkEmailFormat(val);
                    if (!check.valid) {
                        showLoginError('login-account', 'error-login-account', check.message);
                    } else {
                        clearLoginError('login-account', 'error-login-account');
                    }
                }
            });
        }

        if (loginPasswordInput) {
            loginPasswordInput.addEventListener('input', () => {
                clearLoginError('login-password', 'error-login-password');
            });
        }

        loginForm.addEventListener('submit', (e) => {
            let isValid = true;

            const accountVal = loginAccountInput?.value?.trim();
            const passwordVal = loginPasswordInput?.value;

            if (!accountVal) {
                showLoginError('login-account', 'error-login-account', 'Vui lòng nhập email hoặc tên tài khoản của bạn.');
                isValid = false;
            } else if (accountVal.includes('@')) {
                // Kiểm tra định dạng email chuẩn và phát hiện lỗi gõ nhầm
                const check = checkEmailFormat(accountVal);
                if (!check.valid) {
                    showLoginError('login-account', 'error-login-account', check.message);
                    isValid = false;
                } else {
                    clearLoginError('login-account', 'error-login-account');
                }
            } else {
                clearLoginError('login-account', 'error-login-account');
            }

            if (!passwordVal) {
                showLoginError('login-password', 'error-login-password', 'Vui lòng nhập mật khẩu.');
                isValid = false;
            } else {
                clearLoginError('login-password', 'error-login-password');
            }

            if (!isValid) {
                e.preventDefault();
                const firstInvalid = loginForm.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
                return;
            }

            const btnSubmit = document.getElementById('btn-submit-login');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="auth-spinner"></span><span>Đang đăng nhập...</span>';
            }
        });
    }

    // Hiệu ứng Spinner & Kiểm tra Client-side form Đăng ký
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        // Hàm hiển thị lỗi dưới input
        function showFieldError(inputId, errorSpanId, message) {
            const input = document.getElementById(inputId);
            const span = document.getElementById(errorSpanId);
            if (input) input.classList.add('is-invalid');
            if (span) {
                span.innerHTML = `<i class="bi bi-exclamation-circle-fill"></i> ${message}`;
                span.style.display = 'flex';
            }
        }

        // Hàm xóa lỗi dưới input
        function clearFieldError(inputId, errorSpanId) {
            const input = document.getElementById(inputId);
            const span = document.getElementById(errorSpanId);
            if (input) input.classList.remove('is-invalid');
            if (span) {
                span.innerHTML = '';
                span.style.display = 'none';
            }
        }

        // Tự động xóa lỗi khi người dùng gõ phím
        const fieldMappings = [
            { id: 'reg-fullname', errId: 'error-reg-fullname' },
            { id: 'reg-username', errId: 'error-reg-username' },
            { id: 'reg-email', errId: 'error-reg-email' },
            { id: 'reg-phone', errId: 'error-reg-phone' },
            { id: 'reg-password', errId: 'error-reg-password' },
            { id: 'reg-confirm-password', errId: 'error-reg-confirm-password' },
            { id: 'accept-terms', errId: 'error-accept-terms', event: 'change' },
        ];

        fieldMappings.forEach(item => {
            const el = document.getElementById(item.id);
            if (el) {
                el.addEventListener(item.event || 'input', () => {
                    clearFieldError(item.id, item.errId);
                });
            }
        });

        registerForm.addEventListener('submit', (e) => {
            let isValid = true;

            // Kiểm tra Họ và tên
            const fullName = document.getElementById('reg-fullname');
            if (!fullName || !fullName.value.trim()) {
                showFieldError('reg-fullname', 'error-reg-fullname', 'Vui lòng nhập họ và tên của bạn.');
                isValid = false;
            } else {
                clearFieldError('reg-fullname', 'error-reg-fullname');
            }

            // Kiểm tra Tên tài khoản
            const username = document.getElementById('reg-username');
            if (!username || !username.value.trim()) {
                showFieldError('reg-username', 'error-reg-username', 'Vui lòng nhập tên tài khoản.');
                isValid = false;
            } else {
                clearFieldError('reg-username', 'error-reg-username');
            }

            // Kiểm tra Email
            const email = document.getElementById('reg-email');
            const emailVal = email?.value?.trim();
            const checkEmail = checkEmailFormat(emailVal);
            if (!checkEmail.valid) {
                showFieldError('reg-email', 'error-reg-email', checkEmail.message);
                isValid = false;
            } else {
                clearFieldError('reg-email', 'error-reg-email');
            }

            // Kiểm tra Mật khẩu tối thiểu 8 ký tự và có ít nhất 1 ký tự đặc biệt
            const pwd = document.getElementById('reg-password');
            const specialCharRegex = /[^a-zA-Z0-9]/;
            if (!pwd || !pwd.value) {
                showFieldError('reg-password', 'error-reg-password', 'Vui lòng nhập mật khẩu.');
                isValid = false;
            } else if (pwd.value.length < 8) {
                showFieldError('reg-password', 'error-reg-password', 'Mật khẩu phải có ít nhất 8 ký tự.');
                isValid = false;
            } else if (!specialCharRegex.test(pwd.value)) {
                showFieldError('reg-password', 'error-reg-password', 'Mật khẩu phải chứa ít nhất 1 ký tự đặc biệt (ví dụ: @, #, $, %, !...).');
                isValid = false;
            } else {
                clearFieldError('reg-password', 'error-reg-password');
            }

            // Kiểm tra Xác nhận mật khẩu
            const confirmPwd = document.getElementById('reg-confirm-password');
            if (!confirmPwd || !confirmPwd.value) {
                showFieldError('reg-confirm-password', 'error-reg-confirm-password', 'Vui lòng nhập lại mật khẩu xác nhận.');
                isValid = false;
            } else if (pwd && pwd.value && pwd.value !== confirmPwd.value) {
                showFieldError('reg-confirm-password', 'error-reg-confirm-password', 'Mật khẩu xác nhận không khớp.');
                isValid = false;
            } else {
                clearFieldError('reg-confirm-password', 'error-reg-confirm-password');
            }

            // Kiểm tra ô checkbox Điều khoản
            const termsChecked = document.getElementById('accept-terms')?.checked;
            if (!termsChecked) {
                showFieldError('accept-terms', 'error-accept-terms', 'Bạn vui lòng tích chọn đồng ý với Điều khoản sử dụng và Chính sách bảo mật.');
                isValid = false;
            } else {
                clearFieldError('accept-terms', 'error-accept-terms');
            }

            // Nếu có lỗi -> Ngăn submit và cuộn nhẹ đến trường lỗi đầu tiên
            if (!isValid) {
                e.preventDefault();
                const firstInvalid = registerForm.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                }
                return;
            }

            // Hợp lệ -> Bật Spinner
            const btnSubmit = document.getElementById('btn-submit-register');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="auth-spinner"></span><span>Đang tạo tài khoản...</span>';
            }
        });
    }

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
            // Chuyển Tab active
            if (tabBtnLogin) tabBtnLogin.classList.remove('active');
            if (tabBtnRegister) tabBtnRegister.classList.add('active');

            // Chuyển Banner (bên trái)
            if (bannerSlideLogin) bannerSlideLogin.classList.remove('active');
            if (bannerSlideRegister) bannerSlideRegister.classList.add('active');

            // Chuyển Form (bên phải)
            slideLogin.classList.remove('active');
            slideLogin.classList.add('slide-out-left');

            slideRegister.classList.remove('slide-out-right', 'slide-out-left');
            slideRegister.classList.add('active');

            window.history.replaceState(null, 'Tạo tài khoản mới', '/register');
            document.title = 'Tạo tài khoản mới - Handmade House';
        } else {
            // Chuyển Tab active
            if (tabBtnRegister) tabBtnRegister.classList.remove('active');
            if (tabBtnLogin) tabBtnLogin.classList.add('active');

            // Chuyển Banner (bên trái)
            if (bannerSlideRegister) bannerSlideRegister.classList.remove('active');
            if (bannerSlideLogin) bannerSlideLogin.classList.add('active');

            // Chuyển Form (bên phải)
            slideRegister.classList.remove('active');
            slideRegister.classList.add('slide-out-right');

            slideLogin.classList.remove('slide-out-left', 'slide-out-right');
            slideLogin.classList.add('active');

            window.history.replaceState(null, 'Đăng nhập tài khoản', '/login');
            document.title = 'Đăng nhập tài khoản - Handmade House';
        }
    }

    if (tabBtnLogin) {
        tabBtnLogin.addEventListener('click', (e) => {
            e.preventDefault();
            switchToTab('login');
        });
    }

    if (tabBtnRegister) {
        tabBtnRegister.addEventListener('click', (e) => {
            e.preventDefault();
            switchToTab('register');
        });
    }

    switchLinksToRegister.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            switchToTab('register');
        });
    });

    switchLinksToLogin.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            switchToTab('login');
        });
    });

    // Tự động nhận diện đường dẫn hiện tại khi load trang
    if (window.location.pathname.includes('register')) {
        switchToTab('register');
    } else {
        switchToTab('login');
    }
});
