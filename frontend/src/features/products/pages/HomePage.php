<?php
require_once __DIR__ . '/../services/ProductService.php';

$featured   = ProductService::getFeatured();
$newest     = array_slice(ProductService::getAll(), 0, 8);
$categories = getCategories();
?>

<section class="hero">
    <div class="container hero__inner">
        <div class="hero__text">
            <h1>Đồ thủ công <span>len xinh</span> cho ngày thêm yêu</h1>
            <p>Mỗi sản phẩm được đan tay tỉ mỉ, mang theo hơi ấm và tình cảm của người làm.</p>
            <a href="?route=products" class="btn btn--primary btn--lg">Khám phá ngay →</a>
        </div>
        <div class="hero__image">
            <img src="https://picsum.photos/seed/hero-shop/700/500" alt="Shop Handmade">
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section__title">Danh mục sản phẩm</h2>
        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
                <a href="?route=products&category=<?= $cat['slug'] ?>" class="category-card">
                    <div class="category-card__icon"><?= $cat['icon'] ?></div>
                    <span><?= htmlspecialchars($cat['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title">⭐ Sản phẩm nổi bật</h2>
            <a href="?route=products" class="link-more">Xem tất cả →</a>
        </div>
        <div class="product-grid">
            <?php foreach ($featured as $p): ?>
                <?php include __DIR__ . '/../components/ProductCard.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title">🆕 Sản phẩm mới</h2>
            <a href="?route=products" class="link-more">Xem tất cả →</a>
        </div>
        <div class="product-grid">
            <?php foreach ($newest as $p): ?>
                <?php include __DIR__ . '/../components/ProductCard.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container features">
        <div class="feature">
            <div class="feature__icon">🚚</div>
            <h3>Miễn phí vận chuyển</h3>
            <p>Đơn từ 300.000đ</p>
        </div>
        <div class="feature">
            <div class="feature__icon">💯</div>
            <h3>Hàng chính hãng</h3>
            <p>100% handmade</p>
        </div>
        <div class="feature">
            <div class="feature__icon">🔄</div>
            <h3>Đổi trả 7 ngày</h3>
            <p>Nếu lỗi từ shop</p>
        </div>
        <div class="feature">
            <div class="feature__icon">💬</div>
            <h3>Hỗ trợ 24/7</h3>
            <p>Hotline luôn mở</p>
        </div>
    </div>
</section>