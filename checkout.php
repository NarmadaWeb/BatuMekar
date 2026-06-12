<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/functions.php';
require_once 'config/db.php';

if (empty($_SESSION['cart'])) {
    header('Location: katalog.php');
    exit;
}

$page_title = 'Checkout';
require_once 'includes/header.php';

$cart_items = [];
$subtotal = 0;
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

$shipping_cost = 25000;
$admin_fee = 2000;
$grand_total = $subtotal + $shipping_cost + $admin_fee;
?>

<main class="py-xl">
    <div class="container">
        <h1 class="font-display" style="font-size: 48px; margin-bottom: 24px;">Secure Checkout</h1>

        <form action="process_checkout.php" method="POST" style="display: flex; gap: 48px; align-items: start;">
            <div style="flex-grow: 1; display: flex; flex-direction: column; gap: 32px;">
                <section class="card">
                    <h2 style="margin-bottom: 24px;">Shipping Address</h2>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                        <div style="grid-column: span 2;">
                            <label for="name" style="display: block; margin-bottom: 8px;">Full Name</label>
                            <input id="name" name="name" required style="width: 100%; padding: 12px; border: 1px solid var(--outline-variant); border-radius: 8px;">
                        </div>
                        <div>
                            <label for="email" style="display: block; margin-bottom: 8px;">Email Address</label>
                            <input id="email" type="email" name="email" required style="width: 100%; padding: 12px; border: 1px solid var(--outline-variant); border-radius: 8px;">
                        </div>
                        <div>
                            <label for="phone" style="display: block; margin-bottom: 8px;">Phone Number</label>
                            <input id="phone" type="tel" name="phone" required style="width: 100%; padding: 12px; border: 1px solid var(--outline-variant); border-radius: 8px;">
                        </div>
                        <div style="grid-column: span 2;">
                            <label for="address" style="display: block; margin-bottom: 8px;">Complete Address</label>
                            <textarea id="address" name="address" required style="width: 100%; padding: 12px; border: 1px solid var(--outline-variant); border-radius: 8px;" rows="3"></textarea>
                        </div>
                    </div>
                </section>

                <section class="card">
                    <h2 style="margin-bottom: 24px;">Payment Method</h2>
                    <div style="padding: 24px; border: 2px solid var(--primary); border-radius: 12px; background: var(--surface-variant);">
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                            <input type="radio" checked name="payment_method" value="Bank Transfer">
                            <span style="font-weight: 600;">Bank Transfer (Manual)</span>
                        </label>
                    </div>
                </section>
            </div>

            <aside style="width: 350px;">
                <div class="card" style="position: sticky; top: 120px;">
                    <h2 style="margin-bottom: 24px;">Order Summary</h2>
                    <?php foreach ($cart_items as $item): ?>
                    <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                        <img src="<?php echo e($item['image']); ?>" alt="<?php echo e($item['name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                        <div style="flex-grow: 1;">
                            <div style="font-weight: 600; font-size: 14px;"><?php echo e($item['name']); ?></div>
                            <div style="font-size: 12px; color: var(--on-surface-variant);">Qty: <?php echo e($item['qty']); ?></div>
                        </div>
                        <div style="font-weight: 600; font-size: 14px;"><?php echo e(format_rupiah($item['total'])); ?></div>
                    </div>
                    <?php endforeach; ?>

                    <div style="border-top: 1px solid var(--outline-variant); padding-top: 16px; margin-top: 16px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>Subtotal</span>
                            <span><?php echo e(format_rupiah($subtotal)); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>Shipping</span>
                            <span><?php echo e(format_rupiah($shipping_cost)); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 20px; margin-top: 12px;">
                            <span>Total</span>
                            <span class="text-primary"><?php echo e(format_rupiah($grand_total)); ?></span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 24px; padding: 16px;">Confirm & Pay</button>
                </div>
            </aside>
        </form>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
