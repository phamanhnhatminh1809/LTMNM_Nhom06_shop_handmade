<div class="top-bar">
    <div class="container top-bar__inner">
        <div class="top-bar__left">
            <span>Handmade 100% - Made in Vietnam</span>
        </div>
        <div class="top-bar__right">
            <a href="{{ route('about') }}">Giới thiệu</a>
            <span class="top-bar__sep">|</span>
            <a href="{{ route('contact') }}">Liên hệ</a>
            <span class="top-bar__sep">|</span>
            @auth
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}"><strong>Quản trị</strong></a>
                    <span class="top-bar__sep">|</span>
                @endif
                <a href="{{ route('orders.index') }}">Đơn hàng</a>
                <span class="top-bar__sep">|</span>
                <a href="{{ route('profile.show') }}">{{ Auth::user()->full_name }}</a>
                <span class="top-bar__sep">|</span>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" style="background:none;border:none;color:inherit;cursor:pointer;font:inherit;">Đăng xuất</button>
                </form>
            @else
                <a href="{{ route('login') }}">Đăng nhập</a>
                <span class="top-bar__sep">|</span>
                <a href="{{ route('register') }}">Đăng ký</a>
            @endauth
        </div>
    </div>
</div>