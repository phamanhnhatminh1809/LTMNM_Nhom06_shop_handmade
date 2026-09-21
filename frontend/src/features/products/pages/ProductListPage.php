<?php
require_once __DIR__ . '/../services/ProductService.php';

$categorySlug = $_GET['category'] ?? '';
$keyword      = trim($_GET['q'] ?? '');

if ($keyword !== '') {
    $products = ProductService::search($keyword);
} elseif ($categorySlug !== '') {
    $products = ProductService::getByCategory($categorySlug);
} else {
    $products = ProductService::getAll();
}

$categories = getCategories();
$total = count($products);
?>

<section class="page-header">
    <div class="container">
        <h1>Danh sách sản phẩm</h1>
        <p>
            <?php if ($keyword !== ''): ?>
                Kết quả cho "<strong><?= htmlspecialchars($keyword) ?></strong>": <?= $total ?> sản phẩm
            <?php else: ?>
                Khám phá <?= $total ?> sản phẩm handmade xinh xắn
            <?php endif; ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container product-layout">
        <aside class="sidebar">
            <div class="filter-box">
                <h3>Danh mục</h3>
                <ul class="filter-list">
                    <li><a href="?route=products" class="<?= $categorySlug === '' ? 'active' : '' ?>">Tất cả</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="?route=products&category=<?= $cat['slug'] ?>"
                               class="<?= $categorySlug === $cat['slug'] ? 'active' : '' ?>">
                                <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="filter-box">
                <h3>Khoảng giá</h3>
                <ul class="filter-list">
                    <li><a href="#">Dưới 100.000đ</a></li>
                    <li><a href="#">100.000đ - 200.000đ</a></li>
                    <li><a href="#">200.000đ - 500.000đ</a></li>
                    <li><a href="#">Trên 500.000đ</a></li>
                </ul>
            </div>
            <div class="filter-box">
                <h3>Đánh giá</h3>
                <ul class="filter-list">
                    <li><a href="#">⭐⭐⭐⭐⭐ (5 sao)</a></li>
                    <li><a href="#">⭐⭐⭐⭐ (4 sao)</a></li>
                    <li><a href="#">⭐⭐⭐ (3 sao)</a></li>
                </ul>
            </div>
        </aside>

        <div class="product-main">
            <div class="toolbar">
                <span>Hiển thị <strong><?= $total ?></strong> sản phẩm</span>
                <select class="sort-select">
                    <option>Mặc định</option>
                    <option>Giá tăng dần</option>
                    <option>Giá giảm dần</option>
                    <option>Mới nhất</option>
                    <option>Bán chạy</option>
                </select>
            </div>

            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <div class="empty-state__icon">🔍</div>
                    <h3>Không tìm thấy sản phẩm</h3>
                    <p>Hãy thử từ khóa khác hoặc xem tất cả sản phẩm.</p>
                </div>
            <?php else: ?>
                <div class="product-grid product-grid--3">
                    <?php foreach ($products as $p): ?>
                        <?php include __DIR__ . '/../components/ProductCard.php'; ?>
                    <?php endforeach; ?>
                </div>
                <nav class="pagination">
                    <a href="#" class="active">1</a>
                    <a href="#">2</a>
                    <a href="#">3</a>
                    <a href="#">→</a>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</section>