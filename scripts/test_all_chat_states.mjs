import { chromium } from 'playwright';

async function testAll() {
    const browser = await chromium.launch({ headless: true });

    // 1. Desktop 1440x900
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    await page.goto('http://127.0.0.1:8000/', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1000);

    // Open chat in Light Mode
    await page.evaluate(() => {
        const root = document.querySelector('#cooca-live-chat-root');
        if (root && root._x_dataStack && root._x_dataStack[0]) {
            root._x_dataStack[0].isOpen = true;
            root._x_dataStack[0].isMounted = true;
        }
    });
    await page.waitForTimeout(500);
    await page.screenshot({ path: 'scripts/desktop_light_form.png' });
    console.log('Saved desktop_light_form.png');

    // Switch to Dark Mode (View 1)
    await page.evaluate(() => {
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('cooca-theme', 'dark');
    });
    await page.waitForTimeout(500);
    await page.screenshot({ path: 'scripts/desktop_dark_form.png' });
    console.log('Saved desktop_dark_form.png');

    // Switch to View 2 (Active conversation) in Dark Mode
    await page.evaluate(() => {
        const root = document.querySelector('#cooca-live-chat-root');
        if (root && root._x_dataStack && root._x_dataStack[0]) {
            const data = root._x_dataStack[0];
            data.sessionToken = 'mock-session-test';
            data.visitorData = { name: 'Agung', phone: '08123456789', email: 'agung@example.com' };
            data.messages = [
                {
                    id: 1,
                    message: 'Halo, saya ingin bertanya tentang fitur integrasi WhatsApp dan Kasir POS.',
                    time: '14:20',
                    is_cooca: false,
                    is_from_page: false
                },
                {
                    id: 2,
                    message: 'Halo Kak Agung! Terima kasih telah menghubungi COOCA. Fitur POS kami terintegrasi otomatis ke WhatsApp secara realtime untuk kirim nota & katalog produk.',
                    time: '14:21',
                    is_cooca: true,
                    is_from_page: false
                }
            ];
            data.isWaitingReply = true;
        }
    });
    await page.waitForTimeout(500);
    await page.screenshot({ path: 'scripts/desktop_dark_chat.png' });
    console.log('Saved desktop_dark_chat.png');

    // Switch to Light Mode (View 2)
    await page.evaluate(() => {
        document.documentElement.classList.remove('dark');
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('cooca-theme', 'light');
    });
    await page.waitForTimeout(500);
    await page.screenshot({ path: 'scripts/desktop_light_chat.png' });
    console.log('Saved desktop_light_chat.png');

    // Mobile Viewport (375x812)
    const mobilePage = await browser.newPage({ viewport: { width: 375, height: 812 } });
    await mobilePage.goto('http://127.0.0.1:8000/', { waitUntil: 'domcontentloaded' });
    await mobilePage.waitForTimeout(1000);

    // Mobile Dark Mode
    await mobilePage.evaluate(() => {
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-theme', 'dark');
        const root = document.querySelector('#cooca-live-chat-root');
        if (root && root._x_dataStack && root._x_dataStack[0]) {
            root._x_dataStack[0].isOpen = true;
            root._x_dataStack[0].isMounted = true;
        }
    });
    await mobilePage.waitForTimeout(500);
    await mobilePage.screenshot({ path: 'scripts/mobile_dark_form.png' });
    console.log('Saved mobile_dark_form.png');

    await browser.close();
    console.log('All chat states successfully tested and captured!');
}

testAll().catch(console.error);
