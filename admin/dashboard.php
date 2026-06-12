<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_admin();

// Simple stats
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$recent_orders = $pdo->query("SELECT orders.*, users.name as user_name FROM orders JOIN users ON orders.user_id = users.id ORDER BY orders.created_at DESC LIMIT 5")->fetchAll();

$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<main style="padding: 48px 0;">
    <div class="container" style="display: flex; gap: 48px;">
        <aside style="width: 250px; flex-shrink: 0;">
            <nav class="sidebar-nav">
                <a href="#" class="btn sidebar-link active" style="justify-content: flex-start;">Dashboard</a>
                <a href="#" class="btn sidebar-link" style="justify-content: flex-start;">Products</a>
                <a href="#" class="btn sidebar-link" style="justify-content: flex-start;">Orders</a>
            </nav>
        </aside>

        <section style="flex-grow: 1;">
            <h1 style="font-size: 32px; margin-bottom: 32px;">Admin Overview</h1>

            <div class="grid grid-2" style="margin-bottom: 48px;">
                <div class="card">
                    <div style="font-size: 14px; font-weight: 700; color: var(--secondary); text-transform: uppercase;">Total Products</div>
                    <div style="font-size: 32px; font-weight: 700; color: var(--primary);"><?php echo e($total_products); ?></div>
                </div>
                <div class="card">
                    <div style="font-size: 14px; font-weight: 700; color: var(--secondary); text-transform: uppercase;">Total Orders</div>
                    <div style="font-size: 32px; font-weight: 700; color: var(--primary);"><?php echo e($total_orders); ?></div>
                </div>
            </div>

            <div class="card" style="padding: 0;">
                <div style="padding: 24px; border-bottom: 1px solid var(--outline-variant);">
                    <h2 style="font-size: 20px;">Recent Orders</h2>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="font-size: 12px; text-transform: uppercase;">Order ID</th>
                            <th style="font-size: 12px; text-transform: uppercase;">Customer</th>
                            <th style="font-size: 12px; text-transform: uppercase;">Status</th>
                            <th style="font-size: 12px; text-transform: uppercase;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                        <tr style="border-bottom: 1px solid var(--outline-variant);">
                            <td style="padding: 16px 24px;">#MBM-<?php echo e($order['id']); ?></td>
                            <td style="padding: 16px 24px;"><?php echo e($order['user_name']); ?></td>
                            <td style="padding: 16px 24px;"><?php echo e($order['status']); ?></td>
                            <td style="padding: 16px 24px; font-weight: 600;"><?php echo e(format_rupiah($order['total_amount'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
