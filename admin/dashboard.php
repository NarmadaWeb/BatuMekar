<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_admin();

// Auto-create notifikasi table if not exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifikasi (
        notifikasi_id INTEGER PRIMARY KEY AUTOINCREMENT,
        pesanan_id INTEGER DEFAULT NULL,
        judul TEXT NOT NULL,
        pesan TEXT DEFAULT NULL,
        dibaca INTEGER DEFAULT 0,
        dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

// Handle status updates
$updated_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)$_POST['order_id'];
    $new_status = trim($_POST['status'] ?? '');
    if (in_array($new_status, ['Pending', 'Processed', 'Shipped', 'Completed'])) {
        try {
            $stmt = $pdo->prepare("UPDATE pesanan SET status = ? WHERE pesanan_id = ?");
            $stmt->execute([$new_status, $order_id]);
            $updated_msg = "Status pesanan #MBM-{$order_id} berhasil diperbarui.";
        } catch (PDOException $e) {
            $updated_msg = "Gagal memperbarui status: " . $e->getMessage();
        }
    }
}

// Simple stats
$total_products = $pdo->query("SELECT COUNT(*) FROM produk")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM pengguna")->fetchColumn();
$total_revenue = $pdo->query("SELECT COALESCE(SUM(total_harga), 0) FROM pesanan WHERE status = 'Completed'")->fetchColumn();
$pending_count = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'Pending'")->fetchColumn();

// Notifications
$unread_notif = $pdo->query("SELECT COUNT(*) FROM notifikasi WHERE dibaca = 0")->fetchColumn();
$notifications = $pdo->query("SELECT * FROM notifikasi ORDER BY dibuat_pada DESC LIMIT 5")->fetchAll();

$recent_orders = $pdo->query("SELECT pesanan.*, pengguna.nama as user_name FROM pesanan JOIN pengguna ON pesanan.pengguna_id = pengguna.pengguna_id ORDER BY pesanan.dibuat_pada DESC LIMIT 10")->fetchAll();

