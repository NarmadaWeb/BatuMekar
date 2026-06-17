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
?>

<main class="py-xl">
    <div class="container">
        <div class="grid grid-2" style="align-items: start; gap: 80px;">
            <div>
                <img src="<?php echo e($product['gambar']); ?>" alt="<?php echo e($product['nama']); ?>" class="product-img-large">
            </div>
            <div>
                <h1 class="text-primary" style="font-size: 48px; margin-bottom: 16px;"><?php echo e($product['nama']); ?></h1>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 24px;">
                    <span class="text-primary" style="font-weight: 600;">(<?php echo e($product['rating']); ?>/5)</span>
                    <span class="text-on-surface-variant">• <?php echo e($product['jumlah_ulasan']); ?> Review</span>
                </div>

                <div class="card" style="margin-bottom: 24px; background: var(--surface-variant);">
                    <div class="text-primary font-display" style="font-size: 32px; font-weight: 700;" id="product-price"><?php echo e(format_rupiah($product['harga'])); ?></div>
                </div>

                <?php if (!empty($sizes)): 
                    $is_first_size = true;
                    $any_stok = false;
                    foreach ($sizes as $s) { if ($s['stok'] > 0) { $any_stok = true; break; } }
                ?>
                <div style="margin-bottom: 24px;">
                    <label style="font-weight: 700; color: var(--secondary); display: block; margin-bottom: 12px;">Pilih Ukuran (ml):</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;" id="size-options">
                        <?php foreach ($sizes as $size): 
                            $size_price = $size['harga'] ?? $product['harga'];
                            $has_stok = $size['stok'] > 0;
                        ?>
                        <label class="size-option" style="cursor: pointer; <?php echo $has_stok ? '' : 'opacity: 0.5;'; ?>">
                            <input type="radio" name="ukuran_id" value="<?php echo e($size['ukuran_id']); ?>" 
                                   data-price="<?php echo e($size_price); ?>" 
                                   data-label="<?php echo e($size['ukuran_ml']); ?>ml"
                                   <?php echo $is_first_size ? 'checked' : ''; ?>
                                   <?php echo $has_stok ? '' : 'disabled'; ?>
                                   style="display: none;" onchange="updatePrice(this)">
                            <div style="padding: 12px 20px; border: 2px solid var(--outline-variant); border-radius: 10px; text-align: center; transition: all 0.2s; background: white; <?php echo $has_stok ? '' : 'background: #f1f5f9;'; ?>"
                                 onmouseover="this.style.borderColor='var(--primary)'" 
                                 onmouseout="this.style.borderColor=this.parentElement.querySelector('input').checked ? 'var(--primary)' : 'var(--outline-variant)'">
                                <div style="font-weight: 800; font-size: 16px; color: var(--secondary);"><?php echo e($size['ukuran_ml']); ?> ml</div>
                                <div style="font-size: 13px; color: var(--primary); font-weight: 600;"><?php echo e(format_rupiah($size_price)); ?></div>
                                <?php if (!$has_stok): ?>
                                    <div style="font-size: 11px; color: #ef4444; font-weight: 600;">Stok habis</div>
                                <?php endif; ?>
                            </div>
                        </label>
                        <?php $is_first_size = false; endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <form action="keranjang.php" method="POST" id="add-to-cart-form">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo e($product['produk_id']); ?>">
                        <input type="hidden" name="ukuran_id" id="selected-ukuran" value="<?php echo !empty($sizes) ? e($sizes[0]['ukuran_id']) : '0'; ?>">
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 16px;" <?php echo (!$any_stok && !empty($sizes)) ? 'disabled style="opacity:0.5;"' : ''; ?>>
                            Tambah ke Keranjang
                        </button>
                    </form>
                    <a href="https://wa.me/6281234567890" target="_blank" class="btn btn-secondary" style="background: #25D366; border: none; color: white;">Chat via WhatsApp</a>
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

                <div style="margin-top: 48px; border-top: 1px solid var(--outline-variant); padding-top: 24px;">
                    <h2 class="text-primary" style="margin-bottom: 16px;">Deskripsi Produk</h2>
                    <p class="text-on-surface-variant"><?php echo e($product['deskripsi']); ?></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
