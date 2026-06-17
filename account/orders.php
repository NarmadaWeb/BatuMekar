<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_login();

$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE pengguna_id = ? ORDER BY dibuat_pada DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Fetch order items for each order
$orders_with_items = [];
foreach ($orders as $order) {
    $stmt_items = $pdo->prepare("
        SELECT oi.detail_pesanan_id, oi.pesanan_id, oi.produk_id, oi.ukuran_id, oi.jumlah as quantity, oi.harga as price, p.nama as product_name, p.gambar as product_image 
        FROM detail_pesanan oi
        JOIN produk p ON oi.produk_id = p.produk_id
        WHERE oi.pesanan_id = ?
    ");
    $stmt_items->execute([$order['pesanan_id']]);
    $order['items'] = $stmt_items->fetchAll();
    $orders_with_items[] = $order;
}
$orders = $orders_with_items;

$page_title = 'Pesanan Saya';
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

        <!-- Main Orders Content -->
        <section style="flex-grow: 1;">
            <h1 style="font-size: 32px; margin-bottom: 32px; color: var(--secondary); font-weight: 800;">Riwayat Pesanan</h1>

            <div style="display: flex; flex-direction: column; gap: 24px;">
                <?php if (empty($orders)): ?>
                    <div class="card" style="text-align: center; padding: 48px; border-radius: 16px;">
                        <span class="material-symbols-outlined" style="font-size: 64px; color: var(--outline); margin-bottom: 16px;">receipt_long</span>
                        <h2 style="font-size: 20px; color: var(--secondary); margin-bottom: 8px;">Belum Ada Pesanan</h2>
                        <p style="color: var(--on-surface-variant); margin-bottom: 24px;">Anda belum melakukan pemesanan produk apa pun saat ini.</p>
                        <a href="../katalog.php" class="btn btn-primary">Mulai Belanja</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <div class="order-card" style="border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.05); background: white; overflow: hidden; margin-bottom: 8px;">
                        <!-- Order Header -->
                        <div class="order-header" style="background: #f8fafc; padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                            <div>
                                <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">ID Pesanan</span>
                                <div style="font-weight: 800; font-size: 16px; color: var(--primary); margin-top: 2px;">#MBM-<?php echo e($order['pesanan_id']); ?></div>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Tanggal Transaksi</span>
                                <div style="font-weight: 600; font-size: 14px; color: #334155; margin-top: 2px;"><?php echo e(date('d M Y, H:i', strtotime($order['dibuat_pada']))); ?></div>
                            </div>
                        </div>
                        
                        <!-- Order Body -->
                        <div class="order-body" style="padding: 24px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; flex-wrap: wrap;">
                                <div>
                                    <div style="font-size: 12px; color: var(--secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Detail Pengiriman</div>
                                    <div style="font-size: 14px; color: var(--on-surface-variant); line-height: 1.6;">
                                        <strong>Alamat:</strong> <?php echo e($order['alamat_pengiriman'] ?? 'N/A'); ?><br>
                                        <strong>Metode:</strong> <?php echo e($order['metode_pengiriman'] ?? 'N/A'); ?><br>
                                        <strong>Pembayaran:</strong> <?php echo e($order['metode_pembayaran'] ?? 'N/A'); ?>
                                    </div>
                                </div>
                                <div style="text-align: right; min-width: 180px;">
                                    <div style="font-size: 12px; color: var(--secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Total Pembayaran</div>
                                    <div style="font-size: 22px; font-weight: 800; color: var(--primary);"><?php echo e(format_rupiah($order['total_harga'])); ?></div>
                                    
                                    <?php 
                                        $status_class = 'badge-pending';
                                        $status_text = 'Menunggu Pembayaran';
                                        $status = strtolower($order['status'] ?? 'pending');
                                        if ($status === 'completed') {
                                            $status_class = 'badge-completed';
                                            $status_text = 'Selesai';
                                        } elseif ($status === 'shipped') {
                                            $status_class = 'badge-shipped';
                                            $status_text = 'Dikirim';
                                        } elseif ($status === 'processed') {
                                            $status_class = 'badge-processed';
                                            $status_text = 'Diproses';
                                        }
                                    ?>
                                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px; margin-top: 12px;">
                                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                        
                                        <?php if ($status === 'pending' && $order['metode_pembayaran'] === 'Midtrans'): ?>
                                            <a href="../pembayaran.php?order_id=<?php echo $order['pesanan_id']; ?>&method=midtrans" class="btn btn-primary" style="padding: 8px 16px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; justify-content: center; text-decoration: none; border-radius: 6px; font-weight: 600; background: #3b82f6; border-color: #3b82f6;">
                                                <span class="material-symbols-outlined" style="font-size: 16px;">payments</span> Bayar Sekarang
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
 
                            <!-- Expandable Items -->
                            <?php if (!empty($order['items'])): ?>
                            <div style="margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 18px;">
                                <button class="order-details-toggle" onclick="toggleDetails(<?php echo $order['pesanan_id']; ?>)" style="background: none; border: none; padding: 0; color: var(--primary); font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                    <span class="material-symbols-outlined" id="toggle-icon-<?php echo $order['pesanan_id']; ?>" style="font-size: 18px;">expand_more</span>
                                    <span>Tampilkan Item (<?php echo count($order['items']); ?>)</span>
                                </button>
                                
                                <div id="items-list-<?php echo $order['pesanan_id']; ?>" class="order-items-list" style="display: none; flex-direction: column; gap: 12px; margin-top: 16px; background: #f8fafc; padding: 16px; border-radius: 10px; border: 1px solid #e2e8f0;">
                                    <?php foreach ($order['items'] as $item):
                                        $size_label = '';
                                        if (!empty($item['ukuran_id'])) {
                                            $size_info = get_size_by_id($pdo, $item['ukuran_id']);
                                            if ($size_info) $size_label = $size_info['ukuran_ml'] . ' ml';
                                        }
                                    ?>
                                    <div class="order-item-row" style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
                                        <div class="order-item-info" style="display: flex; align-items: center; gap: 12px;">
                                            <?php if (!empty($item['product_image'])): ?>
                                                <img src="<?php echo e($item['product_image']); ?>" alt="<?php echo e($item['product_name']); ?>" class="order-item-img" style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1;">
                                            <?php endif; ?>
                                            <div>
                                                <div style="font-weight: 600; color: var(--on-surface); font-size: 14px;"><?php echo e($item['product_name']); ?></div>
                                                <div style="font-size: 12px; color: var(--on-surface-variant);">
                                                    <?php if ($size_label): ?><span style="color: #2563eb; font-weight: 600;"><?php echo e($size_label); ?></span> &middot; <?php endif; ?>
                                                    <?php echo e($item['quantity']); ?> x <?php echo e(format_rupiah($item['price'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="font-weight: 700; color: var(--secondary); font-size: 14px;">
                                            <?php echo e(format_rupiah($item['quantity'] * $item['price'])); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<script>
function toggleDetails(orderId) {
    const list = document.getElementById('items-list-' + orderId);
    const icon = document.getElementById('toggle-icon-' + orderId);
    const text = icon.nextElementSibling;
    
    if (list.style.display === 'none') {
        list.style.display = 'flex';
        icon.textContent = 'expand_less';
        text.textContent = text.textContent.replace('Tampilkan', 'Sembunyikan');
    } else {
        list.style.display = 'none';
        icon.textContent = 'expand_more';
        text.textContent = text.textContent.replace('Sembunyikan', 'Tampilkan');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
