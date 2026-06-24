<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_admin();

$msg = '';
$error = '';

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM artikel WHERE artikel_id = ?");
        $stmt->execute([$id]);
        $msg = "Artikel berhasil dihapus.";
    } catch (PDOException $e) {
        $error = "Gagal menghapus artikel: " . $e->getMessage();
    }
}

// ADD / EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $judul = trim($_POST['judul'] ?? '');
    $kutipan = trim($_POST['kutipan'] ?? '');
    $konten = trim($_POST['konten'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $penulis = trim($_POST['penulis'] ?? 'Admin');
    $gambar = '';

    // Handle image upload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['gambar']['tmp_name'];
            $file_name = $_FILES['gambar']['name'];
            $file_size = $_FILES['gambar']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($file_ext, $allowed_exts)) {
                $error = 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WebP.';
            } elseif ($file_size > 2 * 1024 * 1024) {
                $error = 'Ukuran gambar maksimal 2MB.';
            } else {
                $upload_dir = __DIR__ . '/../assets/uploads/blog/';
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
                $new_file_name = 'artikel_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
                if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                    $gambar = 'assets/uploads/blog/' . $new_file_name;
                } else {
                    $error = 'Gagal menyimpan gambar.';
                }
            }
        } else {
            $error = 'Error upload gambar. Coba lagi.';
        }
    }

    if (empty($error)) {
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO artikel (judul, kutipan, konten, kategori, penulis, gambar) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$judul, $kutipan, $konten, $kategori, $penulis, $gambar]);
                $msg = "Artikel '{$judul}' berhasil ditambahkan.";
            } elseif ($action === 'edit') {
                $id = (int)$_POST['artikel_id'];
                if ($gambar) {
                    $stmt = $pdo->prepare("UPDATE artikel SET judul=?, kutipan=?, konten=?, kategori=?, penulis=?, gambar=? WHERE artikel_id=?");
                    $stmt->execute([$judul, $kutipan, $konten, $kategori, $penulis, $gambar, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE artikel SET judul=?, kutipan=?, konten=?, kategori=?, penulis=? WHERE artikel_id=?");
                    $stmt->execute([$judul, $kutipan, $konten, $kategori, $penulis, $id]);
                }
                $msg = "Artikel '{$judul}' berhasil diperbarui.";
            }
        } catch (PDOException $e) {
            $error = "Gagal menyimpan artikel: " . $e->getMessage();
        }
    }
}

$posts = $pdo->query("SELECT * FROM artikel ORDER BY dibuat_pada DESC")->fetchAll();
$edit_post = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM artikel WHERE artikel_id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_post = $stmt->fetch();
}

$categories = $pdo->query("SELECT DISTINCT kategori FROM artikel WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori")->fetchAll(PDO::FETCH_COLUMN);

