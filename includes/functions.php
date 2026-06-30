<?php
if (!function_exists('format_rupiah')) {
    function format_rupiah($number) {
        return "Rp " . number_format($number, 0, ',', '.');
    }
}

/**
 * Escapes HTML output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Gets the base URL path for the project (e.g. /batumekar/ or /)
 * Handles subdirectory deployment (XAMPP, shared hosting, etc.)
 */
function base_url($path = '') {
    static $base = null;
    if ($base === null) {
        $script_dir = dirname($_SERVER['SCRIPT_NAME']);
        // Strip known subdirectory segments to always get the project root
        $script_dir = preg_replace('#/(admin|account|reseller)(/.*)?$#', '', $script_dir);
        $base = rtrim($script_dir, '/') . '/';
    }
    return $base . ltrim($path, '/');
}

/**
 * Basic Authentication Check
 */
function require_login() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . base_url('login.php'));
        exit;
    }
}

/**
 * Admin Authentication Check
 */
function require_admin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: " . base_url('login.php'));
        exit;
    }
}

function get_all_products($pdo) {
    $stmt = $pdo->query("SELECT * FROM produk");
    return $stmt->fetchAll();
}

function get_featured_products($pdo) {
    $stmt = $pdo->query("SELECT * FROM produk WHERE unggulan = 1");
    return $stmt->fetchAll();
}

function get_product_by_id($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE produk_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function get_faqs_by_category($pdo) {
    $faqs = $pdo->query("SELECT * FROM faq")->fetchAll();
    $grouped = [];
    foreach ($faqs as $faq) {
        $grouped[$faq['kategori']][] = $faq;
    }
    return $grouped;
}

function get_all_blog_posts($pdo) {
    return $pdo->query("SELECT * FROM artikel ORDER BY dibuat_pada DESC")->fetchAll();
}

function img_url($path) {
    if (!$path) return '';
    if (str_starts_with($path, 'http')) return $path;
    return base_url($path);
}

function ensure_size_table($pdo) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS ukuran_produk (
            ukuran_id INTEGER PRIMARY KEY AUTOINCREMENT,
            produk_id INTEGER NOT NULL,
            ukuran_ml INTEGER NOT NULL,
            harga REAL DEFAULT NULL,
            stok INTEGER DEFAULT 0,
            FOREIGN KEY (produk_id) REFERENCES produk(produk_id) ON DELETE CASCADE
        )");
    } catch (PDOException $e) {}
    try {
        $pdo->exec("ALTER TABLE detail_pesanan ADD COLUMN ukuran_id INTEGER DEFAULT NULL");
    } catch (PDOException $e) {}

    // Ensure extra tables (reviews and returns)
    try {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            $pdo->exec("CREATE TABLE IF NOT EXISTS ulasan_produk (
                ulasan_id INT AUTO_INCREMENT PRIMARY KEY,
                produk_id INT NOT NULL,
                pengguna_id INT NOT NULL,
                rating INT NOT NULL,
                ulasan TEXT,
                dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (produk_id) REFERENCES produk(produk_id) ON DELETE CASCADE,
                FOREIGN KEY (pengguna_id) REFERENCES pengguna(pengguna_id) ON DELETE CASCADE
            )");
            $pdo->exec("CREATE TABLE IF NOT EXISTS pengembalian_pesanan (
                pengembalian_id INT AUTO_INCREMENT PRIMARY KEY,
                pesanan_id INT NOT NULL,
                pengguna_id INT NOT NULL,
                bukti_file VARCHAR(255) NOT NULL,
                alasan TEXT NOT NULL,
                status VARCHAR(20) DEFAULT 'Pending',
                dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE CASCADE,
                FOREIGN KEY (pengguna_id) REFERENCES pengguna(pengguna_id) ON DELETE CASCADE
            )");
            $pdo->exec("ALTER TABLE pengguna ADD COLUMN saldo DECIMAL(15,2) DEFAULT 0");
            $pdo->exec("ALTER TABLE pesanan ADD COLUMN ongkos_kirim DECIMAL(15,2) DEFAULT 0");
            $pdo->exec("ALTER TABLE pesanan ADD COLUMN latitude TEXT DEFAULT NULL");
            $pdo->exec("ALTER TABLE pesanan ADD COLUMN longitude TEXT DEFAULT NULL");
        } else {
            $pdo->exec("CREATE TABLE IF NOT EXISTS ulasan_produk (
                ulasan_id INTEGER PRIMARY KEY AUTOINCREMENT,
                produk_id INTEGER NOT NULL,
                pengguna_id INTEGER NOT NULL,
                rating INTEGER NOT NULL,
                ulasan TEXT,
                dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            $pdo->exec("CREATE TABLE IF NOT EXISTS pengembalian_pesanan (
                pengembalian_id INTEGER PRIMARY KEY AUTOINCREMENT,
                pesanan_id INTEGER NOT NULL,
                pengguna_id INTEGER NOT NULL,
                bukti_file TEXT NOT NULL,
                alasan TEXT NOT NULL,
                status TEXT DEFAULT 'Pending',
                dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            try { $pdo->exec("ALTER TABLE pengguna ADD COLUMN saldo REAL DEFAULT 0"); } catch (Exception $e) {}
            try { $pdo->exec("ALTER TABLE pesanan ADD COLUMN ongkos_kirim REAL DEFAULT 0"); } catch (Exception $e) {}
            try { $pdo->exec("ALTER TABLE pesanan ADD COLUMN latitude TEXT DEFAULT NULL"); } catch (Exception $e) {}
            try { $pdo->exec("ALTER TABLE pesanan ADD COLUMN longitude TEXT DEFAULT NULL"); } catch (Exception $e) {}
        }
    } catch (PDOException $e) {}
}

