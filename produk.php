<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = get_product_by_id($pdo, $id);

if (!$product) {
    header('Location: katalog.php');
    exit;
}

$page_title = $product['name'];
require_once 'includes/header.php';
?>

<main class="py-xl">
    <div class="container">
        <div class="grid grid-2" style="align-items: start; gap: 80px;">
            <div>
                <img src="<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>" class="product-img-large">
            </div>
            <div>
                <h1 class="text-primary" style="font-size: 48px; margin-bottom: 16px;"><?php echo e($product['name']); ?></h1>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 24px;">
                    <span class="text-primary" style="font-weight: 600;">(<?php echo e($product['rating']); ?>/5)</span>
                    <span class="text-on-surface-variant">• <?php echo e($product['review_count']); ?> Review</span>
                </div>
                <div class="card" style="margin-bottom: 32px; background: var(--surface-variant);">
                    <div class="text-primary font-display" style="font-size: 32px; font-weight: 700;"><?php echo e(format_rupiah($product['price'])); ?></div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <form action="keranjang.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 16px;">Tambah ke Keranjang</button>
                    </form>
                    <a href="https://wa.me/6281234567890" target="_blank" class="btn btn-secondary" style="background: #25D366; border: none; color: white;">Chat via WhatsApp</a>
                </div>

                <div style="margin-top: 48px; border-top: 1px solid var(--outline-variant); padding-top: 24px;">
                    <h2 class="text-primary" style="margin-bottom: 16px;">Deskripsi Produk</h2>
                    <p class="text-on-surface-variant"><?php echo e($product['description']); ?></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
