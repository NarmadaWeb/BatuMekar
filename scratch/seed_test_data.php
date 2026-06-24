<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

echo "=== SEEDING UKURAN DAN PESANAN TEST DI MYSQL ===\n";

$user_id = 2; // I Gede Sukarsa

// 1. Seed Ukuran Produk
$pdo->exec("DELETE FROM ukuran_produk");
$stmt_sz = $pdo->prepare("INSERT INTO ukuran_produk (produk_id, ukuran_ml, harga, stok) VALUES (?, ?, ?, ?)");

// Madu Multiflora (ID 1)
$stmt_sz->execute([1, 250, 75000, 50]);
$stmt_sz->execute([1, 500, 125000, 50]);

// Madu Hutan Liar (ID 3)
$stmt_sz->execute([3, 250, 95000, 40]);
$stmt_sz->execute([3, 500, 160000, 40]);

echo "[+] Berhasil memasukkan data ukuran produk.\n";

// Get some size IDs
$stmt_get_sizes = $pdo->prepare("SELECT * FROM ukuran_produk WHERE produk_id = ? AND ukuran_ml = ?");
$stmt_get_sizes->execute([1, 500]);
$multiflora_500_size = $stmt_get_sizes->fetch();

$stmt_get_sizes->execute([3, 500]);
$hutan_500_size = $stmt_get_sizes->fetch();

// 2. Clear old orders for clean slate
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
$pdo->exec("DELETE FROM detail_pesanan;");
$pdo->exec("DELETE FROM pesanan;");
$pdo->exec("DELETE FROM pengembalian_pesanan;");
$pdo->exec("DELETE FROM ulasan_produk;");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

// 3. Seed Order 1: Status Pending (dapat dibatalkan)
$stmt_order = $pdo->prepare("
    INSERT INTO pesanan (pesanan_id, pengguna_id, total_harga, status, metode_pengiriman, metode_pembayaran, alamat_pengiriman)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
// Order #1: Pending
$stmt_order->execute([
    1,
    $user_id,
    152000.00, // 125000 (Madu Multiflora 500ml) + 25000 shipping + 2000 admin
    'Pending',
    'Standard',
    'Midtrans',
    'Jl. Raya BatuMekar No. 42, Lombok Barat'
]);

$stmt_detail = $pdo->prepare("
    INSERT INTO detail_pesanan (pesanan_id, produk_id, ukuran_id, jumlah, harga)
    VALUES (?, ?, ?, ?, ?)
");
$stmt_detail->execute([
    1,
    1, // Madu Multiflora
    $multiflora_500_size ? $multiflora_500_size['ukuran_id'] : null,
    1,
    125000.00
]);

// Order #2: Completed (bisa direturn dan diulas)
$stmt_order->execute([
    2,
    $user_id,
    187000.00, // 160000 (Madu Hutan Liar 500ml) + 25000 shipping + 2000 admin
    'Completed',
    'Standard',
    'Transfer Bank',
    'Jl. Raya BatuMekar No. 42, Lombok Barat'
]);
$stmt_detail->execute([
    2,
    3, // Madu Hutan Liar
    $hutan_500_size ? $hutan_500_size['ukuran_id'] : null,
    1,
    160000.00
]);

echo "[+] Berhasil seeding data pesanan test (Order #1 Pending, Order #2 Completed) di MySQL.\n";
?>