function get_product_sizes($pdo, $product_id) {
    ensure_size_table($pdo);
    $stmt = $pdo->prepare("SELECT * FROM ukuran_produk WHERE produk_id = ? ORDER BY ukuran_ml ASC");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll();
}

function get_size_by_id($pdo, $ukuran_id) {
    $stmt = $pdo->prepare("SELECT * FROM ukuran_produk WHERE ukuran_id = ?");
    $stmt->execute([$ukuran_id]);
    return $stmt->fetch();
}

function get_cart_items_with_sizes($pdo) {
    $items = [];
    if (empty($_SESSION['cart'])) return $items;

    $product_ids = [];
    $size_map = [];

    foreach ($_SESSION['cart'] as $key => $qty) {
        $parts = explode('_', $key);
        $pid = (int)$parts[0];
        $uid = isset($parts[1]) ? (int)$parts[1] : 0;
        $product_ids[] = $pid;
        $size_map[$key] = ['product_id' => $pid, 'ukuran_id' => $uid, 'qty' => $qty];
    }

    $product_ids = array_unique($product_ids);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE produk_id IN ($placeholders)");
    $stmt->execute(array_values($product_ids));
    $products = $stmt->fetchAll();
    $products_by_id = [];
    foreach ($products as $p) {
        $products_by_id[$p['produk_id']] = $p;
    }

    foreach ($size_map as $key => $info) {
        $p = $products_by_id[$info['product_id']] ?? null;
        if (!$p) continue;
        $size_info = null;
        $size_price = $p['harga'];
        $size_stok = $p['stok'];
        $size_label = '';
        if ($info['ukuran_id'] > 0) {
            $size_info = get_size_by_id($pdo, $info['ukuran_id']);
            if ($size_info) {
                $size_label = $size_info['ukuran_ml'] . ' ml';
                $size_price = $size_info['harga'] ?? $p['harga'];
                $size_stok = $size_info['stok'];
            }
        }
        $total = $size_price * $info['qty'];
        $items[] = array_merge($p, [
            'cart_key' => $key,
            'ukuran_id' => $info['ukuran_id'],
            'ukuran_label' => $size_label,
            'size_price' => $size_price,
            'qty' => $info['qty'],
            'total' => $total,
            'size_stok' => $size_stok,
        ]);
    }

    return $items;
}

function calculate_cart_totals($pdo) {
    $items = get_cart_items_with_sizes($pdo);
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['total'];
    }
    $item_count = array_sum(array_column($items, 'qty'));
    return [$items, $subtotal, $item_count];
}
?>
