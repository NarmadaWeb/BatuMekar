<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += $qty;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($page_title) ? e($page_title) . ' | Madu Batu Meka' : 'Madu Batu Meka | Artisanal Village Honey'; ?></title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet"/>
    <!-- Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo e(str_starts_with($_SERVER['PHP_SELF'], '/account/') || str_starts_with($_SERVER['PHP_SELF'], '/admin/') || str_starts_with($_SERVER['PHP_SELF'], '/reseller/') ? '../' : ''); ?>assets/css/style.css">
</head>
<body>
<!-- Navbar -->
<nav class="navbar">
    <div class="container navbar-content">
        <div class="logo">
            <a href="index.php" class="text-primary font-display" style="font-size: 32px; font-weight: 700;">Madu Batu Meka</a>
        </div>
        <div class="nav-links">
            <a href="tentang-desa.php" class="text-secondary">Our Story</a>
            <a href="blog.php" class="text-secondary">Blog</a>
            <a href="bantuan.php" class="text-secondary">Support</a>
            <a href="katalog.php" class="text-secondary">Shop</a>
        </div>
        <div class="nav-actions">
            <button class="text-primary"><span class="material-symbols-outlined">search</span></button>
            <a href="keranjang.php" class="text-primary" style="position: relative;">
                <span class="material-symbols-outlined">shopping_cart</span>
                <?php if ($cart_count > 0): ?>
                    <span style="position: absolute; top: -8px; right: -8px; background: var(--primary); color: white; border-radius: 50%; width: 16px; height: 16px; font-size: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold;"><?php echo e($cart_count); ?></span>
                <?php endif; ?>
            </a>
            <a href="account/dashboard.php" class="text-primary"><span class="material-symbols-outlined">person</span></a>
        </div>
    </div>
</nav>
