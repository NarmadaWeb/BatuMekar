<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/functions.php';
require_once 'config/db.php';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = (int)($_POST['product_id'] ?? 0);
    $ukuran_id = (int)($_POST['ukuran_id'] ?? 0);

    if ($action === 'add' && $product_id > 0) {
        $cart_key = $product_id . '_' . $ukuran_id;
        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key]++;
        } else {
            $_SESSION['cart'][$cart_key] = 1;
        }
    } elseif ($action === 'remove') {
        if ($ukuran_id > 0) {
            unset($_SESSION['cart'][$product_id . '_' . $ukuran_id]);
        } else {
            // Remove all entries for this product
            foreach ($_SESSION['cart'] as $key => $qty) {
                $parts = explode('_', $key);
                if ((int)$parts[0] === $product_id) {
                    unset($_SESSION['cart'][$key]);
                }
            }
        }
    } elseif ($action === 'update') {
        $qty = (int)($_POST['quantity'] ?? 1);
        $cart_key = $product_id . '_' . $ukuran_id;
        if ($qty > 0) {
            $_SESSION['cart'][$cart_key] = $qty;
        } else {
            unset($_SESSION['cart'][$cart_key]);
        }
    } elseif ($action === 'clear') {
        $_SESSION['cart'] = [];
    }
    header('Location: keranjang.php');
    exit;
}

// Migrate old cart format (just product_id key) to new format
if (!empty($_SESSION['cart'])) {
    $migrated = false;
    foreach ($_SESSION['cart'] as $key => $qty) {
        if (is_numeric($key)) {
            $_SESSION['cart'][$key . '_0'] = $qty;
            unset($_SESSION['cart'][$key]);
            $migrated = true;
        }
    }
    if ($migrated) {
        header('Location: keranjang.php');
        exit;
    }
}

$page_title = 'Keranjang Belanja';
require_once 'includes/header.php';

list($cart_items, $subtotal, $item_count) = calculate_cart_totals($pdo);

