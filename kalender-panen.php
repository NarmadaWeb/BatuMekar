<?php
$page_title = 'Kalender Panen';
require_once 'includes/header.php';
?>
<main>
    <section class="hero">
        <img class="hero-img" src="<?php echo base_url('assets/images/hutan.jpg'); ?>" alt="Harvest Calendar"/>
        <div class="container" style="text-align: center;">
            <span style="background: var(--primary-container); color: var(--on-primary-container); padding: 4px 16px; border-radius: 20px; font-weight: 700; margin-bottom: 24px; display: inline-block;">HARVEST CALENDAR</span>
            <h1 style="font-size: 56px; margin-bottom: 24px;">Kalender Panen Madu</h1>
            <p style="font-size: 18px; max-width: 600px; margin: 0 auto;">Setiap musim membawa cita rasa unik dari nektar bunga yang berbeda. Simak jadwal panen madu BatuMekar sepanjang tahun.</p>
        </div>
    </section>

    <section style="padding: 80px 0;">
        <div class="container">
            <div style="text-align: center; margin-bottom: 64px;">
                <h2 class="font-display" style="font-size: 40px; margin-bottom: 16px;">Musim Panen 2026</h2>
                <p style="color: var(--on-surface-variant); font-size: 18px; max-width: 600px; margin: 0 auto;">Madu murni dipanen sesuai musim berbunga untuk menjaga kualitas terbaik.</p>
            </div>

            <div class="grid grid-2" style="gap: 32px;">
                <div class="card" style="padding: 32px; border-left: 4px solid #22c55e;">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                        <span class="material-symbols-outlined" style="font-size: 36px; color: #22c55e;">spa</span>
                        <div>
                            <h3 style="font-size: 24px; font-weight: 700;">Musim Semi</h3>
                            <span style="font-size: 14px; color: var(--on-surface-variant);">September - November</span>
                        </div>
                    </div>
                    <p style="color: var(--on-surface-variant); line-height: 1.7;">Nektar dari bunga-bunga liar yang mekar di kaki gunung. Menghasilkan madu Multiflora dengan rasa ringan dan aroma bunga yang semerbak.</p>
                    <div style="margin-top: 16px; display: flex; gap: 8px; flex-wrap: wrap;">
                        <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Madu Multiflora</span>
                        <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Madu Rambutan</span>
                    </div>
                </div>

                <div class="card" style="padding: 32px; border-left: 4px solid #eab308;">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                        <span class="material-symbols-outlined" style="font-size: 36px; color: #eab308;">wb_sunny</span>
                        <div>
                            <h3 style="font-size: 24px; font-weight: 700;">Musim Kemarau</h3>
                            <span style="font-size: 14px; color: var(--on-surface-variant);">Desember - Februari</span>
                        </div>
                    </div>
                    <p style="color: var(--on-surface-variant); line-height: 1.7;">Saat matahari bersinar terik, nektar mengental sempurna. Kelengkeng dan Kaliandra memberikan madu dengan rasa manis legit dan tekstur kental.</p>
                    <div style="margin-top: 16px; display: flex; gap: 8px; flex-wrap: wrap;">
                        <span style="background: #fef9c3; color: #854d0e; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Madu Kelengkeng</span>
                        <span style="background: #fef9c3; color: #854d0e; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Madu Kaliandra</span>
                    </div>
                </div>

                <div class="card" style="padding: 32px; border-left: 4px solid #f97316;">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                        <span class="material-symbols-outlined" style="font-size: 36px; color: #f97316;">ac_unit</span>
                        <div>
                            <h3 style="font-size: 24px; font-weight: 700;">Musim Gugur</h3>
                            <span style="font-size: 14px; color: var(--on-surface-variant);">Maret - Mei</span>
                        </div>
                    </div>
                    <p style="color: var(--on-surface-variant); line-height: 1.7;">Dedaunan berguguran, lebah hutan mulai bersiap. Ini adalah masa panen Madu Hutan Liar yang langka dengan rasa kompleks dan kaya antioksidan.</p>
                    <div style="margin-top: 16px; display: flex; gap: 8px; flex-wrap: wrap;">
                        <span style="background: #fed7aa; color: #9a3412; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Madu Hutan Liar</span>
                    </div>
                </div>

                <div class="card" style="padding: 32px; border-left: 4px solid #3b82f6;">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                        <span class="material-symbols-outlined" style="font-size: 36px; color: #3b82f6;">water_drop</span>
                        <div>
                            <h3 style="font-size: 24px; font-weight: 700;">Musim Hujan</h3>
                            <span style="font-size: 14px; color: var(--on-surface-variant);">Juni - Agustus</span>
                        </div>
                    </div>
                    <p style="color: var(--on-surface-variant); line-height: 1.7;">Hujan membawa kesuburan. Bunga-bunga liar bermekaran kembali. Panen kecil-kecilan tetap berlangsung untuk menjaga pasokan sepanjang tahun.</p>
                    <div style="margin-top: 16px; display: flex; gap: 8px; flex-wrap: wrap;">
                        <span style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Madu Multiflora</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section style="padding: 80px 0; background: var(--surface-variant);">
        <div class="container" style="text-align: center;">
            <h2 class="font-display" style="font-size: 40px; margin-bottom: 24px;">Pemesanan Panen Mendatang</h2>
            <p style="color: var(--on-surface-variant); font-size: 18px; max-width: 600px; margin: 0 auto 32px;">Ingin mendapatkan madu dari panen terbaru? Pesan sekarang dan dapatkan prioritas pengiriman.</p>
            <a href="katalog.php" class="btn btn-primary" style="padding: 16px 40px; font-size: 18px;">Pesan Sekarang</a>
        </div>
    </section>
</main>
<?php require_once 'includes/footer.php'; ?>
