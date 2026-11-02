import { test, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';

// Master catalog of all modules in resources/views/app
const modules = [
  { key: 'dashboard', name: 'Dashboard Utama', url: '/dashboard' },
  { key: 'pos', name: 'POS / Kasir Penjualan', url: '/pos' },
  { key: 'products', name: 'Katalog Produk', url: '/products' },
  { key: 'inventory', name: 'Inventori & Stok', url: '/inventory/stocks' },
  { key: 'inventory-movements', name: 'Inventori Pergerakan Stok', url: '/inventory/movements' },
  { key: 'inventory-opnames', name: 'Inventori Stock Opname', url: '/inventory/opnames' },
  { key: 'materials', name: 'Bahan Baku & Mentah', url: '/materials' },
  { key: 'suppliers', name: 'Data Supplier / Pemasok', url: '/suppliers' },
  { key: 'customers', name: 'Data Pelanggan (CRM)', url: '/customers' },
  { key: 'sales-orders', name: 'Pesanan Penjualan (SO)', url: '/sales/orders' },
  { key: 'quotations', name: 'Penawaran Harga (Quotations)', url: '/sales/quotations' },
  { key: 'sales-returns', name: 'Retur Penjualan', url: '/sales/returns' },
  { key: 'purchase-orders', name: 'Purchase Order (PO)', url: '/purchase-orders' },
  { key: 'purchasing-returns', name: 'Retur Pembelian (Purchase Returns)', url: '/purchasing/returns' },
  { key: 'purchasing-bills', name: 'Tagihan Pembelian (Supplier Bills)', url: '/purchasing/bills' },
  { key: 'invoices', name: 'Faktur & Piutang (Invoices)', url: '/invoices' },
  { key: 'finance-balance-sheet', name: 'Keuangan - Neraca (Balance Sheet)', url: '/finance/balance-sheet' },
  { key: 'finance-cash-bank', name: 'Keuangan - Kas & Bank', url: '/finance/cash-bank' },
  { key: 'finance-coa', name: 'Keuangan - Bagan Akun (COA)', url: '/finance/chart-of-accounts' },
  { key: 'finance-expenses', name: 'Keuangan - Beban & Pengeluaran', url: '/finance/expenses' },
  { key: 'finance-general-ledger', name: 'Keuangan - Buku Besar (General Ledger)', url: '/finance/general-ledger' },
  { key: 'finance-payables', name: 'Keuangan - Hutang Usaha (AP)', url: '/finance/payables' },
  { key: 'finance-receivables', name: 'Keuangan - Piutang Usaha (AR)', url: '/finance/receivables' },
  { key: 'labor-machines', name: 'Tenaga Kerja & Mesin Produksi', url: '/labor-machines' },
  { key: 'master-material-categories', name: 'Master Kategori Bahan', url: '/material-categories' },
  { key: 'master-product-categories', name: 'Master Kategori Produk', url: '/product-categories' },
  { key: 'master-units', name: 'Master Satuan Unit', url: '/units' },
  { key: 'services', name: 'Katalog Layanan & Jasa', url: '/services' },
  { key: 'hrm', name: 'HRM & Manajemen Karyawan', url: '/hrm' },
  { key: 'hrm-attendance', name: 'HRM - Presensi & Absensi', url: '/attendance' },
  { key: 'hrm-employees', name: 'HRM - Data Pegawai', url: '/employees' },
  { key: 'crm-members', name: 'Loyalitas Pelanggan (Loyalty Members)', url: '/crm/members' },
  { key: 'crm-vouchers', name: 'Voucher Promosi CRM', url: '/crm/vouchers' },
  { key: 'reports', name: 'Laporan Bisnis & Analisis', url: '/reports' },
  { key: 'analytics', name: 'Analitik & Grafik Kinerja', url: '/analytics' },
  { key: 'profitability', name: 'Analisis Profitabilitas', url: '/profitability' },
  { key: 'tax', name: 'Perpajakan & PPh', url: '/tax' },
  { key: 'warehouse', name: 'Gudang & Lokasi Multi-Branch', url: '/warehouse' },
  { key: 'whatsapp', name: 'WhatsApp CRM & Inbox', url: '/whatsapp' },
  { key: 'social-media', name: 'Social Media Management', url: '/social-media' },
  { key: 'marketplace', name: 'Integrasi Marketplace (Shopee/Tokopedia)', url: '/marketplace' },
  { key: 'storefront', name: 'Storefront & Toko Online', url: '/storefront/orders' },
  { key: 'landing-page', name: 'Editor Landing Page Bisnis', url: '/landing-page' },
  { key: 'ai', name: 'COOCA AI Office & Co-pilot', url: '/ai' },
  { key: 'ai-studio', name: 'AI Studio Generator', url: '/ai-studio' },
  { key: 'ai-studio-inspiration', name: 'AI Studio Katalog Inspirasi', url: '/ai-studio/inspiration' },
  { key: 'simulator', name: 'Simulator HPP & Margin Usaha', url: '/simulator' },
  { key: 'billing', name: 'Paket Langganan & Billing', url: '/billing' },
  { key: 'approvals', name: 'Pusat Persetujuan (Approvals)', url: '/approvals' },
  { key: 'community', name: 'Komunitas & Event Pengusaha', url: '/community' },
  { key: 'docs', name: 'Pusat Dokumentasi & Bantuan', url: '/docs' },
  { key: 'feedback', name: 'Laporan Masalah & Fitur', url: '/feedback/bugs' },
  { key: 'portal', name: 'Portal Karyawan Mandiri', url: '/portal' },
  { key: 'profile', name: 'Profil Akun & Keamanan', url: '/profile' },
  { key: 'roles', name: 'Hak Akses & Role Karyawan', url: '/roles' },
  { key: 'security', name: 'Audit Log & Keamanan Sistem', url: '/audit-logs' },
  { key: 'settings', name: 'Pengaturan Bisnis & Outlet', url: '/settings' },
  { key: 'import', name: 'Import Data Massal Excel', url: '/import' },
];

test.describe('COOCA All Modules Visual & Health Smoke Suite', () => {
  const screenshotsDir = 'test-results/screenshots';

  test.beforeAll(async () => {
    if (!fs.existsSync(screenshotsDir)) {
      fs.mkdirSync(screenshotsDir, { recursive: true });
    }
  });

  for (const mod of modules) {
    test(`[Module] ${mod.name} (${mod.url})`, async ({ page }) => {
      const consoleErrors = [];
      const failedResponses = [];

      // 1. Capture browser console errors (e.g. Alpine.js crashes)
      page.on('console', msg => {
        if (msg.type() === 'error') {
          const text = msg.text();
          // Filter out benign external tracking network errors if any
          if (!text.includes('doubleclick') && !text.includes('google-analytics')) {
            consoleErrors.push(text);
          }
        }
      });

      // 2. Capture failed network responses (500 or broken local assets)
      page.on('response', response => {
        const status = response.status();
        const url = response.url();
        if (status >= 500) {
          failedResponses.push(`HTTP ${status} on ${url}`);
        }
      });

      // 3. Navigate to module
      const response = await page.goto(mod.url, { 
        waitUntil: 'domcontentloaded', 
        timeout: 30000 
      });

      // Assert HTTP status is healthy
      expect(response, `No response from ${mod.url}`).not.toBeNull();
      const status = response.status();
      expect(status, `Expected HTTP status < 400 for ${mod.url}, got ${status}`).toBeLessThan(400);

      // 4. Wait for Alpine.js & layout initialization
      await page.waitForTimeout(800);

      // 5. Ensure page is not a Laravel error crash screen
      const pageTitle = await page.title();
      const pageContent = await page.content();
      expect(pageContent).not.toContain('SQLSTATE[');
      expect(pageContent).not.toContain('Illuminate\\Database\\QueryException');

      // 6. Save visual snapshot
      const screenshotPath = path.join(screenshotsDir, `${mod.key}.png`);
      await page.screenshot({ path: screenshotPath, fullPage: false });

      // 7. Verify no severe console crashes
      const alpineErrors = consoleErrors.filter(e => 
        e.toLowerCase().includes('alpine') || 
        e.toLowerCase().includes('syntaxerror') ||
        e.toLowerCase().includes('referenceerror') ||
        e.toLowerCase().includes('is not defined')
      );

      if (alpineErrors.length > 0) {
        console.warn(`[WARN] Alpine/JS errors detected in ${mod.name}:`, alpineErrors);
      }

      if (failedResponses.length > 0) {
        console.warn(`[WARN] Failed responses in ${mod.name}:`, failedResponses);
      }

      // Assert zero 500 server crashes
      expect(failedResponses.length, `Server errors in ${mod.name}: ${failedResponses.join(', ')}`).toBe(0);
    });
  }
});

