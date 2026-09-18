<?php
/**
 * Navbar - Logo + Search to + Cart + Menu ngang
 */
$currentRoute = $currentRoute ?? ($_GET['route'] ?? 'home');
$cartCount    = $cartCount ?? 0;
$searchQuery  = htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8');
$categories   = function_exists('getCategories') ? getCategories() : [];
?>
<header class="site-header">
    <div class="container site-header__inner">

        <button class="hamburger-btn" aria-label="Menu" onclick="toggleSidebar()">
            <span></span><span></span><span></span>
        </button>

        <a href="?route=home" class="logo">
            <span class="logo__icon">🧶</span>
            <span class="logo__text">Shop<strong>Handmade</strong></span>
        </a>

        <form action="" method="GET" class="search-form search-form--lg">
            <input type="hidden" name="route" value="products">
            <input type="text" name="q" placeholder="Tìm kiếm sản phẩm handmade..."
                   value="<?= $searchQuery ?>">
            <button type="submit" aria-label="Tìm kiếm">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>
        </form>

        <a href="?route=cart" class="cart-btn" aria-label="Giỏ hàng">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <span class="cart-count"><?= (int) $cartCount ?></span>
        </a>
    </div>

    <nav class="main-nav main-nav--horizontal">
        <div class="container">
            <ul>
                <li><a href="?route=home" class="<?= $currentRoute === 'home' ? 'active' : '' ?>">Trang chủ</a></li>
                <li><a href="?route=products" class="<?= in_array($currentRoute, ['products', 'product-detail']) ? 'active' : '' ?>">Tất cả sản phẩm</a></li>
                <li><a href="?route=products&category=tui-xach">Túi xách</a></li>
                <li><a href="?route=products&category=moc-khoa">Móc khóa</a></li>
                <li><a href="?route=products&category=trang-suc">Trang sức</a></li>
                <li><a href="?route=products&category=gau-bong">Gấu bông</a></li>
                <li><a href="?route=products&category=khan-len">Khăn len</a></li>
                <li><a href="?route=about">Giới thiệu</a></li>
                <li><a href="?route=contact">Liên hệ</a></li>
            </ul>
        </div>
    </nav>
</header>

<aside class="sidebar-drawer" id="sidebarDrawer">
    <div class="sidebar-drawer__header">
        <h3>Danh mục</h3>
        <button class="sidebar-drawer__close" onclick="toggleSidebar()" aria-label="Đóng">✕</button>
    </div>

    <nav class="sidebar-drawer__nav">
        <ul>
            <li><a href="?route=home">🏠 Trang chủ</a></li>
            <li><a href="?route=products">📦 Tất cả sản phẩm</a></li>
            <li class="sidebar-drawer__divider"></li>
            <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="?route=products&category=<?= $cat['slug'] ?>">
                        <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <li class="sidebar-drawer__divider"></li>
            <li><a href="?route=about">ℹ️ Giới thiệu</a></li>
            <li><a href="?route=contact">📞 Liên hệ</a></li>
            <li><a href="?route=cart">🛒 Giỏ hàng</a></li>
        </ul>
    </nav>

    <div class="sidebar-drawer__footer">
        <p>📞 Hotline: <strong>0123 456 789</strong></p>
        <div class="sidebar-drawer__socials">
            <a href="#" aria-label="Facebook">📘</a>
            <a href="#" aria-label="Instagram">📷</a>
            <a href="#" aria-label="TikTok">🎵</a>
        </div>
    </div>
</aside>
<div class="sidebar-drawer__overlay" onclick="toggleSidebar()"></div>

<a href="#" class="zalo-float" aria-label="Chat Zalo">
    <span>Zalo</span>
</a>