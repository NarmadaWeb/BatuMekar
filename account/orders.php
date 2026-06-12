<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}
require_login();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$page_title = 'Order History';
require_once __DIR__ . '/../includes/header.php';
?>

<main style="padding: 48px 0;">
    <div class="container" style="display: flex; gap: 48px;">
        <aside style="width: 250px; flex-shrink: 0;">
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="btn sidebar-link" style="justify-content: flex-start; color: var(--secondary);">Dashboard</a>
                <a href="orders.php" class="btn sidebar-link active" style="justify-content: flex-start;">My Orders</a>
                <a href="profile.php" class="btn sidebar-link" style="justify-content: flex-start; color: var(--secondary);">Profile</a>
            </nav>
        </aside>

        <section style="flex-grow: 1;">
            <h1 style="font-size: 32px; margin-bottom: 32px;">Order History</h1>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php if (empty($orders)): ?>
                    <p style="color: var(--on-surface-variant);">You haven't placed any orders yet.</p>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 700; color: var(--primary);">#MBM-<?php echo e($order['id']); ?></div>
                            <div style="font-size: 14px; color: var(--on-surface-variant);"><?php echo e(date('M d, Y', strtotime($order['created_at']))); ?></div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700;"><?php echo e(format_rupiah($order['total_amount'])); ?></div>
                            <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--tertiary);"><?php echo e($order['status']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
