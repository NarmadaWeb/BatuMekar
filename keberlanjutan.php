<?php
$page_title = 'Keberlanjutan';
require_once 'includes/header.php';
?>
<main>
    <section class="hero">
        <img class="hero-img" src="<?php echo base_url('assets/images/hutan.jpg'); ?>" alt="Sustainability"/>
        <div class="container" style="text-align: center;">
            <span style="background: var(--primary-container); color: var(--on-primary-container); padding: 4px 16px; border-radius: 20px; font-weight: 700; margin-bottom: 24px; display: inline-block;">SUSTAINABILITY</span>
            <h1 style="font-size: 56px; margin-bottom: 24px;">Keberlanjutan & Pelestarian</h1>
            <p style="font-size: 18px; max-width: 600px; margin: 0 auto;">Menjaga alam bukan hanya tanggung jawab, tapi warisan yang kami junjung tinggi dari generasi ke generasi.</p>
        </div>
    </section>

    <section style="padding: 80px 0;">
        <div class="container">
            <div class="grid grid-2" style="align-items: center; gap: 64px; margin-bottom: 80px;">
                <div>
                    <h2 class="font-display" style="font-size: 40px; margin-bottom: 24px;">Live & Let Live</h2>
                    <p style="color: var(--on-surface-variant); font-size: 18px; line-height: 1.8; margin-bottom: 16px;">Kami tidak pernah merusak sarang lebah. Madu diambil dengan teknik tradisional yang tidak melukai lebah atau merusak koloni.</p>
                    <p style="color: var(--on-surface-variant); font-size: 18px; line-height: 1.8;">Setiap sarang yang dipanen selalu menyisakan 30% madu sebagai cadangan makanan lebah. Ini adalah prinsip "Batu Mekar" — memberi tanpa mengambil semuanya.</p>
                </div>
                <img src="<?php echo base_url('assets/images/honey_bowl.jpg'); ?>" alt="Sustainable Harvest" style="border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            </div>

            <div class="grid grid-3" style="gap: 32px;">
                <div class="card" style="padding: 32px; text-align: center;">
                    <span class="material-symbols-outlined" style="font-size: 48px; color: #22c55e; margin-bottom: 16px;">forest</span>
                    <h3 style="font-size: 20px; margin-bottom: 12px;">Perlindungan Hutan</h3>
                    <p style="color: var(--on-surface-variant); line-height: 1.7;">Setiap pembelian madu menyumbang 5.000 pohon baru setiap tahun melalui program reboisasi desa.</p>
                </div>
                <div class="card" style="padding: 32px; text-align: center;">
                    <span class="material-symbols-outlined" style="font-size: 48px; color: #eab308; margin-bottom: 16px;">emoji_nature</span>
                    <h3 style="font-size: 20px; margin-bottom: 12px;">Kesejahteraan Lebah</h3>
                    <p style="color: var(--on-surface-variant); line-height: 1.7;">Lebah tidak diganggu. Kami menggunakan asap alami dari daun kering untuk menenangkan lebah, bukan bahan kimia.</p>
                </div>
                <div class="card" style="padding: 32px; text-align: center;">
                    <span class="material-symbols-outlined" style="font-size: 48px; color: #3b82f6; margin-bottom: 16px;">groups</span>
                    <h3 style="font-size: 20px; margin-bottom: 12px;">Pemberdayaan Desa</h3>
                    <p style="color: var(--on-surface-variant); line-height: 1.7;">100% madu dikelola oleh petani lokal. Keuntungan kembali ke desa untuk pendidikan dan kesehatan.</p>
                </div>
            </div>
        </div>
    </section>

    <section style="padding: 80px 0; background: var(--surface-variant);">
        <div class="container">
            <h2 class="font-display" style="font-size: 40px; margin-bottom: 48px; text-align: center;">Dampak Kami</h2>
            <div class="grid grid-4" style="gap: 24px;">
                <div style="text-align: center; padding: 32px;">
                    <div style="font-size: 48px; font-weight: 800; color: var(--primary);">5.000+</div>
                    <div style="font-size: 14px; color: var(--on-surface-variant); font-weight: 600;">Pohon Ditanam</div>
                </div>
                <div style="text-align: center; padding: 32px;">
                    <div style="font-size: 48px; font-weight: 800; color: var(--primary);">200+</div>
                    <div style="font-size: 14px; color: var(--on-surface-variant); font-weight: 600;">Keluarga Petani</div>
                </div>
                <div style="text-align: center; padding: 32px;">
                    <div style="font-size: 48px; font-weight: 800; color: var(--primary);">50+</div>
                    <div style="font-size: 14px; color: var(--on-surface-variant); font-weight: 600;">Sarang Terjaga</div>
                </div>
                <div style="text-align: center; padding: 32px;">
                    <div style="font-size: 48px; font-weight: 800; color: var(--primary);">0%</div>
                    <div style="font-size: 14px; color: var(--on-surface-variant); font-weight: 600;">Bahan Kimia</div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php require_once 'includes/footer.php'; ?>
