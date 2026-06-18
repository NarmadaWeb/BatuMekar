<?php
$page_title = 'Tentang Desa';
require_once 'includes/header.php';
?>
<main>
    <section class="hero">
        <img class="hero-img" src="<?php echo base_url('assets/images/village_hero.jpg'); ?>" alt="Desa BatuMekar"/>
        <div class="container" style="text-align: center;">
            <span style="background: var(--primary-container); color: var(--on-primary-container); padding: 4px 16px; border-radius: 20px; font-weight: 700; margin-bottom: 24px; display: inline-block;">WARISAN DESA</span>
            <h1 style="font-size: 56px; margin-bottom: 24px;">Jantung Desa BatuMekar</h1>
            <p style="font-size: 18px; max-width: 600px; margin: 0 auto;">Temukan tradisi leluhur dan hutan alami tempat lahirnya cairan emas murni kami, tetes demi tetes.</p>
        </div>
    </section>

    <section style="padding: 80px 0;">
        <div class="container grid grid-2" style="align-items: center;">
            <div>
                <h2 style="font-size: 40px; margin-bottom: 24px;">Dibentuk Alam, Dijaga Masyarakat.</h2>
                <div style="color: var(--on-surface-variant); font-size: 18px; line-height: 1.8;">
                    <p style="margin-bottom: 24px;">Selama berabad-abad, masyarakat BatuMekar hidup berdampingan dengan Apis Dorsata—lebah raksasa dari hutan hujan Indonesia.</p>
                    <p>Setiap panen diawali dengan 'Ritual Batu', upacara syukur yang memastikan lebah tetap aman dan hutan terus tumbuh subur.</p>
                </div>
            </div>
            <img src="<?php echo base_url('assets/images/honey_bowl.jpg'); ?>" alt="Madu" style="border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
        </div>
    </section>
</main>
<?php require_once 'includes/footer.php'; ?>
