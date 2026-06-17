<?php
$page_title = 'Beranda';
require_once 'includes/header.php';
$featured_products = get_featured_products($pdo);
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <img class="hero-img" src="assets/images/hero.jpg" alt="BatuMekar Hero"/>
        <div class="container">
            <div class="hero-content">
                <h1 style="font-size: 56px; margin-bottom: 24px;">Madu Asli dari Hutan Desa BatuMekar</h1>
                <p style="font-size: 18px; margin-bottom: 48px;">Merasakan kemurnian alam dari setiap tetes madu yang dipanen secara tradisional oleh masyarakat desa kami.</p>
                <a href="katalog.php" class="btn btn-primary">Belanja Sekarang</a>
            </div>
        </div>
    </section>

    <!-- Values -->
    <section class="py-xl">
        <div class="container grid grid-3">
            <div class="text-center">
                <span class="material-symbols-outlined text-primary" style="font-size: 48px;">nature_people</span>
                <h3 class="text-primary mb-md" style="margin-top: 16px;">100% Alami</h3>
                <p class="text-on-surface-variant">Murni tanpa olahan kimia, langsung dari sarang lebah hutan.</p>
            </div>
            <div class="text-center">
                <span class="material-symbols-outlined text-primary" style="font-size: 48px;">no_food</span>
                <h3 class="text-primary mb-md" style="margin-top: 16px;">Tanpa Pemanis</h3>
                <p class="text-on-surface-variant">Rasa manis alami dari nektar bunga hutan pilihan.</p>
            </div>
            <div class="text-center">
                <span class="material-symbols-outlined text-primary" style="font-size: 48px;">eco</span>
                <h3 class="text-primary mb-md" style="margin-top: 16px;">Panen Berkelanjutan</h3>
                <p class="text-on-surface-variant">Menjaga ekosistem lebah demi kelestarian alam BatuMekar.</p>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section style="padding: 80px 0; background: var(--surface-variant);">
        <div class="container">
            <h2 class="text-secondary" style="font-size: 40px; margin-bottom: 48px;">Koleksi Madu Terbaik</h2>
            <div class="grid grid-4">
                <?php foreach ($featured_products as $product):
                    $fp_sizes = get_product_sizes($pdo, $product['produk_id']);
                ?>
                <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <a href="produk.php?id=<?php echo e($product['produk_id']); ?>">
                            <img src="<?php echo e($product['gambar']); ?>" alt="<?php echo e($product['nama']); ?>" style="width: 100%; height: 250px; object-fit: cover;">
                        </a>
                        <div style="padding: 24px;">
                            <h3 class="text-secondary" style="font-size: 20px; font-weight: 700; margin-bottom: 8px;"><?php echo e($product['nama']); ?></h3>
                            <?php if (!empty($fp_sizes)): ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px;">
                                <?php foreach ($fp_sizes as $s): ?>
                                <span style="background: #eff6ff; color: #2563eb; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;"><?php echo e($s['ukuran_ml']); ?>ml</span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <p class="text-on-surface-variant" style="font-size: 14px; margin-bottom: 16px;"><?php echo e(e($product['deskripsi'] ?? '')); ?></p>
                        </div>
                    </div>
                    <div style="padding: 0 24px 24px 24px;">
                        <p class="text-primary" style="font-size: 18px; font-weight: 700; margin-bottom: 16px;"><?php echo e(format_rupiah($product['harga'])); ?></p>
                        <a href="produk.php?id=<?php echo e($product['produk_id']); ?>" class="btn btn-primary" style="width: 100%; text-decoration: none; display: inline-block; text-align: center; padding: 12px 0;">Pilih Ukuran</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-xl" style="background: var(--surface);">
        <div class="container">
            <div class="text-center" style="margin-bottom: 48px;">
                <span class="text-primary" style="font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Kisah Pelanggan</span>
                <h2 class="text-secondary" style="font-size: 40px; margin-top: 8px;">Apa Kata Mereka?</h2>
            </div>
            <div class="grid grid-3">
                <div class="card text-center" style="display: flex; flex-direction: column; align-items: center; justify-content: space-between; padding: 32px 24px;">
                    <div>
                        <span class="material-symbols-outlined text-primary" style="font-size: 36px; opacity: 0.3; margin-bottom: 16px;">format_quote</span>
                        <p style="font-style: italic; color: var(--on-surface-variant); margin-bottom: 24px; line-height: 1.6;">"Madu Hutan BatuMekar bener-bener beda. Rasanya sangat kaya dan teksturnya murni banget. Senang bisa dukung petani lokal."</p>
                    </div>
                    <div>
                        <p style="font-weight: 700; color: var(--secondary); margin-bottom: 4px;">Siti Aminah</p>
                        <p style="font-size: 12px; color: var(--outline); margin: 0;">Pecinta Hidup Sehat</p>
                    </div>
                </div>

                <div class="card text-center" style="display: flex; flex-direction: column; align-items: center; justify-content: space-between; padding: 32px 24px;">
                    <div>
                        <span class="material-symbols-outlined text-primary" style="font-size: 36px; opacity: 0.3; margin-bottom: 16px;">format_quote</span>
                        <p style="font-style: italic; color: var(--on-surface-variant); margin-bottom: 24px; line-height: 1.6;">"Kami menggunakan Madu Multiflora BatuMekar untuk semua hidangan penutup di restoran kami. Aroma khasnya memberikan rasa manis alami yang tak tertandingi."</p>
                    </div>
                    <div>
                        <p style="font-weight: 700; color: var(--secondary); margin-bottom: 4px;">Budi Santoso</p>
                        <p style="font-size: 12px; color: var(--outline); margin: 0;">Chef & Pemilik Restoran</p>
                    </div>
                </div>

                <div class="card text-center" style="display: flex; flex-direction: column; align-items: center; justify-content: space-between; padding: 32px 24px;">
                    <div>
                        <span class="material-symbols-outlined text-primary" style="font-size: 36px; opacity: 0.3; margin-bottom: 16px;">format_quote</span>
                        <p style="font-style: italic; color: var(--on-surface-variant); margin-bottom: 24px; line-height: 1.6;">"Madu Kaliandra mereka adalah favorit keluarga kami. Teksturnya yang lembut dan rasa manisnya pas untuk dinikmati setiap pagi."</p>
                    </div>
                    <div>
                        <p style="font-weight: 700; color: var(--secondary); margin-bottom: 4px;">Wayan Sudarma</p>
                        <p style="font-size: 12px; color: var(--outline); margin: 0;">Pelanggan Setia</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

