<?php
require_once __DIR__ . '/../services/ProductService.php';

$featured   = ProductService::getFeatured();
$newest     = array_slice(ProductService::getAll(), 0, 8);
$categories = getCategories();
$banners = [
    [
        'image' => 'https://images.unsplash.com/photo-1591561954557-26941169b49e?w=1600&h=800&fit=crop',
        'title' => 'Đồ thủ công len xinh',
        'subtitle' => 'Mỗi sản phẩm là một tác phẩm nhỏ được đan tay tỉ mỉ',
        'cta' => 'Khám phá ngay',
        'link' => '?route=products',
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?w=1600&h=800&fit=crop',
        'title' => 'Khăn len mùa đông',
        'subtitle' => 'Giữ ấm cho bạn trong những ngày se lạnh',
        'cta' => 'Xem khăn len',
        'link' => '?route=products&category=khan-len',
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1615486364267-3f7a0e9c62e8?w=1600&h=800&fit=crop',
        'title' => 'Móc khóa dễ thương',
        'subtitle' => 'Làm quà tặng bạn bè, người thân cực xinh',
        'cta' => 'Xem móc khóa',
        'link' => '?route=products&category=moc-khoa',
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1562040506-a9b32cb51b94?w=1600&h=800&fit=crop',
        'title' => 'Gấu bông handmade',
        'subtitle' => 'Mềm mịn, an toàn, đáng yêu cho bé',
        'cta' => 'Xem gấu bông',
        'link' => '?route=products&category=gau-bong',
    ],
];
?>

<div class="banner-slider">
    <?php foreach ($banners as $i => $b): ?>
        <div class="banner-slide <?= $i === 0 ? 'banner-slide--active' : '' ?>">
            <img src="<?= htmlspecialchars($b['image']) ?>" alt="<?= htmlspecialchars($b['title']) ?>">
            <div class="banner-slide__content">
                <h2><?= htmlspecialchars($b['title']) ?></h2>
                <p><?= htmlspecialchars($b['subtitle']) ?></p>
                <a href="<?= $b['link'] ?>" class="btn btn--primary btn--lg"><?= htmlspecialchars($b['cta']) ?> →</a>
            </div>
        </div>
    <?php endforeach; ?>

    <button class="banner-prev" aria-label="Trước">‹</button>
    <button class="banner-next" aria-label="Sau">›</button>

    <div class="banner-dots">
        <?php foreach ($banners as $i => $b): ?>
            <button class="banner-dot <?= $i === 0 ? 'banner-dot--active' : '' ?>"
                    aria-label="Slide <?= $i + 1 ?>"></button>
        <?php endforeach; ?>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="section__header">
            <h2 class="section__title">Danh mục sản phẩm</h2>
            <a href="?route=products" class="link-more">Xem tất cả →</a>
        </div>
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