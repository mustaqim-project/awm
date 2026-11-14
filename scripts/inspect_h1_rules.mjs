import { chromium } from 'playwright';

async function checkH1Rules() {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    await page.goto('http://127.0.0.1:8000/', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);

    const rules = await page.evaluate(() => {
        const h1 = document.querySelector('h1');
        const matched = [];
        for (const sheet of document.styleSheets) {
            try {
                for (const rule of sheet.cssRules) {
                    if (rule.selectorText && h1.matches(rule.selectorText)) {
                        matched.push({
                            sheet: sheet.href || 'inline/style tag',
                            selector: rule.selectorText,
                            cssText: rule.cssText
                        });
                    }
                }
            } catch (e) {}
        }
        return matched;
    });

    console.log('Matched rules count:', rules.length);
    for (const r of rules) {
        if (r.cssText.includes('color')) {
            console.log(`Sheet: ${r.sheet}\nSelector: ${r.selector}\nRule: ${r.cssText}\n---`);
        }
    }
    await browser.close();
}

checkH1Rules().catch(console.error);
