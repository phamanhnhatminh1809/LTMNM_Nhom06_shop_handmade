<?php
/** @var array $p */
$discount = 0;
if (!empty($p['old_price']) && $p['old_price'] > $p['price']) {
    $discount = round((1 - $p['price'] / $p['old_price']) * 100);
}
?>
<?php
$discount = 0;
if (!empty($p['old_price']) && $p['old_price'] > $p['price']) {
    $discount = round((1 - $p['price'] / $p['old_price']) * 100);
}
?>
<div class="product-card">
    <a href="?route=product-detail&id=<?= $p['id'] ?>" class="product-card__image">
        <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
        <?php if ($discount > 0): ?>
            <span class="badge badge--sale">-<?= $discount ?>%</span>
        <?php endif; ?>
    </a>
    <div class="product-card__body">
        <span class="product-card__cat"><?= htmlspecialchars($p['category']) ?></span>
        <h3 class="product-card__name">
            <a href="?route=product-detail&id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
        </h3>
        <div class="product-card__rating">
            ⭐ <?= $p['rating'] ?> · Đã bán <?= $p['sold'] ?>
        </div>
        <div class="product-card__price">
            <span class="price"><?= number_format($p['price'], 0, ',', '.') ?>đ</span>
            <?php if (!empty($p['old_price'])): ?>
                <span class="price-old"><?= number_format($p['old_price'], 0, ',', '.') ?>đ</span>
            <?php endif; ?>
        </div>
        <a href="?route=product-detail&id=<?= $p['id'] ?>" class="btn btn--primary btn--sm">Xem chi tiết</a>
    </div>
</div>