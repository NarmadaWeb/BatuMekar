<?php
$page_title = 'Blog Edukasi';
require_once 'includes/header.php';
$posts = get_all_blog_posts($pdo);
?>

<main class="py-xl">
    <div class="container">
        <h1 class="font-display" style="font-size: 56px; margin-bottom: 24px;">The Gilded Jar</h1>
        <p style="font-size: 18px; color: var(--on-surface-variant); margin-bottom: 48px; max-width: 700px;">Exploring the golden traditions of Batu Meka Village, from ancient harvesting rituals to modern wellness recipes.</p>

        <div class="grid grid-3">
            <?php foreach ($posts as $post): ?>
            <article class="card" style="padding: 0; overflow: hidden;">
                <img src="<?php echo e($post['image']); ?>" alt="<?php echo e($post['title']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                <div style="padding: 24px;">
                    <div style="font-size: 12px; font-weight: 700; color: var(--secondary); margin-bottom: 8px; text-transform: uppercase;">
                        <?php echo e($post['category']); ?> • <?php echo e(date('M d, Y', strtotime($post['created_at']))); ?>
                    </div>
                    <h3 style="margin-bottom: 12px;"><?php echo e($post['title']); ?></h3>
                    <p style="color: var(--on-surface-variant); font-size: 14px; margin-bottom: 16px;"><?php echo e($post['excerpt']); ?></p>
                    <a href="#" style="color: var(--primary); font-weight: 700; font-size: 14px;">Read More →</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
