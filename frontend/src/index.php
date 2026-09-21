<?php
/**
 * Entry point - Shop Handmade FE
 * Chạy: php -S localhost:8000 (từ thư mục frontend/src)
 */
header('Content-Type: text/html; charset=utf-8');

// Lấy route hiện tại
$route = $_GET['route'] ?? 'home';

// Nạp router
require_once __DIR__ . '/routes/router.php';

// Biến truyền cho layout
$currentRoute = $route;
$cartCount = 0;
$pageTitle = 'Shop Handmade';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Đồ thủ công xinh xắn</title>
    <meta name="description" content="Shop Handmade - Đồ thủ công len, móc khóa, túi xách handmade dễ thương.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/tokens.css">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/layout.css">
    <link rel="stylesheet" href="assets/css/components.css">
</head>
<body>

    <?php include __DIR__ . '/components/Header.php'; ?>
    <?php include __DIR__ . '/components/Navbar.php'; ?>

    <main class="main-content">
        <?php renderRoute($route); ?>
    </main>

    <?php include __DIR__ . '/components/Footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>