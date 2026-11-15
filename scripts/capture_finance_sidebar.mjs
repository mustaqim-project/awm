import { chromium } from 'playwright';

async function main() {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded' });
    await page.fill('input[type="email"]', 'reviewer@cooca.id');
    await page.fill('input[type="password"]', 'MetaReview2026!');
    await page.click('button[type="submit"]');

    await page.waitForNavigation({ waitUntil: 'networkidle', timeout: 10000 }).catch(() => {});

    // Scroll sidebar nav down to Finance & Accounting
    await page.evaluate(() => {
        const nav = document.querySelector('.sidebar-nav');
        if (nav) nav.scrollTop = 550;
    });
    await page.waitForTimeout(500);

    const sidebar = await page.$('.app-sidebar, aside');
    if (sidebar) {
        await sidebar.screenshot({ path: 'scripts/sidebar_finance_after.png' });
        console.log('Saved sidebar_finance_after.png');
    }

    await browser.close();
}

main().catch(console.error);
