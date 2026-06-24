<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_login();

$product_id = (int)($_GET['product_id'] ?? 0);
$order_id = (int)($_GET['order_id'] ?? 0);

// Verify order is completed and contains the product
$stmt = $pdo->prepare("
    SELECT o.*, oi.produk_id 
    FROM pesanan o 
    JOIN detail_pesanan oi ON o.pesanan_id = oi.pesanan_id 
    WHERE o.pesanan_id = ? AND o.pengguna_id = ? AND oi.produk_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id'], $product_id]);
$order_item = $stmt->fetch();

if (!$order_item || strtolower($order_item['status'] ?? '') !== 'completed') {
    header('Location: orders.php');
    exit;
}

// Fetch product details
$product = get_product_by_id($pdo, $product_id);
if (!$product) {
    header('Location: orders.php');
    exit;
}

// Check if already reviewed
$stmt_check = $pdo->prepare("SELECT * FROM ulasan_produk WHERE produk_id = ? AND pengguna_id = ?");
$stmt_check->execute([$product_id, $_SESSION['user_id']]);
$existing_review = $stmt_check->fetch();

if ($existing_review) {
    header('Location: orders.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 5);
    $ulasan = trim($_POST['ulasan'] ?? '');
    
    if ($rating < 1 || $rating > 5) {
        $error = 'Rating harus bernilai antara 1 sampai 5.';
    } elseif (empty($ulasan)) {
        $error = 'Teks ulasan harus diisi.';
    } else {
        // Insert review
        $stmt_insert = $pdo->prepare("INSERT INTO ulasan_produk (produk_id, pengguna_id, rating, ulasan) VALUES (?, ?, ?, ?)");
        $stmt_insert->execute([$product_id, $_SESSION['user_id'], $rating, $ulasan]);
        
        // Recalculate average rating & total reviews for this product
        $stmt_stats = $pdo->prepare("SELECT COUNT(*) as count, AVG(rating) as average FROM ulasan_produk WHERE produk_id = ?");
        $stmt_stats->execute([$product_id]);
        $stats = $stmt_stats->fetch();
        
        $new_count = (int)$stats['count'];
        $new_avg = round((float)$stats['average'], 1);
        
        $stmt_update = $pdo->prepare("UPDATE produk SET rating = ?, jumlah_ulasan = ? WHERE produk_id = ?");
        $stmt_update->execute([$new_avg, $new_count, $product_id]);
        
        $_SESSION['success_message'] = "Terima kasih! Ulasan Anda untuk produk '{$product['nama']}' berhasil disimpan.";
        header('Location: orders.php');
        exit;
    }
}

$page_title = 'Beri Ulasan Produk';
require_once __DIR__ . '/../includes/header.php';
?>

<main style="padding: 48px 0; background: var(--background); font-family: 'Inter', sans-serif;">
    <div class="container" style="display: flex; gap: 48px; align-items: flex-start;">
        <!-- Sidebar Navigation -->
        <aside style="width: 280px; flex-shrink: 0;">
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link">
                    <span class="material-symbols-outlined">dashboard</span>
                    Dashboard
                </a>
                <a href="orders.php" class="sidebar-link active">
                    <span class="material-symbols-outlined">shopping_bag</span>
                    Pesanan Saya
                </a>
                <a href="profile.php" class="sidebar-link">
                    <span class="material-symbols-outlined">person</span>
                    Profil Saya
                </a>
                <a href="../logout.php" class="sidebar-link" style="color: var(--error) !important;">
                    <span class="material-symbols-outlined" style="color: var(--error) !important;">logout</span>
                    Keluar
                </a>
            </nav>
        </aside>

        <!-- Form Content -->
        <section style="flex-grow: 1; max-width: 600px;">
            <div style="margin-bottom: 24px;">
                <a href="orders.php" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary); font-weight: 600; text-decoration: none; font-size: 15px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">arrow_back</span> Kembali ke Pesanan Saya
                </a>
            </div>

            <div class="card" style="padding: 32px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); background: white; border: 1px solid rgba(0,0,0,0.05);">
                <h1 style="font-size: 26px; font-weight: 800; color: var(--secondary); margin: 0 0 20px 0;">Beri Ulasan Produk</h1>
                
                <!-- Product Overview -->
                <div style="display: flex; align-items: center; gap: 16px; padding: 16px; background: #fafaf9; border-radius: 12px; border: 1px solid #e7e5e4; margin-bottom: 24px;">
                    <?php if (!empty($product['gambar'])): ?>
                        <img src="<?php echo e(img_url($product['gambar'])); ?>" alt="<?php echo e($product['nama']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #d6d3d1;">
                    <?php endif; ?>
                    <div>
                        <h2 style="font-size: 16px; font-weight: 700; color: var(--secondary); margin: 0;"><?php echo e($product['nama']); ?></h2>
                        <span style="font-size: 13px; color: var(--on-surface-variant);"><?php echo e($product['kategori']); ?></span>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div style="background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; padding: 14px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined">error</span>
                        <?php echo e($error); ?>
                    </div>
                <?php endif; ?>

                <form action="review.php?product_id=<?php echo $product_id; ?>&order_id=<?php echo $order_id; ?>" method="POST" style="display: flex; flex-direction: column; gap: 24px;">
                    <!-- Star Rating Picker -->
                    <div style="text-align: center;">
                        <label style="display: block; font-size: 15px; font-weight: 700; color: var(--secondary); margin-bottom: 12px;">Berikan Rating Anda</label>
                        <div style="display: flex; justify-content: center; gap: 12px;" id="star-picker">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="material-symbols-outlined star-icon" data-value="<?php echo $i; ?>" style="font-size: 40px; color: #f59e0b; cursor: pointer; transition: transform 0.1s;">star</span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="rating-input" value="5">
                    </div>

                    <!-- Review Text Field -->
                    <div>
                        <label for="ulasan" style="display: block; font-size: 14px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;">Ulasan Anda</label>
                        <textarea name="ulasan" id="ulasan" rows="5" style="width: 100%; border: 1px solid var(--outline); border-radius: 8px; padding: 12px; font-size: 14px; font-family: inherit; line-height: 1.6; resize: vertical;" placeholder="Tuliskan pengalaman Anda menggunakan produk ini..."><?php echo e($_POST['ulasan'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="padding: 14px; font-size: 15px; font-weight: 700; justify-content: center; border-radius: 8px;">
                        Kirim Ulasan
                    </button>
                </form>
            </div>
        </section>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('#star-picker .star-icon');
    const ratingInput = document.getElementById('rating-input');
    
    function setRating(ratingValue) {
        ratingInput.value = ratingValue;
        stars.forEach(star => {
            const val = parseInt(star.getAttribute('data-value'));
            if (val <= ratingValue) {
                star.textContent = 'star';
                star.style.color = '#f59e0b';
            } else {
                star.textContent = 'star_rate'; // Outline-ish or dimmer star
                star.style.color = '#e7e5e4';
            }
        });
    }
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const val = parseInt(this.getAttribute('data-value'));
            setRating(val);
        });
        
        star.addEventListener('mouseover', function() {
            this.style.transform = 'scale(1.15)';
        });
        
        star.addEventListener('mouseout', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Set default rating
    setRating(5);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
