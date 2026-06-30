<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/functions.php';
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['cart'])) {
    header('Location: katalog.php');
    exit;
}

require_login();

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: admin/dashboard.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$shipping_method = $_POST['shipping_method'] ?? 'Standard';
$payment_method = $_POST['payment_method'] ?? 'Bank Transfer';
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;
$shipping_cost_post = (int)($_POST['shipping_cost'] ?? 0);

// Calculate total
list($cart_items, $subtotal, $item_count) = calculate_cart_totals($pdo);

$items_to_save = [];
foreach ($cart_items as $item) {
    $items_to_save[] = [
        'product_id' => $item['produk_id'],
        'ukuran_id' => $item['ukuran_id'],
        'quantity' => $item['qty'],
        'price' => $item['size_price'],
        'name' => $item['nama'] . ($item['ukuran_label'] ? ' (' . $item['ukuran_label'] . ')' : ''),
    ];
}

$shipping_cost = ($shipping_cost_post > 0) ? $shipping_cost_post : 25000;
$admin_fee = 2000;
$grand_total = $subtotal + $shipping_cost + $admin_fee;

// Ensure tables/columns exist before transaction (DDL auto-commits in MySQL)
try { $pdo->exec("ALTER TABLE detail_pesanan ADD COLUMN ukuran_id INTEGER DEFAULT NULL"); } catch (PDOException $e) {}
try { $pdo->exec("ALTER TABLE pesanan ADD COLUMN snap_token TEXT DEFAULT NULL"); } catch (PDOException $e) {}
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

try {
    $pdo->beginTransaction();

    $status = ($payment_method === 'COD') ? 'Processed' : 'Pending';

    // Insert Order
    $stmt = $pdo->prepare("INSERT INTO pesanan (pengguna_id, total_harga, metode_pengiriman, metode_pembayaran, alamat_pengiriman, status, ongkos_kirim, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $grand_total, $shipping_method, $payment_method, $address, $status, $shipping_cost, $latitude, $longitude]);
    $order_id = $pdo->lastInsertId();

    // Insert Order Items
    $stmt = $pdo->prepare("INSERT INTO detail_pesanan (pesanan_id, produk_id, ukuran_id, jumlah, harga) VALUES (?, ?, ?, ?, ?)");
    foreach ($items_to_save as $item) {
        $ukuran_id = !empty($item['ukuran_id']) ? $item['ukuran_id'] : null;
        $stmt->execute([$order_id, $item['product_id'], $ukuran_id, $item['quantity'], $item['price']]);
    }

    // Create notification
    $stmt_notif = $pdo->prepare("INSERT INTO notifikasi (pesanan_id, judul, pesan) VALUES (?, ?, ?)");
    $stmt_notif->execute([
        $order_id,
        'Pesanan Baru #MBM-' . $order_id,
        'Pesanan baru senilai ' . format_rupiah($grand_total) . ' dari ' . e($name) . ' (' . e($payment_method) . ')'
    ]);

    $pdo->commit();

    $_SESSION['cart'] = [];

    // MIDTRANS: Get Snap token for real payment
    if ($payment_method === 'Midtrans') {
        require_once 'config/midtrans.php';

        $finish_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
            . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')
            . '/pembayaran.php?order_id=' . $order_id . '&method=midtrans';

        $midtrans_items = [];
        foreach ($items_to_save as $item) {
            $midtrans_items[] = [
                'id' => (string) $item['product_id'],
                'price' => (int) $item['price'],
                'quantity' => $item['quantity'],
                'name' => substr($item['name'], 0, 50),
            ];
        }
        if ($shipping_cost > 0) {
            $midtrans_items[] = [
                'id' => 'SHIPPING',
                'price' => (int) $shipping_cost,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];
        }
        if ($admin_fee > 0) {
            $midtrans_items[] = [
                'id' => 'ADMIN',
                'price' => (int) $admin_fee,
                'quantity' => 1,
                'name' => 'Biaya Admin',
            ];
        }

        $customer = [
            'first_name' => $name,
            'phone' => $phone,
            'billing_address' => [
                'first_name' => $name,
                'phone' => $phone,
                'address' => $address,
            ],
        ];

        $snap_result = midtrans_get_snap_token($order_id, $grand_total, $midtrans_items, $customer, $finish_url);

        if ($snap_result && isset($snap_result['token'])) {
            $stmt = $pdo->prepare("UPDATE pesanan SET snap_token = ? WHERE pesanan_id = ?");
            $stmt->execute([$snap_result['token'], $order_id]);
        }

        header("Location: pembayaran.php?order_id=" . $order_id . "&method=midtrans");
        exit;
    }

    // COD flow
    header("Location: pembayaran.php?order_id=" . $order_id . "&method=cod");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error processing order: " . $e->getMessage());
}
?>
