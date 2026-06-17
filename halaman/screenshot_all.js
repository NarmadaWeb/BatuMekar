const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = 'http://127.0.0.1:8082';
const OUTPUT_DIR = __dirname; // Current folder: /var/home/indra12/skripsi/BatuMekar/halaman/

async function capture(page, urlPath, filename) {
  const fullUrl = `${BASE_URL}/${urlPath}`;
  const destPath = path.join(OUTPUT_DIR, filename);
  console.log(`Navigating to ${fullUrl} -> ${filename}`);
  try {
    await page.goto(fullUrl, { waitUntil: 'networkidle', timeout: 15000 });
    // Wait for a little bit to ensure transitions are finished
    await page.waitForTimeout(1000);
    await page.screenshot({ path: destPath, fullPage: true });
    console.log(`Saved screenshot: ${filename}`);
  } catch (err) {
    console.error(`Failed to capture ${urlPath}:`, err.message);
  }
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  // Create browser context with a clean state and viewport
  const context = await browser.newContext({
    viewport: { width: 1280, height: 800 }
  });
  const page = await context.newPage();

  console.log('--- CAPTURING PUBLIC PAGES ---');
  await capture(page, 'index.php', '01_halaman_utama.png');
  await capture(page, 'katalog.php', '02_katalog.png');
  await capture(page, 'produk.php?id=1', '03_detail_produk.png');
  await capture(page, 'keranjang.php', '04_keranjang.png');
  await capture(page, 'login.php', '05_login.png');
  await capture(page, 'register.php', '06_register.png');
  await capture(page, 'kontak.php', '07_kontak.png');
  await capture(page, 'bantuan.php', '08_bantuan.png');
  await capture(page, 'blog.php', '09_blog.png');
  await capture(page, 'kalender-panen.php', '10_kalender_panen.png');
  await capture(page, 'keberlanjutan.php', '11_keberlanjutan.png');
  await capture(page, 'kebijakan-pengiriman.php', '12_kebijakan_pengiriman.png');
  await capture(page, 'syarat-ketentuan.php', '13_syarat_ketentuan.png');
  await capture(page, 'tentang-desa.php', '14_tentang_desa.png');

  console.log('\n--- LOGGING IN AS CUSTOMER ---');
  await page.goto(`${BASE_URL}/login.php`, { waitUntil: 'networkidle' });
  await page.fill('#username', 'gede');
  await page.fill('#password', 'user123');
  await Promise.all([
    page.click('main form button[type="submit"]'),
    page.waitForURL('**/account/dashboard.php', { waitUntil: 'networkidle', timeout: 15000 })
  ]);
  console.log('Logged in as customer Gede.');

  console.log('--- CAPTURING CUSTOMER PAGES ---');
  await capture(page, 'account/dashboard.php', '15_customer_dashboard.png');
  await capture(page, 'account/orders.php', '16_customer_orders.png');
  await capture(page, 'account/profile.php', '17_customer_profile.png');
  await capture(page, 'checkout.php', '18_checkout.png');
  await capture(page, 'pembayaran.php', '19_pembayaran.png');
  await capture(page, 'upload_bukti.php', '20_upload_bukti.png');

  console.log('\n--- LOGGING OUT CUSTOMER ---');
  await page.goto(`${BASE_URL}/logout.php`, { waitUntil: 'networkidle' });
  console.log('Logged out customer.');

  console.log('\n--- LOGGING IN AS ADMIN ---');
  await page.goto(`${BASE_URL}/login.php`, { waitUntil: 'networkidle' });
  await page.fill('#username', 'admin');
  await page.fill('#password', 'admin123');
  await Promise.all([
    page.click('main form button[type="submit"]'),
    page.waitForURL('**/admin/dashboard.php', { waitUntil: 'networkidle', timeout: 15000 })
  ]);
  console.log('Logged in as admin.');

  console.log('--- CAPTURING ADMIN PAGES ---');
  await capture(page, 'admin/dashboard.php', '21_admin_dashboard.png');
  await capture(page, 'admin/produk.php', '22_admin_produk.png');
  await capture(page, 'admin/kategori.php', '23_admin_kategori.png');
  await capture(page, 'admin/artikel.php', '24_admin_artikel.png');
  await capture(page, 'admin/pesanan.php', '25_admin_pesanan.png');

  console.log('\n--- LOGGING OUT ADMIN ---');
  await page.goto(`${BASE_URL}/logout.php`, { waitUntil: 'networkidle' });
  console.log('Logged out admin.');

  await browser.close();
  console.log('\nAll screenshots captured successfully!');
})();
