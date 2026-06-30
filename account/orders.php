<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_login();

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE pengguna_id = ? ORDER BY dibuat_pada DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'cancel_order') {
        $order_id = (int)$_POST['order_id'];
        $stmt_check = $pdo->prepare("SELECT * FROM pesanan WHERE pesanan_id = ? AND pengguna_id = ? AND status = 'Pending'");
        $stmt_check->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt_check->fetch();
        if ($order) {
            $stmt_update = $pdo->prepare("UPDATE pesanan SET status = 'Cancelled' WHERE pesanan_id = ?");
            $stmt_update->execute([$order_id]);
            $success_message = "Pesanan #MBM-{$order_id} berhasil dibatalkan.";
            // Reload orders to reflect changes
            $stmt->execute([$_SESSION['user_id']]);
            $orders = $stmt->fetchAll();
        } else {
            $error_message = "Pesanan tidak dapat dibatalkan.";
        }
    } elseif ($_POST['action'] === 'complete_order') {
        $order_id = (int)$_POST['order_id'];
        $stmt_check = $pdo->prepare("SELECT * FROM pesanan WHERE pesanan_id = ? AND pengguna_id = ? AND status = 'Shipped'");
        $stmt_check->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt_check->fetch();
        if ($order) {
            $stmt_update = $pdo->prepare("UPDATE pesanan SET status = 'Completed' WHERE pesanan_id = ?");
            $stmt_update->execute([$order_id]);
            $success_message = "Pesanan #MBM-{$order_id} telah selesai. Terima kasih!";
            // Reload orders
            $stmt->execute([$_SESSION['user_id']]);
            $orders = $stmt->fetchAll();
        }
    }
}

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

            <?php if ($success_message): ?>
                <div style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 500; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined">check_circle</span>
                    <?php echo e($success_message); ?>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div style="background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 500; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined">error</span>
                    <?php echo e($error_message); ?>
                </div>
            <?php endif; ?>

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
                                        
                                        <?php if ($status === 'pending'): ?>
                                            <?php if ($order['metode_pembayaran'] === 'Midtrans'): ?>
                                                <a href="../pembayaran.php?order_id=<?php echo $order['pesanan_id']; ?>&method=midtrans" class="btn btn-primary" style="padding: 8px 16px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; justify-content: center; text-decoration: none; border-radius: 6px; font-weight: 600; background: #3b82f6; border-color: #3b82f6;">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">payments</span> Bayar Sekarang
                                                </a>
                                            <?php endif; ?>
                                            
                                            <form action="orders.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')" style="display:inline; margin:0;">
                                                <input type="hidden" name="action" value="cancel_order">
                                                <input type="hidden" name="order_id" value="<?php echo $order['pesanan_id']; ?>">
                                                <button type="submit" class="btn btn-secondary" style="padding: 8px 16px; font-size: 12px; border-radius: 6px; font-weight: 600; color: #dc2626; border-color: #fca5a5; background: #fef2f2; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; border: 1px solid #fca5a5;">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">cancel</span> Batalkan Pesanan
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($status === 'shipped'): ?>
                                            <form action="orders.php" method="POST" onsubmit="return confirm('Konfirmasi bahwa Anda telah menerima pesanan ini?')" style="display:inline; margin:0;">
                                                <input type="hidden" name="action" value="complete_order">
                                                <input type="hidden" name="order_id" value="<?php echo $order['pesanan_id']; ?>">
                                                <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 12px; border-radius: 6px; font-weight: 600; background: #059669; border-color: #059669; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">check_circle</span> Pesanan Selesai
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($status === 'completed'): ?>
                                            <?php 
                                                // Check if already returned
                                                $stmt_ret = $pdo->prepare("SELECT * FROM pengembalian_pesanan WHERE pesanan_id = ?");
                                                $stmt_ret->execute([$order['pesanan_id']]);
                                                $return_data = $stmt_ret->fetch();
                                            ?>
                                            <?php if (!$return_data): ?>
                                                <a href="return.php?order_id=<?php echo $order['pesanan_id']; ?>" class="btn" style="padding: 8px 16px; font-size: 12px; border-radius: 6px; font-weight: 600; color: #d97706; border: 1px solid #fde68a; background: #fffbeb; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">assignment_return</span> Ajukan Pengembalian
                                                </a>
                                            <?php else: ?>
                                                <?php 
                                                    $ret_bg = '#f1f5f9';
                                                    $ret_fg = '#475569';
                                                    if ($return_data['status'] === 'Disetujui') {
                                                        $ret_bg = '#d1fae5';
                                                        $ret_fg = '#065f46';
                                                    } elseif ($return_data['status'] === 'Ditolak') {
                                                        $ret_bg = '#fee2e2';
                                                        $ret_fg = '#991b1b';
                                                    }
                                                ?>
                                                <span style="font-size: 12px; padding: 6px 12px; border-radius: 6px; font-weight: 600; background: <?php echo $ret_bg; ?>; color: <?php echo $ret_fg; ?>; border: 1px solid rgba(0,0,0,0.05); display: inline-flex; align-items: center; gap: 4px;">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">info</span> Return: <?php echo e($return_data['status']); ?>
                                                </span>
                                            <?php endif; ?>
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
                                                <img src="<?php echo e(img_url($item['product_image'])); ?>" alt="<?php echo e($item['product_name']); ?>" class="order-item-img" style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1;">
                                            <?php endif; ?>
                                            <div>
                                                <div style="font-weight: 600; color: var(--on-surface); font-size: 14px;"><?php echo e($item['product_name']); ?></div>
                                                <div style="font-size: 12px; color: var(--on-surface-variant);">
                                                    <?php if ($size_label): ?><span style="color: #2563eb; font-weight: 600;"><?php echo e($size_label); ?></span> &middot; <?php endif; ?>
                                                    <?php echo e($item['quantity']); ?> x <?php echo e(format_rupiah($item['price'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                                            <div style="font-weight: 700; color: var(--secondary); font-size: 14px;">
                                                <?php echo e(format_rupiah($item['quantity'] * $item['price'])); ?>
                                            </div>
                                            
                                            <?php if ($status === 'completed'): ?>
                                                <?php
                                                    // Check if already reviewed
                                                    $stmt_rev = $pdo->prepare("SELECT * FROM ulasan_produk WHERE produk_id = ? AND pengguna_id = ?");
                                                    $stmt_rev->execute([$item['produk_id'], $_SESSION['user_id']]);
                                                    $review_data = $stmt_rev->fetch();
                                                ?>
                                                <?php if (!$review_data): ?>
                                                    <a href="review.php?product_id=<?php echo $item['produk_id']; ?>&order_id=<?php echo $order['pesanan_id']; ?>" style="padding: 4px 10px; font-size: 11px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 2px;">
                                                        <span class="material-symbols-outlined" style="font-size: 14px;">star</span> Beri Ulasan
                                                    </a>
                                                <?php else: ?>
                                                    <span style="font-size: 11px; color: #10b981; font-weight: 600; display: inline-flex; align-items: center; gap: 2px;">
                                                        <span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span> Sudah Diulas (<?php echo $review_data['rating']; ?>★)
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
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
