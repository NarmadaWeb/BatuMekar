<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_admin();

// Auto-create tables if not exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS kategori (
        kategori_id INTEGER PRIMARY KEY AUTOINCREMENT,
        nama_kategori TEXT NOT NULL,
        deskripsi TEXT DEFAULT NULL,
        dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS ukuran_produk (
        ukuran_id INTEGER PRIMARY KEY AUTOINCREMENT,
        produk_id INTEGER NOT NULL,
        ukuran_ml INTEGER NOT NULL,
        harga REAL DEFAULT NULL,
        stok INTEGER DEFAULT 0,
        FOREIGN KEY (produk_id) REFERENCES produk(produk_id) ON DELETE CASCADE
    )");
} catch (PDOException $e) {}

try {
    $pdo->exec("ALTER TABLE detail_pesanan ADD COLUMN ukuran_id INTEGER DEFAULT NULL");
} catch (PDOException $e) {}

// Handle CRUD actions
$msg = '';
$error = '';

// DELETE product
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM produk WHERE produk_id = ?");
        $stmt->execute([$id]);
        $msg = "Produk berhasil dihapus.";
    } catch (PDOException $e) {
        $error = "Gagal menghapus produk: " . $e->getMessage();
    }
}

// DELETE size
if (isset($_GET['delete_size'])) {
    $size_id = (int)$_GET['delete_size'];
    $prod_id = (int)$_GET['product_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM ukuran_produk WHERE ukuran_id = ? AND produk_id = ?");
        $stmt->execute([$size_id, $prod_id]);
        $msg = "Ukuran berhasil dihapus.";
    } catch (PDOException $e) {
        $error = "Gagal menghapus ukuran: " . $e->getMessage();
    }
}

// ADD / EDIT product & ADD size
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_size') {
        $produk_id = (int)$_POST['produk_id'];
        $ukuran_ml = (int)$_POST['ukuran_ml'];
        $harga_size = $_POST['harga_size'] !== '' ? (float)$_POST['harga_size'] : null;
        $stok_size = (int)$_POST['stok_size'];
        try {
            $stmt = $pdo->prepare("INSERT INTO ukuran_produk (produk_id, ukuran_ml, harga, stok) VALUES (?, ?, ?, ?)");
            $stmt->execute([$produk_id, $ukuran_ml, $harga_size, $stok_size]);
            $msg = "Ukuran {$ukuran_ml}ml berhasil ditambahkan.";
        } catch (PDOException $e) {
            $error = "Gagal menambah ukuran: " . $e->getMessage();
        }
    }

    if ($action === 'add' || $action === 'edit') {
        $nama = trim($_POST['nama'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $harga = (float)($_POST['harga'] ?? 0);
        $kategori = trim($_POST['kategori'] ?? '');
        $stok = (int)($_POST['stok'] ?? 0);
        $unggulan = isset($_POST['unggulan']) ? 1 : 0;
        $gambar = '';

        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi batas server.',
                    UPLOAD_ERR_FORM_SIZE => 'Ukuran file terlalu besar.',
                    UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian.',
                    UPLOAD_ERR_NO_FILE => '',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan.',
                    UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
                ];
                $error = 'Upload gagal: ' . ($upload_errors[$_FILES['gambar']['error']] ?? 'Error tidak diketahui.');
            } else {
                $file_tmp = $_FILES['gambar']['tmp_name'];
                $file_name = $_FILES['gambar']['name'];
                $file_size = $_FILES['gambar']['size'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($file_ext, $allowed_exts)) {
                    $error = 'Format file tidak didukung. Gunakan JPG, PNG, atau WebP.';
                } elseif ($file_size > 2 * 1024 * 1024) {
                    $error = 'Ukuran file maksimal 2MB.';
                } else {
                    $upload_dir = __DIR__ . '/../assets/uploads/produk/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    $new_file_name = 'produk_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
                    if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                        $gambar = 'assets/uploads/produk/' . $new_file_name;
                    } else {
                        $error = 'Gagal menyimpan file. Coba lagi.';
                    }
                }
            }
        }

        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO produk (nama, deskripsi, harga, kategori, stok, unggulan, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nama, $deskripsi, $harga, $kategori, $stok, $unggulan, $gambar]);
                $new_id = $pdo->lastInsertId();
                $msg = "Produk '{$nama}' berhasil ditambahkan.";
                echo "<script>window.location.href='produk.php?edit=" . $new_id . "&added=1';</script>";
                exit;
            } elseif ($action === 'edit') {
                $id = (int)$_POST['produk_id'];
                if ($gambar) {
                    $stmt = $pdo->prepare("UPDATE produk SET nama=?, deskripsi=?, harga=?, kategori=?, stok=?, unggulan=?, gambar=? WHERE produk_id=?");
                    $stmt->execute([$nama, $deskripsi, $harga, $kategori, $stok, $unggulan, $gambar, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE produk SET nama=?, deskripsi=?, harga=?, kategori=?, stok=?, unggulan=? WHERE produk_id=?");
                    $stmt->execute([$nama, $deskripsi, $harga, $kategori, $stok, $unggulan, $id]);
                }
                $msg = "Produk '{$nama}' berhasil diperbarui.";
            }
        } catch (PDOException $e) {
            $error = "Gagal menyimpan produk: " . $e->getMessage();
        }
    }
}

