@extends('layouts.auth')

@section('title', 'Tạo tài khoản mới')

@section('content')
<div class="auth-card">
    <!-- Cột Trái: Banner & chữ trên nền ảnh -->
    <div class="auth-banner-col">
        <!-- Banner dành cho Đăng nhập -->
        <div class="banner-panel-slide" id="banner-slide-login">
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

            <p class="banner-quote">"Mỗi sản phẩm là một câu chuyện sáng tạo từ trái tim người nghệ nhân"</p>
        </div>

        <!-- Banner dành cho Đăng ký -->
        <div class="banner-panel-slide active" id="banner-slide-register">
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
    </div>

    <!-- Cột Phải: Form Xác thực -->
    <div class="auth-form-col">
        <!-- Tabs chuyển đổi -->
        <div class="auth-tabs">
            <a href="{{ route('login') }}" class="auth-tab-link" data-tab="login" id="tab-btn-login">
                <i class="bi bi-box-arrow-in-right"></i>
                Đăng nhập
            </a>
            <a href="{{ route('register') }}" class="auth-tab-link active" data-tab="register" id="tab-btn-register">
                <i class="bi bi-person-plus"></i>
                Đăng ký mới
            </a>
        </div>

        @if ($errors->any())
        <div style="background-color: #ffdad6; color: #93000a; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (session('success'))
        <div style="background-color: #c0dcc3; color: #324a37; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;">
            {{ session('success') }}
        </div>
        @endif

        <!-- Khung chứa 2 form -->
        <div class="auth-forms-slider-track">
            <!-- Form đăng nhập -->
            <div class="auth-form-slide" id="slide-login">
                <div class="form-title-area">
                    <h1 class="form-main-title">Đăng nhập tài khoản</h1>
                    <p class="form-sub-title">Đăng nhập để mua sắm sản phẩm handmade hoặc quản lý cửa hàng.</p>
                </div>

                <form action="{{ route('login.submit') }}" method="POST" class="auth-form" id="login-form" novalidate>
                    @csrf
                    <!-- Tài khoản / Email -->
                    <div class="form-group">
                        <label for="login-account" class="form-label">
                            EMAIL HOẶC TÊN TÀI KHOẢN <span class="required">*</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon-left">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" id="login-account" name="account" value="{{ old('account') }}" class="form-control has-icon-left @error('account') is-invalid @enderror" placeholder="Nhập email hoặc tên tài khoản của bạn" required>
                        </div>
                        <span class="client-feedback-error" id="error-login-account" style="display: none;"></span>
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
                            <input type="password" id="login-password" name="password" class="form-control has-icon-left has-icon-right @error('password') is-invalid @enderror" placeholder="Nhập mật khẩu của bạn" required>
                            <button type="button" class="btn-toggle-pwd" data-target="login-password" title="Hiện/Ẩn mật khẩu">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <span class="client-feedback-error" id="error-login-password" style="display: none;"></span>
                    </div>

                    <!-- Ghi nhớ & Quên mật khẩu -->
                    <div class="form-options">
                        <label class="checkbox-label" for="remember-me">
                            <input type="checkbox" name="remember" id="remember-me">
                            <span>Ghi nhớ đăng nhập</span>
                        </label>
                        <a href="javascript:void(0)" class="link-forgot" id="btn-open-forgot">Quên mật khẩu?</a>
                    </div>

                    <!-- Nút Đăng nhập -->
                    <button type="submit" class="btn-submit" id="btn-submit-login">
                        <span>Đăng nhập</span>
                    </button>

                    <!-- Chuyển sang đăng ký -->
                    <p class="switch-auth-link">
                        Chưa có tài khoản? <a href="javascript:void(0)" class="link-switch-to-register">Đăng ký ngay</a>
                    </p>
                </form>
            </div>

            <!-- Form đăng ký -->
            <div class="auth-form-slide active" id="slide-register">
                <div class="form-title-area">
                    <h1 class="form-main-title">Tạo tài khoản</h1>
                    <p class="form-sub-title">Điền thông tin để đăng ký tài khoản thành viên Handmade House.</p>
                </div>

                <form action="{{ route('register.submit') }}" method="POST" class="auth-form" id="register-form" novalidate>
                    @csrf
                    <!-- Hàng Họ và tên & Tên tài khoản -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg-fullname" class="form-label">
                                HỌ VÀ TÊN <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="reg-fullname" name="full_name" value="{{ old('full_name') }}" class="form-control @error('full_name') is-invalid @enderror" placeholder="Ví dụ: Nguyễn Văn An" required>
                            </div>
                            <span class="client-feedback-error" id="error-reg-fullname" style="display: none;"></span>
                        </div>

                        <div class="form-group">
                            <label for="reg-username" class="form-label">
                                TÊN TÀI KHOẢN <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="text" id="reg-username" name="username" value="{{ old('username') }}" class="form-control @error('username') is-invalid @enderror" placeholder="Ví dụ: vanan_nguyen" required>
                            </div>
                            <span class="client-feedback-error" id="error-reg-username" style="display: none;"></span>
                        </div>
                    </div>

                    <!-- Hàng Email & Số điện thoại -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg-email" class="form-label">
                                EMAIL <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="email" id="reg-email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="nguyenvanan@gmail.com" required>
                            </div>
                            <span class="client-feedback-error" id="error-reg-email" style="display: none;"></span>
                        </div>

                        <div class="form-group">
                            <label for="reg-phone" class="form-label">
                                SỐ ĐIỆN THOẠI
                            </label>
                            <div class="input-wrapper">
                                <input type="tel" id="reg-phone" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="0901 234 567">
                            </div>
                            <span class="client-feedback-error" id="error-reg-phone" style="display: none;"></span>
                        </div>
                    </div>

                    <!-- Hàng Mật khẩu & Xác nhận mật khẩu -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg-password" class="form-label">
                                MẬT KHẨU <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="password" id="reg-password" name="password" class="form-control has-icon-right @error('password') is-invalid @enderror" placeholder="≥ 8 ký tự, gồm ký tự đặc biệt" minlength="8" required>
                                <button type="button" class="btn-toggle-pwd" data-target="reg-password" title="Hiện/Ẩn mật khẩu">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <span class="client-feedback-error" id="error-reg-password" style="display: none;"></span>
                        </div>

                        <div class="form-group">
                            <label for="reg-confirm-password" class="form-label">
                                XÁC NHẬN MẬT KHẨU <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="password" id="reg-confirm-password" name="password_confirmation" class="form-control has-icon-right" placeholder="Nhập lại mật khẩu" minlength="8" required>
                                <button type="button" class="btn-toggle-pwd" data-target="reg-confirm-password" title="Hiện/Ẩn mật khẩu">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <span class="client-feedback-error" id="error-reg-confirm-password" style="display: none;"></span>
                        </div>
                    </div>

                    <!-- Ô nhập OTP Đăng ký -->
                    <div class="form-group" style="margin-bottom: 20px; padding: 15px; background: #f9fafb; border-radius: 8px; border: 1px dashed var(--color-primary);">
                        <label class="form-label" style="color: var(--color-primary); margin-bottom: 8px;">
                            Mã xác thực email (OTP) <span class="required">*</span>
                        </label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="register-otp" name="otp" class="form-control" placeholder="Nhập 6 số OTP" maxlength="6" required style="flex: 1; letter-spacing: 2px; text-align: center; font-weight: bold;">
                            <button type="button" id="btn-send-reg-otp" class="btn-outline-custom" style="white-space: nowrap; height: 44px;">
                                Gửi mã OTP
                            </button>
                        </div>
                        <div id="register-otp-feedback" style="display: none; margin-top: 8px; padding: 8px 12px; border-radius: 6px; font-size: 12.5px; align-items: center; gap: 6px;"></div>
                        <span class="client-feedback-error" id="error-register-otp" style="display: none;"></span>
                        @error('otp')
                            <span style="color: #ef4444; font-size: 12px; margin-top: 4px; display: block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Điều khoản -->
                    <div class="form-options">
                        <label class="checkbox-label" for="accept-terms">
                            <input type="checkbox" name="terms" id="accept-terms" required>
                            <span>Tôi đồng ý với <a href="javascript:void(0)" id="btn-open-terms" style="color: var(--color-primary); text-decoration: underline; font-weight: 600;">điều khoản sử dụng</a> và <a href="javascript:void(0)" id="btn-open-privacy" style="color: var(--color-primary); text-decoration: underline; font-weight: 600;">chính sách bảo mật</a> của Handmade House. <span class="required">*</span></span>
                        </label>
                    </div>
                    <span class="client-feedback-error" id="error-accept-terms" style="display: none; margin-top: -6px; margin-bottom: 8px;"></span>

                    <!-- Nút Tạo tài khoản -->
                    <button type="submit" class="btn-submit" id="btn-submit-register">
                        <span>Tạo tài khoản</span>
                    </button>

                    <!-- Chuyển sang đăng nhập -->
                    <p class="switch-auth-link">
                        Đã có tài khoản? <a href="javascript:void(0)" class="link-switch-to-login">Đăng nhập ngay</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

@include('auth.partials.forgot-password-modal')
@include('auth.partials.terms-modal')
@endsection