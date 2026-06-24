<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_admin();

// Auto-create kategori table if not exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS kategori (
        kategori_id INTEGER PRIMARY KEY AUTOINCREMENT,
        nama_kategori TEXT NOT NULL,
        deskripsi TEXT DEFAULT NULL,
        dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    // ignore
}

$msg = '';
$error = '';

// DELETE category
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM kategori WHERE kategori_id = ?");
        $stmt->execute([$id]);
        $msg = "Kategori berhasil dihapus.";
    } catch (PDOException $e) {
        $error = "Gagal menghapus kategori: " . $e->getMessage();
    }
}

// ADD / EDIT category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $nama_kategori = trim($_POST['nama_kategori'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if (empty($nama_kategori)) {
        $error = "Nama kategori harus diisi.";
    } else {
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)");
                $stmt->execute([$nama_kategori, $deskripsi]);
                $msg = "Kategori '{$nama_kategori}' berhasil ditambahkan.";
            } elseif ($action === 'edit') {
                $id = (int)$_POST['kategori_id'];
                $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori=?, deskripsi=? WHERE kategori_id=?");
                $stmt->execute([$nama_kategori, $deskripsi, $id]);
                $msg = "Kategori '{$nama_kategori}' berhasil diperbarui.";
            }
        } catch (PDOException $e) {
            $error = "Gagal menyimpan kategori: " . $e->getMessage();
        }
    }
}

$categories = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$edit_cat = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM kategori WHERE kategori_id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_cat = $stmt->fetch();
}

// Additionally get categories used in products
$used_categories = $pdo->query("SELECT DISTINCT kategori FROM produk WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori")->fetchAll(PDO::FETCH_COLUMN);

