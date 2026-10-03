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

                // Đổi icon con mắt Bootstrap Icons
                const icon = button.querySelector('i');
                if (icon) {
                    icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
                }
            });
        });
    }
    setupPasswordToggles();

    // Xử lý Modal Quên mật khẩu đa bước
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

    if (modalForgot) {
        modalForgot.addEventListener('click', (e) => {
            if (e.target === modalForgot) closeModal();
        });
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    let savedOtp = '';

    // Submit Bước 1: Nhập email -> gửi OTP qua backend
    if (formStep1) {
        formStep1.addEventListener('submit', async (e) => {
            e.preventDefault();
            const emailInput = document.getElementById('forgot-email');
            savedEmail = emailInput ? emailInput.value.trim() : '';
            if (!savedEmail) return;

            const submitBtn = formStep1.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Đang gửi mã OTP...</span>';
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
                    alert(data.message || 'Không thể gửi mã OTP. Vui lòng thử lại!');
                }
            } catch (err) {
                console.error(err);
                alert('Có lỗi kết nối đến máy chủ. Vui lòng thử lại sau!');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    // Nút quay lại Bước 1
    if (btnBackStep1) {
        btnBackStep1.addEventListener('click', () => {
            switchStep(1);
        });
    }

    // Nút gửi lại mã OTP
    if (btnResendOtp) {
        btnResendOtp.addEventListener('click', async () => {
            if (!savedEmail) return;
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
                    alert('Mã OTP mới đã được gửi lại tới email của bạn!');
                    startResendCountdown(60);
                    if (data.dev_otp) {
                        console.log('%c[Shop Handmade] MÃ OTP MỚI: ' + data.dev_otp, 'font-size: 16px; font-weight: bold; color: #e76f51;');
                    }
                } else {
                    alert(data.message || 'Gửi lại mã thất bại!');
                    btnResendOtp.disabled = false;
                    btnResendOtp.textContent = 'Gửi lại mã OTP';
                }
            } catch (err) {
                console.error(err);
                alert('Có lỗi kết nối khi gửi lại mã OTP!');
                btnResendOtp.disabled = false;
                btnResendOtp.textContent = 'Gửi lại mã OTP';
            }
        });
    }

    // Submit Bước 2: Nhập OTP -> Gọi API xác thực OTP
    if (formStep2) {
        formStep2.addEventListener('submit', async (e) => {
            e.preventDefault();
            let otpVal = '';
            otpDigits.forEach(d => otpVal += d.value);

            if (otpVal.length < 6) {
                alert('Vui lòng nhập đủ 6 chữ số mã xác thực OTP!');
                return;
            }

            const submitBtn = formStep2.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Đang xác thực...</span>';
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
                    alert(data.message || 'Mã OTP không hợp lệ hoặc đã hết hạn!');
                }
            } catch (err) {
                console.error(err);
                alert('Có lỗi xảy ra khi xác thực OTP!');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    // Submit Bước 3: Đổi mật khẩu mới qua API
    if (formStep3) {
        formStep3.addEventListener('submit', async (e) => {
            e.preventDefault();
            const newPwd = document.getElementById('new-password').value;
            const confirmNewPwd = document.getElementById('confirm-new-password').value;

            if (newPwd.length < 8) {
                alert('Mật khẩu mới phải có tối thiểu 8 ký tự!');
                return;
            }

            if (newPwd !== confirmNewPwd) {
                alert('Mật khẩu xác nhận không khớp! Vui lòng kiểm tra lại.');
                return;
            }

            const submitBtn = formStep3.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Đang đổi mật khẩu...</span>';
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
                    alert(data.message || 'Không thể đặt lại mật khẩu!');
                }
            } catch (err) {
                console.error(err);
                alert('Có lỗi xảy ra khi cập nhật mật khẩu mới!');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    // Hiệu ứng chuyển động giữa Đăng nhập và Đăng ký
    const navLinks = document.querySelectorAll('.auth-tab-link, .switch-auth-link a');
    const authFormCol = document.querySelector('.auth-form-col');

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const targetUrl = this.getAttribute('href');
            // Nếu là trang khác và link hợp lệ
            if (targetUrl && targetUrl !== '#' && !targetUrl.startsWith('javascript') && !this.classList.contains('active')) {
                if (authFormCol) {
                    e.preventDefault();
                    // Hiệu ứng fade mờ nhẹ 90ms
                    authFormCol.style.transition = 'opacity 0.09s ease';
                    authFormCol.style.opacity = '0.35';
                    setTimeout(() => {
                        window.location.href = targetUrl;
                    }, 85);
                }
            }
        });
    });
});
