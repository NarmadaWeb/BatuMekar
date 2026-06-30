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
        dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (pesanan_id) REFERENCES pesanan(pesanan_id) ON DELETE SET NULL
    )");
} catch (PDOException $e) {}

try {
    $pdo->exec("ALTER TABLE detail_pesanan ADD COLUMN ukuran_id INTEGER DEFAULT NULL");
} catch (PDOException $e) {}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS ukuran_produk (
        ukuran_id INTEGER PRIMARY KEY AUTOINCREMENT,
        produk_id INTEGER NOT NULL,
        ukuran_ml INTEGER NOT NULL,
        harga REAL DEFAULT NULL,
        stok INTEGER DEFAULT 0,
        FOREIGN KEY (produk_id) REFERENCES produk(produk_id) ON DELETE CASCADE
    )");
} catch (PDOException $e) {}

try {
    $pdo->exec("ALTER TABLE pesanan ADD COLUMN snap_token TEXT DEFAULT NULL");
} catch (PDOException $e) {}

$msg = '';
$error = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $order_id = (int)$_POST['order_id'];
        $new_status = trim($_POST['status'] ?? '');
        if (in_array($new_status, ['Pending', 'Processed', 'Shipped', 'Completed', 'Returned'])) {
            try {
                $stmt = $pdo->prepare("UPDATE pesanan SET status = ? WHERE pesanan_id = ?");
                $stmt->execute([$new_status, $order_id]);
                $msg = "Status pesanan #MBM-{$order_id} berhasil diperbarui.";
            } catch (PDOException $e) {
                $error = "Gagal memperbarui status: " . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'handle_return') {
        $return_id = (int)$_POST['return_id'];
        $new_status = trim($_POST['status'] ?? '');
        if (in_array($new_status, ['Disetujui', 'Ditolak'])) {
            try {
                $stmt = $pdo->prepare("UPDATE pengembalian_pesanan SET status = ? WHERE pengembalian_id = ?");
                $stmt->execute([$new_status, $return_id]);
                
                // Get the return request info to find order ID
                $stmt_ret = $pdo->prepare("SELECT * FROM pengembalian_pesanan WHERE pengembalian_id = ?");
                $stmt_ret->execute([$return_id]);
                $ret_req = $stmt_ret->fetch();
                
                if ($ret_req) {
                    if ($new_status === 'Disetujui') {
                        // Mark order as Returned
                        $stmt_up_order = $pdo->prepare("UPDATE pesanan SET status = 'Returned' WHERE pesanan_id = ?");
                        $stmt_up_order->execute([$ret_req['pesanan_id']]);

                        // Refund to user saldo
                        $stmt_order = $pdo->prepare("SELECT pengguna_id, total_harga FROM pesanan WHERE pesanan_id = ?");
                        $stmt_order->execute([$ret_req['pesanan_id']]);
                        $order_info = $stmt_order->fetch();
                        if ($order_info) {
                            $stmt_refund = $pdo->prepare("UPDATE pengguna SET saldo = saldo + ? WHERE pengguna_id = ?");
                            $stmt_refund->execute([$order_info['total_harga'], $order_info['pengguna_id']]);
                        }
                    }
                }
                
                $msg = "Pengajuan pengembalian berhasil di-" . strtolower($new_status) . ".";
            } catch (PDOException $e) {
                $error = "Gagal memperbarui status pengembalian: " . $e->getMessage();
            }
        }
    }
}

// Filters
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT pesanan.*, pengguna.nama as user_name, pengguna.username as user_username, pengguna.telepon as user_phone FROM pesanan JOIN pengguna ON pesanan.pengguna_id = pengguna.pengguna_id";
$conditions = [];
$params = [];

