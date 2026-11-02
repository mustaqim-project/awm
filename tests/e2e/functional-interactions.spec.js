import { test, expect } from '@playwright/test';

test.describe('COOCA Critical User Interactions Suite', () => {

  test('POS Terminal interactive search and layout', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    await page.goto('/pos', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);

    // Search input typing test
    const searchInput = page.locator('input[x-ref="barcodeSearchInput"], input[x-model="searchQuery"]').first();
    if (await searchInput.isVisible()) {
      await searchInput.fill('Kopi Test');
      await expect(searchInput).toHaveValue('Kopi Test');
      await searchInput.clear();
    }

    // Take interaction screenshot
    await page.screenshot({ path: 'test-results/screenshots/interaction-pos-terminal.png', fullPage: false });

    // Assert no fatal JS syntax/runtime crashes
    const fatalErrors = consoleErrors.filter(err => 
      !err.includes('favicon.ico') && 
      !err.includes('doubleclick') && 
      !err.includes('google-analytics')
    );
    expect(fatalErrors.length, `Fatal errors on POS: ${fatalErrors.join(' | ')}`).toBe(0);
  });

  test('Customer Management: modal open, form input typing, and close', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    await page.goto('/customers', { waitUntil: 'networkidle' });
    await page.waitForTimeout(800);

    // Click the Add Customer button
    const addBtn = page.locator('button').filter({ hasText: /Tambah Pelanggan|Add Customer|Add First Customer/i }).first();
    if (await addBtn.isVisible()) {
      await addBtn.click({ force: true });
      await page.waitForTimeout(600);

      // Verify the customer form inside the teleported modal is visible
      const nameInput = page.locator('form[action*="customers"] input[name="name"]').first();
      await expect(nameInput).toBeVisible({ timeout: 5000 });

      // Type customer name
      await nameInput.fill('PT Mitra Maju Bersama');
      await expect(nameInput).toHaveValue('PT Mitra Maju Bersama');

      // Close modal using Escape
      await page.keyboard.press('Escape');
      await page.waitForTimeout(500);
      await expect(nameInput).not.toBeVisible();
    }

    await page.screenshot({ path: 'test-results/screenshots/interaction-customers.png', fullPage: false });

    const fatalErrors = consoleErrors.filter(err => 
      !err.includes('favicon.ico') && 
      !err.includes('doubleclick') && 
      !err.includes('google-analytics')
    );
    expect(fatalErrors.length, `Fatal errors on Customers: ${fatalErrors.join(' | ')}`).toBe(0);
  });

  test('Products Catalog: search filtering interaction', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    await page.goto('/products', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);

    const searchInput = page.locator('input[name="search"]').first();
    if (await searchInput.isVisible()) {
      await searchInput.fill('Espresso');
      await searchInput.press('Enter');
      await page.waitForLoadState('domcontentloaded');
      await page.waitForTimeout(500);
    }

    await page.screenshot({ path: 'test-results/screenshots/interaction-products.png', fullPage: false });

    const fatalErrors = consoleErrors.filter(err => 
      !err.includes('favicon.ico') && 
      !err.includes('doubleclick') && 
      !err.includes('google-analytics')
    );
    expect(fatalErrors.length, `Fatal errors on Products: ${fatalErrors.join(' | ')}`).toBe(0);
  });

  test('Finance Kas & Bank: view balances and data tables', async ({ page }) => {
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    await page.goto('/finance/cash-bank', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);

    // Verify main container rendered
    const bodyText = await page.textContent('body');
    expect(bodyText).toContain('Kas');

    await page.screenshot({ path: 'test-results/screenshots/interaction-finance-cash-bank.png', fullPage: false });

    const fatalErrors = consoleErrors.filter(err => 
      !err.includes('favicon.ico') && 
      !err.includes('doubleclick') && 
      !err.includes('google-analytics')
    );
    expect(fatalErrors.length, `Fatal errors on Cash & Bank: ${fatalErrors.join(' | ')}`).toBe(0);
  });

});