$page_title = 'Manajemen Artikel';
require_once __DIR__ . '/../includes/header.php';
?>
<main style="padding: 48px 0; background: var(--background); font-family: 'Inter', sans-serif;">
    <div class="container" style="display: flex; gap: 48px; align-items: flex-start;">
        <aside style="width: 250px; flex-shrink: 0;">
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link">
                    <span class="material-symbols-outlined">dashboard</span> Overview Admin
                </a>
                <a href="produk.php" class="sidebar-link">
                    <span class="material-symbols-outlined">inventory_2</span> Produk
                </a>
                <a href="kategori.php" class="sidebar-link">
                    <span class="material-symbols-outlined">category</span> Kategori
                </a>
                <a href="pesanan.php" class="sidebar-link">
                    <span class="material-symbols-outlined">receipt_long</span> Pesanan
                </a>
                <a href="artikel.php" class="sidebar-link active">
                    <span class="material-symbols-outlined">article</span> Artikel
                </a>
                <a href="../logout.php" class="sidebar-link" style="color: var(--error) !important;">
                    <span class="material-symbols-outlined" style="color: var(--error) !important;">logout</span> Keluar
                </a>
            </nav>
        </aside>

        <section style="flex-grow: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                <h1 style="font-size: 32px; color: var(--secondary); font-weight: 800;">Manajemen Artikel</h1>
                <button onclick="document.getElementById('form-artikel').scrollIntoView({behavior:'smooth'})" class="btn btn-primary" style="gap: 8px;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">add</span> Tambah Artikel
                </button>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-success" style="margin-bottom: 24px; padding: 12px 20px; border-radius: 8px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; display: flex; align-items: center; gap: 10px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">check_circle</span> <div><?php echo e($msg); ?></div>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger" style="margin-bottom: 24px; padding: 12px 20px; border-radius: 8px; background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; display: flex; align-items: center; gap: 10px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">error</span> <div><?php echo e($error); ?></div>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <div id="form-artikel" class="card" style="padding: 32px; margin-bottom: 32px; border-radius: 16px;">
                <h2 style="font-size: 20px; font-weight: 800; color: var(--secondary); margin-bottom: 24px;">
                    <?php echo $edit_post ? 'Edit Artikel' : 'Tulis Artikel Baru'; ?>
                </h2>
                <form action="artikel.php<?php echo $edit_post ? '?edit=' . $edit_post['artikel_id'] : ''; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $edit_post ? 'edit' : 'add'; ?>">
                    <?php if ($edit_post): ?>
                        <input type="hidden" name="artikel_id" value="<?php echo $edit_post['artikel_id']; ?>">
                    <?php endif; ?>

                    <div class="grid grid-2" style="gap: 16px;">
                        <div class="form-group">
                            <label for="judul">Judul Artikel</label>
                            <input type="text" id="judul" name="judul" class="form-control" value="<?php echo $edit_post ? e($edit_post['judul']) : ''; ?>" required>
                        </div>
                        <div style="display: flex; gap: 12px;">
                            <div class="form-group" style="flex: 1;">
                                <label for="kategori">Kategori</label>
                                <input type="text" id="kategori" name="kategori" class="form-control" list="cat-list" value="<?php echo $edit_post ? e($edit_post['kategori']) : ''; ?>">
                                <datalist id="cat-list">
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?php echo e($c); ?>">
                                    <?php endforeach; ?>
                                    <option value="Panen">
                                    <option value="Kesehatan">
                                    <option value="Resep">
                                    <option value="Budaya">
                                </datalist>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label for="penulis">Penulis</label>
                                <input type="text" id="penulis" name="penulis" class="form-control" value="<?php echo $edit_post ? e($edit_post['penulis']) : 'Admin'; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="kutipan">Kutipan / Ringkasan</label>
                        <textarea id="kutipan" name="kutipan" class="form-control" rows="2" required><?php echo $edit_post ? e($edit_post['kutipan']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="konten">Konten Lengkap</label>
                        <textarea id="konten" name="konten" class="form-control" rows="8" required><?php echo $edit_post ? e($edit_post['konten']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="gambar">Gambar Header</label>
                        <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*" onchange="previewImage(event)">
                        <div id="preview-container" style="margin-top: 10px;">
                            <?php if ($edit_post && $edit_post['gambar']): ?>
                                <div style="display: flex; align-items: center; gap: 12px; padding: 8px 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <img src="<?php echo e(img_url($edit_post['gambar'])); ?>" style="width: 80px; height: 50px; object-fit: cover; border-radius: 6px;">
                                    <div>
                                        <div style="font-size: 13px; font-weight: 600; color: #334155;">Gambar saat ini</div>
                                        <div style="font-size: 11px; color: #94a3b8;">Kosongkan jika tidak ingin mengubah</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="margin-top: 6px; font-size: 12px; color: #94a3b8;">Format: JPG, PNG, WebP. Maks 2MB.</div>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 8px;">
                        <button type="submit" class="btn btn-primary" style="padding: 12px 32px;">
                            <span class="material-symbols-outlined" style="font-size: 18px;"><?php echo $edit_post ? 'save' : 'add_circle'; ?></span>
                            <?php echo $edit_post ? 'Simpan Perubahan' : 'Terbitkan Artikel'; ?>
                        </button>
                        <?php if ($edit_post): ?>
                            <a href="artikel.php" class="btn btn-secondary" style="padding: 12px 32px; text-decoration: none;">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="card" style="padding: 0; border-radius: 16px; background: white; overflow: hidden;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--outline-variant); display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="font-size: 18px; font-weight: 800; color: var(--secondary); margin: 0;">Daftar Artikel</h2>
                    <span style="font-size: 12px; color: #64748b; background: #f1f5f9; padding: 4px 10px; border-radius: 20px; font-weight: 600;"><?php echo count($posts); ?> Artikel</span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="admin-table" style="width: 100%; border-collapse: collapse; min-width: 700px;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="font-size: 12px; text-transform: uppercase; padding: 14px 20px; color: #475569; font-weight: 700;">Judul</th>
                                <th style="font-size: 12px; text-transform: uppercase; padding: 14px 20px; color: #475569; font-weight: 700;">Kategori</th>
                                <th style="font-size: 12px; text-transform: uppercase; padding: 14px 20px; color: #475569; font-weight: 700;">Penulis</th>
                                <th style="font-size: 12px; text-transform: uppercase; padding: 14px 20px; color: #475569; font-weight: 700;">Tanggal</th>
                                <th style="font-size: 12px; text-transform: uppercase; padding: 14px 20px; color: #475569; font-weight: 700;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 14px 20px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <?php if ($post['gambar']): ?>
                                            <img src="<?php echo e(img_url($post['gambar'])); ?>" style="width: 40px; height: 30px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0;">
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight: 600; color: #334155;"><?php echo e($post['judul']); ?></div>
                                            <div style="font-size: 12px; color: #94a3b8;"><?php echo e(substr($post['kutipan'], 0, 60)); ?><?php echo strlen($post['kutipan'] ?? '') > 60 ? '...' : ''; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 14px 20px;">
                                    <span style="background: #f1f5f9; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; color: #475569;"><?php echo e($post['kategori'] ?: 'Umum'); ?></span>
                                </td>
                                <td style="padding: 14px 20px; color: #475569; font-size: 14px;"><?php echo e($post['penulis']); ?></td>
                                <td style="padding: 14px 20px; font-size: 13px; color: #64748b;"><?php echo e(date('d M Y', strtotime($post['dibuat_pada']))); ?></td>
                                <td style="padding: 14px 20px;">
                                    <div style="display: flex; gap: 6px;">
                                        <a href="artikel.php?edit=<?php echo $post['artikel_id']; ?>" class="btn" style="padding: 5px 10px; font-size: 11px; background: #eff6ff; color: #2563eb; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="material-symbols-outlined" style="font-size: 13px;">edit</span> Edit
                                        </a>
                                        <a href="artikel.php?delete=<?php echo $post['artikel_id']; ?>" onclick="return confirm('Hapus artikel <?php echo e($post['judul']); ?>?')" class="btn" style="padding: 5px 10px; font-size: 11px; background: #fef2f2; color: #dc2626; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="material-symbols-outlined" style="font-size: 13px;">delete</span> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="5" style="padding: 48px; text-align: center; color: #94a3b8;">
                                    <span class="material-symbols-outlined" style="font-size: 48px; display: block; margin-bottom: 12px;">article</span>
                                    Belum ada artikel. Tulis artikel pertama Anda!
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>
<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const container = document.getElementById('preview-container');
        container.innerHTML = '<div style="display: flex; align-items: center; gap: 12px; padding: 8px 12px; background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;"><img src="' + e.target.result + '" style="width: 80px; height: 50px; object-fit: cover; border-radius: 6px;"><div><div style="font-size: 13px; font-weight: 600; color: #166534;">Gambar baru</div><div style="font-size: 11px; color: #64748b;">' + file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)</div></div></div>';
    };
    reader.readAsDataURL(file);
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
