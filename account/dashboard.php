<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_login();

$stmt = $pdo->prepare("SELECT * FROM pengguna WHERE pengguna_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    // If user record no longer exists, destroy session and redirect
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Fetch recent orders
$stmt_orders = $pdo->prepare("SELECT * FROM pesanan WHERE pengguna_id = ? ORDER BY dibuat_pada DESC LIMIT 3");
$stmt_orders->execute([$_SESSION['user_id']]);
$recent_orders = $stmt_orders->fetchAll();

$page_title = 'Dashboard Pengguna';
require_once __DIR__ . '/../includes/header.php';
?>

<main style="padding: 48px 0; background: var(--background);">
    <div class="container" style="display: flex; gap: 48px; align-items: flex-start;">
        <!-- Sidebar Navigation -->
        <aside style="width: 280px; flex-shrink: 0;">
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link active">
                    <span class="material-symbols-outlined">dashboard</span>
                    Dashboard
                </a>
                <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
                <a href="orders.php" class="sidebar-link">
                    <span class="material-symbols-outlined">shopping_bag</span>
                    Pesanan Saya
                </a>
                <?php endif; ?>
                <a href="profile.php" class="sidebar-link">
                    <span class="material-symbols-outlined">person</span>
                    Profil Saya
                </a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="../admin/dashboard.php" class="sidebar-link" style="color: var(--primary) !important;">
                    <span class="material-symbols-outlined" style="color: var(--primary) !important;">admin_panel_settings</span>
                    Panel Admin
                </a>
                <?php endif; ?>
                <a href="../logout.php" class="sidebar-link" style="color: var(--error) !important;">
                    <span class="material-symbols-outlined" style="color: var(--error) !important;">logout</span>
                    Keluar
                </a>
            </nav>
        </aside>

        <!-- Main Dashboard Content -->
        <section style="flex-grow: 1;">
            <!-- Welcome Header Card -->
            <div class="welcome-card" style="margin-bottom: 32px;">
                <div>
                    <span class="badge badge-completed" style="margin-bottom: 12px; background: rgba(120, 89, 0, 0.1); color: var(--primary);">Portal Pengguna</span>
                    <h1 style="font-size: 32px; margin-bottom: 8px; color: var(--on-primary-container);">Selamat datang kembali, <?php echo e($user['nama']); ?>!</h1>
                    <p style="opacity: 0.9; color: var(--on-primary-container); margin-bottom: 0;">Perjalanan rasa madu hutan alami Anda berlanjut. Jelajahi hasil panen terbaru kami.</p>
                </div>
                <?php if (!empty($user['foto_profil']) && file_exists(__DIR__ . '/../' . $user['foto_profil'])): ?>
                    <img src="../<?php echo e($user['foto_profil']); ?>" alt="Profile" class="welcome-avatar" style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255, 255, 255, 0.8); box-shadow: 0 4px 10px rgba(0,0,0,0.1); flex-shrink: 0;">
                <?php else: ?>
                    <div class="welcome-avatar" style="display: flex; align-items: center; justify-content: center; background: var(--primary); color: white; font-weight: 800; font-size: 28px; border-radius: 50%; width: 70px; height: 70px; text-transform: uppercase; border: 3px solid rgba(255, 255, 255, 0.8); box-shadow: 0 4px 10px rgba(0,0,0,0.1); flex-shrink: 0;">
                        <?php echo substr(e($user['nama']), 0, 1); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Detailed Grid -->
            <div class="grid <?php echo (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? '' : 'grid-2'; ?>">
                <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
                <!-- Balance & Recent Orders Card -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <!-- Balance Card -->
                    <div class="card" style="padding: 24px; background: linear-gradient(135deg, var(--secondary) 0%, #1e293b 100%); color: white;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <div style="font-size: 13px; font-weight: 600; opacity: 0.8; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Saldo Anda</div>
                                <div style="font-size: 32px; font-weight: 800; color: #fbbf24;"><?php echo format_rupiah($user['saldo'] ?? 0); ?></div>
                                <div style="font-size: 12px; opacity: 0.7; margin-top: 12px; display: flex; align-items: center; gap: 4px;">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">info</span> Saldo otomatis bertambah jika return disetujui.
                                </div>
                            </div>
                            <div style="background: rgba(255,255,255,0.1); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <span class="material-symbols-outlined" style="font-size: 28px; color: #fbbf24;">account_balance_wallet</span>
                            </div>
                        </div>
                    </div>

                <!-- Recent Orders Card -->
                <div class="card" style="padding: 28px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="font-size: 22px; font-weight: 700; color: var(--secondary);">Pesanan Terbaru</h2>
                        <a href="orders.php" style="font-size: 14px; color: var(--primary); font-weight: 600; display: flex; align-items: center; gap: 4px; text-decoration: none;">
                            Lihat Semua <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                        </a>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php if (empty($recent_orders)): ?>
                            <p style="color: var(--on-surface-variant); font-size: 14px; margin: 0; padding: 12px 0;">Anda belum melakukan pemesanan apa pun.</p>
                            <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
                                <a href="../katalog.php" class="btn btn-primary" style="font-size: 14px; padding: 10px 20px; align-self: flex-start;">Belanja Sekarang</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--outline-variant)/20; margin-bottom: 4px;">
                                    <div>
                                        <div style="font-weight: 700; font-size: 14px; color: var(--primary);">#MBM-<?php echo e($order['pesanan_id']); ?></div>
                                        <div style="font-size: 12px; color: var(--on-surface-variant);"><?php echo e(date('d M Y, H:i', strtotime($order['dibuat_pada']))); ?></div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-weight: 700; font-size: 14px;"><?php echo e(format_rupiah($order['total_harga'])); ?></div>
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
                                        <span class="badge <?php echo $status_class; ?>" style="font-size: 10px; padding: 2px 8px; margin-top: 4px;"><?php echo $status_text; ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
                <?php endif; ?>

                <!-- Quick Actions / Quick Links Card -->
                <div class="card" style="padding: 28px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h2 style="font-size: 22px; font-weight: 700; color: var(--secondary); margin-bottom: 16px;">Aksi Cepat</h2>
                        <p style="font-size: 14px; color: var(--on-surface-variant); margin-bottom: 24px;">Butuh bantuan atau ingin mengubah informasi detail akun Anda? Pilih opsi di bawah ini untuk melanjutkan.</p>
                        
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
                                <a href="../katalog.php" class="btn btn-primary" style="justify-content: center; font-size: 14px; padding: 12px 20px; text-decoration: none;">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">shopping_cart</span> Belanja Produk Madu
                                </a>
                            <?php endif; ?>
                            <a href="profile.php" class="btn btn-secondary" style="justify-content: center; font-size: 14px; padding: 12px 20px; border-color: var(--primary); color: var(--primary); text-decoration: none;">
                                <span class="material-symbols-outlined" style="font-size: 18px;">manage_accounts</span> Ubah Informasi Profil
                            </a>
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <a href="../admin/dashboard.php" class="btn" style="justify-content: center; font-size: 14px; padding: 12px 20px; text-decoration: none; background: #1d2b4f; color: white; border: none; display: flex; align-items: center; gap: 8px; border-radius: 8px; font-weight: 600;">
                                <span class="material-symbols-outlined" style="font-size: 18px;">admin_panel_settings</span> Buka Panel Admin
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

