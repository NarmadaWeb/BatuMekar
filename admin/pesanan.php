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
                        $stmt_up_order = $pdo->prepare("UPDATE pesanan SET status = 'Returned' WHERE pesanan_id = ?");
                        $stmt_up_order->execute([$ret_req['pesanan_id']]);
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
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php if (empty($orders)): ?>
                    <div class="card" style="text-align: center; padding: 48px;">
                        <span class="material-symbols-outlined" style="font-size: 64px; color: var(--outline); margin-bottom: 16px;">receipt_long</span>
                        <h2 style="font-size: 20px; color: var(--secondary); margin-bottom: 8px;">Tidak Ada Pesanan</h2>
                        <p style="color: var(--on-surface-variant);">Belum ada pesanan yang masuk.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <?php
                        $s = strtolower($order['status'] ?? 'pending');
                        $badge_style = 'background: #fffbeb; color: #d97706; border: 1px solid #fde68a;';
                        $status_text = 'Menunggu Pembayaran';
                        
                        if ($s === 'completed') {
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
                    <div class="order-card" style="border-radius: 16px; background: white; border: 1px solid rgba(0,0,0,0.05); overflow: hidden;">
                        <div class="order-header" style="background: #f8fafc; padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div>
                                    <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">ID Pesanan</span>
                                    <div style="font-weight: 800; font-size: 16px; color: var(--primary); margin-top: 2px;">#MBM-<?php echo e($order['pesanan_id']); ?></div>
                                </div>
                                <span style="font-size: 12px; font-weight: 700; padding: 6px 12px; border-radius: 20px; display: inline-block; <?php echo $badge_style; ?>"><?php echo $status_text; ?></span>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Tanggal</span>
                                <div style="font-weight: 600; font-size: 14px; color: #334155; margin-top: 2px;"><?php echo e(date('d M Y, H:i', strtotime($order['dibuat_pada']))); ?></div>
                            </div>
                        </div>

                        <div class="order-body" style="padding: 24px;">
                            <!-- Customer Info -->
                            <div style="display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 20px;">
                                <div style="flex: 1; min-width: 200px;">
                                    <div style="font-size: 12px; color: var(--secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Data Pelanggan</div>
                                    <div style="font-size: 14px; color: var(--on-surface-variant); line-height: 1.8;">
                                        <strong>Nama:</strong> <?php echo e($order['user_name']); ?><br>
                                        <strong>Username:</strong> <?php echo e($order['user_username']); ?><br>
                                        <strong>Telepon:</strong> <?php echo e($order['user_phone'] ?: '-'); ?>
                                    </div>
                                </div>
                                <div style="flex: 1; min-width: 200px;">
                                    <div style="font-size: 12px; color: var(--secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Detail Pengiriman</div>
                                    <div style="font-size: 14px; color: var(--on-surface-variant); line-height: 1.8;">
                                        <strong>Alamat:</strong> <?php echo e($order['alamat_pengiriman']); ?><br>
                                        <strong>Metode Kirim:</strong> <?php echo e($order['metode_pengiriman']); ?><br>
                                        <strong>Pembayaran:</strong> <?php echo e($order['metode_pembayaran']); ?>
                                    </div>
                                </div>
                                <div style="min-width: 180px; text-align: right;">
                                    <div style="font-size: 12px; color: var(--secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Total</div>
                                    <div style="font-size: 24px; font-weight: 800; color: var(--primary);"><?php echo e(format_rupiah($order['total_harga'])); ?></div>
                                </div>
                            </div>

                            <!-- Items -->
                            <?php if (!empty($order['items'])): ?>
                            <div style="border-top: 1px solid #e2e8f0; padding-top: 16px; margin-bottom: 16px;">
                                <div style="font-size: 12px; color: var(--secondary); font-weight: 700; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;">Item Pesanan</div>
                                <?php foreach ($order['items'] as $item): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <?php if (!empty($item['product_image'])): ?>
                                            <img src="<?php echo e(img_url($item['product_image'])); ?>" alt="<?php echo e($item['product_name']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight: 600; color: #334155; font-size: 14px;"><?php echo e($item['product_name']); ?></div>
                                            <div style="font-size: 12px; color: #94a3b8;">
                                                <?php if ($item['ukuran_label']): ?><span style="color: #2563eb; font-weight: 600;"><?php echo e($item['ukuran_label']); ?></span> &middot; <?php endif; ?>
                                                <?php echo e($item['jumlah']); ?> x <?php echo e(format_rupiah($item['harga'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="font-weight: 700; color: var(--secondary); font-size: 14px;">
                                        <?php echo e(format_rupiah($item['jumlah'] * $item['harga'])); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Return Request Detail -->
                            <?php if (!empty($order['return_request'])): 
                                $ret = $order['return_request'];
                                $ret_status = $ret['status'];
                                $ret_bg = '#fef3c7';
                                $ret_border = '#fde68a';
                                $ret_fg = '#92400e';
                                if ($ret_status === 'Disetujui') {
                                    $ret_bg = '#ecfdf5';
                                    $ret_border = '#a7f3d0';
                                    $ret_fg = '#065f46';
                                } elseif ($ret_status === 'Ditolak') {
                                    $ret_bg = '#fef2f2';
                                    $ret_border = '#fca5a5';
                                    $ret_fg = '#991b1b';
                                }
                            ?>
                            <div style="margin-top: 16px; padding: 18px; border-radius: 12px; background: <?php echo $ret_bg; ?>; border: 1px solid <?php echo $ret_border; ?>; color: <?php echo $ret_fg; ?>; margin-bottom: 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                    <div style="font-weight: 800; font-size: 14px; display: flex; align-items: center; gap: 6px; color: <?php echo $ret_fg; ?>;">
                                        <span class="material-symbols-outlined">assignment_return</span>
                                        Detail Pengajuan Pengembalian (Return)
                                    </div>
                                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; background: rgba(0,0,0,0.05); padding: 4px 8px; border-radius: 4px;"><?php echo $ret_status; ?></span>
                                </div>
                                <div style="font-size: 14px; line-height: 1.6; margin-bottom: 12px;">
                                    <strong>Alasan:</strong> <?php echo e($ret['alasan']); ?>
                                </div>
                                <div style="margin-bottom: 16px;">
                                    <strong>Bukti File:</strong><br>
                                    <?php 
                                        $file_path = '../' . $ret['bukti_file']; 
                                        $ext = strtolower(pathinfo($ret['bukti_file'], PATHINFO_EXTENSION));
                                        $is_video = in_array($ext, ['mp4', 'mov', 'avi', 'webm', '3gp']);
                                    ?>
                                    <?php if ($is_video): ?>
                                        <video src="<?php echo e(img_url($ret['bukti_file'])); ?>" controls style="max-width: 100%; max-height: 240px; border-radius: 8px; margin-top: 8px; border: 1px solid rgba(0,0,0,0.1);"></video>
                                    <?php else: ?>
                                        <a href="<?php echo e(img_url($ret['bukti_file'])); ?>" target="_blank" style="display: inline-block; margin-top: 8px;">
                                            <img src="<?php echo e(img_url($ret['bukti_file'])); ?>" alt="Bukti Return" style="max-width: 100%; max-height: 180px; border-radius: 8px; border: 1px solid rgba(0,0,0,0.1); object-fit: contain;">
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($ret_status === 'Pending'): ?>
                                    <div style="display: flex; gap: 8px;">
                                        <form action="pesanan.php" method="POST" style="margin: 0;">
                                            <input type="hidden" name="action" value="handle_return">
                                            <input type="hidden" name="return_id" value="<?php echo $ret['pengembalian_id']; ?>">
                                            <input type="hidden" name="status" value="Disetujui">
                                            <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 12px; font-weight: 700; border-radius: 6px; background: #059669; border-color: #059669; cursor: pointer; color: white;">
                                                Setujui Return
                                            </button>
                                        </form>
                                        <form action="pesanan.php" method="POST" style="margin: 0;">
                                            <input type="hidden" name="action" value="handle_return">
                                            <input type="hidden" name="return_id" value="<?php echo $ret['pengembalian_id']; ?>">
                                            <input type="hidden" name="status" value="Ditolak">
                                            <button type="submit" class="btn" style="padding: 8px 16px; font-size: 12px; font-weight: 700; border-radius: 6px; color: #dc2626; border: 1px solid #fca5a5; background: #fef2f2; cursor: pointer;">
                                                Tolak Return
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Action: Update Status -->
                            <div style="border-top: 1px solid #e2e8f0; padding-top: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                                <form action="pesanan.php" method="POST" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?php echo $order['pesanan_id']; ?>">
                                    <span style="font-size: 12px; font-weight: 600; color: #64748b;">Ubah Status:</span>
                                    <select name="status" onchange="this.form.submit()" style="padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; border: 1px solid #cbd5e1; cursor: pointer; outline: none;">
                                        <option value="Pending" <?php if ($s === 'pending') echo 'selected'; ?>>Menunggu Pembayaran</option>
                                        <option value="Processed" <?php if ($s === 'processed') echo 'selected'; ?>>Diproses</option>
                                        <option value="Shipped" <?php if ($s === 'shipped') echo 'selected'; ?>>Dikirim</option>
                                        <option value="Completed" <?php if ($s === 'completed') echo 'selected'; ?>>Selesai</option>
                                        <option value="Returned" <?php if ($s === 'returned') echo 'selected'; ?>>Returned / Dikembalikan</option>
                                    </select>
                                </form>

                                <?php if ($order['bukti_pembayaran']): ?>
                                    <a href="<?php echo e(img_url($order['bukti_pembayaran'])); ?>" target="_blank" class="btn" style="padding: 8px 14px; font-size: 12px; background: #f1f5f9; color: #334155; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">image</span> Lihat Bukti Bayar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
