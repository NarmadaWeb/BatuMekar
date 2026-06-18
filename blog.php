<?php
$page_title = 'Blog Edukasi';
require_once 'includes/header.php';
$posts = get_all_blog_posts($pdo);
?>
<main class="py-xl">
    <div class="container">
        <h1 class="font-display" style="font-size: 56px; margin-bottom: 24px;">Blog Madu BatuMekar</h1>
        <p style="font-size: 18px; color: var(--on-surface-variant); margin-bottom: 48px; max-width: 700px;">Menjelajahi tradisi emas Desa BatuMekar, dari ritual panen kuno hingga resep kesehatan modern.</p>

        <div class="grid grid-3">
            <?php foreach ($posts as $post): ?>
            <article class="card" style="padding: 0; overflow: hidden;">
                <img src="<?php echo e(img_url($post['gambar'])); ?>" alt="<?php echo e($post['judul']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                <div style="padding: 24px;">
                    <div style="font-size: 12px; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">
                        <?php echo e($post['kategori'] ?: 'Umum'); ?> • <?php echo e(date('d M Y', strtotime($post['dibuat_pada']))); ?>
                    </div>
                    <h3 style="margin-bottom: 12px;"><?php echo e($post['judul']); ?></h3>
                    <p style="color: var(--on-surface-variant); font-size: 14px; margin-bottom: 16px;"><?php echo e($post['kutipan']); ?></p>
                    <a href="blog-detail.php?id=<?php echo e($post['artikel_id']); ?>" style="color: var(--primary); font-weight: 700; font-size: 14px;">Baca Selengkapnya →</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>
