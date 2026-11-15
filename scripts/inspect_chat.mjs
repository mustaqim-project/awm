import { chromium } from 'playwright';

async function main() {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

    await page.goto('http://127.0.0.1:8000/', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);

    // 1. Check Light Mode - open chat
    console.log('Opening chat in Light mode...');
    await page.evaluate(() => {
        const root = document.querySelector('#cooca-live-chat-root');
        if (root && root._x_dataStack && root._x_dataStack[0]) {
            root._x_dataStack[0].isOpen = true;
            root._x_dataStack[0].isMounted = true;
        } else {
            const btn = document.querySelector('#cooca-live-chat-root button');
            if (btn) btn.click();
        }
    });
    await page.waitForTimeout(500);
    await page.screenshot({ path: 'scripts/chat_light.png' });

    // Inspect computed style of chat window in Light mode
    const lightInfo = await page.evaluate(() => {
        const win = document.querySelector('.floating-live-chat-window');
        if (!win) return null;
        return {
            bg: window.getComputedStyle(win).backgroundColor,
            color: window.getComputedStyle(win).color,
            formBg: window.getComputedStyle(win.querySelector('form') || win).backgroundColor
        };
    });
    console.log('Light info:', lightInfo);

    // 2. Switch to Dark Mode
    console.log('Switching to Dark mode...');
    await page.evaluate(() => {
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('cooca-theme', 'dark');
    });
    await page.waitForTimeout(500);
    await page.screenshot({ path: 'scripts/chat_dark.png' });

    // Inspect computed style of chat window in Dark mode
    const darkInfo = await page.evaluate(() => {
        const win = document.querySelector('.floating-live-chat-window');
        if (!win) return null;
        const header = win.querySelector('div:first-child');
        const formView = win.querySelector('[x-show="!sessionToken"]');
        const inputs = Array.from(win.querySelectorAll('input, textarea')).map(i => ({
            tag: i.tagName,
            bg: window.getComputedStyle(i).backgroundColor,
            color: window.getComputedStyle(i).color,
            border: window.getComputedStyle(i).borderColor
        }));
        return {
            winBg: window.getComputedStyle(win).backgroundColor,
            headerBg: header ? window.getComputedStyle(header).backgroundColor : null,
            formViewBg: formView ? window.getComputedStyle(formView).backgroundColor : null,
            labelsColor: win.querySelector('label') ? window.getComputedStyle(win.querySelector('label')).color : null,
            headingColor: win.querySelector('h4') ? window.getComputedStyle(win.querySelector('h4')).color : null,
            inputs
        };
    });
    console.log('Dark info:', JSON.stringify(darkInfo, null, 2));

    await browser.close();
}

main().catch(console.error);
