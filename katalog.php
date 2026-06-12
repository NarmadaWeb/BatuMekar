<?php
$page_title = 'Katalog Produk';
require_once 'includes/header.php';
$products = get_all_products($pdo);
?>

<main class="py-xl">
    <div class="container">
        <h1 class="font-display" style="font-size: 56px; margin-bottom: 48px;">Katalog Madu Murni</h1>

        <div style="display: flex; gap: 48px;">
            <!-- Sidebar -->
            <aside style="width: 250px; flex-shrink: 0;">
                <h3 style="margin-bottom: 24px;">Kategori</h3>
                <form action="katalog.php" method="GET">
                    <ul style="list-style: none; padding: 0;">
                        <?php
                        $categories = ['Multiflora', 'Kaliandra', 'Rambutan', 'Hutan', 'Kelengkeng'];
                        $selected_cat = isset($_GET['category']) ? $_GET['category'] : '';
                        foreach ($categories as $cat): ?>
                        <li style="margin-bottom: 12px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="category" value="<?php echo e($cat); ?>" <?php echo $selected_cat === $cat ? 'checked' : ''; ?> onchange="this.form.submit()"> <span><?php echo e($cat); ?></span>
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
                <div class="grid grid-3">
                    <?php
                    if ($selected_cat) {
                        $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ?");
                        $stmt->execute([$selected_cat]);
                        $filtered_products = $stmt->fetchAll();
                    } else {
                        $filtered_products = $products;
                    }
                    foreach ($filtered_products as $product): ?>
                    <div class="card" style="padding: 0; overflow: hidden;">
                        <a href="produk.php?id=<?php echo e($product['id']); ?>">
                            <img src="<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>" style="width: 100%; height: 250px; object-fit: cover;">
                        </a>
                        <div style="padding: 24px;">
                            <h3 class="text-secondary"><a href="produk.php?id=<?php echo e($product['id']); ?>"><?php echo e($product['name']); ?></a></h3>
                            <p class="text-primary" style="font-weight: 700; margin: 8px 0;"><?php echo e(format_rupiah($product['price'])); ?></p>
                            <form action="keranjang.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                                <button type="submit" class="btn btn-primary" style="width: 100%;">Beli</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
