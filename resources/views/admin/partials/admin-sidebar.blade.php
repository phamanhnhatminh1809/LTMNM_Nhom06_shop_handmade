@php
    $currentMenu = $activeMenu ?? 'dashboard';
    $authUser = Auth::user();
    $roleShortLabel = match ($authUser?->role?->name) {
        'admin'           => 'Quản trị viên',
        'warehouse_staff' => 'Nhân viên kho',
        'sales_staff'     => 'Nhân viên bán hàng',
        default           => 'Quản trị viên',
    };
@endphp
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Nút bấm thu gọn / trượt ra -->
    <button type="button" class="btn-sidebar-toggle" id="btnSidebarToggle" title="Thu gọn / Mở rộng sidebar">
        <i class="bi bi-chevron-left" id="sidebarToggleIcon"></i>
    </button>

    <!-- Khung cuộn nội dung độc lập bên trong sidebar -->
    <div class="sidebar-scroll-container">
        <!-- Header Sidebar: Logo ngang -->
        <div class="sidebar-header">
            <div class="sidebar-brand-logo">
                <a href="{{ route('admin.dashboard') }}" title="Handmade House">
                    <img src="{{ asset('assets/images/logo_admin.png') }}" alt="Handmade House Logo">
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

            <!-- Nhóm HỆ THỐNG: Chỉ Quản trị viên (admin) -->
            @if(!$authUser || $authUser->isAdmin())
            <div class="nav-group">
                <div class="nav-group-title">Hệ thống</div>
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('admin.staff.index') }}" class="nav-link {{ in_array($currentMenu, ['staff', 'permissions']) ? 'active' : '' }}" title="Nhân viên">
                            <i class="bi bi-person-vcard"></i>
                            <span class="nav-text">Nhân viên</span>
                        </a>
                    </li>
                </ul>
            </div>
            @endif

            <!-- Nhóm QUẢN LÝ KHO: Admin và Nhân viên kho -->
            @if(!$authUser || $authUser->canManageWarehouse())
            <div class="nav-group">
                <div class="nav-group-title">Quản lý kho</div>
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('admin.categories.index') }}" class="nav-link {{ $currentMenu === 'categories' ? 'active' : '' }}" title="Danh mục sản phẩm">
                            <i class="bi bi-tags"></i>
                            <span class="nav-text">Danh mục sản phẩm</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.products.index') }}" class="nav-link {{ $currentMenu === 'products' ? 'active' : '' }}" title="Sản phẩm">
                            <i class="bi bi-bag"></i>
                            <span class="nav-text">Sản phẩm</span>
                        </a>
                    </li>
                </ul>
            </div>
            @endif

            <!-- Nhóm BÁN HÀNG: Admin và Nhân viên bán hàng -->
            @if(!$authUser || $authUser->canManageSales())
            <div class="nav-group">
                <div class="nav-group-title">Kinh doanh</div>
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('admin.orders.index') }}" class="nav-link {{ $currentMenu === 'orders' ? 'active' : '' }}" title="Đơn hàng">
                            <i class="bi bi-receipt-cutoff"></i>
                            <span class="nav-text">Đơn hàng</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ $currentMenu === 'reviews' ? 'active' : '' }}" title="Đánh giá">
                            <i class="bi bi-star"></i>
                            <span class="nav-text">Đánh giá</span>
                        </a>
                    </li>
                </ul>
            </div>
            @endif

            <!-- Nhóm KHÁCH HÀNG: Chỉ Quản trị viên (admin) -->
            @if(!$authUser || $authUser->isAdmin())
            <div class="nav-group">
                <div class="nav-group-title">Khách hàng</div>
                <ul class="nav-list">
                    <li>
                        <a href="{{ route('admin.customers.index') }}" class="nav-link {{ $currentMenu === 'customers' ? 'active' : '' }}" title="Khách hàng">
                            <i class="bi bi-people"></i>
                            <span class="nav-text">Khách hàng</span>
                        </a>
                    </li>
                </ul>
            </div>
            @endif
        </nav>

        <!-- Footer Sidebar -->
        <div class="sidebar-footer">
            <a href="{{ route('admin.profile') }}" class="admin-profile-card {{ request()->routeIs('admin.profile*') ? 'admin-profile-card--active' : '' }}" title="Chỉnh sửa thông tin cá nhân" style="text-decoration: none; color: inherit; cursor: pointer;">
                <div class="profile-avatar">
                    <i class="bi bi-person"></i>
                </div>
                <div class="profile-info">
                    <div class="profile-name">{{ $authUser->full_name ?? 'Quản trị viên' }}</div>
                    <div class="profile-role">{{ $roleShortLabel }}</div>
                </div>
            </a>

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
    </div>
</aside>