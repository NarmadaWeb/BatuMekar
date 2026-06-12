<!-- Footer -->
<footer class="bg-surface-container-low mt-xl">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-gutter py-xl px-lg max-w-container-max mx-auto">
        <div class="md:col-span-1">
            <span class="font-display-md text-display-md text-on-surface">Madu Batu Meka</span>
            <p class="mt-md text-secondary font-body-md">Bringing the golden warmth of the village to your home, one jar at a time.</p>
        </div>
        <div>
            <h4 class="font-label-lg text-label-lg text-on-surface mb-md uppercase tracking-widest">Village</h4>
            <ul class="space-y-sm">
                <li><a class="font-label-sm text-label-sm text-secondary hover:text-primary transition-colors underline" href="tentang-desa.php">About Us</a></li>
                <li><a class="font-label-sm text-label-sm text-secondary hover:text-primary transition-colors underline" href="#">Harvest Calendar</a></li>
                <li><a class="font-label-sm text-label-sm text-secondary hover:text-primary transition-colors underline" href="#">Sustainability</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-label-lg text-label-lg text-on-surface mb-md uppercase tracking-widest">Support</h4>
            <ul class="space-y-sm">
                <li><a class="font-label-sm text-label-sm text-primary font-semibold" href="bantuan.php">Help Center</a></li>
                <li><a class="font-label-sm text-label-sm text-secondary hover:text-primary transition-colors underline" href="#">Shipping Policy</a></li>
                <li><a class="font-label-sm text-label-sm text-secondary hover:text-primary transition-colors underline" href="#">Terms of Service</a></li>
            </ul>
        </div>
        <div>
            <h4 class="font-label-lg text-label-lg text-on-surface mb-md uppercase tracking-widest">Connect</h4>
            <ul class="space-y-sm">
                <li><a class="font-label-sm text-label-sm text-secondary hover:text-primary transition-colors underline" href="kontak.php">Contact Us</a></li>
                <li><a class="font-label-sm text-label-sm text-secondary hover:text-primary transition-colors underline" href="reseller/dashboard.php">Wholesale</a></li>
            </ul>
        </div>
    </div>
    <div class="max-w-container-max mx-auto px-lg pb-lg border-t border-outline-variant/20 pt-md text-center">
        <p class="font-label-sm text-label-sm text-secondary">© 2024 Madu Batu Meka Village. Crafted by Nature.</p>
    </div>
</footer>
<script>
    function toggleAccordion(element) {
        const item = element.parentElement;
        const isActive = item.classList.contains('active');

        // Close all items
        document.querySelectorAll('.accordion-item').forEach(el => {
            el.classList.remove('active');
        });

        // Toggle clicked item
        if (!isActive) {
            item.classList.add('active');
        }
    }
</script>
</body>
</html>
