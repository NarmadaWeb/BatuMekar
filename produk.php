<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = get_product_by_id($pdo, $id);

if (!$product) {
    header('Location: katalog.php');
    exit;
}

$sizes = get_product_sizes($pdo, $id);
$page_title = $product['nama'];
require_once 'includes/header.php';

$related = $pdo->prepare("SELECT * FROM produk WHERE kategori = ? AND produk_id != ? ORDER BY dibuat_pada DESC LIMIT 4");
$related->execute([$product['kategori'], $id]);
$related_products = $related->fetchAll();
?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,50..200">

<main style="padding: 48px 0; background: #fafaf9;">
    <div class="container" style="max-width: 1120px;">
        <!-- Breadcrumb -->
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 32px; font-size: 14px; color: #78716c;">
            <a href="katalog.php" style="color: #78716c; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                <span class="material-symbols-outlined" style="font-size: 16px;">storefront</span> Katalog
            </a>
            <span class="material-symbols-outlined" style="font-size: 14px; color: #d6d3d1;">chevron_right</span>
            <?php if ($product['kategori']): ?>
            <a href="katalog.php?category=<?php echo e(urlencode($product['kategori'])); ?>" style="color: #78716c; text-decoration: none;"><?php echo e($product['kategori']); ?></a>
            <span class="material-symbols-outlined" style="font-size: 14px; color: #d6d3d1;">chevron_right</span>
            <?php endif; ?>
            <span style="color: #292524; font-weight: 600;"><?php echo e($product['nama']); ?></span>
        </div>

        <!-- Product Detail -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 56px; align-items: start;">

            <!-- Left: Image -->
            <div style="position: sticky; top: 32px;">
                <div style="background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.06); border: 1px solid #e7e5e4;">
                    <img src="<?php echo e(img_url($product['gambar'])); ?>" alt="<?php echo e($product['nama']); ?>" 
                         style="width: 100%; height: 520px; object-fit: cover; display: block; transition: transform 0.4s ease;"
                         onmouseover="this.style.transform='scale(1.05)'" 
                         onmouseout="this.style.transform='scale(1)'">
                </div>
            </div>

            <!-- Right: Info -->
            <div>
                <!-- Category Badge -->
                <?php if ($product['kategori']): ?>
                <div style="display: inline-block; background: #fffbeb; color: #b45309; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #fde68a;">
                    <?php echo e($product['kategori']); ?>
                </div>
                <?php endif; ?>

                <!-- Title -->
                <h1 style="font-size: 36px; font-weight: 800; color: #292524; margin: 0 0 8px 0; line-height: 1.2;"><?php echo e($product['nama']); ?></h1>

                <!-- Rating -->
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; gap: 4px;">
                        <?php $rating = (int)($product['rating'] ?? 5); for ($i = 1; $i <= 5; $i++): ?>
                        <span class="material-symbols-outlined" style="font-size: 18px; color: <?php echo $i <= $rating ? '#f59e0b' : '#e7e5e4'; ?>;">
                            <?php echo $i <= $rating ? 'star' : 'star'; ?>
                        </span>
                        <?php endfor; ?>
                    </div>
                    <span style="font-size: 14px; color: #78716c; font-weight: 500;"><?php echo e($product['jumlah_ulasan'] ?: 0); ?> Review</span>
                </div>

                <!-- Price Card -->
                <div style="background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1px solid #fde68a; border-radius: 16px; padding: 24px; margin-bottom: 28px;">
                    <div style="font-size: 14px; color: #92400e; font-weight: 500; margin-bottom: 4px;">Harga</div>
                    <div style="font-size: 40px; font-weight: 800; color: #78350f;" id="product-price"><?php echo e(format_rupiah($product['harga'])); ?></div>
                </div>

                <!-- Size Selection -->
                <?php if (!empty($sizes)):
                    $any_stok = false;
                    foreach ($sizes as $s) { if ($s['stok'] > 0) { $any_stok = true; break; } }
                ?>
                <div style="margin-bottom: 28px;">
                    <label style="font-size: 15px; font-weight: 700; color: #292524; display: block; margin-bottom: 12px;">
                        Pilih Ukuran
                    </label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px;" id="size-options">
                        <?php $is_first_size = true; ?>
                        <?php foreach ($sizes as $size):
                            $size_price = $size['harga'] ?? $product['harga'];
                            $has_stok = $size['stok'] > 0;
                        ?>
                        <label class="size-option" style="cursor: pointer;">
                            <input type="radio" name="ukuran_id" value="<?php echo e($size['ukuran_id']); ?>"
                                   data-price="<?php echo e($size_price); ?>"
                                   data-label="<?php echo e($size['ukuran_ml']); ?>ml"
                                   <?php echo $is_first_size ? 'checked' : ''; ?>
                                   <?php echo $has_stok ? '' : 'disabled'; ?>
                                   style="display: none;" onchange="updatePrice(this)">
                            <div style="padding: 14px 16px; border: 2px solid var(--outline-variant); border-radius: 12px; text-align: center; transition: all 0.15s; background: white; position: relative; <?php echo $has_stok ? '' : 'background: #f5f5f4; opacity: 0.6;'; ?>">
                                <div style="font-weight: 800; font-size: 17px; color: #292524;"><?php echo e($size['ukuran_ml']); ?> ml</div>
                                <div style="font-size: 13px; color: #d97706; font-weight: 600; margin-top: 2px;"><?php echo e(format_rupiah($size_price)); ?></div>
                                <?php if (!$has_stok): ?>
                                    <div style="font-size: 10px; color: #ef4444; font-weight: 700; margin-top: 4px; text-transform: uppercase;">Habis</div>
                                <?php endif; ?>
                            </div>
                        </label>
                        <?php $is_first_size = false; endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 32px;">
                    <?php if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'): ?>
                    <form action="keranjang.php" method="POST" id="add-to-cart-form">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo e($product['produk_id']); ?>">
                        <input type="hidden" name="ukuran_id" id="selected-ukuran" value="<?php echo !empty($sizes) ? e($sizes[0]['ukuran_id']) : '0'; ?>">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 16px; font-size: 16px; justify-content: center; border-radius: 12px;" <?php echo (!$any_stok && !empty($sizes)) ? 'disabled' : ''; ?>>
                            <span class="material-symbols-outlined" style="font-size: 20px;">shopping_cart</span>
                            Tambah ke Keranjang
                        </button>
                    </form>
                    <?php else: ?>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; padding: 16px; border-radius: 12px; font-weight: 600; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="font-size: 20px; color: var(--primary);">admin_panel_settings</span>
                        Akun Admin tidak diperbolehkan belanja/melakukan pembelian.
                    </div>
                    <?php endif; ?>
                    <a href="https://wa.me/6281234567890?text=Halo%20saya%20tertarik%20dengan%20<?php echo e(urlencode($product['nama'])); ?>" target="_blank" 
                       style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 14px; background: #25D366; color: white; border-radius: 12px; font-weight: 700; font-size: 15px; text-decoration: none; transition: opacity 0.2s; border: none;"
                       onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <span class="material-symbols-outlined" style="font-size: 20px;">chat</span>
                        Chat via WhatsApp
                    </a>
                </div>

                <script>
                function updatePrice(el) {
                    const price = el.getAttribute('data-price');
                    const label = el.getAttribute('data-label');
                    document.getElementById('product-price').textContent = 'Rp ' + Number(price).toLocaleString('id-ID');
                    document.getElementById('selected-ukuran').value = el.value;
                    document.querySelectorAll('#size-options .size-option > div').forEach(d => {
                        d.style.borderColor = 'var(--outline-variant)';
                    });
                    el.nextElementSibling.style.borderColor = 'var(--primary)';
                }
                <?php if (!empty($sizes)): ?>
                document.querySelector('#size-options input:checked')?.dispatchEvent(new Event('change'));
                <?php endif; ?>
                </script>

                <!-- Description -->
                <div style="border-top: 1px solid #e7e5e4; padding-top: 28px;">
                    <h2 style="font-size: 20px; font-weight: 800; color: #292524; margin: 0 0 16px 0;">
                        <span class="material-symbols-outlined" style="font-size: 20px; vertical-align: middle; margin-right: 6px;">description</span>
                        Deskripsi Produk
                    </h2>
                    <div style="color: #57534e; line-height: 1.8; font-size: 15px; white-space: pre-wrap;">
                        <?php echo e($product['deskripsi']); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <div style="margin-top: 64px; padding-top: 48px; border-top: 1px solid #e7e5e4;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px;">
                <h2 style="font-size: 24px; font-weight: 800; color: #292524; margin: 0;">
                    <span class="material-symbols-outlined" style="font-size: 22px; vertical-align: middle; margin-right: 6px;">category</span>
                    Produk Terkait
                </h2>
                <a href="katalog.php?category=<?php echo e(urlencode($product['kategori'])); ?>" style="color: #d97706; font-weight: 600; font-size: 14px; text-decoration: none;">
                    Lihat Semua →
                </a>
            </div>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                <?php foreach ($related_products as $rp):
                    $rp_sizes = get_product_sizes($pdo, $rp['produk_id']);
                ?>
                <a href="produk.php?id=<?php echo e($rp['produk_id']); ?>" style="text-decoration: none; color: inherit;">
                    <div style="background: white; border-radius: 16px; overflow: hidden; border: 1px solid #e7e5e4; transition: box-shadow 0.2s, transform 0.2s;" 
                         onmouseover="this.style.boxShadow='0 8px 30px rgba(0,0,0,0.08)'; this.style.transform='translateY(-2px)'" 
                         onmouseout="this.style.boxShadow='none'; this.style.transform='none'">
                        <img src="<?php echo e(img_url($rp['gambar'])); ?>" alt="<?php echo e($rp['nama']); ?>" style="width: 100%; height: 180px; object-fit: cover; display: block;">
                        <div style="padding: 16px;">
                            <div style="font-weight: 700; color: #292524; font-size: 14px; margin-bottom: 4px;"><?php echo e($rp['nama']); ?></div>
                            <div style="font-weight: 700; color: #d97706; font-size: 15px;"><?php echo e(format_rupiah($rp['harga'])); ?></div>
                            <?php if (!empty($rp_sizes)): ?>
                            <div style="display: flex; gap: 4px; margin-top: 8px; flex-wrap: wrap;">
                                <?php foreach ($rp_sizes as $s): ?>
                                <span style="background: #f5f5f4; color: #78716c; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600;"><?php echo e($s['ukuran_ml']); ?>ml</span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
