<!-- ================= TOP BAR ================= -->
<div class="top-bar" style="background-color: #8b3a2b; color: white; width: 100%; padding: 8px 0; font-size: 13px;">
    <div class="container top-bar__inner" style="display: flex !important; justify-content: space-between !important; align-items: center !important; max-width: 1200px !important; margin: 0 auto !important; padding: 0 15px !important; width: 100% !important;">
        
        <!-- Bên trái: Slogan -->
        <div class="top-bar__left" style="display: block;">
            <span>Handmade 100% - Made in Vietnam</span>
        </div>
        
        <!-- Bên phải: Menu người dùng -->
        <div class="top-bar__right" style="display: flex !important; align-items: center !important; gap: 15px !important; justify-content: flex-end !important;">
            <a href="{{ route('about') }}" style="color: white; text-decoration: none;">Giới thiệu</a>
            <span class="top-bar__sep">|</span>
            <a href="{{ route('contact') }}" style="color: white; text-decoration: none;">Liên hệ</a>
            <span class="top-bar__sep">|</span>
            
            @auth
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" style="color: white; text-decoration: none;"><strong>Quản trị</strong></a>
                    <span class="top-bar__sep">|</span>
                @endif
                <a href="{{ route('orders.index') }}" style="color: white; text-decoration: none;">Đơn hàng</a>
                <span class="top-bar__sep">|</span>
                <a href="{{ route('profile.show') }}" style="color: white; text-decoration: none;">{{ Auth::user()->full_name }}</a>
                <span class="top-bar__sep">|</span>
                <form action="{{ route('logout') }}" method="POST" style="display:inline; margin: 0;">
                    @csrf
                    <button type="submit" style="background:none; border:none; color:white; cursor:pointer; font:inherit; padding: 0;">Đăng xuất</button>
                </form>
            @else
                <a href="{{ route('login') }}" style="color: white; text-decoration: none;">Đăng nhập</a>
                <span class="top-bar__sep">|</span>
                <a href="{{ route('register') }}" style="color: white; text-decoration: none;">Đăng ký</a>
            @endauth
        </div>
        
    </div>
</div>

<!-- ================= MAIN HEADER ================= -->
<header class="header" style="background-color: #fff; border-bottom: 1px solid #eee; padding: 15px 0; width: 100%;">
    <div class="container header__inner" style="display: flex !important; justify-content: space-between !important; align-items: center !important; max-width: 1200px !important; margin: 0 auto !important; padding: 0 15px !important; width: 100% !important; gap: 20px !important;">
        
        <!-- Bên trái: Logo -->
        <div style="display: flex; align-items: center; gap: 15px; flex-shrink: 0;">
            <a href="{{ route('home') }}" class="logo" style="font-size: 24px; font-weight: 800; color: #8b3a2b; text-decoration: none; white-space: nowrap;">
                ShopHandmade
            </a>
        </div>

        <!-- Ở giữa: Thanh tìm kiếm (Sửa name="q" để khớp với Controller) -->
        <div class="header__search" style="flex: 1; max-width: 600px; margin: 0 auto;">
            <form action="{{ route('products.index') }}" method="GET" style="position: relative; width: 100%; margin: 0;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm kiếm sản phẩm handmade..." 
                       style="width: 100%; padding: 10px 40px 10px 15px; border-radius: 20px; border: 1px solid #f0dcd6; background-color: #fffaf8; outline: none; font-size: 14px;">
                <button type="submit" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #8b3a2b; cursor: pointer; padding: 5px; display: flex;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Bên phải: Giỏ hàng (Dùng SVG thay cho thẻ i) -->
        <div class="header__actions" style="display: flex; align-items: center; gap: 20px; flex-shrink: 0; padding-right: 10px;">
            <a href="{{ route('cart.index') }}" class="cart-icon" style="color: #333; text-decoration: none; position: relative; display: flex; align-items: center;">
                <!-- Icon Giỏ hàng SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                </svg>
                <!-- Badge đếm số lượng -->
                <span class="cart-count" style="position: absolute; top: -6px; right: -8px; background-color: #d9534f; color: white; font-size: 10px; font-weight: bold; padding: 1px 5px; border-radius: 50%; border: 2px solid #fff;">
                    {{-- Biến đếm giỏ hàng thật --}}
                    0
                </span>
            </a>
        </div>
        
    </div>
</header>

<!-- ================= NAVBAR ================= -->
<nav class="navbar" style="background-color: #fff; border-bottom: 1px solid #eee; width: 100%;">
    <div class="container" style="max-width: 1200px !important; margin: 0 auto !important; padding: 0 15px !important; width: 100% !important;">
        <ul class="navbar__list" style="display: flex !important; justify-content: center !important; gap: 30px !important; list-style: none !important; margin: 0 !important; padding: 0 !important; font-size: 15px; font-weight: 500;">
            <li><a href="{{ route('home') }}" style="color: #8b3a2b; text-decoration: none; display: block; padding: 15px 0; border-bottom: 2px solid #8b3a2b;">Trang chủ</a></li>
            <li><a href="{{ route('products.index') }}" style="color: #333; text-decoration: none; display: block; padding: 15px 0;">Tất cả sản phẩm</a></li>
            <li><a href="{{ route('products.index', ['category' => 'tui-xach']) }}" style="color: #333; text-decoration: none; display: block; padding: 15px 0;">Túi xách</a></li>
            <li><a href="{{ route('products.index', ['category' => 'moc-khoa']) }}" style="color: #333; text-decoration: none; display: block; padding: 15px 0;">Móc khóa</a></li>
            <li><a href="{{ route('products.index', ['category' => 'trang-suc']) }}" style="color: #333; text-decoration: none; display: block; padding: 15px 0;">Trang sức</a></li>
            <li><a href="{{ route('products.index', ['category' => 'gau-bong']) }}" style="color: #333; text-decoration: none; display: block; padding: 15px 0;">Gấu bông</a></li>
            <li><a href="{{ route('products.index', ['category' => 'khan-len-non']) }}" style="color: #333; text-decoration: none; display: block; padding: 15px 0;">Khăn len + Nón</a></li>
            <li><a href="{{ route('products.index', ['category' => 'hoa-handmade']) }}" style="color: #333; text-decoration: none; display: block; padding: 15px 0;">Hoa handmade</a></li>
            <li><a href="{{ route('about') }}" style="color: #333; text-decoration: none; display: block; padding: 15px 0;">Giới thiệu</a></li>
            <li><a href="{{ route('contact') }}" style="color: #333; text-decoration: none; display: block; padding: 15px 0;">Liên hệ</a></li>
        </ul>
    </div>
</nav>