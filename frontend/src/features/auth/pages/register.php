<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo tài khoản mới - Handmade House</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/features/auth/assets/css/auth.css">
</head>

<body>

    <!-- Header Logo dùng chung -->
    <?php include __DIR__ . '/../../../components/auth_header.php'; ?>

    <!-- Form chính -->
    <main class="auth-main">
        <div class="auth-card">

            <!-- Cột Trái: Banner & chữ trên nền ảnh -->
            <div class="auth-banner-col">
                <div class="banner-content-top">
                    <h2 class="banner-heading">Bắt đầu hành trình cùng<br><span class="text-artisan-highlight">nghệ thuật thủ công.</span></h2>
                    <p class="banner-desc">
                        Tạo tài khoản để thuận tiện mua sắm, theo dõi đơn hàng và khám phá những sản phẩm handmade yêu thích.
                    </p>
                </div>

                <hr class="banner-divider">

                <!-- Các thẻ tính năng nổi bật -->
                <div class="banner-features">
                    <div class="feature-badge-item">
                        <div class="feature-badge-icon">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                        <div class="feature-badge-text">
                            <h4>100% Sản phẩm được làm thủ công</h4>
                            <p>Mỗi sản phẩm đều được làm tỉ mỉ, từ chất liệu đến từng chi tiết nhỏ.</p>
                        </div>
                    </div>

                    <div class="feature-badge-item">
                        <div class="feature-badge-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="feature-badge-text">
                            <h4>Cộng đồng yêu thích đồ handmade</h4>
                            <p>Giao lưu, chia sẻ kinh nghiệm và kết nối với những người yêu handmade.</p>
                        </div>
                    </div>

                    <div class="feature-badge-item">
                        <div class="feature-badge-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="feature-badge-text">
                            <h4>Bảo mật tài khoản & hỗ trợ người dùng</h4>
                            <p>Bảo mật thông tin tài khoản và hỗ trợ người dùng khi cần.</p>
                        </div>
                    </div>
                </div>

                <p class="banner-quote">"Mỗi món đồ handmade đều có nét riêng, bởi nó được làm bằng đôi tay và sự sáng tạo"</p>
            </div>

            <!-- Cột Phải: Form Đăng ký -->
            <div class="auth-form-col">
                <!-- Tabs chuyển đổi -->
                <div class="auth-tabs">
                    <a href="/features/auth/pages/login.php" class="auth-tab-link">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Đăng nhập
                    </a>
                    <a href="/features/auth/pages/register.php" class="auth-tab-link active">
                        <i class="bi bi-person-plus"></i>
                        Đăng ký mới
                    </a>
                </div>

                <div class="form-title-area">
                    <h1 class="form-main-title">Tạo tài khoản</h1>
                    <p class="form-sub-title">Điền thông tin để đăng ký tài khoản thành viên Handmade House.</p>
                </div>

                <form action="/features/auth/pages/login.php" method="GET" class="auth-form" id="register-form">
                    <!-- Hàng 1: Họ và tên & Tên tài khoản -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg-fullname" class="form-label">
                                HỌ VÀ TÊN <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="reg-fullname" name="full_name" class="form-control" placeholder="Ví dụ: Nguyễn Văn An" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reg-username" class="form-label">
                                TÊN TÀI KHOẢN <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="reg-username" name="username" class="form-control" placeholder="Ví dụ: vanan_nguyen" required>
                            </div>
                        </div>
                    </div>

                    <!-- Hàng 2: Email & Số điện thoại -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg-email" class="form-label">
                                EMAIL <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="email" id="reg-email" name="email" class="form-control" placeholder="nguyenvanan@gmail.com" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reg-phone" class="form-label">
                                SỐ ĐIỆN THOẠI <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="tel" id="reg-phone" name="phone" class="form-control" placeholder="0901 234 567" required>
                            </div>
                        </div>
                    </div>

                    <!-- Hàng 3: Mật khẩu & Xác nhận mật khẩu -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg-password" class="form-label">
                                MẬT KHẨU <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="password" id="reg-password" name="password" class="form-control has-icon-right" placeholder="Tối thiểu 8 ký tự" minlength="8" required>
                                <button type="button" class="btn-toggle-pwd" data-target="reg-password" title="Hiện/Ẩn mật khẩu">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reg-confirm-password" class="form-label">
                                XÁC NHẬN MẬT KHẨU <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="password" id="reg-confirm-password" name="confirm_password" class="form-control has-icon-right" placeholder="Nhập lại mật khẩu" minlength="8" required>
                                <button type="button" class="btn-toggle-pwd" data-target="reg-confirm-password" title="Hiện/Ẩn mật khẩu">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Điều khoản -->
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" id="accept-terms" required>
                            <span>Tôi đồng ý với <a href="#" style="color: var(--color-primary); text-decoration: underline;">điều khoản sử dụng</a> và <a href="#" style="color: var(--color-primary); text-decoration: underline;">chính sách bảo mật</a> của hệ thống Handmade House.</span>
                        </label>
                    </div>

                    <!-- Nút Tạo tài khoản -->
                    <button type="submit" class="btn-submit">
                        Tạo tài khoản
                    </button>

                    <!-- Chuyển sang đăng nhập -->
                    <p class="switch-auth-link">
                        Đã có tài khoản? <a href="/features/auth/pages/login.php">Đăng nhập ngay</a>
                    </p>
                </form>
            </div>

        </div>
    </main>

    <!-- Footer dùng chung -->
    <?php include __DIR__ . '/../../../components/auth_footer.php'; ?>

    <script src="/features/auth/assets/js/auth.js"></script>
</body>

</html>