<!-- ================= TOP BAR ================= -->
<div class="top-bar" style="background-color: var(--color-primary, #792a16); color: #ffffff; width: 100%; padding: 7px 0; font-size: 13px;">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 15px; width: 100%;">
        
        <!-- Bên trái: Slogan -->
        <div style="display: flex; align-items: center; gap: 6px; opacity: 0.95;">
            <i class="bi bi-patch-check" style="font-size: 14px;"></i>
            <span>100% Đồ thủ công tinh tế · Made with love in Vietnam</span>
        </div>
        
        <!-- Bên phải: Menu người dùng -->
        <div style="display: flex; align-items: center; gap: 18px; justify-content: flex-end;">
            <a href="{{ route('about') }}" style="color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; opacity: 0.9; transition: opacity 0.2s;">
                <i class="bi bi-info-circle"></i> Giới thiệu
            </a>
            <a href="{{ route('contact') }}" style="color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; opacity: 0.9; transition: opacity 0.2s;">
                <i class="bi bi-telephone"></i> Liên hệ
            </a>
            
            @auth
                @if(Auth::user()->isStaffOrAdmin())
                    <a href="{{ route('admin.dashboard') }}" style="color: #ffdad2; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                        <i class="bi bi-speedometer2"></i> Quản trị
                    </a>
                @endif
                <a href="{{ route('orders.index') }}" style="color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; opacity: 0.9;">
                    <i class="bi bi-box-seam"></i> Đơn hàng
                </a>
                <a href="{{ route('profile.show') }}" style="color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-weight: 600;">
                    <i class="bi bi-person-circle"></i> {{ Auth::user()->full_name }}
                </a>
                <form action="{{ route('logout') }}" method="POST" style="display: inline; margin: 0;">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: #fff; cursor: pointer; font: inherit; padding: 0; display: inline-flex; align-items: center; gap: 4px; opacity: 0.85;" title="Đăng xuất">
                        <i class="bi bi-box-arrow-right"></i> Đăng xuất
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" style="color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; opacity: 0.95;">
                    <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                </a>
                <a href="{{ route('register') }}" style="color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; opacity: 0.95;">
                    <i class="bi bi-person-plus"></i> Đăng ký
                </a>
            @endauth
        </div>
        
    </div>
</div>

<!-- ================= MAIN HEADER ================= -->
<header class="header" style="background-color: #ffffff; border-bottom: 1px solid #f0eae5; padding: 14px 0; width: 100%;">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 15px; width: 100%; gap: 24px;">
        
        <!-- Bên trái: Logo -->
        <div style="display: flex; align-items: center; flex-shrink: 0;">
            <a href="{{ route('home') }}" style="display: inline-flex; align-items: center; gap: 10px; text-decoration: none;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 10px; background: var(--color-primary, #792a16); color: #ffffff; font-size: 19px; box-shadow: 0 2px 6px rgba(121,42,22,0.25);">
                    <i class="bi bi-gem"></i>
                </span>
                <span style="font-size: 22px; font-weight: 800; color: var(--color-primary, #792a16); letter-spacing: -0.5px;">
                    Shop<span style="color: var(--color-on-surface, #251914);">Handmade</span>
                </span>
            </a>
        </div>

        <!-- Ở giữa: Thanh tìm kiếm -->
        <div style="flex: 1; max-width: 560px; margin: 0 auto;">
            <form action="{{ route('products.index') }}" method="GET" style="position: relative; width: 100%; margin: 0;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm kiếm móc khóa, túi xách, gấu bông handmade..." 
                       style="width: 100%; padding: 10px 45px 10px 18px; border-radius: 24px; border: 1px solid #ebdcd7; background-color: #faf7f5; outline: none; font-size: 14px; color: var(--color-on-surface); transition: all 0.2s;"
                       onfocus="this.style.borderColor='var(--color-primary)'; this.style.backgroundColor='#ffffff'; this.style.boxShadow='0 0 0 3px rgba(121,42,22,0.1)';"
                       onblur="this.style.borderColor='#ebdcd7'; this.style.backgroundColor='#faf7f5'; this.style.boxShadow='none';">
                <button type="submit" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--color-primary, #792a16); cursor: pointer; padding: 6px 10px; font-size: 16px; display: flex; align-items: center;" title="Tìm kiếm">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>

        <!-- Bên phải: Giỏ hàng -->
        <div style="display: flex; align-items: center; gap: 16px; flex-shrink: 0;">
            <a href="{{ route('cart.index') }}" class="cart-icon" style="color: var(--color-on-surface, #251914); text-decoration: none; position: relative; display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 50%; background: #faf7f5; border: 1px solid #ebdcd7; transition: all 0.2s;"
               onmouseover="this.style.backgroundColor='#fff5f2'; this.style.borderColor='var(--color-primary)';"
               onmouseout="this.style.backgroundColor='#faf7f5'; this.style.borderColor='#ebdcd7';">
                <i class="bi bi-bag" style="font-size: 19px; color: var(--color-primary, #792a16);"></i>
                <span class="cart-count" style="position: absolute; top: -3px; right: -3px; background-color: var(--color-primary, #792a16); color: white; font-size: 11px; font-weight: 700; min-width: 18px; height: 18px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; padding: 0 4px; border: 2px solid #ffffff;">
                    @auth
                        {{ \App\Models\Cart::where('user_id', Auth::id())->first()?->total_quantity ?? 0 }}
                    @else
                        0
                    @endauth
                </span>
            </a>
        </div>
        
    </div>
