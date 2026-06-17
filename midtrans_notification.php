<?php
require_once 'config/db.php';
require_once 'config/midtrans.php';

$notification_body = file_get_contents('php://input');
$notification = json_decode($notification_body, true);

if (!$notification || !isset($notification['order_id'])) {
    http_response_code(400);
    exit('Invalid notification');
}

$order_id_str = $notification['order_id'];
$transaction_status = $notification['transaction_status'] ?? '';
$fraud_status = $notification['fraud_status'] ?? '';

// Extract numeric order ID from 'MBM-123' format
$order_id = 0;
if (str_starts_with($order_id_str, 'MBM-')) {
    $order_id = (int) substr($order_id_str, 4);
}

if (!$order_id) {
    http_response_code(400);
    exit('Invalid order ID');
}

// Verify signature
$server_key = MIDTRANS_SERVER_KEY;
$status_code = $notification['status_code'] ?? '';
$gross_amount = $notification['gross_amount'] ?? '';
$signature = $notification['signature_key'] ?? '';
$calculated = hash('sha512', $order_id_str . $status_code . $gross_amount . $server_key);

if ($signature !== $calculated) {
    http_response_code(403);
    exit('Invalid signature');
}

$payment_ok = in_array($transaction_status, ['capture', 'settlement']);
$denied = in_array($transaction_status, ['deny', 'cancel', 'expire', 'failure']);

try {
    $pdo->exec("ALTER TABLE pesanan ADD COLUMN snap_token TEXT DEFAULT NULL");
} catch (PDOException $e) {}

if ($payment_ok && $fraud_status !== 'deny') {
    $stmt = $pdo->prepare("UPDATE pesanan SET status = 'Processed' WHERE pesanan_id = ? AND status = 'Pending'");
    $stmt->execute([$order_id]);

    try {
        $stmt_pay = $pdo->prepare("INSERT INTO pembayaran (pesanan_id, pengguna_id, transaksi_id) VALUES (?, (SELECT pengguna_id FROM pesanan WHERE pesanan_id = ?), ?)");
        $tx_id = $notification['transaction_id'] ?? ('TX-MIDTRANS-' . strtoupper(bin2hex(random_bytes(4))));
        $stmt_pay->execute([$order_id, $order_id, $tx_id]);
    } catch (PDOException $e) {}
} elseif ($denied) {
    $stmt = $pdo->prepare("UPDATE pesanan SET status = 'Cancelled' WHERE pesanan_id = ? AND status = 'Pending'");
    $stmt->execute([$order_id]);
}

http_response_code(200);
echo 'OK';
