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
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || empty($username) || empty($password)) {
        $error = 'Nama, Username, dan Password wajib diisi.';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal terdiri dari 6 karakter.';
    } else {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT pengguna_id FROM pengguna WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username ini sudah terdaftar.';
        } else {
            // Insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO pengguna (nama, username, password, peran, telepon, alamat) VALUES (?, ?, ?, 'user', ?, ?)");
            $success_insert = $stmt->execute([$name, $username, $hashed_password, $phone, $address]);
            
            if ($success_insert) {
                $success = 'Pendaftaran berhasil! Silakan masuk.';
            } else {
                $error = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
            }
        }
    }
}

$page_title = 'Daftar Akun';
require_once 'includes/header.php';
?>

<main style="padding: 100px 0; background: linear-gradient(135deg, var(--background) 0%, #fffbeb 100%); min-height: 90vh; display: flex; align-items: center;">
    <div class="container" style="display: flex; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 550px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); border-radius: 20px;">
            <div class="text-center" style="margin-bottom: 32px;">
                <span class="material-symbols-outlined text-primary" style="font-size: 48px; margin-bottom: 12px;">person_add</span>
                <h1 style="font-size: 32px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;">Daftar Akun Baru</h1>
                <p style="color: var(--on-surface-variant); font-size: 14px;">Bergabunglah dan nikmati kemurnian madu hutan premium.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="margin-bottom: 24px; font-size: 14px; padding: 12px 16px;">
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success" style="margin-bottom: 24px; font-size: 14px; padding: 12px 16px;">
                    <?php echo e($success); ?> <a href="login.php" style="font-weight: 700; color: inherit; text-decoration: underline;">Masuk disini</a>.
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" style="display: flex; flex-direction: column; gap: 8px;">
                <div class="form-group">
                    <label for="name">Nama Lengkap *</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Nama Lengkap Anda" value="<?php echo e($_POST['name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" value="<?php echo e($_POST['username'] ?? ''); ?>" required>
                </div>

                <div class="grid grid-2" style="gap: 16px; margin-bottom: 8px;">
                    <div class="form-group" style="margin: 0;">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Min 6 karakter" required>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label for="password_confirm">Konfirmasi Password *</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Ulangi password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Nomor Telepon / WhatsApp</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="+62 812..." value="<?php echo e($_POST['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="address">Alamat Pengiriman Lengkap</label>
                    <textarea id="address" name="address" class="form-control" rows="3" placeholder="Alamat lengkap tujuan pengiriman madu" style="resize: vertical;"><?php echo e($_POST['address'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; margin-top: 16px; justify-content: center; font-size: 15px;">
                    Daftar Sekarang
                </button>
            </form>

            <div class="text-center" style="margin-top: 24px; border-top: 1px solid var(--outline-variant)/20; padding-top: 20px; font-size: 14px;">
                <span style="color: var(--on-surface-variant);">Sudah punya akun?</span>
                <a href="login.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Masuk Disini</a>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
