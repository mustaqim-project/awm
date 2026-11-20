import { chromium } from '@playwright/test';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const ROOT_DIR = path.resolve(__dirname, '..');
const OUTPUT_DIR = path.join(ROOT_DIR, 'documentation', 'ui-captures', 'web');
const BASE_URL = process.env.APP_URL || 'http://cooca_core.test';
const SKIP_EXISTING = process.env.SKIP_EXISTING === 'true';

// Ensure output directory exists
if (!fs.existsSync(OUTPUT_DIR)) {
  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

console.log('====================================================');
console.log('   COOCA WEB BACKOFFICE UI SCREENSHOT AUTOMATION    ');
console.log('   Target Resolution: 1440 x 900 Desktop Viewport    ');
console.log(`   Base URL: ${BASE_URL}                            `);
console.log(`   Output Folder: ${OUTPUT_DIR}                     `);
console.log(`   Skip Existing: ${SKIP_EXISTING}                  `);
console.log('====================================================\n');

function areAllFilesPresent(files) {
  if (!SKIP_EXISTING) return false;
  return files.every(f => fs.existsSync(path.join(OUTPUT_DIR, f)));
}

async function loginUser(page, email, password = 'password123') {
  console.log(`[AUTH] Authenticating as ${email}...`);
  await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await page.waitForFunction(() => !window.location.pathname.includes('/login'), { timeout: 15000 });
  await page.waitForTimeout(1000);
  console.log(`[AUTH] Successfully authenticated. Final URL: ${page.url()}\n`);
}

async function captureScreen(page, filename, urlPath, options = {}) {
  const targetPath = path.join(OUTPUT_DIR, filename);
  if (SKIP_EXISTING && fs.existsSync(targetPath)) {
    console.log(`[SKIP] ${filename} already exists (${fs.statSync(targetPath).size} bytes).`);
    return;
  }

  const targetUrl = urlPath.startsWith('http') ? urlPath : `${BASE_URL}${urlPath}`;
  console.log(`[CAPTURE START] ${filename} -> ${targetUrl}`);
  
  await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.waitForTimeout(options.waitMs || 1500);

  if (typeof options.action === 'function') {
    console.log(`[TRIGGER] Executing UI action for ${filename}...`);
    await options.action(page);
    await page.waitForTimeout(options.postActionWaitMs || 1000);
  }

  // Ensure scroll is at top
  await page.evaluate(() => window.scrollTo(0, 0));

  await page.screenshot({
    path: targetPath,
    fullPage: false // Strictly 1440x900 viewport capture
  });

  const stat = fs.statSync(targetPath);
  console.log(`[CAPTURE COMPLETE] ${filename} [${stat.size} bytes]\n`);
}

async function main() {
  const browser = await chromium.launch({
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
  });

  try {
    // ----------------------------------------------------
    // Context 1: Guest (Unauthenticated Context)
    // ----------------------------------------------------
    const stage1Files = ['01_auth_login.png', '02_auth_register.png', '03_auth_forgot_password.png'];
    if (areAllFilesPresent(stage1Files)) {
      console.log('>>> STAGE 1: [SKIPPED] All authentication screens already exist.');
    } else {
      console.log('>>> STAGE 1: Capturing Authentication Screens (Guest Context)');
      const guestContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
      const guestPage = await guestContext.newPage();

      await captureScreen(guestPage, '01_auth_login.png', '/login', { waitMs: 1200 });
      await captureScreen(guestPage, '02_auth_register.png', '/register', { waitMs: 1200 });
      await captureScreen(guestPage, '03_auth_forgot_password.png', '/forgot-password', { waitMs: 1200 });

      await guestContext.close();
    }

    // ----------------------------------------------------
    // Context 2: Retail & Distribution (COOCA Mart)
    // ----------------------------------------------------
    const stage2Files = [
      '04_dashboard_overview.png', '05_dashboard_owner_pulse.png', '07_pos_terminal.png',
      '08_pos_cart_active.png', '09_pos_payment_modal.png', '10_sales_orders_list.png',
      '11_purchasing_orders_list.png', '12_purchase_order_detail.png', '13_goods_receipt.png',
      '14_inventory_products_list.png', '15_inventory_stock_levels.png', '16_stock_card_movement.png',
      '17_customers_directory.png', '18_suppliers_directory.png', '19_accounting_general_ledger.png',
      '20_accounting_journal_entries.png', '21_accounting_profit_loss.png', '22_accounting_balance_sheet.png',
      '23_reports_sales_analytics.png', '24_reports_inventory_valuation.png', '25_retail_wholesale_catalog.png',
      '26_retail_pos_fmcg.png'
    ];
    if (areAllFilesPresent(stage2Files)) {
      console.log('>>> STAGE 2: [SKIPPED] All Retail & Core ERP screens already exist.');
    } else {
      console.log('>>> STAGE 2: Capturing Retail & Core ERP Screens (COOCA Mart)');
      const retailContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
      const retailPage = await retailContext.newPage();
      await loginUser(retailPage, 'owner@coocamart.id', 'password123');

      await captureScreen(retailPage, '04_dashboard_overview.png', '/dashboard', { waitMs: 2000 });
      await captureScreen(retailPage, '05_dashboard_owner_pulse.png', '/analytics', { waitMs: 2500 });
      await captureScreen(retailPage, '07_pos_terminal.png', '/pos', { waitMs: 2000 });

      await captureScreen(retailPage, '08_pos_cart_active.png', '/pos', {
        waitMs: 2000,
        action: async (page) => {
          await page.evaluate(() => {
            const el = document.querySelector('[x-data]');
            if (el && el._x_dataStack && el._x_dataStack[0]) {
              const data = el._x_dataStack[0];
              if (data.allProducts && data.allProducts.length > 0) {
                data.addToCart(data.allProducts[0]);
                if (data.allProducts.length > 1) data.addToCart(data.allProducts[1]);
                if (data.allProducts.length > 2) data.addToCart(data.allProducts[2]);
              }
            }
          });
        },
        postActionWaitMs: 1200
      });

      await captureScreen(retailPage, '09_pos_payment_modal.png', '/pos', {
        waitMs: 2000,
        action: async (page) => {
          await page.evaluate(() => {
            const el = document.querySelector('[x-data]');
            if (el && el._x_dataStack && el._x_dataStack[0]) {
              const data = el._x_dataStack[0];
              if (data.cart.length === 0 && data.allProducts && data.allProducts.length > 0) {
                data.addToCart(data.allProducts[0]);
                data.addToCart(data.allProducts[1]);
              }
              if (typeof data.openPaymentModal === 'function') {
                data.openPaymentModal();
              }
            }
          });
        },
        postActionWaitMs: 1200
      });

      await captureScreen(retailPage, '10_sales_orders_list.png', '/pos/orders', { waitMs: 1800 });
      await captureScreen(retailPage, '11_purchasing_orders_list.png', '/purchase-orders', { waitMs: 1800 });
      await captureScreen(retailPage, '12_purchase_order_detail.png', '/purchase-orders/c0901370-b3b4-4ffa-bf6a-fcb0df5d9a73', { waitMs: 1800 });
      await captureScreen(retailPage, '13_goods_receipt.png', '/purchasing/receipts/c0901370-b3b4-4ffa-bf6a-fcb0df5d9a73/create', { waitMs: 1800 });
      await captureScreen(retailPage, '14_inventory_products_list.png', '/products', { waitMs: 2000 });
      await captureScreen(retailPage, '15_inventory_stock_levels.png', '/inventory/stocks', { waitMs: 1800 });
      await captureScreen(retailPage, '16_stock_card_movement.png', '/inventory/movements', { waitMs: 1800 });
      await captureScreen(retailPage, '17_customers_directory.png', '/customers', { waitMs: 1800 });
      await captureScreen(retailPage, '18_suppliers_directory.png', '/suppliers', { waitMs: 1800 });
      await captureScreen(retailPage, '19_accounting_general_ledger.png', '/finance/general-ledger', { waitMs: 2000 });
      await captureScreen(retailPage, '20_accounting_journal_entries.png', '/finance/journals', { waitMs: 2000 });
      await captureScreen(retailPage, '21_accounting_profit_loss.png', '/reports?tab=income_statement', { waitMs: 2000 });
      await captureScreen(retailPage, '22_accounting_balance_sheet.png', '/finance/balance-sheet', { waitMs: 2000 });
      await captureScreen(retailPage, '23_reports_sales_analytics.png', '/pos/reports', { waitMs: 2000 });
      await captureScreen(retailPage, '24_reports_inventory_valuation.png', '/reports?tab=stock_valuation', { waitMs: 2000 });

      await captureScreen(retailPage, '25_retail_wholesale_catalog.png', '/products', {
        waitMs: 2000,
        action: async (page) => {
          await page.evaluate(() => {
            const el = document.querySelector('[x-data]');
            if (el && el._x_dataStack && el._x_dataStack[0]) {
              const data = el._x_dataStack[0];
              const prod = (data.products || []).find(p => p.wholesale_price > 0) || (data.products && data.products[0]);
              if (prod && typeof data.openBranchPricesModal === 'function') {
                data.openBranchPricesModal(prod.id);
              }
            }
          });
        },
        postActionWaitMs: 1200
      });

      await captureScreen(retailPage, '26_retail_pos_fmcg.png', '/pos', {
        waitMs: 2000,
        action: async (page) => {
          await page.evaluate(() => {
            const catButtons = Array.from(document.querySelectorAll('button'));
            const sembakoBtn = catButtons.find(b => b.textContent && b.textContent.includes('Sembako'));
            if (sembakoBtn) {
              sembakoBtn.click();
            } else {
              const el = document.querySelector('[x-data]');
              if (el && el._x_dataStack && el._x_dataStack[0]) {
                const data = el._x_dataStack[0];
                if (data.categories && data.categories.length > 0) {
                  data.selectedCategory = data.categories[0].id;
                  data.filterProducts();
                }
              }
            }
          });
        },
        postActionWaitMs: 1200
      });

      await retailContext.close();
    }

    // ----------------------------------------------------
    // Context 3: Multi-Business Switcher (Demo Context)
    // ----------------------------------------------------
    const stage3Files = ['06_business_switcher.png'];
    if (areAllFilesPresent(stage3Files)) {
      console.log('>>> STAGE 3: [SKIPPED] Business switcher screen already exists.');
    } else {
      console.log('>>> STAGE 3: Capturing Multi-Tenant Business Switcher (Demo User)');
      const demoContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
      const demoPage = await demoContext.newPage();
      await loginUser(demoPage, 'demo@cooca.id', 'password123');

      await captureScreen(demoPage, '06_business_switcher.png', '/select-business', { waitMs: 1500 });

      await demoContext.close();
    }

    // ----------------------------------------------------
    // Context 4: Food & Beverage (COOCA Bistro & Coffee)
    // ----------------------------------------------------
    const stage4Files = ['27_fnb_table_layout.png', '28_fnb_menu_modifiers.png', '29_fnb_kitchen_display.png'];
    if (areAllFilesPresent(stage4Files)) {
      console.log('>>> STAGE 4: [SKIPPED] All F&B screens already exist.');
    } else {
      console.log('>>> STAGE 4: Capturing F&B Outlets & Operations (COOCA Bistro)');
      const fnbContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
      const fnbPage = await fnbContext.newPage();
      await loginUser(fnbPage, 'owner@coocabistro.id', 'password123');

      await captureScreen(fnbPage, '27_fnb_table_layout.png', '/pos/tables', { waitMs: 2000 });
      await captureScreen(fnbPage, '28_fnb_menu_modifiers.png', '/pos/modifiers', { waitMs: 2000 });
      await captureScreen(fnbPage, '29_fnb_kitchen_display.png', '/pos/kitchen', { waitMs: 2000 });

      await fnbContext.close();
    }

    // ----------------------------------------------------
    // Context 5: Manufacturing (COOCA Manufacturing)
    // ----------------------------------------------------
    const stage5Files = ['30_mfg_bom_formula.png', '31_mfg_production_order.png', '32_mfg_work_center.png'];
    if (areAllFilesPresent(stage5Files)) {
      console.log('>>> STAGE 5: [SKIPPED] All Manufacturing screens already exist.');
    } else {
      console.log('>>> STAGE 5: Capturing Manufacturing & Production (COOCA Manufacturing)');
      const mfgContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
      const mfgPage = await mfgContext.newPage();
      await loginUser(mfgPage, 'owner@coocamfg.id', 'password123');

      await captureScreen(mfgPage, '30_mfg_bom_formula.png', '/products/1b288ad7-9684-4207-baf3-1cb4a4b65e67/bom', { waitMs: 2000 });
      await captureScreen(mfgPage, '31_mfg_production_order.png', '/production/e5fd2249-4ad0-403e-9af0-530b33d80dff', { waitMs: 2000 });
      await captureScreen(mfgPage, '32_mfg_work_center.png', '/labor-machines', { waitMs: 2000 });

      await mfgContext.close();
    }

    // ----------------------------------------------------
    // Context 6: Automotive Workshop (COOCA AutoCare)
    // ----------------------------------------------------
    const stage6Files = ['33_workshop_vehicle_queue.png', '34_workshop_spk_order.png', '35_workshop_service_history.png'];
    if (areAllFilesPresent(stage6Files)) {
      console.log('>>> STAGE 6: [SKIPPED] All Automotive Workshop screens already exist.');
    } else {
      console.log('>>> STAGE 6: Capturing Automotive Workshop Operations (COOCA AutoCare)');
      const workshopContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
      const workshopPage = await workshopContext.newPage();
      await loginUser(workshopPage, 'owner@coocaautocare.id', 'password123');

      await captureScreen(workshopPage, '33_workshop_vehicle_queue.png', '/storefront/reservations', { waitMs: 2000 });

      await captureScreen(workshopPage, '34_workshop_spk_order.png', '/pos/orders', {
        waitMs: 2000,
        action: async (page) => {
          await page.evaluate(() => {
            const btn = Array.from(document.querySelectorAll('button')).find(b => {
              const clk = b.getAttribute('@click') || '';
              return clk.includes('viewDetail');
            });
            if (btn) {
              btn.click();
              return;
            }
            const el = Array.from(document.querySelectorAll('[x-data]')).find(e => {
              return e._x_dataStack && typeof e._x_dataStack[0]?.viewDetail === 'function';
            });
            if (el && el._x_dataStack && el._x_dataStack[0]) {
              const data = el._x_dataStack[0];
              if (window.COOCA_POS_ORDERS && window.COOCA_POS_ORDERS.length > 0) {
                data.viewDetail(window.COOCA_POS_ORDERS[0].id);
              }
            }
          });
        },
        postActionWaitMs: 1500
      });

      await captureScreen(workshopPage, '35_workshop_service_history.png', '/customers', { waitMs: 2000 });

      await workshopContext.close();
    }

    console.log('====================================================');
    console.log('   ALL 35 WEB SCREENSHOTS CAPTURED SUCCESSFULLY!    ');
    console.log('====================================================\n');

  } catch (err) {
    console.error('ERROR during screenshot automation execution:', err);
    process.exit(1);
  } finally {
    await browser.close();
  }
}

main();
