import { chromium } from 'playwright';

async function main() {
    const browser = await chromium.launch({ headless: true });
    // Test on Mobile viewport (390x844)
    const page = await browser.newPage({ viewport: { width: 390, height: 844 } });

    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'domcontentloaded' });
    
    await page.fill('input[type="email"]', 'reviewer@cooca.id');
    await page.fill('input[type="password"]', 'MetaReview2026!');
    await page.click('button[type="submit"]');

    await page.waitForNavigation({ waitUntil: 'networkidle', timeout: 10000 }).catch(() => {});

    // Open mobile sidebar drawer if closed
    await page.evaluate(() => {
        // Find hamburger or set Alpine state
        window.dispatchEvent(new CustomEvent('toggle-sidebar'));
    });
    await page.waitForTimeout(1000);

    const financeItems = await page.evaluate(() => {
        const results = [];
        document.querySelectorAll('.app-sidebar .sidebar-nav .sidebar-item').forEach(el => {
            const txt = el.innerText.trim().replace(/\n/g, ' ');
            if (txt.includes('Cash') || txt.includes('Expenses') || txt.includes('Corporate') || txt.includes('Costing') || txt.includes('Customer') || txt.includes('Payable') || txt.includes('Receivable')) {
                const span = el.querySelector('span.truncate') || el.querySelector('span:not(.sidebar-icon-box)') || el;
                const cEl = window.getComputedStyle(el);
                const cSpan = window.getComputedStyle(span);
                results.push({
                    text: txt,
                    tag: el.tagName,
                    elClasses: el.className,
                    spanClasses: span ? span.className : '',
                    elFontSize: cEl.fontSize,
                    elFontWeight: cEl.fontWeight,
                    spanFontSize: cSpan.fontSize,
                    spanFontWeight: cSpan.fontWeight
                });
            }
        });
        return results;
    });

    console.log('Mobile viewport 390px items inspection:');
    console.table(financeItems);

    await browser.close();
}

main().catch(console.error);
