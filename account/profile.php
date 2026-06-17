<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_login();

$stmt = $pdo->prepare("SELECT * FROM pengguna WHERE pengguna_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || empty($username)) {
        $error_msg = 'Nama dan Username wajib diisi.';
    } else {
        try {
            $update_stmt = $pdo->prepare("UPDATE pengguna SET nama = ?, username = ?, telepon = ?, alamat = ? WHERE pengguna_id = ?");
            $update_stmt->execute([$name, $username, $phone, $address, $_SESSION['user_id']]);
            
            // Handle profile photo upload
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['profile_photo']['tmp_name'];
                $file_name = $_FILES['profile_photo']['name'];
                $file_size = $_FILES['profile_photo']['size'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                $allowed_exts = ['jpg', 'jpeg', 'png'];
                if (in_array($file_ext, $allowed_exts)) {
                    if ($file_size <= 2 * 1024 * 1024) {
                        $upload_dir = __DIR__ . '/../assets/uploads/profile/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        $new_photo_name = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
                        if (move_uploaded_file($file_tmp, $upload_dir . $new_photo_name)) {
                            $photo_path = 'assets/uploads/profile/' . $new_photo_name;
                            
                            // Delete old photo file if it exists
                            if (!empty($user['foto_profil']) && file_exists(__DIR__ . '/../' . $user['foto_profil'])) {
                                @unlink(__DIR__ . '/../' . $user['foto_profil']);
                            }
                            
                            $update_photo = $pdo->prepare("UPDATE pengguna SET foto_profil = ? WHERE pengguna_id = ?");
                            $update_photo->execute([$photo_path, $_SESSION['user_id']]);
                        }
                    } else {
                        $error_msg = 'Ukuran foto terlalu besar. Maksimal 2MB.';
                    }
                } else {
                    $error_msg = 'Format file tidak didukung. Harap upload gambar JPG atau PNG.';
                }
            }
            
            // Re-fetch user details
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (empty($error_msg)) {
                $success_msg = 'Profil berhasil diperbarui!';
            }
        } catch (PDOException $e) {
            $error_msg = 'Gagal memperbarui profil. Username mungkin sudah digunakan.';
        }
    }
}

$page_title = 'Pengaturan Profil';
require_once __DIR__ . '/../includes/header.php';
?>

<main style="padding: 48px 0; background: var(--background);">
    <div class="container" style="display: flex; gap: 48px; align-items: flex-start;">
        <!-- Sidebar Navigation -->
        <aside style="width: 280px; flex-shrink: 0;">
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link">
                    <span class="material-symbols-outlined">dashboard</span>
                    Dashboard
                </a>
                <a href="orders.php" class="sidebar-link">
                    <span class="material-symbols-outlined">shopping_bag</span>
                    Pesanan Saya
                </a>
                <a href="profile.php" class="sidebar-link active">
                    <span class="material-symbols-outlined">person</span>
                    Profil Saya
                </a>
                <a href="../logout.php" class="sidebar-link" style="color: var(--error) !important;">
                    <span class="material-symbols-outlined" style="color: var(--error) !important;">logout</span>
                    Keluar
                </a>
            </nav>
        </aside>

        <!-- Main Profile Content -->
        <section style="flex-grow: 1;">
            <h1 style="font-size: 32px; margin-bottom: 32px; color: var(--secondary);">Pengaturan Profil</h1>

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success">
                    <?php echo e($success_msg); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger">
                    <?php echo e($error_msg); ?>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 650px; padding: 32px; display: flex; flex-direction: column; gap: 24px;">
                <div style="display: flex; align-items: center; gap: 20px; border-bottom: 1px solid var(--outline-variant)/20; padding-bottom: 20px; margin-bottom: 8px;">
                    <?php if (!empty($user['foto_profil']) && file_exists(__DIR__ . '/../' . $user['foto_profil'])): ?>
                        <img src="../<?php echo e($user['foto_profil']); ?>" alt="Profile" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.08); flex-shrink: 0;">
                    <?php else: ?>
                        <div style="display: flex; align-items: center; justify-content: center; background: var(--primary); color: white; font-weight: 800; font-size: 32px; border-radius: 50%; width: 80px; height: 80px; text-transform: uppercase; border: 3px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.08); flex-shrink: 0;">
                            <?php echo substr(e($user['nama']), 0, 1); ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h2 style="font-size: 24px; margin: 0; color: var(--secondary); font-weight: 700;"><?php echo e($user['nama']); ?></h2>
                        <p style="color: var(--on-surface-variant); margin: 4px 0 0 0; font-size: 14px;">Atur informasi profil Anda</p>
                    </div>
                </div>
                
                <form action="profile.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 8px;">
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="profile_photo" style="font-weight: 600; display: block; margin-bottom: 6px;">Foto Profil (Format: JPG, PNG, Maks 2MB)</label>
                        <input type="file" id="profile_photo" name="profile_photo" class="form-control" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo e($user['nama']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo e($user['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo e($user['telepon']); ?>">
                    </div>
 
                    <div class="form-group">
                        <label for="address">Shipping Address</label>
                        <textarea id="address" name="address" class="form-control" rows="4" style="resize: vertical;"><?php echo e($user['alamat'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="align-self: flex-start; padding: 14px 32px; margin-top: 12px;">
                        <span class="material-symbols-outlined" style="font-size: 20px;">save</span> Save Changes
                    </button>
                </form>
            </div>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