// Get all products with stock info
$products = $pdo->query("SELECT * FROM produk ORDER BY dibuat_pada DESC")->fetchAll();
$total_products = count($products);
$out_of_stock = $pdo->query("SELECT COUNT(*) FROM produk WHERE stok <= 0")->fetchColumn();

// Get categories for dropdown
$categories = $pdo->query("SELECT nama_kategori FROM kategori ORDER BY nama_kategori")->fetchAll(PDO::FETCH_COLUMN);

// If editing, load product
$edit_product = null;
$edit_sizes = [];
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_product = get_product_by_id($pdo, $edit_id);
    if ($edit_product) {
        $edit_sizes = get_product_sizes($pdo, $edit_id);
    }
}

$page_title = 'Manajemen Produk';
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
                <a href="produk.php" class="sidebar-link active">
                    <span class="material-symbols-outlined">inventory_2</span>
                    Produk
                </a>
                <a href="kategori.php" class="sidebar-link">
                    <span class="material-symbols-outlined">category</span>
                    Kategori
                </a>
                <a href="pesanan.php" class="sidebar-link">
                    <span class="material-symbols-outlined">receipt_long</span>
                    Pesanan
                </a>
                <a href="../katalog.php" class="sidebar-link">
                    <span class="material-symbols-outlined">storefront</span>
                    Lihat Toko
                </a>
                <a href="../logout.php" class="sidebar-link" style="color: var(--error) !important;">
                    <span class="material-symbols-outlined" style="color: var(--error) !important;">logout</span>
                    Keluar
                </a>
            </nav>
        </aside>

        <section style="flex-grow: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                <h1 style="font-size: 32px; color: var(--secondary); font-weight: 800;">Manajemen Produk</h1>
                <button onclick="document.getElementById('form-produk').scrollIntoView({behavior:'smooth'})" class="btn btn-primary" style="gap: 8px;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">add</span> Tambah Produk
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

            <!-- Stats -->
            <div class="grid grid-3" style="margin-bottom: 32px; gap: 16px;">
                <div class="card" style="padding: 20px; display: flex; align-items: center; gap: 16px;">
                    <div style="background: #eff6ff; color: #2563eb; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 24px;">inventory_2</span>
                    </div>
                    <div>
                        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Produk</div>
                        <div style="font-size: 24px; font-weight: 800; color: var(--primary);"><?php echo e($total_products); ?></div>
                    </div>
                </div>
                <div class="card" style="padding: 20px; display: flex; align-items: center; gap: 16px;">
                    <div style="background: #fef2f2; color: #ef4444; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 24px;">warning</span>
                    </div>
                    <div>
                        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Stok Habis</div>
                        <div style="font-size: 24px; font-weight: 800; color: var(--error);"><?php echo e($out_of_stock); ?></div>
                    </div>
                </div>
                <div class="card" style="padding: 20px; display: flex; align-items: center; gap: 16px;">
                    <?php
                        $total_stok = $pdo->query("SELECT SUM(stok) FROM produk")->fetchColumn();
                    ?>
                    <div style="background: #ecfdf5; color: #10b981; width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 24px;">package_2</span>
                    </div>
                    <div>
                        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Stok</div>
                        <div style="font-size: 24px; font-weight: 800; color: var(--primary);"><?php echo e($total_stok ?: 0); ?></div>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Product Form -->
            <div id="form-produk" class="card" style="padding: 32px; margin-bottom: 32px; border-radius: 16px;">
                <h2 style="font-size: 20px; font-weight: 800; color: var(--secondary); margin-bottom: 24px;">
                    <?php echo $edit_product ? 'Edit Produk' : 'Tambah Produk Baru'; ?>
                </h2>
                <form action="produk.php<?php echo $edit_product ? '?edit=' . $edit_product['produk_id'] : ''; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $edit_product ? 'edit' : 'add'; ?>">
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="produk_id" value="<?php echo $edit_product['produk_id']; ?>">
                    <?php endif; ?>

                    <div class="grid grid-2" style="gap: 16px;">
                        <div class="form-group">
                            <label for="nama">Nama Produk</label>
                            <input type="text" id="nama" name="nama" class="form-control" value="<?php echo $edit_product ? e($edit_product['nama']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="harga">Harga (Rp)</label>
                            <input type="number" id="harga" name="harga" class="form-control" value="<?php echo $edit_product ? e($edit_product['harga']) : ''; ?>" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="kategori">Kategori</label>
                            <select id="kategori" name="kategori" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo e($cat); ?>" <?php echo ($edit_product && $edit_product['kategori'] === $cat) ? 'selected' : ''; ?>><?php echo e($cat); ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled style="color:#94a3b8;">Belum ada kategori. Buat di menu Kategori.</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="stok">Stok</label>
                            <input type="number" id="stok" name="stok" class="form-control" value="<?php echo $edit_product ? e($edit_product['stok']) : '100'; ?>" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="gambar">Gambar Produk</label>
                            <input type="file" id="gambar" name="gambar" class="form-control" accept="image/*" onchange="previewImage(event)">
                            <div id="preview-container" style="margin-top: 10px;">
                                <?php if ($edit_product && $edit_product['gambar']): ?>
                                    <div style="display: flex; align-items: center; gap: 12px; padding: 8px 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <img src="<?php echo e(img_url($edit_product['gambar'])); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        <div>
                                            <div style="font-size: 13px; font-weight: 600; color: #334155;">Gambar saat ini</div>
                                            <div style="font-size: 11px; color: #94a3b8;">Kosongkan jika tidak ingin mengubah</div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="margin-top: 6px; font-size: 12px; color: #94a3b8;">Format: JPG, PNG, WebP. Maks 2MB.</div>
                        </div>
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 8px; margin-top: 32px;">
                                <input type="checkbox" name="unggulan" value="1" <?php echo ($edit_product && $edit_product['unggulan']) ? 'checked' : ''; ?>>
                                <span style="font-weight: 600; color: var(--secondary);">Produk Unggulan (tampil di beranda)</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi Produk</label>
                        <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3"><?php echo $edit_product ? e($edit_product['deskripsi']) : ''; ?></textarea>
                    </div>
                    <div style="display: flex; gap: 12px; margin-top: 16px;">
                        <button type="submit" class="btn btn-primary" style="padding: 12px 32px;">
                            <span class="material-symbols-outlined" style="font-size: 18px;"><?php echo $edit_product ? 'save' : 'add_circle'; ?></span>
                            <?php echo $edit_product ? 'Simpan Perubahan' : 'Tambah Produk'; ?>
                        </button>
                        <?php if ($edit_product): ?>
                            <a href="produk.php" class="btn btn-secondary" style="padding: 12px 32px; text-decoration: none;">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Size Management -->
            <?php if ($edit_product): ?>
            <div id="form-ukuran" class="card" style="padding: 32px; margin-bottom: 32px; border-radius: 16px;">
                <h2 style="font-size: 20px; font-weight: 800; color: var(--secondary); margin-bottom: 24px;">
                    <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 8px;">straighten</span>
                    Ukuran (ml) — <?php echo e($edit_product['nama']); ?>
                </h2>

                <?php if (!empty($edit_sizes)): ?>
                <div style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 12px;">
                    <?php foreach ($edit_sizes as $size): ?>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; gap: 16px;">
                        <div>
                            <div style="font-weight: 700; font-size: 16px; color: var(--secondary);"><?php echo e($size['ukuran_ml']); ?> ml</div>
                            <div style="font-size: 12px; color: #64748b;">
                                <?php if ($size['harga']): ?>
                                    Harga: <?php echo e(format_rupiah($size['harga'])); ?>
                                <?php else: ?>
                                    Harga: Mengikuti produk
                                <?php endif; ?>
                                &middot; Stok: <?php echo e($size['stok']); ?>
                            </div>
                        </div>
                        <a href="produk.php?edit=<?php echo $edit_product['produk_id']; ?>&delete_size=<?php echo $size['ukuran_id']; ?>&product_id=<?php echo $edit_product['produk_id']; ?>" onclick="return confirm('Hapus ukuran <?php echo $size['ukuran_ml']; ?>ml?')" style="color: #ef4444; text-decoration: none; padding: 4px 8px; border-radius: 6px; background: #fef2f2; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                            <span class="material-symbols-outlined" style="font-size: 14px;">delete</span> Hapus
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="padding: 16px; background: #fffbeb; border-radius: 8px; margin-bottom: 20px; font-size: 14px; color: #92400e;">
                    <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">info</span>
                    Belum ada ukuran. Tambahkan ukuran (ml) untuk produk ini agar pembeli bisa memilih.
                </div>
                <?php endif; ?>

                <form action="produk.php?edit=<?php echo $edit_product['produk_id']; ?>" method="POST" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
                    <input type="hidden" name="action" value="add_size">
                    <input type="hidden" name="produk_id" value="<?php echo $edit_product['produk_id']; ?>">
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Ukuran (ml)</label>
                        <input type="number" name="ukuran_ml" class="form-control" style="width: 120px;" placeholder="250" min="1" required>
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Harga (kosongkan jika sama)</label>
                        <input type="number" name="harga_size" class="form-control" style="width: 150px;" placeholder="Kosongkan" min="0">
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: #475569; display: block; margin-bottom: 4px;">Stok</label>
                        <input type="number" name="stok_size" class="form-control" style="width: 100px;" value="0" min="0" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">add</span> Tambah Ukuran
                    </button>
                </form>
                <div style="margin-top: 12px; font-size: 12px; color: #94a3b8;">
                    Biarkan harga kosong jika harga ukuran sama dengan harga produk. Stok ukuran akan dikurangi saat pembelian.
                </div>
            </div>
            <?php endif; ?>

            <!-- Product Table -->
            <div class="card" style="padding: 0; border-radius: 16px; border: 1px solid rgba(0,0,0,0.05); background: white; overflow: hidden;">
                <div style="padding: 24px; border-bottom: 1px solid var(--outline-variant); display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="font-size: 20px; font-weight: 800; color: var(--secondary); margin: 0;">Daftar Produk</h2>
                    <span style="font-size: 12px; color: #64748b; background: #f1f5f9; padding: 4px 10px; border-radius: 20px; font-weight: 600;"><?php echo e($total_products); ?> Produk</span>
                </div>
                <div style="overflow-x: auto;">
                    <table class="admin-table" style="width: 100%; border-collapse: collapse; text-align: left; min-width: 900px;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="font-size: 12px; text-transform: uppercase; padding: 16px 20px; color: #475569; font-weight: 700;">Gambar</th>
                                <th style="font-size: 12px; text-transform: uppercase; padding: 16px 20px; color: #475569; font-weight: 700;">Nama Produk</th>
                                <th style="font-size: 12px; text-transform: uppercase; padding: 16px 20px; color: #475569; font-weight: 700;">Kategori</th>
                                <th style="font-size: 12px; text-transform: uppercase; padding: 16px 20px; color: #475569; font-weight: 700;">Ukuran (ml)</th>
                                <th style="font-size: 12px; text-transform: uppercase; padding: 16px 20px; color: #475569; font-weight: 700;">Harga</th>
                                <th style="font-size: 12px; text-transform: uppercase; padding: 16px 20px; color: #475569; font-weight: 700;">Stok</th>
                                <th style="font-size: 12px; text-transform: uppercase; padding: 16px 20px; color: #475569; font-weight: 700;">Status</th>
                                <th style="font-size: 12px; text-transform: uppercase; padding: 16px 20px; color: #475569; font-weight: 700;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p):
                                $p_sizes = get_product_sizes($pdo, $p['produk_id']);
                                $stok_status = 'tersedia';
                                $stok_color = '#10b981';
                                $stok_bg = '#ecfdf5';
                                if ($p['stok'] <= 0) {
                                    $stok_status = 'Habis';
                                    $stok_color = '#ef4444';
                                    $stok_bg = '#fef2f2';
                                } elseif ($p['stok'] <= 10) {
                                    $stok_status = 'Menipis';
                                    $stok_color = '#f59e0b';
                                    $stok_bg = '#fffbeb';
                                }
                            ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                                <td style="padding: 12px 20px;">
                                    <?php if ($p['gambar']): ?>
                                        <img src="<?php echo e(img_url($p['gambar'])); ?>" alt="<?php echo e($p['nama']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                            <span class="material-symbols-outlined" style="color: #94a3b8; font-size: 20px;">image</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 20px; font-weight: 600; color: #334155;"><?php echo e($p['nama']); ?></td>
                                <td style="padding: 12px 20px; color: #475569;">
                                    <span style="background: #f1f5f9; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; color: #475569;"><?php echo e($p['kategori'] ?: '-'); ?></span>
                                </td>
                                <td style="padding: 12px 20px;">
                                    <?php if (!empty($p_sizes)): ?>
                                        <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                        <?php foreach ($p_sizes as $s): ?>
                                            <span style="background: #eff6ff; color: #2563eb; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;"><?php echo e($s['ukuran_ml']); ?>ml</span>
                                        <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-size: 12px;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 20px; font-weight: 700; color: var(--primary);"><?php echo e(format_rupiah($p['harga'])); ?></td>
                                <td style="padding: 12px 20px; font-weight: 700; color: #0f172a;"><?php echo e($p['stok']); ?></td>
                                <td style="padding: 12px 20px;">
                                    <span style="display: inline-flex; align-items: center; gap: 6px; background: <?php echo $stok_bg; ?>; color: <?php echo $stok_color; ?>; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: <?php echo $stok_color; ?>;"></span>
                                        <?php echo $stok_status; ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 20px;">
                                    <div style="display: flex; gap: 8px;">
                                        <a href="produk.php?edit=<?php echo $p['produk_id']; ?>" class="btn" style="padding: 6px 12px; font-size: 12px; background: #eff6ff; color: #2563eb; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="material-symbols-outlined" style="font-size: 14px;">edit</span> Edit
                                        </a>
                                        <a href="produk.php?delete=<?php echo $p['produk_id']; ?>" onclick="return confirm('Yakin ingin menghapus produk <?php echo e($p['nama']); ?>?')" class="btn" style="padding: 6px 12px; font-size: 12px; background: #fef2f2; color: #dc2626; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="material-symbols-outlined" style="font-size: 14px;">delete</span> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="8" style="padding: 48px; text-align: center; color: #94a3b8;">
                                    <span class="material-symbols-outlined" style="font-size: 48px; display: block; margin-bottom: 12px;">inventory_2</span>
                                    Belum ada produk. Tambahkan produk pertama Anda!
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
        container.innerHTML = '<div style="display: flex; align-items: center; gap: 12px; padding: 8px 12px; background: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;"><img src="' + e.target.result + '" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0;"><div><div style="font-size: 13px; font-weight: 600; color: #166534;">Gambar baru</div><div style="font-size: 11px; color: #64748b;">' + file.name + ' (' + (file.size/1024).toFixed(1) + ' KB)</div></div></div>';
    };
    reader.readAsDataURL(file);
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
