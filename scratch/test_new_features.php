<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

echo "=== MEMULAI VERIFIKASI FITUR BARU DI MYSQL ===\n\n";

$user_id = 2; // gede

// 1. Uji Batal Pesanan (Order ID 1 - Pending)
echo "\n--- 1. Simulasi Pembatalan Pesanan ---\n";
$order_id = 1;
$stmt_check = $pdo->prepare("SELECT * FROM pesanan WHERE pesanan_id = ? AND pengguna_id = ? AND status = 'Pending'");
$stmt_check->execute([$order_id, $user_id]);
$order = $stmt_check->fetch();

if ($order) {
    echo "[+] Pesanan #MBM-{$order_id} ditemukan dengan status Pending.\n";
    $stmt_update = $pdo->prepare("UPDATE pesanan SET status = 'Cancelled' WHERE pesanan_id = ?");
    $stmt_update->execute([$order_id]);
    
    // Verifikasi
    $stmt_verify = $pdo->prepare("SELECT status FROM pesanan WHERE pesanan_id = ?");
    $stmt_verify->execute([$order_id]);
    $new_status = $stmt_verify->fetchColumn();
    echo "[+] Status baru pesanan #MBM-{$order_id}: {$new_status} (Harus Cancelled)\n";
} else {
    echo "[!] Pesanan #MBM-{$order_id} tidak ditemukan dalam status Pending untuk pengguna ID {$user_id}.\n";
}

// 2. Uji Pengajuan Pengembalian (Order ID 2 - Completed)
echo "\n--- 2. Simulasi Pengajuan Return ---\n";
$return_order_id = 2;
$stmt_check_ret = $pdo->prepare("SELECT * FROM pesanan WHERE pesanan_id = ? AND pengguna_id = ? AND status = 'Completed'");
$stmt_check_ret->execute([$return_order_id, $user_id]);
$ret_order = $stmt_check_ret->fetch();

if ($ret_order) {
    echo "[+] Pesanan #MBM-{$return_order_id} ditemukan dengan status Completed.\n";
    
    // Hapus return lama jika ada untuk konsistensi pengujian
    $pdo->prepare("DELETE FROM pengembalian_pesanan WHERE pesanan_id = ?")->execute([$return_order_id]);
    
    $bukti_file = 'assets/uploads/returns/return_test_bukti.jpg';
    $alasan = 'Barang pecah saat pengiriman dan isinya bocor.';
    
    $stmt_insert = $pdo->prepare("INSERT INTO pengembalian_pesanan (pesanan_id, pengguna_id, bukti_file, alasan, status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmt_insert->execute([$return_order_id, $user_id, $bukti_file, $alasan]);
    echo "[+] Pengajuan return untuk pesanan #MBM-{$return_order_id} dimasukkan ke database.\n";
} else {
    echo "[!] Pesanan #MBM-{$return_order_id} tidak ditemukan dalam status Completed untuk pengguna ID {$user_id}.\n";
}

// 3. Uji Pengisian Ulasan & Rating (Product ID 3)
echo "\n--- 3. Simulasi Pengisian Ulasan ---\n";
$product_id = 3;

// Hapus ulasan lama jika ada dari user ini untuk konsistensi pengujian
$pdo->prepare("DELETE FROM ulasan_produk WHERE produk_id = ? AND pengguna_id = ?")->execute([$product_id, $user_id]);

// Dapatkan rating produk saat ini
$stmt_prod_before = $pdo->prepare("SELECT nama, rating, jumlah_ulasan FROM produk WHERE produk_id = ?");
$stmt_prod_before->execute([$product_id]);
$prod_before = $stmt_prod_before->fetch();
echo "[+] Produk: {$prod_before['nama']} | Rating saat ini: {$prod_before['rating']} | Jumlah ulasan saat ini: {$prod_before['jumlah_ulasan']}\n";

// Insert ulasan baru
$rating = 5;
$ulasan_text = 'Madu sarang sangat manis, berkualitas tinggi, dan segar!';
$stmt_rev = $pdo->prepare("INSERT INTO ulasan_produk (produk_id, pengguna_id, rating, ulasan) VALUES (?, ?, ?, ?)");
$stmt_rev->execute([$product_id, $user_id, $rating, $ulasan_text]);
echo "[+] Ulasan berhasil disimpan.\n";

// Recalculate average rating & total reviews
$stmt_stats = $pdo->prepare("SELECT COUNT(*) as count, AVG(rating) as average FROM ulasan_produk WHERE produk_id = ?");
$stmt_stats->execute([$product_id]);
$stats = $stmt_stats->fetch();

$new_count = (int)$stats['count'];
$new_avg = round((float)$stats['average'], 1);

$stmt_update_prod = $pdo->prepare("UPDATE produk SET rating = ?, jumlah_ulasan = ? WHERE produk_id = ?");
$stmt_update_prod->execute([$new_avg, $new_count, $product_id]);

// Verifikasi setelah update
$stmt_prod_after = $pdo->prepare("SELECT rating, jumlah_ulasan FROM produk WHERE produk_id = ?");
$stmt_prod_after->execute([$product_id]);
$prod_after = $stmt_prod_after->fetch();
echo "[+] Statistik Produk Baru | Rating: {$prod_after['rating']} | Jumlah ulasan: {$prod_after['jumlah_ulasan']}\n";

// 4. Uji Proses Return Admin (Menyetujui Return)
echo "\n--- 4. Simulasi Admin Menyetujui Return ---\n";
$stmt_get_return = $pdo->prepare("SELECT * FROM pengembalian_pesanan WHERE pesanan_id = ? AND status = 'Pending'");
$stmt_get_return->execute([$return_order_id]);
$return_req = $stmt_get_return->fetch();

if ($return_req) {
    echo "[+] Pengajuan return Pending ditemukan dengan ID: {$return_req['pengembalian_id']}.\n";
    $return_id = $return_req['pengembalian_id'];
    
    // Setujui return
    $stmt_approve = $pdo->prepare("UPDATE pengembalian_pesanan SET status = 'Disetujui' WHERE pengembalian_id = ?");
    $stmt_approve->execute([$return_id]);
    
    // Update status pesanan
    $stmt_up_order = $pdo->prepare("UPDATE pesanan SET status = 'Returned' WHERE pesanan_id = ?");
    $stmt_up_order->execute([$return_order_id]);
    
    // Verifikasi
    $stmt_verify_ret = $pdo->prepare("SELECT status FROM pengembalian_pesanan WHERE pengembalian_id = ?");
    $stmt_verify_ret->execute([$return_id]);
    $new_ret_status = $stmt_verify_ret->fetchColumn();
    
    $stmt_verify_ord = $pdo->prepare("SELECT status FROM pesanan WHERE pesanan_id = ?");
    $stmt_verify_ord->execute([$return_order_id]);
    $new_ord_status = $stmt_verify_ord->fetchColumn();
    
    echo "[+] Status pengembalian: {$new_ret_status} (Harus Disetujui)\n";
    echo "[+] Status pesanan: {$new_ord_status} (Harus Returned)\n";
} else {
    echo "[!] Tidak ada pengajuan return Pending untuk pesanan ID {$return_order_id}.\n";
}

echo "\n=== VERIFIKASI SELESAI ===\n";
