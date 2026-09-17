<?php
$currentRoute = $currentRoute ?? ($_GET['route'] ?? 'home');
$cartCount    = $cartCount ?? 0;
$searchQuery  = htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<header class="site-header">
    <div class="container site-header__inner">
        <a href="?route=home" class="logo">
            <span class="logo__icon">🧶</span>
            <span class="logo__text">Shop<strong>Handmade</strong></span>
        </a>

        <nav class="main-nav" id="mainNav">
            <ul>
                <li><a href="?route=home" class="<?= $currentRoute === 'home' ? 'active' : '' ?>">Trang chủ</a></li>
                <li><a href="?route=products" class="<?= in_array($currentRoute, ['products', 'product-detail']) ? 'active' : '' ?>">Sản phẩm</a></li>
                <li><a href="?route=about" class="<?= $currentRoute === 'about' ? 'active' : '' ?>">Giới thiệu</a></li>
                <li><a href="?route=contact" class="<?= $currentRoute === 'contact' ? 'active' : '' ?>">Liên hệ</a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <form action="" method="GET" class="search-form">
                <input type="hidden" name="route" value="products">
                <input type="text" name="q" placeholder="Tìm sản phẩm..." value="<?= $searchQuery ?>">
                <button type="submit" aria-label="Tìm kiếm">🔍</button>
            </form>

            <a href="?route=cart" class="cart-btn" aria-label="Giỏ hàng">
                🛒 <span class="cart-count"><?= (int) $cartCount ?></span>
            </a>
        </div>

        <button class="menu-toggle" aria-label="Menu"
                onclick="document.getElementById('mainNav').classList.toggle('main-nav--open')">☰</button>
    </div>
</header>