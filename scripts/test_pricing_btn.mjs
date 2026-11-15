import { chromium } from 'playwright';

async function main() {
    const browser = await chromium.launch({ headless: true });

    // 1. Desktop (1440x950)
    const page = await browser.newPage({ viewport: { width: 1440, height: 950 } });
    await page.goto('http://127.0.0.1:8000/', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);

    // Remove cookie consent & live chat popups for clean screenshots
    await page.evaluate(() => {
        document.getElementById('cookie-consent-banner')?.remove();
        document.querySelector('[data-nosnippet]')?.remove();
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('cooca-theme', 'dark');
    });
    await page.waitForTimeout(500);

    // Capture Hero on Desktop Dark
    await page.screenshot({ path: 'scripts/hero_desktop_dark.png', clip: { x: 0, y: 0, width: 1440, height: 850 } });
    console.log('Saved hero_desktop_dark.png');

    // Capture Bottom CTA on Desktop Dark
    const bottomCta = await page.$('#final-cta, section:has-text("Time to Switch to COOCA"), section:has-text("Saatnya Beralih ke COOCA")');
    if (bottomCta) {
        await bottomCta.scrollIntoViewIfNeeded();
        await page.waitForTimeout(500);
        await bottomCta.screenshot({ path: 'scripts/bottom_cta_desktop_dark.png' });
        console.log('Saved bottom_cta_desktop_dark.png');
    }

    // Switch to Light Mode
    await page.evaluate(() => {
        document.documentElement.classList.remove('dark');
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('cooca-theme', 'light');
        window.scrollTo(0, 0);
    });
    await page.waitForTimeout(500);

    // Capture Hero on Desktop Light
    await page.screenshot({ path: 'scripts/hero_desktop_light.png', clip: { x: 0, y: 0, width: 1440, height: 850 } });
    console.log('Saved hero_desktop_light.png');

    // Capture Bottom CTA on Desktop Light
    if (bottomCta) {
        await bottomCta.scrollIntoViewIfNeeded();
        await page.waitForTimeout(500);
        await bottomCta.screenshot({ path: 'scripts/bottom_cta_desktop_light.png' });
        console.log('Saved bottom_cta_desktop_light.png');
    }

    // 2. Mobile 375x812 (iPhone standard)
    const mobilePage = await browser.newPage({ viewport: { width: 375, height: 812 } });
    await mobilePage.goto('http://127.0.0.1:8000/', { waitUntil: 'domcontentloaded' });
    await mobilePage.waitForTimeout(1000);

    // Remove cookie banner on mobile
    await mobilePage.evaluate(() => {
        document.getElementById('cookie-consent-banner')?.remove();
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-theme', 'dark');
    });
    await mobilePage.waitForTimeout(500);

    // Capture Hero on Mobile Dark
    await mobilePage.screenshot({ path: 'scripts/hero_mobile_dark_375.png', clip: { x: 0, y: 0, width: 375, height: 750 } });
    console.log('Saved hero_mobile_dark_375.png');

    // 3. Mobile 320x568 (Smallest mobile)
    const smallMobilePage = await browser.newPage({ viewport: { width: 320, height: 600 } });
    await smallMobilePage.goto('http://127.0.0.1:8000/', { waitUntil: 'domcontentloaded' });
    await smallMobilePage.waitForTimeout(1000);

    await smallMobilePage.evaluate(() => {
        document.getElementById('cookie-consent-banner')?.remove();
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-theme', 'dark');
    });
    await smallMobilePage.waitForTimeout(500);

    await smallMobilePage.screenshot({ path: 'scripts/hero_mobile_dark_320.png', clip: { x: 0, y: 0, width: 320, height: 750 } });
    console.log('Saved hero_mobile_dark_320.png');

    await browser.close();
    console.log('All screenshots captured successfully!');
}

main().catch(console.error);