$page_title = 'Manajemen Kategori';
require_once __DIR__ . '/../includes/header.php';
?>
<main style="padding: 48px 0; background: var(--background); font-family: 'Inter', sans-serif;">
    <div class="container" style="display: flex; gap: 48px; align-items: flex-start;">
        <aside style="width: 250px; flex-shrink: 0;">
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="sidebar-link">
                    <span class="material-symbols-outlined">dashboard</span>
                    Overview Admin
                </a>
                <a href="produk.php" class="sidebar-link">
                    <span class="material-symbols-outlined">inventory_2</span>
                    Produk
                </a>
                <a href="kategori.php" class="sidebar-link active">
                    <span class="material-symbols-outlined">category</span>
                    Kategori
                </a>
                <a href="pesanan.php" class="sidebar-link">
                    <span class="material-symbols-outlined">receipt_long</span>
                    Pesanan
                </a>
                <a href="../logout.php" class="sidebar-link" style="color: var(--error) !important;">
                    <span class="material-symbols-outlined" style="color: var(--error) !important;">logout</span>
                    Keluar
                </a>
            </nav>
        </aside>

        <section style="flex-grow: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                <h1 style="font-size: 32px; color: var(--secondary); font-weight: 800;">Manajemen Kategori</h1>
                <button onclick="document.getElementById('form-kategori').scrollIntoView({behavior:'smooth'})" class="btn btn-primary" style="gap: 8px;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">add</span> Tambah Kategori
                </button>
            </div>

            <?php if ($msg): ?>
                <div class="alert alert-success" style="margin-bottom: 24px; padding: 12px 20px; border-radius: 8px; background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; display: flex; align-items: center; gap: 10px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">check_circle</span>
                    <div><?php echo e($msg); ?></div>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger" style="margin-bottom: 24px; padding: 12px 20px; border-radius: 8px; background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; display: flex; align-items: center; gap: 10px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">error</span>
                    <div><?php echo e($error); ?></div>
                </div>
            <?php endif; ?>

            <!-- Add/Edit Form -->
            <div id="form-kategori" class="card" style="padding: 32px; margin-bottom: 32px; border-radius: 16px;">
                <h2 style="font-size: 20px; font-weight: 800; color: var(--secondary); margin-bottom: 24px;">
                    <?php echo $edit_cat ? 'Edit Kategori' : 'Tambah Kategori Baru'; ?>
                </h2>
                <form action="kategori.php<?php echo $edit_cat ? '?edit=' . $edit_cat['kategori_id'] : ''; ?>" method="POST">
                    <input type="hidden" name="action" value="<?php echo $edit_cat ? 'edit' : 'add'; ?>">
                    <?php if ($edit_cat): ?>
                        <input type="hidden" name="kategori_id" value="<?php echo $edit_cat['kategori_id']; ?>">
                    <?php endif; ?>
                    <div class="grid grid-2" style="gap: 16px;">
                        <div class="form-group">
                            <label for="nama_kategori">Nama Kategori</label>
                            <input type="text" id="nama_kategori" name="nama_kategori" class="form-control" value="<?php echo $edit_cat ? e($edit_cat['nama_kategori']) : ''; ?>" required>
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; width: 100%;">
                                <span class="material-symbols-outlined" style="font-size: 18px;"><?php echo $edit_cat ? 'save' : 'add_circle'; ?></span>
                                <?php echo $edit_cat ? 'Simpan Perubahan' : 'Tambah Kategori'; ?>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi (opsional)</label>
                        <textarea id="deskripsi" name="deskripsi" class="form-control" rows="2"><?php echo $edit_cat ? e($edit_cat['deskripsi']) : ''; ?></textarea>
                    </div>
                    <?php if ($edit_cat): ?>
                        <a href="kategori.php" class="btn btn-secondary" style="padding: 8px 20px; text-decoration: none; font-size: 13px;">Batal</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Category lists -->
            <div class="grid grid-2" style="gap: 24px;">
                <!-- Managed Categories -->
                <div class="card" style="padding: 0; border-radius: 16px; background: white; overflow: hidden;">
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--outline-variant);">
                        <h2 style="font-size: 18px; font-weight: 800; color: var(--secondary); margin: 0;">Kategori Terkelola</h2>
                    </div>
                    <div style="padding: 16px 24px;">
                        <?php if (empty($categories)): ?>
                            <p style="color: #94a3b8; text-align: center; padding: 24px;">Belum ada kategori. Tambahkan kategori baru.</p>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <?php
                                    $product_count = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE kategori = ?");
                                    $product_count->execute([$cat['nama_kategori']]);
                                    $count = $product_count->fetchColumn();
                                ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                                    <div>
                                        <div style="font-weight: 600; color: #334155;"><?php echo e($cat['nama_kategori']); ?></div>
                                        <div style="font-size: 12px; color: #94a3b8;"><?php echo e($count); ?> produk</div>
                                    </div>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="kategori.php?edit=<?php echo $cat['kategori_id']; ?>" style="padding: 4px 10px; background: #eff6ff; color: #2563eb; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;">Edit</a>
                                        <a href="kategori.php?delete=<?php echo $cat['kategori_id']; ?>" onclick="return confirm('Hapus kategori <?php echo e($cat['nama_kategori']); ?>?')" style="padding: 4px 10px; background: #fef2f2; color: #dc2626; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;">Hapus</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Categories Used in Products -->
                <div class="card" style="padding: 0; border-radius: 16px; background: white; overflow: hidden;">
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--outline-variant);">
                        <h2 style="font-size: 18px; font-weight: 800; color: var(--secondary); margin: 0;">Kategori yang Digunakan Produk</h2>
                    </div>
                    <div style="padding: 16px 24px;">
                        <?php if (empty($used_categories)): ?>
                            <p style="color: #94a3b8; text-align: center; padding: 24px;">Belum ada kategori yang digunakan produk.</p>
                        <?php else: ?>
                            <?php foreach ($used_categories as $cat): ?>
                                <?php
                                    $product_count = $pdo->prepare("SELECT COUNT(*) FROM produk WHERE kategori = ?");
                                    $product_count->execute([$cat]);
                                    $count = $product_count->fetchColumn();
                                ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                                    <div>
                                        <div style="font-weight: 600; color: #334155;"><?php echo e($cat); ?></div>
                                        <div style="font-size: 12px; color: #94a3b8;"><?php echo e($count); ?> produk</div>
                                    </div>
                                    <span style="background: #f1f5f9; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; color: #64748b;">Tersedia</span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