if ($status_filter) {
    $conditions[] = "pesanan.status = ?";
    $params[] = $status_filter;
}
if ($search) {
    $conditions[] = "(pengguna.nama LIKE ? OR pesanan.pesanan_id LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY pesanan.dibuat_pada DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get order items for each order
$orders_with_items = [];
foreach ($orders as $order) {
    $stmt_items = $pdo->prepare("
        SELECT oi.*, p.nama as product_name, p.gambar as product_image
        FROM detail_pesanan oi
        JOIN produk p ON oi.produk_id = p.produk_id
        WHERE oi.pesanan_id = ?
    ");
    $stmt_items->execute([$order['pesanan_id']]);
    $order['items'] = $stmt_items->fetchAll();
    // Add size info to items
    foreach ($order['items'] as &$item) {
        $size_label = '';
        if (!empty($item['ukuran_id'])) {
            $size_info = get_size_by_id($pdo, $item['ukuran_id']);
            if ($size_info) $size_label = $size_info['ukuran_ml'] . ' ml';
        }
        $item['ukuran_label'] = $size_label;
    }
    unset($item);
    
    // Fetch return request if any
    $stmt_ret = $pdo->prepare("SELECT * FROM pengembalian_pesanan WHERE pesanan_id = ?");
    $stmt_ret->execute([$order['pesanan_id']]);
    $order['return_request'] = $stmt_ret->fetch();
    
    $orders_with_items[] = $order;
}
$orders = $orders_with_items;

// Stats
$total_orders = $pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn();
$pending_count = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'Pending'")->fetchColumn();
$processed_count = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'Processed'")->fetchColumn();
$completed_count = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'Completed'")->fetchColumn();
$total_revenue = $pdo->query("SELECT COALESCE(SUM(total_harga), 0) FROM pesanan WHERE status = 'Completed'")->fetchColumn();

$page_title = 'Manajemen Pesanan';
require_once __DIR__ . '/../includes/header.php';
?>
<main style="padding: 48px 0; background: var(--background); font-family: 'Inter', sans-serif;">
    <div class="container" style="display: flex; gap: 48px; align-items: flex-start;">
        <aside style="width: 250px; flex-shrink: 0;">
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link">
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
                <a href="pesanan.php" class="sidebar-link active">
                    <span class="material-symbols-outlined">receipt_long</span>
                    Pesanan
                </a>
                <a href="../logout.php" class="sidebar-link" style="color: var(--error) !important;">
                    <span class="material-symbols-outlined" style="color: var(--error) !important;">logout</span>
                    Keluar
                </a>
            </nav>
        </aside>

        <section style="flex-grow: 1;">
            <h1 style="font-size: 32px; margin-bottom: 32px; color: var(--secondary); font-weight: 800;">Manajemen Pesanan</h1>

            <?php if ($msg): ?>
                <div class="alert alert-success" style="margin-bottom: 24px; padding: 12px 20px; border-radius: 8px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; display: flex; align-items: center; gap: 10px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">check_circle</span>
                    <div><?php echo e($msg); ?></div>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger" style="margin-bottom: 24px; padding: 12px 20px; border-radius: 8px; background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; display: flex; align-items: center; gap: 10px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">error</span>
                    <div><?php echo e($error); ?></div>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="grid grid-4" style="margin-bottom: 32px; gap: 16px;">
                <div class="card" style="padding: 20px; display: flex; align-items: center; gap: 16px;">
                    <div style="background: #eff6ff; color: #2563eb; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 24px;">receipt_long</span>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total</div>
                        <div style="font-size: 22px; font-weight: 800; color: var(--primary);"><?php echo e($total_orders); ?></div>
                    </div>
                </div>
                <div class="card" style="padding: 20px; display: flex; align-items: center; gap: 16px;">
                    <div style="background: #fffbeb; color: #d97706; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 24px;">hourglass_empty</span>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Pending</div>
                        <div style="font-size: 22px; font-weight: 800; color: #d97706;"><?php echo e($pending_count); ?></div>
                    </div>
                </div>
                <div class="card" style="padding: 20px; display: flex; align-items: center; gap: 16px;">
                    <div style="background: #e0f2fe; color: #0369a1; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 24px;">local_shipping</span>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Diproses</div>
                        <div style="font-size: 22px; font-weight: 800; color: #0369a1;"><?php echo e($processed_count); ?></div>
                    </div>
                </div>
                <div class="card" style="padding: 20px; display: flex; align-items: center; gap: 16px;">
                    <div style="background: #ecfdf5; color: #059669; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 24px;">payments</span>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Pendapatan</div>
                        <div style="font-size: 22px; font-weight: 800; color: #059669;"><?php echo e(format_rupiah($total_revenue)); ?></div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="card" style="padding: 20px 24px; margin-bottom: 24px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap;">
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="pesanan.php" class="btn <?php echo !$status_filter ? 'btn-primary' : 'btn-secondary'; ?>" style="padding: 8px 16px; font-size: 13px; text-decoration: none; border-radius: 8px;">Semua</a>
                    <a href="pesanan.php?status=Pending" class="btn <?php echo $status_filter === 'Pending' ? 'btn-primary' : 'btn-secondary'; ?>" style="padding: 8px 16px; font-size: 13px; text-decoration: none; border-radius: 8px;">Pending</a>
                    <a href="pesanan.php?status=Processed" class="btn <?php echo $status_filter === 'Processed' ? 'btn-primary' : 'btn-secondary'; ?>" style="padding: 8px 16px; font-size: 13px; text-decoration: none; border-radius: 8px;">Diproses</a>
                    <a href="pesanan.php?status=Shipped" class="btn <?php echo $status_filter === 'Shipped' ? 'btn-primary' : 'btn-secondary'; ?>" style="padding: 8px 16px; font-size: 13px; text-decoration: none; border-radius: 8px;">Dikirim</a>
                    <a href="pesanan.php?status=Completed" class="btn <?php echo $status_filter === 'Completed' ? 'btn-primary' : 'btn-secondary'; ?>" style="padding: 8px 16px; font-size: 13px; text-decoration: none; border-radius: 8px;">Selesai</a>
                    <a href="pesanan.php?status=Returned" class="btn <?php echo $status_filter === 'Returned' ? 'btn-primary' : 'btn-secondary'; ?>" style="padding: 8px 16px; font-size: 13px; text-decoration: none; border-radius: 8px;">Returned</a>
                </div>
                <form action="pesanan.php" method="GET" style="display: flex; gap: 8px;">
                    <?php if ($status_filter): ?>
                        <input type="hidden" name="status" value="<?php echo e($status_filter); ?>">
                    <?php endif; ?>
                    <input type="text" name="search" class="form-control" style="width: 200px; padding: 8px 14px; font-size: 13px;" placeholder="Cari pelanggan..." value="<?php echo e($search); ?>">
                    <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px; border-radius: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">search</span>
                    </button>
                </form>
            </div>

            <!-- Orders List -->
            <div class="card" style="padding: 0; overflow: hidden; border-radius: 12px;">
                <div class="table-responsive">
                    <table class="table" style="width: 100%; border-collapse: collapse;">
                        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                            <tr>
                                <th style="padding: 16px 24px; text-align: left; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Pesanan</th>
                                <th style="padding: 16px 24px; text-align: left; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Pelanggan</th>
                                <th style="padding: 16px 24px; text-align: left; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Status</th>
                                <th style="padding: 16px 24px; text-align: right; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total</th>
                                <th style="padding: 16px 24px; text-align: center; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="5" style="padding: 48px; text-align: center; color: #64748b;">
                                        <span class="material-symbols-outlined" style="font-size: 48px; display: block; margin-bottom: 12px;">receipt_long</span>
                                        Tidak ada pesanan ditemukan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                <?php
                                    $s = strtolower($order['status'] ?? 'pending');
                                    $badge_style = 'background: #fffbeb; color: #d97706; border: 1px solid #fde68a;';
                                    $status_text = 'Menunggu Pembayaran';

                                    if ($s === 'completed' || $s === 'selesai') {
                                        $badge_style = 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;';
                                        $status_text = 'Selesai';
                                    } elseif ($s === 'shipped') {
                                        $badge_style = 'background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd;';
                                        $status_text = 'Dikirim';
                                    } elseif ($s === 'processed') {
                                        $badge_style = 'background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;';
                                        $status_text = 'Diproses';
                                    } elseif ($s === 'returned') {
                                        $badge_style = 'background: #f5f5f4; color: #57534e; border: 1px solid #e7e5e4;';
                                        $status_text = 'Returned';
                                    } elseif ($s === 'cancelled' || $s === 'canceled') {
                                        $badge_style = 'background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5;';
                                        $status_text = 'Dibatalkan';
                                    }
                                ?>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 16px 24px;">
                                        <div style="font-weight: 800; color: var(--primary);">#MBM-<?php echo e($order['pesanan_id']); ?></div>
                                        <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;"><?php echo e(date('d/m/Y H:i', strtotime($order['dibuat_pada']))); ?></div>
                                    </td>
                                    <td style="padding: 16px 24px;">
                                        <div style="font-weight: 600; color: #334155;"><?php echo e($order['user_name']); ?></div>
                                        <div style="font-size: 12px; color: #64748b;"><?php echo e($order['user_phone'] ?: '-'); ?></div>
                                    </td>
                                    <td style="padding: 16px 24px;">
                                        <span style="font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; display: inline-block; white-space: nowrap; <?php echo $badge_style; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 16px 24px; text-align: right; font-weight: 700; color: var(--secondary);">
                                        <?php echo e(format_rupiah($order['total_harga'])); ?>
                                    </td>
                                    <td style="padding: 16px 24px; text-align: center;">
                                        <button type="button" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; border-radius: 6px;" onclick="toggleDetail('detail-<?php echo $order['pesanan_id']; ?>')">Detail</button>
                                    </td>
                                </tr>
                                <tr id="detail-<?php echo $order['pesanan_id']; ?>" style="display: none; background: #f8fafc;">
                                    <td colspan="5" style="padding: 24px; border-bottom: 1px solid #e2e8f0;">
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px;">
                                            <div>
                                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Alamat Pengiriman</div>
                                                <div style="font-size: 13px; line-height: 1.6; color: #334155;">
                                                    <?php echo e($order['alamat_pengiriman']); ?><br>
                                                    <strong>Metode:</strong> <?php echo e($order['metode_pengiriman']); ?><br>
                                                    <strong>Pembayaran:</strong> <?php echo e($order['metode_pembayaran']); ?>
                                                </div>
                                                <?php if ($order['latitude'] && $order['longitude']): ?>
                                                    <div style="margin-top: 8px;">
                                                        <a href="https://www.google.com/maps?q=<?php echo $order['latitude']; ?>,<?php echo $order['longitude']; ?>" target="_blank" style="font-size: 12px; color: #2563eb; display: flex; align-items: center; gap: 4px; text-decoration: none;">
                                                            <span class="material-symbols-outlined" style="font-size: 16px;">map</span> Lihat di Maps
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Item Pesanan</div>
                                                <?php foreach ($order['items'] as $item): ?>
                                                    <div style="font-size: 13px; margin-bottom: 4px; display: flex; justify-content: space-between;">
                                                        <span><?php echo e($item['jumlah']); ?>x <?php echo e($item['product_name']); ?> <?php echo $item['ukuran_label'] ? "({$item['ukuran_label']})" : ''; ?></span>
                                                        <span style="font-weight: 600;"><?php echo e(format_rupiah($item['jumlah'] * $item['harga'])); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                                <div style="border-top: 1px solid #cbd5e1; margin-top: 8px; padding-top: 4px; font-size: 13px; display: flex; justify-content: space-between;">
                                                    <span>Ongkos Kirim:</span>
                                                    <span><?php echo e(format_rupiah($order['ongkos_kirim'] ?? 0)); ?></span>
                                                </div>
                                            </div>
                                            <div>
                                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Aksi Cepat</div>
                                                <form action="pesanan.php" method="POST" style="margin-bottom: 12px;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['pesanan_id']; ?>">
                                                    <select name="status" class="form-control" style="font-size: 12px; padding: 6px; margin-bottom: 8px;" onchange="this.form.submit()">
                                                        <option value="Pending" <?php if ($s === 'pending') echo 'selected'; ?> disabled>Menunggu Pembayaran</option>
                                                        <option value="Processed" <?php if ($s === 'processed') echo 'selected'; ?>>Diproses</option>
                                                        <option value="Shipped" <?php if ($s === 'shipped') echo 'selected'; ?>>Dikirim</option>
                                                        <option value="Returned" <?php if ($s === 'returned') echo 'selected'; ?>>Returned / Dikembalikan</option>
                                                    </select>
                                                </form>
                                                <?php if ($order['bukti_pembayaran']): ?>
                                                    <a href="<?php echo e(img_url($order['bukti_pembayaran'])); ?>" target="_blank" class="btn btn-secondary" style="font-size: 11px; padding: 6px 10px; width: 100%; text-align: center; text-decoration: none; display: block;">Lihat Bukti Bayar</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if (!empty($order['return_request'])): ?>
                                            <div style="margin-top: 20px; padding: 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;">
                                                <div style="font-weight: 700; color: #92400e; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                                    <span class="material-symbols-outlined" style="font-size: 18px;">assignment_return</span> Pengajuan Return
                                                </div>
                                                <p style="font-size: 13px; color: #92400e; margin-bottom: 12px;"><strong>Alasan:</strong> <?php echo e($order['return_request']['alasan']); ?></p>
                                                <?php if ($order['return_request']['status'] === 'Pending'): ?>
                                                    <div style="display: flex; gap: 8px;">
                                                        <form action="pesanan.php" method="POST">
                                                            <input type="hidden" name="action" value="handle_return">
                                                            <input type="hidden" name="return_id" value="<?php echo $order['return_request']['pengembalian_id']; ?>">
                                                            <input type="hidden" name="status" value="Disetujui">
                                                            <button type="submit" class="btn btn-primary" style="font-size: 11px; padding: 6px 12px; background: #059669; border: none;">Setujui & Refund</button>
                                                        </form>
                                                        <form action="pesanan.php" method="POST">
                                                            <input type="hidden" name="action" value="handle_return">
                                                            <input type="hidden" name="return_id" value="<?php echo $order['return_request']['pengembalian_id']; ?>">
                                                            <input type="hidden" name="status" value="Ditolak">
                                                            <button type="submit" class="btn" style="font-size: 11px; padding: 6px 12px; background: #dc2626; color: white; border: none;">Tolak</button>
                                                        </form>
                                                    </div>
                                                <?php else: ?>
                                                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #92400e;">Status: <?php echo $order['return_request']['status']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                function toggleDetail(id) {
                    const el = document.getElementById(id);
                    if (el.style.display === 'none') {
                        el.style.display = 'table-row';
                    } else {
                        el.style.display = 'none';
                    }
                }
            </script>
        </section>
    </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
