<?php
$current_page = basename($_SERVER['PHP_SELF']);
$prefix = base_url();
function footer_link($href, $icon, $label) {
    global $current_page, $prefix;
    $active = basename($href) === $current_page;
    $style = $active ? 'color: var(--primary-container); font-weight: 600;' : '';
    return '<a href="' . $prefix . $href . '" style="' . $style . '"><span class="material-symbols-outlined">' . $icon . '</span> ' . $label . '</a>';
}
?>
<!-- Footer -->
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-col footer-brand">
            <a href="<?php echo $prefix; ?>index.php" class="footer-logo">BatuMekar</a>
            <p style="color: #b0a79a; line-height: 1.6; margin: 0;">Bringing the golden warmth of the village to your home, one jar of pure artisanal honey at a time.</p>
            <div class="footer-socials">
                <a href="#" class="social-icon" aria-label="Facebook">
                    <span class="material-symbols-outlined">share</span>
                </a>
                <a href="#" class="social-icon" aria-label="Instagram">
                    <span class="material-symbols-outlined">photo_camera</span>
                </a>
                <a href="#" class="social-icon" aria-label="YouTube">
                    <span class="material-symbols-outlined">smart_display</span>
                </a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Village</h4>
            <ul class="footer-links">
                <li><?php echo footer_link('tentang-desa.php', 'info', 'About Our Story'); ?></li>
                <li><?php echo footer_link('kalender-panen.php', 'calendar_today', 'Kalender Panen'); ?></li>
                <li><?php echo footer_link('keberlanjutan.php', 'eco', 'Keberlanjutan'); ?></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Support</h4>
            <ul class="footer-links">
                <li><?php echo footer_link('bantuan.php', 'help', 'Help Center'); ?></li>
                <li><?php echo footer_link('kebijakan-pengiriman.php', 'local_shipping', 'Kebijakan Pengiriman'); ?></li>
                <li><?php echo footer_link('syarat-ketentuan.php', 'gavel', 'Syarat & Ketentuan'); ?></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Connect</h4>
            <ul class="footer-links">
                <li><?php echo footer_link('kontak.php', 'mail', 'Contact Us'); ?></li>
                <li><?php echo footer_link('reseller/dashboard.php', 'storefront', 'Wholesale Portal'); ?></li>
            </ul>
        </div>
    </div>
    <div class="container footer-bottom">
        <p style="margin: 0;">© 2026 BatuMekar Village. Crafted by Nature with Passion.</p>
        <p style="margin: 0; font-size: 12px; opacity: 0.6;">All rights reserved. Purity Guaranteed.</p>
    </div>
</footer>
<script>
    function togglePassword(id, btn) {
        const input = document.getElementById(id);
        const icon = btn.querySelector('.material-symbols-outlined');
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility';
        }
    }
    function toggleAccordion(element) {
        const item = element.parentElement;
        const isActive = item.classList.contains('active');

        document.querySelectorAll('.accordion-item').forEach(el => {
            el.classList.remove('active');
        });

        if (!isActive) {
            item.classList.add('active');
        }
    }
</script>
</body>
</html>
