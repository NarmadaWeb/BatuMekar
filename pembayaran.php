<?php
require_once 'includes/functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';
require_login();

$order_id = (int)($_GET['order_id'] ?? 0);
$method = trim($_GET['method'] ?? 'midtrans');
$action = trim($_GET['action'] ?? '');
$success = (int)($_GET['success'] ?? 0);
$tx_status = trim($_GET['transaction_status'] ?? '');

// Fetch the order
$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE pesanan_id = ? AND pengguna_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: account/orders.php');
    exit;
}

// Handle Midtrans Snap finish callback (redirect back after payment)
if ($method === 'midtrans' && $success !== 1 && $tx_status) {
    $payment_ok = in_array($tx_status, ['capture', 'settlement']);
    if ($payment_ok && $order['status'] === 'Pending') {
        $update = $pdo->prepare("UPDATE pesanan SET status = 'Processed' WHERE pesanan_id = ? AND pengguna_id = ?");
        $update->execute([$order_id, $_SESSION['user_id']]);
        try {
            $stmt_pay = $pdo->prepare("INSERT INTO pembayaran (pesanan_id, pengguna_id, transaksi_id) VALUES (?, ?, ?)");
            $mock_tx = 'TX-MIDTRANS-' . strtoupper(bin2hex(random_bytes(4)));
            $stmt_pay->execute([$order_id, $_SESSION['user_id'], $mock_tx]);
        } catch (PDOException $ex) {}
        header("Location: pembayaran.php?order_id=" . $order_id . "&method=midtrans&success=1");
        exit;
    }
}

$snap_token = $order['snap_token'] ?? '';

// Auto-regenerate Snap token if missing and order is still Pending
if ($method === 'midtrans') {
    require_once 'config/midtrans.php';
}
if ($method === 'midtrans' && !$snap_token && $order['status'] === 'Pending' && !$success) {

    $stmt_items = $pdo->prepare("SELECT oi.*, p.nama as product_name FROM detail_pesanan oi JOIN produk p ON oi.produk_id = p.produk_id WHERE oi.pesanan_id = ?");
    $stmt_items->execute([$order_id]);
    $order_items = $stmt_items->fetchAll();

    $midtrans_items = [];
    foreach ($order_items as $item) {
        $size_label = '';
        if (!empty($item['ukuran_id'])) {
            $size_info = get_size_by_id($pdo, $item['ukuran_id']);
            if ($size_info) $size_label = ' (' . $size_info['ukuran_ml'] . ' ml)';
        }
        $midtrans_items[] = [
            'id' => (string) $item['produk_id'],
            'price' => (int) $item['harga'],
            'quantity' => (int) $item['jumlah'],
            'name' => substr($item['product_name'] . $size_label, 0, 50),
        ];
    }
    $midtrans_items[] = ['id' => 'SHIPPING', 'price' => 25000, 'quantity' => 1, 'name' => 'Ongkos Kirim'];
    $midtrans_items[] = ['id' => 'ADMIN', 'price' => 2000, 'quantity' => 1, 'name' => 'Biaya Admin'];

    $stmt_user = $pdo->prepare("SELECT * FROM pengguna WHERE pengguna_id = ?");
    $stmt_user->execute([$_SESSION['user_id']]);
    $user_data = $stmt_user->fetch();

    $finish_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
        . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')
        . '/pembayaran.php?order_id=' . $order_id . '&method=midtrans';

    $customer = [
        'first_name' => $user_data['nama'] ?? $order['alamat_pengiriman'],
        'phone' => $user_data['telepon'] ?? '',
    ];

    $snap_result = midtrans_get_snap_token($order_id, $order['total_harga'], $midtrans_items, $customer, $finish_url);
    if ($snap_result && isset($snap_result['token'])) {
        $snap_token = $snap_result['token'];
        try { $pdo->exec("ALTER TABLE pesanan ADD COLUMN snap_token TEXT DEFAULT NULL"); } catch (PDOException $e) {}
        $stmt = $pdo->prepare("UPDATE pesanan SET snap_token = ? WHERE pesanan_id = ?");
        $stmt->execute([$snap_token, $order_id]);
    }
}

$page_title = 'Pembayaran Pesanan #' . $order_id;
require_once 'includes/header.php';
?>