$page_title = 'Dashboard Admin';
require_once __DIR__ . '/../includes/header.php';
?>
<main style="padding: 48px 0; background: var(--background); font-family: 'Inter', sans-serif;">
    <div class="container" style="display: flex; gap: 48px; align-items: flex-start;">
        <!-- Sidebar Menu -->
        <aside style="width: 250px; flex-shrink: 0;">
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link active">
                    <span class="material-symbols-outlined">dashboard</span>
                    Overview Admin
                </a>
                <a href="produk.php" class="sidebar-link">
                    <span class="material-symbols-outlined">inventory_2</span>
                    Produk
                </a>
                <a href="kategori.php" class="sidebar-link">
                    <span class="material-symbols-outlined">category</span>
                    Kategori
                </a>
                <a href="pesanan.php" class="sidebar-link">
                    <span class="material-symbols-outlined">receipt_long</span>
                    Pesanan
                    <?php if ($pending_count > 0): ?>
                        <span style="margin-left: auto; background: #ef4444; color: white; font-size: 10px; padding: 2px 8px; border-radius: 20px; font-weight: 700;"><?php echo $pending_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="artikel.php" class="sidebar-link">
                    <span class="material-symbols-outlined">article</span>
                    Artikel
                </a>
                <a href="../logout.php" class="sidebar-link" style="color: var(--error) !important;">
                    <span class="material-symbols-outlined" style="color: var(--error) !important;">logout</span>
                    Keluar
                </a>
            </nav>
        </aside>

        <!-- Main Admin Content -->
        <section style="flex-grow: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                <h1 style="font-size: 32px; color: var(--secondary); font-weight: 800;">Panel Kontrol Admin</h1>
                
                <!-- Notification Bell -->
                <div style="position: relative;">
                    <button onclick="toggleNotif()" style="background: none; border: none; cursor: pointer; position: relative; padding: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 28px; color: var(--secondary);">notifications</span>
                        <?php if ($unread_notif > 0): ?>
                            <span style="position: absolute; top: 2px; right: 2px; background: #ef4444; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 11px; display: flex; align-items: center; justify-content: center; font-weight: 700; border: 2px solid white;"><?php echo $unread_notif > 9 ? '9+' : $unread_notif; ?></span>
                        <?php endif; ?>
                    </button>
                    <!-- Notification Dropdown -->
                    <div id="notif-dropdown" style="display: none; position: absolute; right: 0; top: 100%; width: 360px; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; z-index: 100; overflow: hidden; margin-top: 8px;">
                        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="font-size: 15px; font-weight: 700; color: #1e293b;">Notifikasi</h3>
                            <a href="pesanan.php" style="font-size: 12px; color: var(--primary); font-weight: 600; text-decoration: none;">Lihat Semua</a>
                        </div>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php if (empty($notifications)): ?>
                                <div style="padding: 32px 20px; text-align: center; color: #94a3b8; font-size: 13px;">
                                    <span class="material-symbols-outlined" style="font-size: 32px; display: block; margin-bottom: 8px;">notifications_off</span>
                                    Belum ada notifikasi
                                </div>
                            <?php else: ?>
                                <?php foreach ($notifications as $notif): ?>
                                <a href="pesanan.php" style="display: block; padding: 14px 20px; border-bottom: 1px solid #f1f5f9; text-decoration: none; <?php echo !$notif['dibaca'] ? 'background: #fffbeb;' : ''; ?>">
                                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                                        <span class="material-symbols-outlined" style="font-size: 20px; color: <?php echo !$notif['dibaca'] ? 'var(--primary)' : '#94a3b8'; ?>;"><?php echo !$notif['dibaca'] ? 'circle_notifications' : 'notifications'; ?></span>
                                        <div style="flex: 1;">
                                            <div style="font-size: 13px; font-weight: 600; color: #1e293b;"><?php echo e($notif['judul']); ?></div>
                                            <div style="font-size: 12px; color: #64748b; margin-top: 2px;"><?php echo e($notif['pesan']); ?></div>
                                            <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;"><?php echo e(date('d M H:i', strtotime($notif['dibuat_pada']))); ?></div>
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($unread_notif > 0): ?>
                        <div style="padding: 12px 20px; border-top: 1px solid #e2e8f0; text-align: center;">
                            <a href="dashboard.php?mark_read=1" style="font-size: 12px; color: #64748b; font-weight: 600; text-decoration: none;">Tandai semua sudah dibaca</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Mark notifications as read -->
            <?php if (isset($_GET['mark_read'])): ?>
                <?php $pdo->exec("UPDATE notifikasi SET dibaca = 1 WHERE dibaca = 0"); ?>
                <meta http-equiv="refresh" content="0;url=dashboard.php">
            <?php endif; ?>

            <?php if (!empty($updated_msg)): ?>
                <div class="alert alert-success" style="margin-bottom: 24px; padding: 12px 20px; font-size: 14px; border-radius: 8px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; display: flex; align-items: center; gap: 10px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">info</span>
                    <div><?php echo e($updated_msg); ?></div>
                </div>
            <?php endif; ?>

            <!-- Statistics Grid -->
            <div class="grid grid-4" style="margin-bottom: 48px; gap: 20px;">
                <div class="card" style="padding: 24px; border-radius: 12px; background: white; border: 1px solid rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px;">
                    <div style="background: #eff6ff; color: #2563eb; width: 60px; height: 60px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 32px;">category</span>
                    </div>
                    <div>
                        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Produk</div>
                        <div style="font-size: 28px; font-weight: 800; color: var(--primary); margin-top: 4px;"><?php echo e($total_products); ?></div>
                    </div>
                </div>
                <div class="card" style="padding: 24px; border-radius: 12px; background: white; border: 1px solid rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px;">
                    <div style="background: #ecfdf5; color: #10b981; width: 60px; height: 60px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 32px;">shopping_cart</span>
                    </div>
                    <div>
                        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Total Pesanan</div>
                        <div style="font-size: 28px; font-weight: 800; color: var(--primary); margin-top: 4px;"><?php echo e($total_orders); ?></div>
                    </div>
                </div>
                <div class="card" style="padding: 24px; border-radius: 12px; background: white; border: 1px solid rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px;">
                    <div style="background: #fef3c7; color: #d97706; width: 60px; height: 60px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 32px;">hourglass_empty</span>
                    </div>
                    <div>
                        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Pending</div>
                        <div style="font-size: 28px; font-weight: 800; color: #d97706; margin-top: 4px;"><?php echo e($pending_count); ?></div>
                    </div>
                </div>
                <div class="card" style="padding: 24px; border-radius: 12px; background: white; border: 1px solid rgba(0,0,0,0.05); display: flex; align-items: center; gap: 20px;">
                    <div style="background: #ecfdf5; color: #059669; width: 60px; height: 60px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 32px;">payments</span>
                    </div>
                    <div>
                        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Pendapatan</div>
                        <div style="font-size: 20px; font-weight: 800; color: #059669; margin-top: 4px;"><?php echo e(format_rupiah($total_revenue)); ?></div>
                    </div>
                </div>
            </div>

            <!-- Orders Table Section -->
            <div class="card" style="padding: 0; border-radius: 16px; border: 1px solid rgba(0,0,0,0.05); background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.02); overflow: hidden;">
                <div style="padding: 24px; border-bottom: 1px solid var(--outline-variant); display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="font-size: 20px; font-weight: 800; color: var(--secondary); margin: 0;">Daftar Transaksi Terbaru</h2>
                    <a href="pesanan.php" style="font-size: 12px; color: var(--primary); font-weight: 700; text-decoration: none;">Lihat Semua</a>
                </div>
                
                <table class="admin-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="font-size: 12px; text-transform: uppercase; padding: 16px 24px; color: #475569; font-weight: 700;">ID Pesanan</th>
                            <th style="font-size: 12px; text-transform: uppercase; padding: 16px 24px; color: #475569; font-weight: 700;">Pelanggan</th>
                            <th style="font-size: 12px; text-transform: uppercase; padding: 16px 24px; color: #475569; font-weight: 700;">Metode Bayar</th>
                            <th style="font-size: 12px; text-transform: uppercase; padding: 16px 24px; color: #475569; font-weight: 700;">Status</th>
                            <th style="font-size: 12px; text-transform: uppercase; padding: 16px 24px; color: #475569; font-weight: 700;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                        <?php
                            $sc = strtolower($order['status'] ?? 'pending');
                            $badge = 'background: #fef3c7; color: #d97706;';
                            if ($sc === 'completed') { $badge = 'background: #d1fae5; color: #065f46;'; }
                            elseif ($sc === 'shipped') { $badge = 'background: #dbeafe; color: #1e40af;'; }
                            elseif ($sc === 'processed') { $badge = 'background: #e0f2fe; color: #0369a1;'; }
                        ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 16px 24px; font-weight: 700; color: var(--primary);">#MBM-<?php echo e($order['pesanan_id']); ?></td>
                            <td style="padding: 16px 24px; color: #334155; font-weight: 600;"><?php echo e($order['user_name']); ?></td>
                            <td style="padding: 16px 24px; color: #475569; font-weight: 500;"><?php echo e($order['metode_pembayaran']); ?></td>
                            <td style="padding: 16px 24px;">
                                <form action="" method="POST" style="margin: 0;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?php echo $order['pesanan_id']; ?>">
                                    <select name="status" onchange="this.form.submit()" style="padding: 6px 10px; border-radius: 8px; font-size: 12px; font-weight: 700; border: 1px solid #cbd5e1; cursor: pointer; outline: none; <?php echo $badge; ?>">
                                        <option value="Pending" <?php if ($sc === 'pending') echo 'selected'; ?>>Pending</option>
                                        <option value="Processed" <?php if ($sc === 'processed') echo 'selected'; ?>>Diproses</option>
                                        <option value="Shipped" <?php if ($sc === 'shipped') echo 'selected'; ?>>Dikirim</option>
                                        <option value="Completed" <?php if ($sc === 'completed') echo 'selected'; ?>>Selesai</option>
                                    </select>
                                </form>
                            </td>
                            <td style="padding: 16px 24px; font-weight: 700; color: #0f172a;"><?php echo e(format_rupiah($order['total_harga'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<script>
function toggleNotif() {
    const el = document.getElementById('notif-dropdown');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
    const el = document.getElementById('notif-dropdown');
    const btn = e.target.closest('button');
    if (!btn || !btn.querySelector('.material-symbols-outlined')) {
        if (el) el.style.display = 'none';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
