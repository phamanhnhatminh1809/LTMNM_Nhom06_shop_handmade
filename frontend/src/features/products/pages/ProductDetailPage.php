<?php
require_once __DIR__ . '/../services/ProductService.php';

$id = (int) ($_GET['id'] ?? 0);
$product = ProductService::getById($id);

if (!$product) {
    echo '<div class="container section"><div class="empty-state">';
    echo '<div class="empty-state__icon">😢</div>';
    echo '<h3>Không tìm thấy sản phẩm</h3>';
    echo '<a href="?route=products" class="btn btn--primary" style="margin-top:16px;">Về danh sách sản phẩm</a>';
    echo '</div></div>';
    return;
}

$related = ProductService::getRelated($product['category_id'], $product['id']);
$discount = 0;
if (!empty($product['old_price']) && $product['old_price'] > $product['price']) {
    $discount = round((1 - $product['price'] / $product['old_price']) * 100);
}
?>

<div class="breadcrumb">
    <div class="container">
        <a href="?route=home">Trang chủ</a>
        <span>/</span>
        <a href="?route=products">Sản phẩm</a>
        <span>/</span>
        <span><?= htmlspecialchars($product['name']) ?></span>
    </div>
</div>

<section class="section">
    <div class="container product-detail">
        <div class="product-gallery">
            <div class="gallery-main">
                <img id="mainImage" src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php if ($discount > 0): ?>
                    <span class="badge badge--sale badge--lg">-<?= $discount ?>%</span>
                <?php endif; ?>
            </div>
            <div class="gallery-thumbs">
                <?php foreach ($product['gallery'] as $i => $img): ?>
                    <img src="<?= htmlspecialchars($img) ?>"
                         alt="thumb <?= $i ?>"
                         class="thumb <?= $i === 0 ? 'active' : '' ?>">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="product-info">
            <span class="product-info__cat"><?= htmlspecialchars($product['category']) ?></span>
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <div class="product-info__rating">
                ⭐ <?= $product['rating'] ?>/5 · Đã bán <?= $product['sold'] ?> · Còn <?= $product['stock'] ?> sản phẩm
            </div>
            <div class="product-info__price">
                <span class="price price--lg"><?= number_format($product['price'], 0, ',', '.') ?>đ</span>
                <?php if (!empty($product['old_price'])): ?>
                    <span class="price-old"><?= number_format($product['old_price'], 0, ',', '.') ?>đ</span>
                    <span class="discount">Tiết kiệm <?= number_format($product['old_price'] - $product['price'], 0, ',', '.') ?>đ</span>
                <?php endif; ?>
            </div>
            <p class="product-info__desc"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <div class="product-info__actions">
                <div class="qty">
                    <button type="button">−</button>
                    <input type="number" value="1" min="1" max="<?= $product['stock'] ?>">
                    <button type="button">+</button>
                </div>
                <button class="btn btn--primary btn--lg">🛒 Thêm vào giỏ</button>
                <button class="btn btn--outline btn--lg">❤️ Yêu thích</button>
            </div>
            <ul class="product-info__meta">
                <li>✅ Cam kết hàng handmade 100%</li>
                <li>🚚 Giao hàng toàn quốc</li>
                <li>🔄 Đổi trả trong 7 ngày nếu lỗi từ shop</li>
            </ul>
        </div>
    </div>
</section>

<?php if (!empty($related)): ?>
<section class="section section--alt">
    <div class="container">
        <h2 class="section__title">Sản phẩm liên quan</h2>
        <div class="product-grid">
            <?php foreach ($related as $p): ?>
                <?php include __DIR__ . '/../components/ProductCard.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>