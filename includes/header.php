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

// Calculate base URL path (handles subdirectory deployment)
$prefix = base_url();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo isset($page_title) ? e($page_title) . ' | BatuMekar' : 'BatuMekar | Madu Hutan Alami Premium'; ?></title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet"/>
    <!-- Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $prefix; ?>assets/css/style.css">
</head>
<body>
<!-- Navbar -->
<nav class="navbar" style="position: relative;">
    <div class="container navbar-content">
        <div class="logo">
            <a href="<?php echo $prefix; ?>index.php" class="text-primary font-display" style="font-size: 32px; font-weight: 700; text-decoration: none;">BatuMekar</a>
        </div>
        <div class="nav-links">
            <a href="<?php echo $prefix; ?>tentang-desa.php" class="text-secondary">Tentang Kami</a>
            <a href="<?php echo $prefix; ?>blog.php" class="text-secondary">Blog</a>
            <a href="<?php echo $prefix; ?>bantuan.php" class="text-secondary">Bantuan</a>
            <a href="<?php echo $prefix; ?>katalog.php" class="text-secondary">Katalog Belanja</a>
        </div>
        <div class="nav-actions" style="display: flex; align-items: center; gap: 16px;">
            <button onclick="toggleSearch()" class="text-primary" style="background: none; border: none; padding: 0; cursor: pointer; display: flex; align-items: center;" title="Cari produk">
                <span class="material-symbols-outlined" id="search-icon">search</span>
            </button>
            <div id="search-bar" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; padding: 16px 24px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); z-index: 100; border-top: 1px solid #e2e8f0;">
                <form action="<?php echo $prefix; ?>katalog.php" method="GET" style="display: flex; gap: 8px; max-width: 600px; margin: 0 auto;">
                    <input type="text" name="search" class="form-control" style="flex: 1; padding: 12px 16px; font-size: 15px;" placeholder="Cari madu, kategori, atau deskripsi..." autocomplete="off" id="search-input">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">search</span> Cari
                    </button>
                    <button type="button" onclick="toggleSearch()" style="padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 8px; background: white; cursor: pointer; color: #64748b;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">close</span>
                    </button>
                </form>
            </div>
            <script>
            function toggleSearch() {
                const bar = document.getElementById('search-bar');
                const icon = document.getElementById('search-icon');
                const input = document.getElementById('search-input');
                if (bar.style.display === 'none') {
                    bar.style.display = 'block';
                    icon.textContent = 'close';
                    setTimeout(() => input?.focus(), 100);
                } else {
                    bar.style.display = 'none';
                    icon.textContent = 'search';
                }
            }
            document.addEventListener('click', function(e) {
                const bar = document.getElementById('search-bar');
                const icon = document.getElementById('search-icon');
                if (bar && bar.style.display !== 'none' && !e.target.closest('#search-bar') && !e.target.closest('button[onclick*=\"toggleSearch\"]') && !e.target.closest('#search-icon')) {
                    bar.style.display = 'none';
                    if (icon) icon.textContent = 'search';
                }
            });
            </script>
            
            <a href="<?php echo $prefix; ?>keranjang.php" class="text-primary" style="position: relative; text-decoration: none;">
                <span class="material-symbols-outlined">shopping_cart</span>
                <?php if ($cart_count > 0): ?>
                    <span style="position: absolute; top: -8px; right: -8px; background: var(--primary); color: white; border-radius: 50%; width: 16px; height: 16px; font-size: 10px; display: flex; align-items: center; justify-content: center; font-weight: bold;"><?php echo e($cart_count); ?></span>
                <?php endif; ?>
            </a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Logged In Links -->
                <a href="<?php echo $prefix; ?>account/dashboard.php" class="text-primary" title="Dashboard Saya" style="text-decoration: none;">
                    <span class="material-symbols-outlined">person</span>
                </a>
            <?php else: ?>
                <!-- Guest Links -->
                <a href="<?php echo $prefix; ?>login.php" class="btn btn-secondary" style="padding: 8px 16px; font-size: 14px; text-decoration: none;">Masuk</a>
                <a href="<?php echo $prefix; ?>register.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 14px; text-decoration: none;">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
