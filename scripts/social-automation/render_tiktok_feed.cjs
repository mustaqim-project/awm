const { chromium } = require('c:/laragon/www/cooca_core/node_modules/@playwright/test');
const fs = require('fs');
const path = require('path');

async function main() {
  const htmlPath = path.resolve('scripts/social-automation/tiktok_feed_template.html');
  const targetDirStorage = path.resolve('storage/app/public/admin/social-media-assets/platform');
  const targetDirPublic = path.resolve('public/storage/admin/social-media-assets/platform');

  if (!fs.existsSync(targetDirStorage)) fs.mkdirSync(targetDirStorage, { recursive: true });
  if (!fs.existsSync(targetDirPublic)) fs.mkdirSync(targetDirPublic, { recursive: true });

  const outFileName = 'tiktok-feed-fraud_prevention-1080x1080.png';
  const outPathStorage = path.join(targetDirStorage, outFileName);
  const outPathPublic = path.join(targetDirPublic, outFileName);

  console.log('[Playwright] Launching Chrome for TikTok 1:1 Feed rendering...');
  const browser = await chromium.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true
  });

  const page = await browser.newPage({
    viewport: { width: 1080, height: 1080 },
    deviceScaleFactor: 1
  });

  const fileUrl = 'file:///' + htmlPath.replace(/\\/g, '/');
  console.log(`[Playwright] Navigating to: ${fileUrl}`);
  await page.goto(fileUrl, { waitUntil: 'networkidle' });
  await page.waitForTimeout(1000);

  const canvas = await page.$('#tiktok-feed');
  if (!canvas) {
    throw new Error('Elemen #tiktok-feed tidak ditemukan di HTML!');
  }

  console.log('[Playwright] Taking screenshot of #tiktok-feed canvas...');
  await canvas.screenshot({ path: outPathStorage, type: 'png' });
  await browser.close();

  // Copy to public storage
  fs.copyFileSync(outPathStorage, outPathPublic);

  const stats = fs.statSync(outPathStorage);
  console.log(`[SUCCESS] Berhasil merender TikTok Feed (1080x1080 px):`);
  console.log(` -> Storage: ${outPathStorage} (${stats.size.toLocaleString()} bytes)`);
  console.log(` -> Public : ${outPathPublic}`);
}

main().catch(err => {
  console.error('[FAILED] Error saat merender feed:', err);
  process.exit(1);
});
