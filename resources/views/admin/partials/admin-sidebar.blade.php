@php
    $currentMenu = $activeMenu ?? 'dashboard';
@endphp
<aside class="admin-sidebar">
    <!-- Header Sidebar: Chỉ có hình Logo bo góc -->
    <div class="sidebar-header">
        <div class="sidebar-brand-logo">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('images/logo_admin.png') }}" alt="Handmade House Logo" onerror="this.src='{{ asset('images/logo.png') }}'">
            </a>
        </div>
    </div>

    <!-- Danh sách Menu điều hướng -->
    <nav class="sidebar-nav">
        <!-- Menu Tổng quan -->
        <div class="nav-single-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ $currentMenu === 'dashboard' ? 'active' : '' }}" title="Tổng quan">
                <i class="bi bi-grid-1x2"></i>
                <span class="nav-text">Tổng quan</span>
            </a>
        </div>

        <!-- Nhóm HỆ THỐNG -->
        <div class="nav-group">
            <div class="nav-group-title">HỆ THỐNG</div>
            <ul class="nav-list">
                <li>
                    <a href="#" class="nav-link {{ $currentMenu === 'staff' ? 'active' : '' }}" title="Nhân viên">
                        <i class="bi bi-person-vcard"></i>
                        <span class="nav-text">Nhân viên</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link {{ $currentMenu === 'permissions' ? 'active' : '' }}" title="Phân quyền">
                        <i class="bi bi-shield-lock"></i>
                        <span class="nav-text">Phân quyền</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Nhóm QUẢN LÝ KHO -->
        <div class="nav-group">
            <div class="nav-group-title">QUẢN LÝ KHO</div>
            <ul class="nav-list">
                <li>
                    <a href="{{ route('admin.products.index') }}" class="nav-link {{ $currentMenu === 'products' ? 'active' : '' }}" title="Sản phẩm">
                        <i class="bi bi-bag"></i>
                        <span class="nav-text">Sản phẩm</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link {{ $currentMenu === 'warehouse' ? 'active' : '' }}" title="Kho hàng">
                        <i class="bi bi-box-seam"></i>
                        <span class="nav-text">Kho hàng</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link {{ $currentMenu === 'orders' ? 'active' : '' }}" title="Đơn hàng">
                        <i class="bi bi-receipt-cutoff"></i>
                        <span class="nav-text">Đơn hàng</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Nhóm TRẢI NGHIỆM -->
        <div class="nav-group">
            <div class="nav-group-title">TRẢI NGHIỆM</div>
            <ul class="nav-list">
                <li>
                    <a href="#" class="nav-link {{ $currentMenu === 'workshop' ? 'active' : '' }}" title="Workshop">
                        <i class="bi bi-calendar-event"></i>
                        <span class="nav-text">Workshop</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="nav-link {{ $currentMenu === 'customers' ? 'active' : '' }}" title="Khách hàng">
                        <i class="bi bi-people"></i>
                        <span class="nav-text">Khách hàng</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Nhóm BÁO CÁO THỐNG KÊ -->
        <div class="nav-group">
            <div class="nav-group-title">BÁO CÁO THỐNG KÊ</div>
            <ul class="nav-list">
                <li>
                    <a href="#" class="nav-link {{ $currentMenu === 'reports' ? 'active' : '' }}" title="Báo cáo">
                        <i class="bi bi-bar-chart-line"></i>
                        <span class="nav-text">Báo cáo</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Footer Sidebar -->
    <div class="sidebar-footer">
        <div class="admin-profile-card">
            <div class="profile-avatar">
                <i class="bi bi-person"></i>
            </div>
            <div class="profile-info">
                <div class="profile-name">{{ Auth::user()->full_name ?? 'Quản trị viên' }}</div>
                <div class="profile-role">Quản trị viên</div>
            </div>
        </div>

        <div class="sidebar-actions">
            <a href="{{ route('home') }}" class="action-link" title="Xem trang chủ">
                <i class="bi bi-house"></i>
                <span class="nav-text">Trang chủ</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" id="admin-logout-form" style="display:none;">
                @csrf
            </form>
            <a href="javascript:void(0)" onclick="document.getElementById('admin-logout-form').submit()" class="action-link text-danger" title="Đăng xuất">
                <i class="bi bi-box-arrow-left"></i>
                <span class="nav-text">Đăng xuất</span>
            </a>
        </div>
    </div>
</aside>
