<?php
$page_title = 'Katalog Produk';
require_once 'includes/header.php';

$search = trim($_GET['search'] ?? '');
$selected_cat = isset($_GET['category']) ? $_GET['category'] : '';

if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE nama LIKE ? OR deskripsi LIKE ? OR kategori LIKE ? ORDER BY dibuat_pada DESC");
    $stmt->execute(["%{$search}%", "%{$search}%", "%{$search}%"]);
    $products = $stmt->fetchAll();
} else {
    $products = get_all_products($pdo);
}
?>

<main class="py-xl">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 48px; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 class="font-display" style="font-size: 56px; margin-bottom: 8px;">Katalog Madu Murni</h1>
                <?php if ($search): ?>
                    <p style="color: var(--on-surface-variant); font-size: 16px;">Hasil pencarian untuk "<strong><?php echo e($search); ?></strong>" — <?php echo count($products); ?> produk ditemukan</p>
                <?php else: ?>
                    <p style="color: var(--on-surface-variant); font-size: 16px;">Temukan madu hutan alami premium dari Desa BatuMekar</p>
                <?php endif; ?>
            </div>
            <form action="katalog.php" method="GET" style="display: flex; gap: 8px;">
                <input type="text" name="search" class="form-control" style="width: 280px; padding: 10px 16px; font-size: 14px;" placeholder="Cari produk..." value="<?php echo e($search); ?>">
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">search</span>
                </button>
            </form>
        </div>

        <div style="display: flex; gap: 48px;">
            <!-- Sidebar -->
            <aside style="width: 250px; flex-shrink: 0;">
                <h3 style="margin-bottom: 24px;">Kategori</h3>
                <form action="katalog.php" method="GET">
                    <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?php echo e($search); ?>">
                    <?php endif; ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php
                        $categories = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
                        foreach ($categories as $cat):
                            $cat_name = $cat['nama_kategori'];
                        ?>
                        <li style="margin-bottom: 12px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="category" value="<?php echo e($cat_name); ?>" <?php echo $selected_cat === $cat_name ? 'checked' : ''; ?> onchange="this.form.submit()"> <span><?php echo e($cat_name); ?></span>
                            </label>
                        </li>
                        <?php endforeach; ?>
                        <li class="mt-md">
                            <a href="katalog.php" style="font-size: 14px; color: var(--primary);">Reset Filter</a>
                        </li>
                    </ul>
                </form>
            </aside>

            <!-- Grid -->
            <div style="flex-grow: 1;">
                <?php if ($selected_cat): ?>
                    <?php if ($search): ?>
                        <?php $stmt = $pdo->prepare("SELECT * FROM produk WHERE kategori = ? AND (nama LIKE ? OR deskripsi LIKE ?) ORDER BY dibuat_pada DESC");
                        $stmt->execute([$selected_cat, "%{$search}%", "%{$search}%"]); ?>
                    <?php else: ?>
                        <?php $stmt = $pdo->prepare("SELECT * FROM produk WHERE kategori = ? ORDER BY dibuat_pada DESC");
                        $stmt->execute([$selected_cat]); ?>
                    <?php endif; ?>
                    <?php $filtered = $stmt->fetchAll(); ?>
                <?php else: ?>
                    <?php $filtered = $products; ?>
                <?php endif; ?>

                <?php if (empty($filtered)): ?>
                    <div style="text-align: center; padding: 80px 20px;">
                        <span class="material-symbols-outlined" style="font-size: 64px; color: var(--outline); margin-bottom: 16px;">search_off</span>
                        <h2 style="font-size: 24px; color: var(--secondary); margin-bottom: 8px;">Produk Tidak Ditemukan</h2>
                        <p style="color: var(--on-surface-variant); margin-bottom: 24px;">Coba gunakan kata kunci lain atau reset filter.</p>
                        <a href="katalog.php" class="btn btn-primary">Reset Pencarian</a>
                    </div>
                <?php endif; ?>

                <div class="grid grid-3">
                    <?php foreach ($filtered as $product):
                        $p_sizes_katalog = get_product_sizes($pdo, $product['produk_id']);
                    ?>
                    <div class="card" style="padding: 0; overflow: hidden;">
                        <a href="produk.php?id=<?php echo e($product['produk_id']); ?>">
                            <img src="<?php echo e(img_url($product['gambar'])); ?>" alt="<?php echo e($product['nama']); ?>" style="width: 100%; height: 250px; object-fit: cover;">
                        </a>
                        <div style="padding: 24px;">
                            <h3 class="text-secondary"><a href="produk.php?id=<?php echo e($product['produk_id']); ?>"><?php echo e($product['nama']); ?></a></h3>
                            <?php if (!empty($p_sizes_katalog)): ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin: 8px 0;">
                                <?php foreach ($p_sizes_katalog as $s): ?>
                                <span style="background: #eff6ff; color: #2563eb; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;"><?php echo e($s['ukuran_ml']); ?>ml</span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <p class="text-primary" style="font-weight: 700; margin: 8px 0;"><?php echo e(format_rupiah($product['harga'])); ?></p>
                            <a href="produk.php?id=<?php echo e($product['produk_id']); ?>" class="btn btn-primary" style="width: 100%; text-decoration: none; display: flex; justify-content: center;">Lihat & Pilih Ukuran</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