$shipping_cost = 25000;
$admin_fee = 2000;
$grand_total = $subtotal + $shipping_cost + $admin_fee;
?>
<main class="py-xl">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 48px; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 class="font-display" style="font-size: 48px; margin-bottom: 8px;">Keranjang Belanja</h1>
                <p style="color: var(--on-surface-variant); font-size: 16px;">
                    <?php if ($item_count > 0): ?>
                        <?php echo e($item_count); ?> item — lengkapi pesanan madu murni Anda
                    <?php else: ?>
                        Temukan madu murni pilihan dari BatuMekar
                    <?php endif; ?>
                </p>
            </div>
            <?php if (!empty($cart_items)): ?>
                <form action="keranjang.php" method="POST" onsubmit="return confirm('Kosongkan seluruh keranjang?')">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" style="display: flex; align-items: center; gap: 6px; padding: 8px 16px; border: 1px solid #e2e8f0; border-radius: 8px; background: white; color: #ef4444; font-size: 13px; font-weight: 600; cursor: pointer;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">delete_sweep</span> Kosongkan
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (empty($cart_items)): ?>
            <div style="text-align: center; padding: 80px 20px; max-width: 400px; margin: 0 auto;">
                <div style="width: 100px; height: 100px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                    <span class="material-symbols-outlined" style="font-size: 48px; color: #94a3b8;">shopping_cart</span>
                </div>
                <h2 style="font-size: 24px; color: var(--secondary); margin-bottom: 8px;">Keranjang Kosong</h2>
                <p style="color: var(--on-surface-variant); margin-bottom: 32px;">Anda belum menambahkan produk madu apapun. Yuk, jelajahi katalog kami!</p>
                <a href="katalog.php" class="btn btn-primary" style="padding: 14px 32px;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">storefront</span> Jelajahi Produk
                </a>
            </div>
        <?php else: ?>
        <div style="display: flex; gap: 40px; align-items: flex-start; flex-wrap: wrap;">
            <!-- Cart Items -->
            <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($cart_items as $item):
                    $img = $item['gambar'] ?: 'assets/images/honey_bowl.jpg';
                ?>
                <div class="card" style="padding: 20px 24px; display: flex; align-items: center; gap: 20px; border-radius: 16px;">
                    <div style="width: 80px; height: 80px; flex-shrink: 0; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; background: #f8fafc;">
                        <img src="<?php echo e($img); ?>" alt="<?php echo e($item['nama']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>

                    <div style="flex: 1; min-width: 0;">
                        <a href="produk.php?id=<?php echo e($item['produk_id']); ?>" style="font-size: 16px; font-weight: 700; color: var(--secondary); text-decoration: none; display: block; margin-bottom: 2px;"><?php echo e($item['nama']); ?></a>
                        <?php if ($item['ukuran_label']): ?>
                            <span style="display: inline-block; background: #eff6ff; color: #2563eb; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; margin-bottom: 6px;"><?php echo e($item['ukuran_label']); ?></span>
                        <?php endif; ?>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <?php if ($item['kategori']): ?>
                                <span style="background: #f1f5f9; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; color: #64748b;"><?php echo e($item['kategori']); ?></span>
                            <?php endif; ?>
                            <span style="font-size: 15px; font-weight: 700; color: var(--primary);"><?php echo e(format_rupiah($item['size_price'])); ?></span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 12px;">
                            <form action="keranjang.php" method="POST" style="display: flex; align-items: center; gap: 0; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo e($item['produk_id']); ?>">
                                <input type="hidden" name="ukuran_id" value="<?php echo e($item['ukuran_id']); ?>">
                                <button type="submit" name="quantity" value="<?php echo e($item['qty'] - 1); ?>" style="padding: 6px 10px; border: none; background: #f8fafc; cursor: pointer; color: #475569; display: flex; align-items: center; transition: background 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f8fafc'">
                                    <span class="material-symbols-outlined" style="font-size: 16px;">remove</span>
                                </button>
                                <span style="padding: 6px 12px; font-weight: 700; font-size: 14px; min-width: 32px; text-align: center; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;"><?php echo e($item['qty']); ?></span>
                                <?php $max_qty = $item['size_stok']; ?>
                                <button type="submit" name="quantity" value="<?php echo e(min($item['qty'] + 1, $max_qty)); ?>" style="padding: 6px 10px; border: none; background: #f8fafc; cursor: pointer; color: #475569; display: flex; align-items: center; transition: background 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f8fafc'" <?php echo $item['qty'] >= $max_qty ? 'disabled style="opacity:0.4;cursor:not-allowed;"' : ''; ?>>
                                    <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                                </button>
                            </form>

                            <form action="keranjang.php" method="POST" onsubmit="return confirm('Hapus <?php echo e($item['nama']); ?> dari keranjang?')" style="margin: 0;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo e($item['produk_id']); ?>">
                                <input type="hidden" name="ukuran_id" value="<?php echo e($item['ukuran_id']); ?>">
                                <button type="submit" style="padding: 6px 10px; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2; color: #dc2626; cursor: pointer; display: flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">delete</span> Hapus
                                </button>
                            </form>
                        </div>
                    </div>

                    <div style="text-align: right; flex-shrink: 0;">
                        <div style="font-size: 13px; color: #94a3b8; font-weight: 600; margin-bottom: 4px;">Subtotal</div>
                        <div style="font-size: 18px; font-weight: 800; color: var(--primary);"><?php echo e(format_rupiah($item['total'])); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary Sidebar -->
            <aside style="width: 360px; flex-shrink: 0;">
                <div class="card" style="padding: 28px; border-radius: 16px; position: sticky; top: 120px;">
                    <h2 style="font-size: 20px; font-weight: 800; color: var(--secondary); margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">receipt</span>
                        Ringkasan Belanja
                    </h2>

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: flex; justify-content: space-between; font-size: 14px;">
                            <span style="color: #64748b;">Subtotal (<?php echo e($item_count); ?> item)</span>
                            <span style="font-weight: 600; color: #1e293b;"><?php echo e(format_rupiah($subtotal)); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px;">
                            <span style="color: #64748b;">Ongkos Kirim</span>
                            <span style="font-weight: 600; color: #1e293b;"><?php echo e(format_rupiah($shipping_cost)); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px;">
                            <span style="color: #64748b;">Biaya Admin</span>
                            <span style="font-weight: 600; color: #1e293b;"><?php echo e(format_rupiah($admin_fee)); ?></span>
                        </div>
                    </div>

                    <div style="border-top: 2px solid var(--primary); padding-top: 16px; margin-top: 16px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 16px; font-weight: 700; color: var(--secondary);">Total Belanja</span>
                        <span style="font-size: 24px; font-weight: 800; color: var(--primary);"><?php echo e(format_rupiah($grand_total)); ?></span>
                    </div>

                    <a href="checkout.php" class="btn btn-primary" style="width: 100%; margin-top: 24px; padding: 16px; justify-content: center; font-size: 16px;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">shopping_cart_checkout</span> Lanjut ke Checkout
                    </a>

                    <a href="katalog.php" style="display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 12px; padding: 10px; color: var(--secondary); font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 10px; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                        <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span> Lanjut Belanja
                    </a>

                    <div style="margin-top: 20px; padding: 16px; background: #fffbeb; border-radius: 10px; display: flex; align-items: flex-start; gap: 12px;">
                        <span class="material-symbols-outlined" style="color: #d97706; font-size: 20px;">local_shipping</span>
                        <div style="font-size: 12px; color: #92400e; line-height: 1.6;">
                            <strong>Pengiriman Standard</strong><br>
                            Pesanan diproses dalam 1x24 jam dan dikirim via kurir terpercaya.
                        </div>
                    </div>
                </div>
            </aside>
        </div>
        <?php endif; ?>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>
