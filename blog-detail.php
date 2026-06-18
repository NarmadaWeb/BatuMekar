<?php
require_once 'includes/functions.php';
require_once 'config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM artikel WHERE artikel_id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: blog.php');
    exit;
}

$page_title = $post['judul'];
require_once 'includes/header.php';
?>

<main class="py-xl">
    <div class="container" style="max-width: 800px;">
        <div style="margin-bottom: 32px;">
            <a href="blog.php" style="display: inline-flex; align-items: center; gap: 6px; color: var(--primary); font-weight: 600; text-decoration: none;">
                <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span> Kembali ke Blog
            </a>
        </div>

        <article>
            <div style="font-size: 13px; font-weight: 700; color: var(--secondary); margin-bottom: 12px; text-transform: uppercase;">
                <?php echo e($post['kategori'] ?: 'Umum'); ?> • <?php echo e(date('d M Y', strtotime($post['dibuat_pada']))); ?> • <?php echo e($post['penulis']); ?>
            </div>

            <h1 style="font-size: 40px; margin-bottom: 24px; line-height: 1.2;"><?php echo e($post['judul']); ?></h1>

            <?php if ($post['gambar']): ?>
            <img src="<?php echo e(img_url($post['gambar'])); ?>" alt="<?php echo e($post['judul']); ?>" style="width: 100%; max-height: 400px; object-fit: cover; border-radius: 16px; margin-bottom: 32px;">
            <?php endif; ?>

            <div style="font-size: 18px; color: var(--secondary); font-weight: 600; margin-bottom: 24px; line-height: 1.6; font-style: italic;">
                <?php echo e($post['kutipan']); ?>
            </div>

            <div style="font-size: 16px; line-height: 1.8; color: var(--on-surface); white-space: pre-wrap;">
                <?php echo e($post['konten']); ?>
            </div>
        </article>

        <div style="margin-top: 48px; padding-top: 32px; border-top: 1px solid var(--outline-variant);">
            <a href="blog.php" class="btn btn-primary" style="text-decoration: none;">
                <span class="material-symbols-outlined" style="font-size: 18px;">article</span> Artikel Lainnya
            </a>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
