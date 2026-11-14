/**
 * M1 Empirical Verification Harness: Chatbot Deferral, TBT, Mobile Theme Switch, and Blog CSS Contrast
 * Runs in headless Chromium via Playwright.
 */

import { chromium } from 'playwright';
import { execSync } from 'child_process';
import assert from 'assert';

console.log('================================================================');
console.log('COOCA M1 EMPIRICAL CHALLENGE TEST HARNESS');
console.log('Testing: Chatbot Deferral, Idle TBT, Mobile Theme & Blog Contrast');
console.log('================================================================\n');

function getRenderedHtml(viewType) {
    console.log(`[Renderer] Rendering Blade view: ${viewType}...`);
    const output = execSync(`php tests/e2e/render_views.php ${viewType}`, {
        maxBuffer: 50 * 1024 * 1024,
        encoding: 'utf8'
    });
    console.log(`[Renderer] ${viewType} rendered: ${output.length} bytes.`);
    return output;
}

// WCAG relative luminance & contrast calculator
function parseRgb(colorStr) {
    const match = colorStr.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
    if (!match) return [0, 0, 0];
    return [parseInt(match[1], 10), parseInt(match[2], 10), parseInt(match[3], 10)];
}

function getLuminance(r, g, b) {
    const toLinear = (c) => {
        const val = c / 255;
        return val <= 0.03928 ? val / 12.92 : Math.pow((val + 0.055) / 1.055, 2.4);
    };
    return 0.2126 * toLinear(r) + 0.7152 * toLinear(g) + 0.0722 * toLinear(b);
}

function getContrast(rgb1, rgb2) {
    const l1 = getLuminance(rgb1[0], rgb1[1], rgb1[2]);
    const l2 = getLuminance(rgb2[0], rgb2[1], rgb2[2]);
    const lighter = Math.max(l1, l2);
    const darker = Math.min(l1, l2);
    return (lighter + 0.05) / (darker + 0.05);
}

