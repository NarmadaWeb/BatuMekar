<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

// In a real app, we'd check for 'reseller' role
require_login();

$page_title = 'Reseller Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<main style="padding: 48px 0;">
    <div class="container">
        <h1 class="text-primary" style="font-size: 32px; margin-bottom: 32px;">Selamat Datang, Mitra Artisanal</h1>

        <div class="grid grid-3">
            <div class="card" style="background: var(--primary-container); grid-column: span 2; display: flex; flex-direction: column; justify-content: center; padding: 48px;">
                <div style="font-size: 14px; font-weight: 700; text-transform: uppercase; opacity: 0.8;">Total Komisi</div>
                <div style="font-size: 48px; font-weight: 700; margin: 16px 0;">Rp 4.820.500</div>
                <button class="btn btn-primary" style="align-self: flex-start;">Request Payout</button>
            </div>

            <div class="card" style="display: flex; flex-direction: column; justify-content: center; text-align: center;">
                <div style="font-size: 14px; font-weight: 700; color: var(--secondary); text-transform: uppercase;">Benefit Grosir</div>
                <div style="font-size: 32px; font-weight: 700; color: var(--primary); margin-top: 16px;">Platinum - 25%</div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
