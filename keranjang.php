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

    if ($action === 'add' && $product_id > 0) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]++;
        } else {
            $_SESSION['cart'][$product_id] = 1;
        }
    } elseif ($action === 'remove' && $product_id > 0) {
        unset($_SESSION['cart'][$product_id]);
    } elseif ($action === 'update' && $product_id > 0) {
        $qty = (int)($_POST['quantity'] ?? 1);
        if ($qty > 0) {
            $_SESSION['cart'][$product_id] = $qty;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    header('Location: keranjang.php');
    exit;
}

$page_title = 'Keranjang Belanja';
require_once 'includes/header.php';

$cart_items = [];
$subtotal = 0;
if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']];
        $total = $p['price'] * $qty;
        $subtotal += $total;
        $cart_items[] = array_merge($p, ['qty' => $qty, 'total' => $total]);
    }
}
?>

<main class="py-xl">
    <div class="container">
        <h1 class="font-display" style="font-size: 56px; margin-bottom: 24px;">Keranjang Belanja</h1>
        <p class="text-secondary" style="margin-bottom: 48px; max-width: 600px;">Lengkapi pesanan Anda dan nikmati kemurnian madu hutan dari lereng pegunungan Batu Meka.</p>

        <?php if (empty($cart_items)): ?>
            <div class="text-center" style="padding: 80px 0;">
                <p class="mb-md" style="color: var(--on-surface-variant);">Keranjang Anda kosong.</p>
                <a href="katalog.php" class="btn btn-primary">Mulai Belanja</a>
            </div>
        <?php else: ?>
        <div style="display: flex; gap: 48px; align-items: start;">
            <div style="flex-grow: 1; display: flex; flex-direction: column; gap: 24px;">
                <?php foreach ($cart_items as $item): ?>
                <div class="card" style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 24px;">
                        <img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>" class="cart-item-img">
                        <div>
                            <h3 style="font-size: 20px;"><?php echo e($item['name']); ?></h3>
                            <p style="color: var(--on-surface-variant);"><?php echo e(format_rupiah($item['price'])); ?></p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <form action="keranjang.php" method="POST" class="qty-control">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="<?php echo e($item['id']); ?>">
                            <button type="submit" name="quantity" value="<?php echo e($item['qty'] - 1); ?>"><span class="material-symbols-outlined" style="font-size: 16px;">remove</span></button>
                            <span style="width: 24px; text-align: center; font-weight: 600;"><?php echo e($item['qty']); ?></span>
                            <button type="submit" name="quantity" value="<?php echo e($item['qty'] + 1); ?>"><span class="material-symbols-outlined" style="font-size: 16px;">add</span></button>
                        </form>
                        <form action="keranjang.php" method="POST">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?php echo e($item['id']); ?>">
                            <button type="submit" style="color: var(--error);"><span class="material-symbols-outlined">delete</span></button>
                        </form>
                    </div>
                    <div style="font-weight: 700; color: var(--primary); min-width: 120px; text-align: right;">
                        <?php echo e(format_rupiah($item['total'])); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <aside style="width: 350px;">
                <div class="card" style="position: sticky; top: 120px;">
                    <h2 style="margin-bottom: 24px;">Ringkasan</h2>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span>Subtotal</span>
                        <span><?php echo e(format_rupiah($subtotal)); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span>Pengiriman</span>
                        <span style="color: var(--tertiary);">Gratis</span>
                    </div>
                    <div style="border-top: 1px solid var(--outline-variant); padding-top: 12px; margin-top: 12px; display: flex; justify-content: space-between; font-weight: 700; font-size: 20px;">
                        <span>Total</span>
                        <span class="text-primary"><?php echo e(format_rupiah($subtotal)); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary" style="width: 100%; margin-top: 24px; padding: 16px;">Lanjutkan ke Pembayaran</a>
                </div>
            </aside>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
