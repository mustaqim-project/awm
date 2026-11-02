import { test as setup, expect } from '@playwright/test';
import fs from 'fs';
import path from 'path';

const authFile = 'tests/e2e/.auth/user.json';

setup('authenticate as owner/demo user', async ({ page }) => {
  // Ensure directory exists
  const authDir = path.dirname(authFile);
  if (!fs.existsSync(authDir)) {
    fs.mkdirSync(authDir, { recursive: true });
  }

  // Navigate to login page
  await page.goto('/login');

  // Verify login page loaded
  await expect(page.locator('input[name="email"]')).toBeVisible({ timeout: 15000 });

  // Fill credentials
  await page.fill('input[name="email"]', 'demo@cooca.id');
  await page.fill('input[name="password"]', 'password123');

  // Submit form
  await Promise.all([
    page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 20000 }),
    page.click('button[type="submit"]')
  ]);

  // Ensure session is established
  console.log('Login successful! Current URL:', page.url());

  // Save auth state
  await page.context().storageState({ path: authFile });
});

