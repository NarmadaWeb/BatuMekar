<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

// Hardcoded for demo/scaffold but would usually redirect to login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'user';
}
require_login();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$page_title = 'User Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<main style="padding: 48px 0;">
    <div class="container" style="display: flex; gap: 48px;">
        <aside style="width: 250px; flex-shrink: 0;">
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="btn sidebar-link active" style="justify-content: flex-start;">Dashboard</a>
                <a href="orders.php" class="btn sidebar-link" style="justify-content: flex-start;">My Orders</a>
                <a href="profile.php" class="btn sidebar-link" style="justify-content: flex-start;">Profile</a>
            </nav>
        </aside>

        <section style="flex-grow: 1;">
            <div class="card" style="background: var(--primary-container); margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; padding: 48px;">
                <div>
                    <h1 style="font-size: 32px; margin-bottom: 8px;">Welcome back, <?php echo e($user['name']); ?>!</h1>
                    <p style="opacity: 0.8;">Your artisanal honey journey continues. Explore the latest harvests.</p>
                </div>
                <img src="../assets/images/wayan.jpg" alt="Profile" style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid white;">
            </div>

            <div class="grid grid-2">
                <div class="card">
                    <div style="font-size: 14px; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">Nectar Points</div>
                    <div style="font-size: 32px; font-weight: 700; color: var(--primary);"><?php echo e(number_format($user['points'])); ?> pts</div>
                </div>
                <div class="card">
                    <div style="font-size: 14px; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">Membership</div>
                    <div style="font-size: 32px; font-weight: 700; color: var(--primary);"><?php echo e($user['membership']); ?></div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
