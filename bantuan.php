<?php
$page_title = 'Support & FAQ';
require_once 'includes/header.php';
$faqs_by_cat = get_faqs_by_category($pdo);
?>

<main class="py-xl">
    <div class="container">
        <h1 class="font-display" style="font-size: 56px; margin-bottom: 24px;">How can we help?</h1>
        <p style="font-size: 18px; color: var(--on-surface-variant); margin-bottom: 48px; max-width: 700px;">Find answers to frequently asked questions about our artisanal honey, shipping processes, and community-led sustainability efforts.</p>

        <div style="display: flex; gap: 48px;">
            <aside style="width: 250px; flex-shrink: 0;">
                <nav class="sidebar-nav" style="position: sticky; top: 120px;">
                    <?php foreach ($faqs_by_cat as $cat => $faqs): ?>
                        <a href="#<?php echo e(strtolower($cat)); ?>" class="sidebar-link"><?php echo e($cat); ?></a>
                    <?php endforeach; ?>
                </nav>
            </aside>

            <div style="flex-grow: 1; display: flex; flex-direction: column; gap: 48px;">
                <?php foreach ($faqs_by_cat as $cat => $faqs): ?>
                <section id="<?php echo e(strtolower($cat)); ?>">
                    <h3 style="font-size: 32px; border-bottom: 2px solid var(--primary-container); padding-bottom: 12px; margin-bottom: 24px; color: var(--primary);"><?php echo e($cat); ?></h3>
                    <?php foreach ($faqs as $faq): ?>
                    <div class="card" style="margin-bottom: 16px;">
                        <h4 style="margin-bottom: 12px; color: var(--on-surface);"><?php echo e($faq['question']); ?></h4>
                        <p style="color: var(--on-surface-variant);"><?php echo e($faq['answer']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </section>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