</header>

<!-- ================= NAVBAR ================= -->
<nav class="navbar" style="background-color: #ffffff; border-bottom: 1px solid #f0eae5; width: 100%;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 15px; width: 100%;">
        <ul style="display: flex; justify-content: center; gap: 26px; list-style: none; margin: 0; padding: 0; font-size: 14px; font-weight: 600; overflow-x: auto; scrollbar-width: none;">
            <li>
                <a href="{{ route('home') }}" 
                   style="color: {{ request()->routeIs('home') ? 'var(--color-primary, #792a16)' : 'var(--color-on-surface, #251914)' }}; text-decoration: none; display: block; padding: 13px 4px; border-bottom: 2px solid {{ request()->routeIs('home') ? 'var(--color-primary, #792a16)' : 'transparent' }}; white-space: nowrap;">
                    Trang chủ
                </a>
            </li>
            <li>
                <a href="{{ route('products.index') }}" 
                   style="color: {{ request()->routeIs('products.index') && !request('category') ? 'var(--color-primary, #792a16)' : 'var(--color-on-surface, #251914)' }}; text-decoration: none; display: block; padding: 13px 4px; border-bottom: 2px solid {{ request()->routeIs('products.index') && !request('category') ? 'var(--color-primary, #792a16)' : 'transparent' }}; white-space: nowrap;">
                    Tất cả sản phẩm
                </a>
            </li>
            @foreach(\App\Models\Category::all() as $cat)
                <li>
                    <a href="{{ route('products.index', ['category' => $cat->slug]) }}" 
                       style="color: {{ request('category') === $cat->slug ? 'var(--color-primary, #792a16)' : 'var(--color-on-surface, #251914)' }}; text-decoration: none; display: block; padding: 13px 4px; border-bottom: 2px solid {{ request('category') === $cat->slug ? 'var(--color-primary, #792a16)' : 'transparent' }}; white-space: nowrap;">
                        {{ $cat->name }}
                    </a>
                </li>
            @endforeach
            <li>
                <a href="{{ route('about') }}" 
                   style="color: {{ request()->routeIs('about') ? 'var(--color-primary, #792a16)' : 'var(--color-on-surface, #251914)' }}; text-decoration: none; display: block; padding: 13px 4px; border-bottom: 2px solid {{ request()->routeIs('about') ? 'var(--color-primary, #792a16)' : 'transparent' }}; white-space: nowrap;">
                    Giới thiệu
                </a>
            </li>
            <li>
                <a href="{{ route('contact') }}" 
                   style="color: {{ request()->routeIs('contact') ? 'var(--color-primary, #792a16)' : 'var(--color-on-surface, #251914)' }}; text-decoration: none; display: block; padding: 13px 4px; border-bottom: 2px solid {{ request()->routeIs('contact') ? 'var(--color-primary, #792a16)' : 'transparent' }}; white-space: nowrap;">
                    Liên hệ
                </a>
            </li>
        </ul>
    </div>
</nav>