async function runEmpiricalHarness() {
    const landingHtml = getRenderedHtml('landing');
    const helpChatbotHtml = getRenderedHtml('help_chatbot');
    const blogShowHtml = getRenderedHtml('blog_show');

    const browser = await chromium.launch({ headless: true });
    let passedTests = 0;
    let totalTests = 4;

    try {
        // =========================================================================
        // TEST 1: LIVE CHAT WIDGET DEFERRED DOM MOUNTING & ZERO IDLE TIMERS / WEBSOCKETS
        // =========================================================================
        console.log('\n--- [TEST 1] Live Chat Widget Deferral & Idle Quarantine ---');
        {
            const context = await browser.newContext();
            const page = await context.newPage();

            page.on('console', msg => {
                if (msg.type() === 'error') console.log('[PAGE CONSOLE ERROR]', msg.text());
            });
            page.on('pageerror', err => console.log('[PAGE JS ERROR]', err.message));

            const spyScript = `
                <script>
                    window.__spiedIntervals = [];
                    window.__spiedWebSockets = [];
                    (function() {
                        const origSetInterval = window.setInterval;
                        window.setInterval = function (fn, delay, ...args) {
                            window.__spiedIntervals.push({ delay: delay, time: Date.now() });
                            return origSetInterval.call(window, fn, delay, ...args);
                        };
                        const origWebSocket = window.WebSocket;
                        window.WebSocket = function (...args) {
                            window.__spiedWebSockets.push({ url: args[0], time: Date.now() });
                            return new origWebSocket(...args);
                        };
                    })();
                </script>
            `;

            const instrumentedLandingHtml = landingHtml.replace('<head>', '<head>' + spyScript);
            
            // Intercept route on origin so localStorage is fully enabled (not opaque about:blank)
            await page.route('http://127.0.0.1:8000/**', route => {
                route.fulfill({
                    status: 200,
                    contentType: 'text/html',
                    body: instrumentedLandingHtml
                });
            });

            await page.goto('http://127.0.0.1:8000/', { waitUntil: 'load' });
            await page.waitForTimeout(600); // Allow Alpine initialization

            // 1.1 Verify live chat window DOM node is NOT mounted initially
            const initialWindow = await page.$('.floating-live-chat-window');
            console.log(`[LiveChat] Initial .floating-live-chat-window in DOM: ${initialWindow !== null ? 'MOUNTED (FAIL)' : 'NOT MOUNTED (PASS)'}`);
            assert.strictEqual(initialWindow, null, 'Live chat window must NOT be mounted on initial page load');

            // 1.2 Verify zero background polling intervals fired for chat
            const spiedIntervals = await page.evaluate(() => window.__spiedIntervals);
            const liveChatIntervals = spiedIntervals.filter(i => i.delay === 3500 || i.delay === 5000);
            console.log(`[LiveChat] Live chat intervals (3500ms / 5000ms) on load: ${liveChatIntervals.length} (PASS: 0)`);
            assert.strictEqual(liveChatIntervals.length, 0, 'Zero live chat polling intervals must fire on initial load');

            // 1.3 Verify zero WebSockets opened on load
            const spiedWebSockets = await page.evaluate(() => window.__spiedWebSockets);
            console.log(`[LiveChat] WebSockets instantiated on load: ${spiedWebSockets.length} (PASS: 0)`);
            assert.strictEqual(spiedWebSockets.length, 0, 'Zero WebSockets must be opened on initial load without user session');

            // 1.4 Click floating trigger button to open chat
            const alpineStatusBefore = await page.evaluate(() => {
                const root = document.querySelector('#cooca-live-chat-root');
                return {
                    alpineLoaded: typeof window.Alpine !== 'undefined',
                    hasDataStack: !!(root && root._x_dataStack),
                    isOpen: root && root._x_dataStack ? root._x_dataStack[0].isOpen : null,
                    isMounted: root && root._x_dataStack ? root._x_dataStack[0].isMounted : null,
                };
            });
            console.log('[LiveChat] Alpine status before click:', JSON.stringify(alpineStatusBefore));

            const fabButton = await page.$('#cooca-live-chat-root button[class*="from-[#6D28D9]"]');
            assert.ok(fabButton, 'Live chat FAB button must exist');
            await fabButton.click();
            await page.waitForTimeout(500);

            const alpineStatusAfter = await page.evaluate(() => {
                const root = document.querySelector('#cooca-live-chat-root');
                return {
                    isOpen: root && root._x_dataStack ? root._x_dataStack[0].isOpen : null,
                    isMounted: root && root._x_dataStack ? root._x_dataStack[0].isMounted : null,
                };
            });
            console.log('[LiveChat] Alpine status after click:', JSON.stringify(alpineStatusAfter));

            // 1.5 Verify live chat window IS mounted in DOM after user click
            const mountedWindow = await page.$('.floating-live-chat-window');
            console.log(`[LiveChat] Post-click .floating-live-chat-window in DOM: ${mountedWindow !== null ? 'MOUNTED (PASS)' : 'NOT MOUNTED (FAIL)'}`);
            assert.ok(mountedWindow !== null, 'Live chat modal window must be mounted into DOM upon user interaction');

            await context.close();
            passedTests++;
            console.log('[TEST 1] RESULT: PASSED');
        }

        // =========================================================================
        // TEST 2: HELP CHATBOT WIDGET DEFERRED DOM MOUNTING & ZERO IDLE TIMERS
        // =========================================================================
        console.log('\n--- [TEST 2] Help Chatbot Widget Deferral & Idle Quarantine ---');
        {
            const context = await browser.newContext();
            const page = await context.newPage();

            page.on('console', msg => {
                if (msg.type() === 'error') console.log('[PAGE CONSOLE ERROR]', msg.text());
            });
            page.on('pageerror', err => console.log('[PAGE JS ERROR]', err.message));

            // Wrap help chatbot in minimal HTML document with Alpine.js and spy script
            const helpDoc = `
                <!DOCTYPE html>
                <html>
                <head>
                    <script>
                        window.__spiedIntervals = [];
                        (function() {
                            const origSetInterval = window.setInterval;
                            window.setInterval = function (fn, delay, ...args) {
                                window.__spiedIntervals.push({ delay: delay, time: Date.now() });
                                return origSetInterval.call(window, fn, delay, ...args);
                            };
                        })();
                    </script>
                    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
                </head>
                <body>
                    ${helpChatbotHtml}
                </body>
                </html>
            `;

            // Intercept route on origin so sessionStorage and localStorage work
            await page.route('http://127.0.0.1:8000/**', route => {
                route.fulfill({
                    status: 200,
                    contentType: 'text/html',
                    body: helpDoc
                });
            });

            await page.goto('http://127.0.0.1:8000/', { waitUntil: 'load' });
            await page.waitForTimeout(600);

            // 2.1 Verify help chatbot modal is NOT mounted initially
            const initialHelpModal = await page.$('#cooca-help-chatbot-root .fixed.right-3');
            console.log(`[HelpChatbot] Initial modal container in DOM: ${initialHelpModal !== null ? 'MOUNTED (FAIL)' : 'NOT MOUNTED (PASS)'}`);
            assert.strictEqual(initialHelpModal, null, 'Help chatbot modal container must NOT be mounted on load');

            // 2.2 Verify zero intervals
            const spiedIntervals = await page.evaluate(() => window.__spiedIntervals);
            console.log(`[HelpChatbot] Background intervals registered on load: ${spiedIntervals.length} (PASS: 0)`);
            assert.strictEqual(spiedIntervals.length, 0, 'Help chatbot must register zero intervals on load');

            // 2.3 User clicks FAB
            const helpFab = await page.$('#cooca-help-chatbot-root button[aria-label="Buka Asisten AI Cooca"]');
            assert.ok(helpFab, 'Help chatbot FAB button must exist');
            await helpFab.click();
            await page.waitForTimeout(300);

            // 2.4 Verify modal IS mounted now
            const mountedHelpModal = await page.$('#cooca-help-chatbot-root .fixed.right-3');
            console.log(`[HelpChatbot] Post-click modal in DOM: ${mountedHelpModal !== null ? 'MOUNTED (PASS)' : 'NOT MOUNTED (FAIL)'}`);
            assert.ok(mountedHelpModal !== null, 'Help chatbot modal container must be mounted after user click');

            await context.close();
            passedTests++;
            console.log('[TEST 2] RESULT: PASSED');
        }

        // =========================================================================
        // TEST 3: MOBILE (< 640px) THEME SWITCH PRESENCE, CLICKABILITY & ANTI-FOUC
        // =========================================================================
        console.log('\n--- [TEST 3] Mobile Theme Switch (< 640px) & Anti-FOUC Sync ---');
        {
            const context = await browser.newContext({
                viewport: { width: 375, height: 667 }, // iPhone SE screen (< 640px)
                isMobile: true,
                hasTouch: true
            });
            const page = await context.newPage();

            await page.route('http://127.0.0.1:8000/**', route => {
                route.fulfill({
                    status: 200,
                    contentType: 'text/html',
                    body: landingHtml
                });
            });

            await page.goto('http://127.0.0.1:8000/', { waitUntil: 'load' });
            await page.waitForTimeout(600);

            // 3.1 Verify theme switcher button is present and visible in mobile header
            const themeBtn = await page.$('header button[aria-label="Tema Tampilan"]');
            assert.ok(themeBtn, 'Theme switch button must exist in mobile header');
            const isVisible = await themeBtn.isVisible();
            console.log(`[MobileTheme] Theme switch button visible at 375px: ${isVisible ? 'YES (PASS)' : 'NO (FAIL)'}`);
            assert.strictEqual(isVisible, true, 'Theme switch button must be visible on mobile screens (< 640px)');

            // 3.2 Verify initial light state
            let isDarkInitial = await page.evaluate(() => document.documentElement.classList.contains('dark'));
            let dataThemeInitial = await page.evaluate(() => document.documentElement.getAttribute('data-theme'));
            console.log(`[MobileTheme] Initial state: class dark=${isDarkInitial}, data-theme=${dataThemeInitial}`);
            assert.strictEqual(isDarkInitial, false);
            assert.strictEqual(dataThemeInitial, 'light');

            // 3.3 Click theme switch button on mobile screen
            await themeBtn.click();
            await page.waitForTimeout(200);

            let isDarkAfter = await page.evaluate(() => document.documentElement.classList.contains('dark'));
            let dataThemeAfter = await page.evaluate(() => document.documentElement.getAttribute('data-theme'));
            let localStorageTheme = await page.evaluate(() => localStorage.getItem('cooca-theme'));
            console.log(`[MobileTheme] After click: class dark=${isDarkAfter}, data-theme=${dataThemeAfter}, localStorage=${localStorageTheme}`);
            assert.strictEqual(isDarkAfter, true, 'html class must include dark after click');
            assert.strictEqual(dataThemeAfter, 'dark', 'html data-theme must be dark after click');
            assert.strictEqual(localStorageTheme, 'dark', 'localStorage cooca-theme must be dark after click');

            // 3.4 Click again to toggle back to light
            await themeBtn.click();
            await page.waitForTimeout(200);

            let isDarkToggleBack = await page.evaluate(() => document.documentElement.classList.contains('dark'));
            let dataThemeToggleBack = await page.evaluate(() => document.documentElement.getAttribute('data-theme'));
            let localStorageToggleBack = await page.evaluate(() => localStorage.getItem('cooca-theme'));
            console.log(`[MobileTheme] Toggle back: class dark=${isDarkToggleBack}, data-theme=${dataThemeToggleBack}, localStorage=${localStorageToggleBack}`);
            assert.strictEqual(isDarkToggleBack, false);
            assert.strictEqual(dataThemeToggleBack, 'light');
            assert.strictEqual(localStorageToggleBack, 'light');

            // 3.5 Anti-FOUC Reload Test: Pre-set dark mode in localStorage
            await page.evaluate(() => localStorage.setItem('cooca-theme', 'dark'));
            // Reload page content and verify synchronous head execution
            await page.reload({ waitUntil: 'domcontentloaded' });
            let isDarkAntiFouc = await page.evaluate(() => document.documentElement.classList.contains('dark'));
            let dataThemeAntiFouc = await page.evaluate(() => document.documentElement.getAttribute('data-theme'));
            console.log(`[MobileTheme] Anti-FOUC synchronous dark mode on reload: class dark=${isDarkAntiFouc}, data-theme=${dataThemeAntiFouc}`);
            assert.strictEqual(isDarkAntiFouc, true, 'Anti-FOUC script must apply dark class immediately before render');
            assert.strictEqual(dataThemeAntiFouc, 'dark', 'Anti-FOUC script must apply data-theme=dark immediately before render');

            await context.close();
            passedTests++;
            console.log('[TEST 3] RESULT: PASSED');
        }

        // =========================================================================
        // TEST 4: BLOG SHOW CSS CONSOLIDATION & COMPUTED WCAG CONTRAST RATIOS
        // =========================================================================
        console.log('\n--- [TEST 4] Blog Show CSS Consolidation & Computed Contrast ---');
        {
            const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
            const page = await context.newPage();

            await page.route('http://127.0.0.1:8000/**', route => {
                route.fulfill({
                    status: 200,
                    contentType: 'text/html',
                    body: blogShowHtml
                });
            });

            await page.goto('http://127.0.0.1:8000/', { waitUntil: 'load' });
            await page.waitForTimeout(600);

            // 4.1 Evaluate computed styles in Light Mode
            const lightStyles = await page.evaluate(() => {
                const prose = document.querySelector('.article-prose');
                const p = document.querySelector('.article-prose p');
                const h2 = document.querySelector('.article-prose h2');
                const h3 = document.querySelector('.article-prose h3');
                const a = document.querySelector('.article-prose a');
                const blockquote = document.querySelector('.article-prose blockquote');
                const code = document.querySelector('.article-prose code');
                const th = document.querySelector('.article-prose th');

                return {
                    bg: window.getComputedStyle(document.body).backgroundColor,
                    proseColor: p ? window.getComputedStyle(p).color : '',
                    h2Color: h2 ? window.getComputedStyle(h2).color : '',
                    h3Color: h3 ? window.getComputedStyle(h3).color : '',
                    aColor: a ? window.getComputedStyle(a).color : '',
                    quoteColor: blockquote ? window.getComputedStyle(blockquote).color : '',
                    codeColor: code ? window.getComputedStyle(code).color : '',
                    codeBg: code ? window.getComputedStyle(code).backgroundColor : '',
                    thColor: th ? window.getComputedStyle(th).color : '',
                    thBg: th ? window.getComputedStyle(th).backgroundColor : ''
                };
            });

            console.log('[BlogContrast] Light mode computed styles:');
            console.log('  Page BG:', lightStyles.bg, '| Prose color:', lightStyles.proseColor);
            console.log('  H2 color:', lightStyles.h2Color, '| Link color:', lightStyles.aColor);

            const whiteRgb = [255, 255, 255]; // Standard card/prose background
            const proseRgb = parseRgb(lightStyles.proseColor);
            const h2Rgb = parseRgb(lightStyles.h2Color);
            const aRgb = parseRgb(lightStyles.aColor);
            const quoteRgb = parseRgb(lightStyles.quoteColor);
            const codeRgb = parseRgb(lightStyles.codeColor);
            const codeBgRgb = parseRgb(lightStyles.codeBg);

            const contrastLightProse = getContrast(proseRgb, whiteRgb);
            const contrastLightH2 = getContrast(h2Rgb, whiteRgb);
            const contrastLightLink = getContrast(aRgb, whiteRgb);
            const contrastLightQuote = getContrast(quoteRgb, whiteRgb);
            const contrastLightCode = getContrast(codeRgb, codeBgRgb);

            console.log(`  Prose contrast on white: ${contrastLightProse.toFixed(2)}:1 (Req >= 4.5:1)`);
            console.log(`  H2 contrast on white: ${contrastLightH2.toFixed(2)}:1 (Req >= 3.0:1)`);
            console.log(`  Link contrast on white: ${contrastLightLink.toFixed(2)}:1 (Req >= 3.0:1 with underline)`);
            console.log(`  Quote contrast on white: ${contrastLightQuote.toFixed(2)}:1 (Req >= 4.5:1)`);
            console.log(`  Code contrast on codeBg: ${contrastLightCode.toFixed(2)}:1 (Req >= 4.5:1)`);

            assert.ok(contrastLightProse >= 4.5, `Light prose contrast ${contrastLightProse}:1 < 4.5:1`);
            assert.ok(contrastLightH2 >= 3.0, `Light H2 contrast ${contrastLightH2}:1 < 3.0:1`);
            assert.ok(contrastLightLink >= 3.0, `Light link contrast ${contrastLightLink}:1 < 3.0:1`);
            assert.ok(contrastLightQuote >= 4.5, `Light quote contrast ${contrastLightQuote}:1 < 4.5:1`);
            assert.ok(contrastLightCode >= 4.5, `Light code contrast ${contrastLightCode}:1 < 4.5:1`);

            // 4.2 Switch to Dark Mode in the DOM
            await page.evaluate(() => {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            });
            await page.waitForTimeout(200);

            const darkStyles = await page.evaluate(() => {
                const p = document.querySelector('.article-prose p');
                const h2 = document.querySelector('.article-prose h2');
                const h3 = document.querySelector('.article-prose h3');
                const a = document.querySelector('.article-prose a');
                const blockquote = document.querySelector('.article-prose blockquote');
                const code = document.querySelector('.article-prose code');
                const th = document.querySelector('.article-prose th');

                return {
                    proseColor: p ? window.getComputedStyle(p).color : '',
                    h2Color: h2 ? window.getComputedStyle(h2).color : '',
                    h3Color: h3 ? window.getComputedStyle(h3).color : '',
                    aColor: a ? window.getComputedStyle(a).color : '',
                    quoteColor: blockquote ? window.getComputedStyle(blockquote).color : '',
                    codeColor: code ? window.getComputedStyle(code).color : '',
                    codeBg: code ? window.getComputedStyle(code).backgroundColor : '',
                    thColor: th ? window.getComputedStyle(th).color : '',
                    thBg: th ? window.getComputedStyle(th).backgroundColor : ''
                };
            });

            console.log('\n[BlogContrast] Dark mode computed styles:');
            console.log('  Prose color:', darkStyles.proseColor, '| H2 color:', darkStyles.h2Color);
            console.log('  Link color:', darkStyles.aColor, '| Quote color:', darkStyles.quoteColor);

            const darkSurfaceRgb = [28, 28, 30]; // #1C1C1E Apple Dark Card Surface
            const darkProseRgb = parseRgb(darkStyles.proseColor);
            const darkH2Rgb = parseRgb(darkStyles.h2Color);
            const darkLinkRgb = parseRgb(darkStyles.aColor);
            const darkQuoteRgb = parseRgb(darkStyles.quoteColor);
            const darkCodeRgb = parseRgb(darkStyles.codeColor);
            const darkCodeBgRgb = parseRgb(darkStyles.codeBg);

            const contrastDarkProse = getContrast(darkProseRgb, darkSurfaceRgb);
            const contrastDarkH2 = getContrast(darkH2Rgb, darkSurfaceRgb);
            const contrastDarkLink = getContrast(darkLinkRgb, darkSurfaceRgb);
            const contrastDarkQuote = getContrast(darkQuoteRgb, darkSurfaceRgb);
            const contrastDarkCode = getContrast(darkCodeRgb, darkCodeBgRgb);

            console.log(`  Dark prose contrast on #1C1C1E: ${contrastDarkProse.toFixed(2)}:1 (Req >= 4.5:1)`);
            console.log(`  Dark H2 contrast on #1C1C1E: ${contrastDarkH2.toFixed(2)}:1 (Req >= 3.0:1)`);
            console.log(`  Dark link contrast on #1C1C1E: ${contrastDarkLink.toFixed(2)}:1 (Req >= 4.5:1)`);
            console.log(`  Dark quote contrast on #1C1C1E: ${contrastDarkQuote.toFixed(2)}:1 (Req >= 4.5:1)`);
            console.log(`  Dark code contrast on codeBg: ${contrastDarkCode.toFixed(2)}:1 (Req >= 4.5:1)`);

            assert.ok(contrastDarkProse >= 4.5, `Dark prose contrast ${contrastDarkProse}:1 < 4.5:1`);
            assert.ok(contrastDarkH2 >= 3.0, `Dark H2 contrast ${contrastDarkH2}:1 < 3.0:1`);
            assert.ok(contrastDarkLink >= 4.5, `Dark link contrast ${contrastDarkLink}:1 < 4.5:1`);
            assert.ok(contrastDarkQuote >= 4.5, `Dark quote contrast ${contrastDarkQuote}:1 < 4.5:1`);
            assert.ok(contrastDarkCode >= 4.5, `Dark code contrast ${contrastDarkCode}:1 < 4.5:1`);

            await context.close();
            passedTests++;
            console.log('[TEST 4] RESULT: PASSED');
        }

    } finally {
        await browser.close();
    }

    console.log('\n================================================================');
    console.log(`HARNESS EXECUTION COMPLETE: ${passedTests}/${totalTests} TESTS PASSED (100%)`);
    console.log('ALL EMPIRICAL CHALLENGES VERIFIED WITH ZERO ANOMALIES.');
    console.log('================================================================\n');
}

runEmpiricalHarness().catch(err => {
    console.error('\n[FATAL ERROR IN HARNESS]:', err);
    process.exit(1);
});
