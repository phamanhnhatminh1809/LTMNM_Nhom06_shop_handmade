<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập tài khoản - Handmade House</title>
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
                    <h2 class="banner-heading">Nơi kết tinh giá trị<br><span class="text-artisan-highlight">thủ công truyền thống</span></h2>
                    <p class="banner-desc">
                        Chào mừng bạn quay trở lại với Handmade House! Cùng khám phá những món đồ handmade xinh xắn, độc đáo và tìm thấy những sản phẩm phù hợp với phong cách của riêng bạn.
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
                            <h4>Những món đồ handmade độc đáo</h4>
                            <p>Những món đồ handmade từ gốm, da và len, được chăm chút tỉ mỉ trong từng chi tiết.</p>
                        </div>
                    </div>

                    <div class="feature-badge-item">
                        <div class="feature-badge-icon">
                            <i class="bi bi-palette-fill"></i>
                        </div>
                        <div class="feature-badge-text">
                            <h4>Trải nghiệm workshop nghệ thuật</h4>
                            <p>Giao lưu, học nghề và tự tay tạo nên những món đồ mang dấu ấn riêng.</p>
                        </div>
                    </div>

                    <div class="feature-badge-item">
                        <div class="feature-badge-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="feature-badge-text">
                            <h4>Quản lý cửa hàng đơn giản và hiệu quả</h4>
                            <p>Đăng nhập an toàn, bảo mật thông tin người dùng.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột Phải: Form Đăng nhập -->
            <div class="auth-form-col">
                <!-- Tabs chuyển đổi -->
                <div class="auth-tabs">
                    <a href="/features/auth/pages/login.php" class="auth-tab-link active">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Đăng nhập
                    </a>
                    <a href="/features/auth/pages/register.php" class="auth-tab-link">
                        <i class="bi bi-person-plus"></i>
                        Đăng ký mới
                    </a>
                </div>

                <div class="form-title-area">
                    <h1 class="form-main-title">Đăng nhập tài khoản</h1>
                    <p class="form-sub-title">Đăng nhập để mua sắm sản phẩm handmade hoặc quản lý cửa hàng.</p>
                </div>

                <form action="/features/admin/pages/dashboard.php" method="GET" class="auth-form" id="login-form">
                    <!-- Tài khoản / Email -->
                    <div class="form-group">
                        <label for="login-account" class="form-label">
                            EMAIL HOẶC TÊN TÀI KHOẢN <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon-left">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" id="login-account" name="account" class="form-control has-icon-left" placeholder="Nhập email hoặc tên tài khoản của bạn" required>
                        </div>
                    </div>

                    <!-- Mật khẩu -->
                    <div class="form-group">
                        <label for="login-password" class="form-label">
                            MẬT KHẨU <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon-left">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" id="login-password" name="password" class="form-control has-icon-left has-icon-right" placeholder="Nhập mật khẩu của bạn" required>
                            <button type="button" class="btn-toggle-pwd" data-target="login-password" title="Hiện/Ẩn mật khẩu">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Ghi nhớ & Quên mật khẩu -->
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" id="remember-me">
                            <span>Ghi nhớ đăng nhập</span>
                        </label>
                        <a href="javascript:void(0)" class="link-forgot" id="btn-open-forgot">Quên mật khẩu?</a>
                    </div>

                    <!-- Nút Đăng nhập -->
                    <button type="submit" class="btn-submit">
                        Đăng nhập
                    </button>

                    <!-- Chuyển sang đăng ký -->
                    <p class="switch-auth-link">
                        Chưa có tài khoản? <a href="/features/auth/pages/register.php">Đăng ký ngay</a>
                    </p>
                </form>
            </div>

        </div>
    </main>

    <!-- Modal Popup Quên mật khẩu -->
    <?php include __DIR__ . '/../components/forgot_password_modal.php'; ?>

    <!-- Footer dùng chung -->
    <?php include __DIR__ . '/../../../components/auth_footer.php'; ?>

    <script src="/features/auth/assets/js/auth.js"></script>
</body>

</html>