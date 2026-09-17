<?php
/**
 * Router - Định tuyến đơn giản qua ?route=...
 * Cách dùng: index.php?route=home, ?route=products, ?route=product-detail&id=1
 */

function renderRoute(string $route): void
{
    $base = __DIR__ . '/../features/products/pages/';

    switch ($route) {
        case 'home':
        case '':
            include $base . 'HomePage.php';
            break;

        case 'products':
            include $base . 'ProductListPage.php';
            break;

        case 'product-detail':
            include $base . 'ProductDetailPage.php';
            break;

        case 'about':
            echo '<section class="section"><div class="container">';
            echo '<h1>Giới thiệu</h1>';
            echo '<p>Shop Handmade - Đồ thủ công len xinh cho ngày thêm yêu.</p>';
            echo '</div></section>';
            break;

        case 'contact':
            echo '<section class="section"><div class="container">';
            echo '<h1>Liên hệ</h1>';
            echo '<p>Hotline: 0123 456 789 - Email: hello@shophandmade.vn</p>';
            echo '</div></section>';
            break;

        case 'login':
        case 'register':
        case 'cart':
            echo '<section class="section"><div class="container">';
            echo '<h1>Chức năng "' . htmlspecialchars($route) . '" đang phát triển</h1>';
            echo '<a href="?route=home" class="btn btn--primary" style="margin-top:16px;">Về trang chủ</a>';
            echo '</div></section>';
            break;

        default:
            http_response_code(404);
            echo '<section class="section"><div class="container empty-state">';
            echo '<div class="empty-state__icon">🔍</div>';
            echo '<h3>Không tìm thấy trang</h3>';
            echo '<a href="?route=home" class="btn btn--primary" style="margin-top:16px;">Về trang chủ</a>';
            echo '</div></section>';
            break;
    }
}