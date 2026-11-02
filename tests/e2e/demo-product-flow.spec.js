import { test, expect } from '@playwright/test';
import fs from 'node:fs';

// Fresh context so the live demo shows real login & session creation
test.use({
  storageState: { cookies: [], origins: [] },
  viewport: { width: 1440, height: 900 },
});

test.describe('COOCA Product Showcase & Merchant Onboarding Demo', () => {

  test('Demo Alur Lengkap: Login -> Setup Jualan & Produk -> CRM -> Kasir POS -> Laporan', async ({ page }) => {
    // Pastikan folder penyimpanan rekaman visual demo tersedia
    const demoDir = 'test-results/demo';
    if (!fs.existsSync(demoDir)) {
      fs.mkdirSync(demoDir, { recursive: true });
    }

    test.setTimeout(180000); // 3 menit untuk demo yang tenang dan nyaman ditonton

    console.log('--- SCENE 1: Autentikasi & Login Pemilik Bisnis ---');
    await page.goto('/login', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1000);

    // Pengetikan email & kata sandi dengan delay alami seperti demonstrasi langsung
    const emailInput = page.locator('input[name="email"]');
    await expect(emailInput).toBeVisible();
    await emailInput.pressSequentially('demo@cooca.id', { delay: 60 });
    await page.waitForTimeout(400);

    const passwordInput = page.locator('input[name="password"]');
    await passwordInput.pressSequentially('password123', { delay: 60 });
    await page.waitForTimeout(600);

    // Klik tombol Masuk pada formulir login
    const submitBtn = page.locator('form[action*="login"] button[type="submit"]').first();
    await submitBtn.click();

    // Tunggu navigasi ke Dashboard
    await page.waitForURL('**/dashboard', { timeout: 20000 });
    await page.waitForTimeout(1500);

    await page.screenshot({ path: `${demoDir}/01-login-success-dashboard.png`, fullPage: false });
    console.log('✓ Scene 1 Berhasil: Login & Masuk ke Dashboard');

    console.log('--- SCENE 2: Tinjauan Dashboard Eksekutif & Metrik Usaha ---');
    // Scroll pelan untuk mendemonstrasikan kelengkapan KPI
    await page.mouse.wheel(0, 350);
    await page.waitForTimeout(1000);
    await page.mouse.wheel(0, -350);
    await page.waitForTimeout(800);

    await page.screenshot({ path: `${demoDir}/02-executive-dashboard.png`, fullPage: false });
    console.log('✓ Scene 2 Berhasil: Eksplorasi Metrik Dashboard');

    console.log('--- SCENE 3: Setup Jualan - Manajemen Katalog Produk ---');
    await page.goto('/products', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1200);

    // Buka modal Tambah Produk Baru
    const addProductBtn = page.locator('button[\\@click*="showAddModal = true"]').first();
    if (await addProductBtn.isVisible()) {
      await addProductBtn.click();
      await page.waitForTimeout(1000);

      // Isi data form produk
      const productNameInput = page.locator('form[action*="products"] input[name="name"]').first();
      if (await productNameInput.isVisible()) {
        await productNameInput.pressSequentially('Kopi Susu Gula Aren Spesial', { delay: 40 });
        await page.waitForTimeout(500);

        const baseCostInput = page.locator('form[action*="products"] input[name="base_cost"]').first();
        if (await baseCostInput.isVisible()) {
          await baseCostInput.fill('8500');
          await page.waitForTimeout(400);
        }

        const sellingPriceInput = page.locator('form[action*="products"] input[name="selling_price"]').first();
        if (await sellingPriceInput.isVisible()) {
          await sellingPriceInput.fill('22000');
          await page.waitForTimeout(500);
        }

        const skuInput = page.locator('form[action*="products"] input[name="sku"]').first();
        if (await skuInput.isVisible()) {
          await skuInput.pressSequentially('KPS-GLA-01', { delay: 30 });
          await page.waitForTimeout(500);
        }
      }

      await page.screenshot({ path: `${demoDir}/03-modal-tambah-produk.png`, fullPage: false });

      // Tutup modal produk menggunakan tombol Batal atau Escape
      await page.keyboard.press('Escape');
      await page.waitForTimeout(800);
    }

    // Demonstrasi pencarian produk di tabel
    const searchProduct = page.locator('input[name="search"]').first();
    if (await searchProduct.isVisible()) {
      await searchProduct.pressSequentially('Kopi', { delay: 50 });
      await searchProduct.press('Enter');
      await page.waitForTimeout(1000);
    }

    await page.screenshot({ path: `${demoDir}/04-katalog-produk-filtered.png`, fullPage: false });
    console.log('✓ Scene 3 Berhasil: Setup & Pencarian Katalog Produk');

    console.log('--- SCENE 4: Setup CRM & Manajemen Pelanggan ---');
    await page.goto('/customers', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1200);

    const addCustomerBtn = page.locator('button').filter({ hasText: /Tambah Pelanggan|Add Customer|Add First Customer/i }).first();
    if (await addCustomerBtn.isVisible()) {
      await addCustomerBtn.click();
      await page.waitForTimeout(1000);

      const custName = page.locator('form[action*="customers"] input[name="name"]').first();
      if (await custName.isVisible()) {
        await custName.pressSequentially('Ibu Ratna Dewi (Pelanggan VIP)', { delay: 40 });
        await page.waitForTimeout(400);

        const custPhone = page.locator('form[action*="customers"] input[name="phone"]').first();
        if (await custPhone.isVisible()) {
          await custPhone.pressSequentially('081298765432', { delay: 30 });
          await page.waitForTimeout(400);
        }
      }

      await page.screenshot({ path: `${demoDir}/05-modal-tambah-pelanggan.png`, fullPage: false });

      await page.keyboard.press('Escape');
      await page.waitForTimeout(800);
    }
    console.log('✓ Scene 4 Berhasil: Setup Direktori Pelanggan');

    console.log('--- SCENE 5: Operasional Kasir Penjualan (POS Terminal) ---');
    await page.goto('/pos', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1500);

    // Cari produk di kasir
    const posSearch = page.locator('input[x-ref="barcodeSearchInput"], input[x-model="searchQuery"]').first();
    if (await posSearch.isVisible()) {
      await posSearch.pressSequentially('Espresso', { delay: 60 });
      await page.waitForTimeout(1000);
      await posSearch.clear();
      await page.waitForTimeout(500);
    }

    await page.screenshot({ path: `${demoDir}/06-kasir-pos-terminal.png`, fullPage: false });
    console.log('✓ Scene 5 Berhasil: Operasional Kasir POS Siap Transaksi');

    console.log('--- SCENE 6: Monitoring Pesanan Penjualan (Sales Orders) ---');
    await page.goto('/sales/orders', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1200);

    await page.screenshot({ path: `${demoDir}/07-pesanan-penjualan.png`, fullPage: false });
    console.log('✓ Scene 6 Berhasil: Monitoring Sales Orders');

    console.log('--- SCENE 7: Arus Kas & Rekening Bank Bisnis ---');
    await page.goto('/finance/cash-bank', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1200);

    await page.screenshot({ path: `${demoDir}/08-keuangan-kas-bank.png`, fullPage: false });
    console.log('✓ Scene 7 Berhasil: Verifikasi Keuangan & Kas Bank');

    console.log('--- SCENE 8: Laporan Analisis Kinerja & Profitabilitas ---');
    await page.goto('/reports', { waitUntil: 'networkidle' });
    await page.waitForTimeout(1200);

    await page.screenshot({ path: `${demoDir}/09-laporan-bisnis.png`, fullPage: false });
    console.log('✓ Scene 8 Berhasil: Laporan Bisnis Eksekutif Terbuka Lengkap');

    console.log('=== DEMO PRODUK COOCA SELESAI DENGAN SEMPURNA! ===');
  });

});
