<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/functions.php';
require_once 'config/db.php';

// Enforce login
require_login();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM pengguna WHERE pengguna_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (empty($_SESSION['cart'])) {
    header('Location: katalog.php');
    exit;
}

$page_title = 'Checkout';
require_once 'includes/header.php';

list($cart_items, $subtotal, $item_count) = calculate_cart_totals($pdo);

$shipping_cost = 25000;
$admin_fee = 2000;
$grand_total = $subtotal + $shipping_cost + $admin_fee;
?>

<main class="py-xl">
    <div class="container">
        <h1 class="font-display" style="font-size: 48px; margin-bottom: 24px;">Checkout Pesanan</h1>

        <form action="process_checkout.php" method="POST" style="display: flex; gap: 48px; align-items: start;">
            <div style="flex-grow: 1; display: flex; flex-direction: column; gap: 32px;">
                <section class="card" style="padding: 32px;">
                    <h2 style="margin-bottom: 24px; color: var(--secondary);">Alamat Pengiriman</h2>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                        <div style="grid-column: span 2;" class="form-group">
                            <label for="name">Nama Lengkap</label>
                            <input id="name" name="name" class="form-control" value="<?php echo e($user['nama'] ?? ''); ?>" required>
                        </div>
                        <div style="grid-column: span 2;" class="form-group">
                            <label for="phone">Nomor Telepon / WA</label>
                            <input id="phone" type="tel" name="phone" class="form-control" value="<?php echo e($user['telepon'] ?? ''); ?>" required>
                        </div>
                        <div style="grid-column: span 2;" class="form-group">
                            <label for="address">Alamat Lengkap</label>
                            <textarea id="address" name="address" class="form-control" rows="3" required style="resize: vertical;"><?php echo e($user['alamat'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </section>
 
                <section class="card" style="padding: 32px;">
                    <h2 style="margin-bottom: 24px; color: var(--secondary);">Metode Pembayaran</h2>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div class="payment-option" style="padding: 20px; border: 2px solid var(--outline-variant); border-radius: 12px; display: flex; align-items: center; gap: 16px; cursor: pointer; transition: all 0.3s ease; background: var(--surface);">
                            <input type="radio" id="pay_cod" name="payment_method" value="COD" checked style="width: 20px; height: 20px; accent-color: var(--primary);">
                            <label for="pay_cod" style="cursor: pointer; display: flex; align-items: center; gap: 12px; width: 100%;">
                                <span class="material-symbols-outlined text-primary" style="font-size: 28px;">local_shipping</span>
                                <div>
                                    <strong style="display: block; font-size: 16px; color: var(--secondary);">COD (Cash on Delivery)</strong>
                                    <span style="font-size: 13px; color: var(--on-surface-variant);">Bayar langsung tunai di tempat saat kurir membawa pesanan Anda</span>
                                </div>
                            </label>
                        </div>
 
                        <div class="payment-option" style="padding: 20px; border: 2px solid var(--outline-variant); border-radius: 12px; display: flex; align-items: center; gap: 16px; cursor: pointer; transition: all 0.3s ease; background: var(--surface);">
                            <input type="radio" id="pay_midtrans" name="payment_method" value="Midtrans" style="width: 20px; height: 20px; accent-color: var(--primary);">
                            <label for="pay_midtrans" style="cursor: pointer; display: flex; align-items: center; gap: 12px; width: 100%;">
                                <span class="material-symbols-outlined text-primary" style="font-size: 28px;">payments</span>
                                <div>
                                    <strong style="display: block; font-size: 16px; color: var(--secondary);">Midtrans Payment Gateway</strong>
                                    <span style="font-size: 13px; color: var(--on-surface-variant);">Bayar otomatis via Virtual Account (BCA, Mandiri, BRI), E-Wallet (GoPay, ShopeePay), dll.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </section>
            </div>
 
            <aside style="width: 350px;">
                <div class="card" style="position: sticky; top: 120px;">
                    <h2 style="margin-bottom: 24px;">Ringkasan Pesanan</h2>
                    <?php foreach ($cart_items as $item): ?>
                    <div style="display: flex; gap: 16px; margin-bottom: 16px; align-items: center;">
                        <img src="<?php echo e($item['gambar']); ?>" alt="<?php echo e($item['nama']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                        <div style="flex-grow: 1;">
                            <div style="font-weight: 600; font-size: 14px;"><?php echo e($item['nama']); ?></div>
                            <div style="font-size: 12px; color: var(--on-surface-variant);">
                                <?php if ($item['ukuran_label']): ?>
                                    <span style="color: #2563eb; font-weight: 600;"><?php echo e($item['ukuran_label']); ?></span> &middot;
                                <?php endif; ?>
                                <?php echo e($item['qty']); ?> x <?php echo e(format_rupiah($item['size_price'])); ?>
                            </div>
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
                            <span>Ongkos Kirim</span>
                            <span><?php echo e(format_rupiah($shipping_cost)); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 20px; margin-top: 12px;">
                            <span>Total</span>
                            <span class="text-primary"><?php echo e(format_rupiah($grand_total)); ?></span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 24px; padding: 16px; justify-content: center; font-size: 15px;">Konfirmasi & Bayar</button>
                </div>
            </aside>
        </form>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
