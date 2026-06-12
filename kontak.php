<?php
$page_title = 'Contact Us';
require_once 'includes/header.php';
?>

<main>
    <section class="hero" style="height: 400px;">
        <img class="hero-img" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDjzpWfMY4hy-jh32Je-SCjhwmgVdtdZw4ZOZfy3bV7LaYH42F0zVPE4-rli3zTO88UdAJaNmnnzvl-Z2TeO_8perbcSlbulJVGkwq1HQUc_Fn1tqA46piU6QCl_fX8yZO4ofCGzibUvp9cAP4vFZuvo8e4w6AAv1llkbOAOrL-VHO4MmUGw7tp5rF-Ar9rE5khuGKZWKqVeUR8ooSAzrWrSKKz2ezC1CFosyoWKzaqO2Pvfx-8V-W6KfPDfWbJ2z7k1w1L4QVTu5Y" alt="Contact Hero"/>
        <div class="container" style="text-align: center;">
            <h1 style="font-size: 48px; margin-bottom: 16px;">Connect With Us</h1>
            <p style="max-width: 700px; margin: 0 auto; font-size: 18px;">From the golden hives of Batu Meka to your home. Reach out for inquiries about our harvest, sustainability practices, or wholesale opportunities.</p>
        </div>
    </section>

    <section style="padding: 80px 0;">
        <div class="container grid grid-2">
            <div class="card">
                <h2 style="margin-bottom: 24px;">Send a Message</h2>
                <form style="display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px;">Full Name</label>
                        <input type="text" style="width: 100%; padding: 12px; border: 1px solid var(--outline-variant); border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px;">Email</label>
                        <input type="email" style="width: 100%; padding: 12px; border: 1px solid var(--outline-variant); border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px;">Message</label>
                        <textarea rows="5" style="width: 100%; padding: 12px; border: 1px solid var(--outline-variant); border-radius: 8px;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <div class="card" style="background: var(--primary-container); color: var(--on-primary-container);">
                    <h3 style="margin-bottom: 16px;">Direct Reach</h3>
                    <p style="margin-bottom: 8px; font-weight: 600;">Email Us</p>
                    <p style="margin-bottom: 16px;">harvest@batumeka.com</p>
                    <p style="margin-bottom: 8px; font-weight: 600;">Visit</p>
                    <p>Batu Meka Village, Klungkung, Bali</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>
