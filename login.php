<?php
require_once 'includes/functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: account/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['pengguna_id'];
            $_SESSION['user_role'] = $user['peran'];
            
            // Redirect based on role
            if ($user['peran'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: account/dashboard.php');
            }
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}

$page_title = 'Login';
require_once 'includes/header.php';
?>

<main style="padding: 100px 0; background: linear-gradient(135deg, var(--background) 0%, #fffbeb 100%); min-height: 80vh; display: flex; align-items: center;">
    <div class="container" style="display: flex; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 450px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); border-radius: 20px;">
            <div class="text-center" style="margin-bottom: 32px;">
                <span class="material-symbols-outlined text-primary" style="font-size: 48px; margin-bottom: 12px;">lock</span>
                <h1 style="font-size: 32px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;">Masuk ke Akun</h1>
                <p style="color: var(--on-surface-variant); font-size: 14px;">Nikmati kemudahan memesan Madu BatuMekar murni.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="margin-bottom: 24px; font-size: 14px; padding: 12px 16px;">
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" style="display: flex; flex-direction: column; gap: 8px;">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; margin-top: 16px; justify-content: center; font-size: 15px;">
                    Masuk
                </button>
            </form>

            <div class="text-center" style="margin-top: 24px; border-top: 1px solid var(--outline-variant)/20; padding-top: 20px; font-size: 14px;">
                <span style="color: var(--on-surface-variant);">Belum punya akun?</span>
                <a href="register.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Daftar Sekarang</a>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
