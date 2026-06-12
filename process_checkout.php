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

$user_id = $_SESSION['user_id'] ?? 1; // Default to Wayan for now
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$shipping_method = $_POST['shipping_method'] ?? 'Standard';
$payment_method = $_POST['payment_method'] ?? 'Bank Transfer';

// Calculate total
$subtotal = 0;
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll();

$items_to_save = [];
foreach ($products as $p) {
    $qty = $_SESSION['cart'][$p['id']];
    $total = $p['price'] * $qty;
    $subtotal += $total;
    $items_to_save[] = [
        'product_id' => $p['id'],
        'quantity' => $qty,
        'price' => $p['price']
    ];
}

$shipping_cost = 25000;
$admin_fee = 2000;
$grand_total = $subtotal + $shipping_cost + $admin_fee;

try {
    $pdo->beginTransaction();

    // Insert Order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_method, payment_method, shipping_address, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $grand_total, $shipping_method, $payment_method, $address, 'Pending']);
    $order_id = $pdo->lastInsertId();

    // Insert Order Items
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($items_to_save as $item) {
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }

    $pdo->commit();

    // Clear Cart
    $_SESSION['cart'] = [];

    // Redirect to success page or orders page
    header("Location: account/orders.php?success=1&order_id=" . $order_id);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error processing order: " . $e->getMessage());
}
?>
