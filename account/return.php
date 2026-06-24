<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

require_login();

$order_id = (int)($_GET['order_id'] ?? 0);

// Fetch order to verify
$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE pesanan_id = ? AND pengguna_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order || strtolower($order['status'] ?? '') !== 'completed') {
    header('Location: orders.php');
    exit;
}

// Check if already returned
$stmt_check = $pdo->prepare("SELECT * FROM pengembalian_pesanan WHERE pesanan_id = ?");
$stmt_check->execute([$order_id]);
$existing_return = $stmt_check->fetch();

if ($existing_return) {
    header('Location: orders.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alasan = trim($_POST['alasan'] ?? '');
    
    if (empty($alasan)) {
        $error = 'Alasan pengembalian harus diisi.';
    } elseif (empty($_FILES['bukti_file']['name'])) {
        $error = 'Bukti foto atau video harus diunggah.';
    } else {
        $file = $_FILES['bukti_file'];
        $filename = $file['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi', 'webm', '3gp'];
        
        if (!in_array($ext, $allowed_exts)) {
            $error = 'Format file tidak diizinkan. Hanya menerima foto (JPG, PNG, GIF) atau video (MP4, MOV, AVI, WEBM, 3GP).';
        } elseif ($file['size'] > 30 * 1024 * 1024) { // 30MB max limit
            $error = 'Ukuran file terlalu besar. Maksimal 30 MB.';
        } else {
            // Ensure upload directory exists
            $upload_dir = __DIR__ . '/../assets/uploads/returns/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = 'return_' . $order_id . '_' . time() . '.' . $ext;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Save to database
                $stmt_insert = $pdo->prepare("INSERT INTO pengembalian_pesanan (pesanan_id, pengguna_id, bukti_file, alasan, status) VALUES (?, ?, ?, ?, 'Pending')");
                $stmt_insert->execute([$order_id, $_SESSION['user_id'], 'assets/uploads/returns/' . $new_filename, $alasan]);
                
                // Add notification for admin
                try {
                    $stmt_notif = $pdo->prepare("INSERT INTO notifikasi (pesanan_id, judul, pesan) VALUES (?, ?, ?)");
                    $stmt_notif->execute([
                        $order_id,
                        'Pengajuan Return #' . $order_id,
                        'Pelanggan mengajukan return untuk pesanan #MBM-' . $order_id . ' dengan alasan: ' . substr($alasan, 0, 50) . '...'
                    ]);
                } catch (PDOException $e) {}
                
                $_SESSION['success_message'] = "Pengajuan pengembalian pesanan #MBM-{$order_id} berhasil dikirim dan sedang menunggu persetujuan admin.";
                header('Location: orders.php');
                exit;
            } else {
                $error = 'Gagal mengunggah file bukti. Silakan coba lagi.';
            }
        }
    }
}

$page_title = 'Ajukan Pengembalian';
require_once __DIR__ . '/../includes/header.php';
?>

