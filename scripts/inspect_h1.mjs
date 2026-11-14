import { chromium } from 'playwright';

async function checkH1() {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    await page.goto('http://127.0.0.1:8000/', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);

    const h1Details = await page.evaluate(() => {
        const h1 = document.querySelector('h1');
        if (!h1) return 'No H1 found';
        
        // Find matched styles
        const computed = window.getComputedStyle(h1);
        const color = computed.color;
        
        // Check inline style, classList, parent elements
        return {
            tagName: h1.tagName,
            className: h1.className,
            text: h1.textContent.trim().substring(0, 50),
            color: color,
            inlineColor: h1.style.color,
            parentBg: window.getComputedStyle(h1.parentElement).backgroundColor,
            sectionBg: window.getComputedStyle(h1.closest('section')).backgroundColor,
        };
    });

    console.log('H1 details:', h1Details);
    await browser.close();
}

checkH1().catch(console.error);