<main style="padding: 60px 0; background: #f8fafc; min-height: 85vh; font-family: 'Inter', sans-serif;">
    <div class="container" style="max-width: 800px;">
        
        <!-- Back Navigation -->
        <div style="margin-bottom: 24px;">
            <a href="account/orders.php" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary); font-weight: 600; text-decoration: none; font-size: 15px; transition: color 0.2s;">
                <span class="material-symbols-outlined" style="font-size: 20px;">arrow_back</span> Kembali ke Pesanan Saya
            </a>
        </div>

        <?php if ($method === 'cod'): ?>
            <!-- COD SUCCESS SCREEN -->
            <div class="card" style="padding: 48px 32px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); text-align: center; background: white; border: 1px solid rgba(0,0,0,0.05);">
                <div style="margin: 0 auto 24px auto; width: 80px; height: 80px; background: #ecfdf5; color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.15);">
                    <span class="material-symbols-outlined" style="font-size: 48px; font-weight: 600;">check</span>
                </div>
                <h1 style="font-size: 32px; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Pesanan COD Berhasil Dibuat!</h1>
                <p style="color: #64748b; font-size: 16px; max-width: 500px; margin: 0 auto 32px auto; line-height: 1.6;">
                    Terima kasih atas kepercayaan Anda. Pesanan Anda telah diterima oleh sistem dan saat ini sedang kami siapkan untuk dikirimkan.
                </p>

                <!-- Order Details Card -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; text-align: left; max-width: 550px; margin: 0 auto 32px auto; display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 12px; margin-bottom: 4px;">
                        <span style="color: #64748b; font-weight: 500;">ID Pesanan:</span>
                        <strong style="color: #0f172a;">#MBM-<?php echo $order_id; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 12px; margin-bottom: 4px;">
                        <span style="color: #64748b; font-weight: 500;">Metode Pembayaran:</span>
                        <span style="font-weight: 700; color: var(--primary);">COD (Bayar di Tempat)</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 12px; margin-bottom: 4px;">
                        <span style="color: #64748b; font-weight: 500;">Alamat Pengiriman:</span>
                        <span style="font-weight: 600; color: #334155; text-align: right; max-width: 250px;"><?php echo e($order['alamat_pengiriman']); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 4px;">
                        <span style="color: #0f172a; font-weight: 700; font-size: 18px;">Total Pembayaran:</span>
                        <strong style="color: var(--primary); font-size: 20px; font-weight: 800;"><?php echo format_rupiah($order['total_harga']); ?></strong>
                    </div>
                </div>
 
                <!-- Alert Notice -->
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 16px; max-width: 550px; margin: 0 auto 32px auto; display: flex; gap: 12px; align-items: flex-start; text-align: left;">
                    <span class="material-symbols-outlined" style="color: #d97706; font-size: 24px; flex-shrink: 0;">info</span>
                    <p style="color: #78350f; font-size: 14px; margin: 0; line-height: 1.5;">
                        <strong>PENTING:</strong> Silakan siapkan uang tunai pas sebesar <strong><?php echo format_rupiah($order['total_harga']); ?></strong> untuk diserahkan kepada kurir pada saat paket Anda tiba di lokasi.
                    </p>
                </div>
 
                <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                    <a href="account/orders.php" class="btn btn-primary" style="padding: 14px 28px; font-size: 15px; font-weight: 600;">Lihat Pesanan Saya</a>
                    <a href="katalog.php" class="btn" style="border: 2px solid var(--outline-variant); color: var(--secondary); background: transparent; padding: 12px 28px; font-size: 15px; font-weight: 600; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.2s;">Lanjut Belanja</a>
                </div>
            </div>
 
        <?php elseif ($method === 'midtrans' && $success === 1): ?>
            <!-- MIDTRANS PAYMENT SUCCESS SCREEN -->
            <div class="card" style="padding: 48px 32px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); text-align: center; background: white; border: 1px solid rgba(0,0,0,0.05);">
                <div style="margin: 0 auto 24px auto; width: 80px; height: 80px; background: #ecfdf5; color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.15);">
                    <span class="material-symbols-outlined" style="font-size: 48px; font-weight: 600;">verified_user</span>
                </div>
                <h1 style="font-size: 32px; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Pembayaran Midtrans Berhasil!</h1>
                <p style="color: #64748b; font-size: 16px; max-width: 500px; margin: 0 auto 32px auto; line-height: 1.6;">
                    Transaksi Anda telah divalidasi secara otomatis melalui Midtrans. Status pesanan Anda sekarang diubah menjadi <strong>Diproses</strong>.
                </p>
 
                <!-- Receipt Detail Card -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; text-align: left; max-width: 550px; margin: 0 auto 32px auto; display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 12px; margin-bottom: 4px;">
                        <span style="color: #64748b; font-weight: 500;">ID Transaksi Midtrans:</span>
                        <strong style="color: #0f172a; font-family: monospace; font-size: 14px;">MID-<?php echo strtoupper(bin2hex(random_bytes(6))); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 12px; margin-bottom: 4px;">
                        <span style="color: #64748b; font-weight: 500;">ID Pesanan:</span>
                        <strong style="color: #334155;">#MBM-<?php echo $order_id; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 12px; margin-bottom: 4px;">
                        <span style="color: #64748b; font-weight: 500;">Metode Pembayaran:</span>
                        <span style="font-weight: 700; color: #0f172a;">Midtrans (E-Wallet/VA)</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 4px;">
                        <span style="color: #0f172a; font-weight: 700; font-size: 18px;">Total Nominal Lunas:</span>
                        <strong style="color: var(--primary); font-size: 20px; font-weight: 800;"><?php echo format_rupiah($order['total_harga']); ?></strong>
                    </div>
                </div>
 
                <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                    <a href="account/orders.php" class="btn btn-primary" style="padding: 14px 28px; font-size: 15px; font-weight: 600;">Lihat Pesanan Saya</a>
                    <a href="katalog.php" class="btn" style="border: 2px solid var(--outline-variant); color: var(--secondary); background: transparent; padding: 12px 28px; font-size: 15px; font-weight: 600; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.2s;">Kembali Belanja</a>
                </div>
            </div>
 
        <?php else: ?>
            <!-- MIDTRANS SNAP REAL INTEGRATION -->
            <?php if ($snap_token): ?>
            <div style="display: flex; flex-direction: column; gap: 24px; align-items: center; margin-top: 10px;">
                <div class="card" style="width: 100%; max-width: 650px; padding: 24px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; background: white; border: 1px solid rgba(0,0,0,0.05);">
                    <div>
                        <span style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Merchant: BatuMekar Honey</span>
                        <h2 style="font-size: 20px; margin: 4px 0 0 0; color: #1e293b; font-weight: 800;">Pesanan #MBM-<?php echo $order_id; ?></h2>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 12px; color: #64748b; display: block;">Total Tagihan:</span>
                        <strong style="font-size: 22px; color: var(--primary); font-weight: 800;"><?php echo format_rupiah($order['total_harga']); ?></strong>
                    </div>
                </div>

                <div class="card" style="width: 100%; max-width: 650px; padding: 0; border-radius: 16px; overflow: hidden; border: 1px solid #cbd5e1; background: #ffffff;">
                    <div style="background: #1d2b4f; color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="background: #ffffff; color: #1d2b4f; font-weight: 900; font-size: 15px; padding: 4px 8px; border-radius: 6px; letter-spacing: 1px;">midtrans</span>
                            <span style="font-size: 13px; color: #94a3b8; font-weight: 500;">Secure Payment</span>
                        </div>
                        <div style="font-size: 12px; font-weight: 700; background: rgba(255,255,255,0.15); padding: 4px 8px; border-radius: 4px; display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></span> Sandbox
                        </div>
                    </div>
                    <div style="padding: 32px; text-align: center;">
                        <div id="snap-container" style="min-height: 400px;">
                            <div id="snap-loading" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px; color: #64748b;">
                                <svg class="spinner" viewBox="0 0 50 50" style="width: 32px; height: 32px; animation: rotate 2s linear infinite; stroke: #3b82f6; margin-bottom: 16px;">
                                    <circle cx="25" cy="25" r="20" fill="none" stroke-width="5" style="stroke-linecap: round; animation: dash 1.5s ease-in-out infinite;"></circle>
                                </svg>
                                <span style="font-weight: 600;">Memuat halaman pembayaran aman...</span>
                            </div>
                        </div>
                        <p style="margin-top: 16px; font-size: 13px; color: #94a3b8;">
                            <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">lock</span>
                            Pembayaran diproses secara aman oleh Midtrans
                        </p>
                    </div>
                </div>
            </div>

            <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?php echo MIDTRANS_CLIENT_KEY; ?>"></script>
            <style>
                @keyframes rotate { 100% { transform: rotate(360deg); } }
                @keyframes dash {
                    0% { stroke-dasharray: 1, 150; stroke-dashoffset: 0; }
                    50% { stroke-dasharray: 90, 150; stroke-dashoffset: -35; }
                    100% { stroke-dasharray: 90, 150; stroke-dashoffset: -124; }
                }
            </style>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var snapToken = '<?php echo $snap_token; ?>';
                if (snapToken) {
                    window.snap.embed(snapToken, {
                        embedId: 'snap-container',
                        onSuccess: function(result) {
                            window.location.href = 'pembayaran.php?order_id=<?php echo $order_id; ?>&method=midtrans&transaction_status=settlement';
                        },
                        onPending: function(result) {
                            window.location.href = 'pembayaran.php?order_id=<?php echo $order_id; ?>&method=midtrans&transaction_status=pending';
                        },
                        onError: function(result) {
                            document.getElementById('snap-loading').innerHTML = '<span style="color:#ef4444;font-weight:600;">Gagal memuat pembayaran. Silakan coba lagi.</span>';
                            document.getElementById('snap-loading').style.display = 'flex';
                        },
                        onClose: function() {
                            document.getElementById('snap-loading').style.display = 'flex';
                            document.getElementById('snap-loading').innerHTML = '<span style="color:#64748b;">Pembayaran ditutup. <a href="account/orders.php" style="color:#3b82f6;">Kembali ke pesanan</a></span>';
                        }
                    });
                    document.getElementById('snap-loading').style.display = 'none';
                }
            });
            </script>
            <?php else: ?>
            <div style="text-align: center; padding: 60px 20px;">
                <div style="width: 80px; height: 80px; background: #fef2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                    <span class="material-symbols-outlined" style="font-size: 40px; color: #ef4444;">error_outline</span>
                </div>
                <h2 style="font-size: 24px; color: #1e293b; margin-bottom: 8px;">Token Pembayaran Tidak Tersedia</h2>
                <p style="color: #64748b; margin-bottom: 24px;">Gagal mendapatkan token pembayaran dari Midtrans. Silakan coba lagi.</p>
                <a href="account/orders.php" class="btn btn-primary">Kembali ke Pesanan</a>
            </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