<main style="padding: 48px 0; background: var(--background); font-family: 'Inter', sans-serif;">
    <div class="container" style="display: flex; gap: 48px; align-items: flex-start;">
        <!-- Sidebar Navigation -->
        <aside style="width: 280px; flex-shrink: 0;">
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link">
                    <span class="material-symbols-outlined">dashboard</span>
                    Dashboard
                </a>
                <a href="orders.php" class="sidebar-link active">
                    <span class="material-symbols-outlined">shopping_bag</span>
                    Pesanan Saya
                </a>
                <a href="profile.php" class="sidebar-link">
                    <span class="material-symbols-outlined">person</span>
                    Profil Saya
                </a>
                <a href="../logout.php" class="sidebar-link" style="color: var(--error) !important;">
                    <span class="material-symbols-outlined" style="color: var(--error) !important;">logout</span>
                    Keluar
                </a>
            </nav>
        </aside>

        <!-- Form Content -->
        <section style="flex-grow: 1; max-width: 700px;">
            <div style="margin-bottom: 24px;">
                <a href="orders.php" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary); font-weight: 600; text-decoration: none; font-size: 15px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">arrow_back</span> Kembali ke Pesanan Saya
                </a>
            </div>

            <div class="card" style="padding: 32px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); background: white; border: 1px solid rgba(0,0,0,0.05);">
                <h1 style="font-size: 26px; font-weight: 800; color: var(--secondary); margin: 0 0 8px 0;">Pengajuan Pengembalian</h1>
                <p style="color: var(--on-surface-variant); font-size: 14px; margin-bottom: 24px;">Silakan unggah bukti foto/video beserta detail alasan pengembalian untuk pesanan <strong>#MBM-<?php echo $order_id; ?></strong>.</p>
                
                <?php if ($error): ?>
                    <div style="background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; padding: 14px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined">error</span>
                        <?php echo e($error); ?>
                    </div>
                <?php endif; ?>

                <form action="return.php?order_id=<?php echo $order_id; ?>" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;">
                    <!-- File Upload Input with Photo/Video Label -->
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;">Unggah Bukti Foto / Video (Maks 30MB)</label>
                        <div style="border: 2px dashed var(--outline); padding: 32px 20px; border-radius: 12px; text-align: center; background: #fafaf9; cursor: pointer; position: relative; transition: all 0.2s;" id="dropzone">
                            <input type="file" name="bukti_file" id="bukti_file" accept="image/*,video/*" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;" onchange="previewFile(this)">
                            <div id="upload-prompt">
                                <span class="material-symbols-outlined" style="font-size: 48px; color: var(--on-surface-variant); margin-bottom: 12px;">upload_file</span>
                                <p style="margin: 0; font-size: 14px; color: var(--on-surface); font-weight: 600;">Klik atau seret file ke sini untuk mengunggah</p>
                                <p style="margin: 4px 0 0 0; font-size: 12px; color: var(--on-surface-variant);">Format: JPG, PNG, MP4, MOV, AVI (Maks 30MB)</p>
                            </div>
                            <div id="preview-container" style="display: none; flex-direction: column; align-items: center; gap: 12px;">
                                <div id="media-preview" style="max-width: 100%; max-height: 250px; overflow: hidden; border-radius: 8px;"></div>
                                <span id="file-info" style="font-size: 13px; color: var(--on-surface); font-weight: 600;"></span>
                                <span style="font-size: 12px; color: var(--primary); text-decoration: underline;">Ubah File</span>
                            </div>
                        </div>
                    </div>

                    <!-- Text Reason Field -->
                    <div>
                        <label for="alasan" style="display: block; font-size: 14px; font-weight: 700; color: var(--secondary); margin-bottom: 8px;">Alasan Pengembalian</label>
                        <textarea name="alasan" id="alasan" rows="5" style="width: 100%; border: 1px solid var(--outline); border-radius: 8px; padding: 12px; font-size: 14px; font-family: inherit; line-height: 1.6; resize: vertical;" placeholder="Tuliskan secara detail alasan pengembalian barang Anda..."><?php echo e($_POST['alasan'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="padding: 14px; font-size: 15px; font-weight: 700; justify-content: center; border-radius: 8px;">
                        Kirim Pengajuan
                    </button>
                </form>
            </div>
        </section>
    </div>
</main>

<script>
function previewFile(input) {
    const file = input.files[0];
    const previewContainer = document.getElementById('preview-container');
    const uploadPrompt = document.getElementById('upload-prompt');
    const mediaPreview = document.getElementById('media-preview');
    const fileInfo = document.getElementById('file-info');
    
    if (file) {
        uploadPrompt.style.display = 'none';
        previewContainer.style.display = 'flex';
        fileInfo.textContent = file.name + ' (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB)';
        mediaPreview.innerHTML = '';
        
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.maxWidth = '100%';
            img.style.maxHeight = '200px';
            img.style.objectFit = 'contain';
            img.style.borderRadius = '6px';
            mediaPreview.appendChild(img);
        } else if (file.type.startsWith('video/')) {
            const video = document.createElement('video');
            video.src = URL.createObjectURL(file);
            video.style.maxWidth = '100%';
            video.style.maxHeight = '200px';
            video.controls = true;
            video.style.borderRadius = '6px';
            mediaPreview.appendChild(video);
        } else {
            const icon = document.createElement('span');
            icon.className = 'material-symbols-outlined';
            icon.style.fontSize = '80px';
            icon.style.color = '#64748b';
            icon.textContent = 'draft';
            mediaPreview.appendChild(icon);
        }
    } else {
        uploadPrompt.style.display = 'block';
        previewContainer.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
