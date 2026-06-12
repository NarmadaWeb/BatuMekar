<?php
$page_title = 'Beranda';
require_once 'includes/header.php';
$featured_products = get_featured_products($pdo);
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <img class="hero-img" src="assets/images/hero.jpg" alt="Batu Meka Hero"/>
        <div class="container">
            <div class="hero-content">
                <h1 style="font-size: 56px; margin-bottom: 24px;">Madu Asli dari Hutan Desa Batu Meka</h1>
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
                <p class="text-on-surface-variant">Menjaga ekosistem lebah demi kelestarian alam Batu Meka.</p>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section style="padding: 80px 0; background: var(--surface-variant);">
        <div class="container">
            <h2 class="text-secondary" style="font-size: 40px; margin-bottom: 48px;">Koleksi Madu Terbaik</h2>
            <div class="grid grid-4">
                <?php foreach ($featured_products as $product): ?>
                <div class="card" style="padding: 0; overflow: hidden;">
                    <a href="produk.php?id=<?php echo e($product['id']); ?>">
                        <img src="<?php echo e($product['image']); ?>" alt="<?php echo e($product['name']); ?>" style="width: 100%; height: 250px; object-fit: cover;">
                    </a>
                    <div style="padding: 24px;">
                        <h3 class="text-secondary"><?php echo e($product['name']); ?></h3>
                        <p class="text-primary" style="font-weight: 700; margin: 8px 0;"><?php echo e(format_rupiah($product['price'])); ?></p>
                        <form action="keranjang.php" method="POST">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo e($product['id']); ?>">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Tambah ke Keranjang</button>
                        </form>
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
                <span class="text-primary" style="font-weight: 700; text-transform: uppercase;">Kisah Pelanggan</span>
                <h2 class="text-secondary" style="font-size: 40px; margin-top: 8px;">Apa Kata Mereka?</h2>
            </div>
            <div class="grid grid-3">
                <div class="card text-center" style="display: flex; flex-direction: column; align-items: center;">
                    <img src="assets/images/wayan.jpg" alt="Siti Aminah" style="width: 80px; height: 80px; border-radius: 50%; border: 4px solid var(--primary-container); margin-bottom: 24px;">
                    <p style="font-style: italic; color: var(--on-surface-variant); margin-bottom: 24px;">"Madu Hutan Meka bener-bener beda. Rasanya sangat kaya dan teksturnya murni banget. Senang bisa dukung petani lokal."</p>
                    <div>
                        <p style="font-weight: 700; color: var(--secondary);">Siti Aminah</p>
                        <p style="font-size: 12px; color: var(--outline);">Pecinta Hidup Sehat</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>
