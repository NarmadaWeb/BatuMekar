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

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$page_title = 'Profile Settings';
require_once __DIR__ . '/../includes/header.php';
?>

<main style="padding: 48px 0;">
    <div class="container" style="display: flex; gap: 48px;">
        <aside style="width: 250px; flex-shrink: 0;">
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="btn sidebar-link" style="justify-content: flex-start; color: var(--secondary);">Dashboard</a>
                <a href="orders.php" class="btn sidebar-link" style="justify-content: flex-start; color: var(--secondary);">My Orders</a>
                <a href="profile.php" class="btn sidebar-link active" style="justify-content: flex-start;">Profile</a>
            </nav>
        </aside>

        <section style="flex-grow: 1;">
            <h1 style="font-size: 32px; margin-bottom: 32px;">Profile Settings</h1>

            <div class="card" style="max-width: 500px;">
                <h2 style="margin-bottom: 24px;">Personal Details</h2>
                <form style="display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px;">Full Name</label>
                        <input type="text" value="<?php echo e($user['name']); ?>" style="width: 100%; padding: 12px; border: 1px solid var(--outline-variant); border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px;">Email</label>
                        <input type="email" value="<?php echo e($user['email']); ?>" style="width: 100%; padding: 12px; border: 1px solid var(--outline-variant); border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px;">Phone</label>
                        <input type="tel" value="<?php echo e($user['phone']); ?>" style="width: 100%; padding: 12px; border: 1px solid var(--outline-variant); border-radius: 8px;">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
