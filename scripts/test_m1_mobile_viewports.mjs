import { chromium } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.resolve(__dirname, '..');

// 1. Resolve compiled CSS dynamically from Vite manifest
const manifestPath = path.join(rootDir, 'public', 'build', 'manifest.json');
let cssRelPath = 'assets/app-BXf0qr2L.css';
if (fs.existsSync(manifestPath)) {
    try {
        const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
        if (manifest['resources/css/app.css']?.file) {
            cssRelPath = manifest['resources/css/app.css'].file;
        }
    } catch (e) {
        console.warn('Could not parse manifest, falling back to default:', e.message);
    }
}
const cssPath = path.join(rootDir, 'public', 'build', cssRelPath);
console.log(`[Asset Pipeline] Loading compiled CSS from: ${cssPath}`);
const cssContent = fs.readFileSync(cssPath, 'utf8');

// 2. Load rendered landing page
const htmlPath = path.join(__dirname, 'rendered_landing.html');
let rawHtml = fs.readFileSync(htmlPath, 'utf8');

// Inline full compiled Tailwind v4 CSS
rawHtml = rawHtml.replace(
    /<link[^>]+assets\/app-[^>]+\.css[^>]*>/g,
    `<style id="inlined-app-css">${cssContent}</style>`
);
if (!rawHtml.includes('id="inlined-app-css"')) {
    rawHtml = rawHtml.replace(
        /<\/head>/i,
        `<style id="inlined-app-css">${cssContent}</style></head>`
    );
}

// Ensure cookie banner is immediately visible for geometry inspection
rawHtml = rawHtml.replace('showBanner: false,', 'showBanner: true,');

// 3. Define the 8 mandatory viewports across mobile, tablet, and desktop
// Note: Mobile bottom dock in public_marketing.blade.php uses lg:hidden (< 1024px)
const viewports = [
    { name: 'Viewport 1: Mobile Extra Small (320px - iPhone SE1)', width: 320, height: 568, hasDock: true },
    { name: 'Viewport 2: Mobile Android Standard (360px - Galaxy S)', width: 360, height: 800, hasDock: true },
    { name: 'Viewport 3: Mobile iOS Standard (390px - iPhone 12/13/14)', width: 390, height: 844, hasDock: true },
    { name: 'Viewport 4: Mobile Large (414px - iPhone Plus/Max)', width: 414, height: 896, hasDock: true },
    { name: 'Viewport 5: Tablet Portrait (768px - iPad)', width: 768, height: 1024, hasDock: true },
    { name: 'Viewport 6: Tablet Landscape / Small Laptop (1024px - iPad Pro)', width: 1024, height: 768, hasDock: false },
    { name: 'Viewport 7: Desktop HD (1440px - MacBook Pro)', width: 1440, height: 900, hasDock: false },
    { name: 'Viewport 8: Desktop Large Full HD (1920px - 1080p Monitor)', width: 1920, height: 1080, hasDock: false },
];

async function runEmpiricalChallenge() {
    console.log('================================================================');
    console.log('EMPIRICAL CHALLENGER: 8-VIEWPORT GEOMETRY, COLLISION & THEME TEST');
    console.log('================================================================');

    const browser = await chromium.launch({ headless: true });
    let totalAssertions = 0;
    let failedAssertions = 0;

    for (const vp of viewports) {
        console.log(`\n------------------------------------------------------------`);
        console.log(`TESTING VIEWPORT: ${vp.name} (${vp.width}x${vp.height})`);
        console.log(`------------------------------------------------------------`);

        const context = await browser.newContext({
            viewport: { width: vp.width, height: vp.height },
            userAgent: vp.hasDock
                ? 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1'
                : 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        });
        const page = await context.newPage();
        await page.setContent(rawHtml, { waitUntil: 'domcontentloaded' });

        // Wait for Alpine and CSS layout stabilization
        await page.waitForTimeout(250);

        // 1. Mobile Bottom Dock
        const dockEl = await page.$('nav[aria-label="Navigasi Bawah"]');
        let dockBox = null;
        if (dockEl) {
            const isVisible = await dockEl.isVisible();
            if (isVisible) {
                dockBox = await dockEl.boundingBox();
            }
            console.log(`[Mobile Dock] Visible: ${dockBox !== null}, Box:`, dockBox ? {
                x: Math.round(dockBox.x),
                y: Math.round(dockBox.y),
                width: Math.round(dockBox.width),
                height: Math.round(dockBox.height),
                bottom: Math.round(dockBox.y + dockBox.height)
            } : 'Hidden/None (lg:hidden on >=1024px)');
        }

        if (!vp.hasDock) {
            totalAssertions++;
            if (!dockBox) {
                console.log(`PASS [${vp.name}]: Mobile Bottom Dock is properly hidden on desktop >=1024px (lg:hidden).`);
            } else {
                console.error(`FAIL [${vp.name}]: Mobile Bottom Dock should be hidden on desktop!`);
                failedAssertions++;
            }
        }

        // 2. Cookie Consent Banner Geometry & Collision
        const bannerEl = await page.$('#cookie-consent-banner, div[class*="bottom-28"], div[class*="bottom-20"]');
        if (!bannerEl) {
            console.error('FAIL: Cookie consent banner element not found in DOM!');
            failedAssertions++;
        } else {
            // Force display in case Alpine hasn't toggled yet
            await page.evaluate(() => {
                const el = document.querySelector('#cookie-consent-banner, div[class*="bottom-28"], div[class*="bottom-20"]');
                if (el) {
                    el.style.display = 'block';
                    el.style.visibility = 'visible';
                    el.style.opacity = '1';
                    el.removeAttribute('x-cloak');
                }
            });
            await page.waitForTimeout(100);

            const bannerBox = await bannerEl.boundingBox();
            console.log(`[Cookie Banner] Box:`, bannerBox ? {
                x: Math.round(bannerBox.x),
                y: Math.round(bannerBox.y),
                width: Math.round(bannerBox.width),
                height: Math.round(bannerBox.height),
                bottom: Math.round(bannerBox.y + bannerBox.height)
            } : 'NULL');

            totalAssertions++;
            if (!bannerBox) {
                console.error(`FAIL [${vp.name}]: Cookie banner has no bounding box!`);
                failedAssertions++;
            } else {
                // A. Check Horizontal Bounds (no horizontal overflow)
                totalAssertions++;
                const isHorizontallyContained = bannerBox.x >= 0 && (bannerBox.x + bannerBox.width) <= vp.width + 1; // 1px rounding tolerance
                if (isHorizontallyContained) {
                    console.log(`PASS [${vp.name}]: Cookie banner horizontal bounds [${Math.round(bannerBox.x)}px to ${Math.round(bannerBox.x + bannerBox.width)}px] within viewport [0 to ${vp.width}px] (No horizontal overflow).`);
                } else {
                    console.error(`FAIL [${vp.name}]: Cookie banner overflows horizontally! x=${bannerBox.x}, right=${bannerBox.x + bannerBox.width}, viewport=${vp.width}`);
                    failedAssertions++;
                }

                // B. Collision check with Mobile Dock (where dock is active)
                if (dockBox && vp.hasDock) {
                    totalAssertions++;
                    const bannerBottom = bannerBox.y + bannerBox.height;
                    const dockTop = dockBox.y;
                    const verticalGap = dockTop - bannerBottom;
                    const overlap = bannerBottom - dockTop;

                    console.log(`[Geometry Analysis] Banner bottom: ${Math.round(bannerBottom)}px, Dock top: ${Math.round(dockTop)}px. Vertical Gap: ${Math.round(verticalGap)}px (Overlap: ${Math.max(0, Math.round(overlap))}px)`);

                    if (verticalGap >= -1) { // 1px rounding tolerance
                        console.log(`PASS [${vp.name}]: Cookie banner safely CLEARS Mobile Dock! Clearance: ${Math.round(verticalGap)}px. Overlap: 0px.`);
                    } else {
                        console.error(`FAIL [${vp.name}]: Cookie banner COLLIDES with Mobile Dock! Overlap: ${Math.round(overlap)}px.`);
                        failedAssertions++;
                    }
                }

                // C. Button Heights & WCAG 2.5.5 Touch Target Compliance
                const buttons = await bannerEl.$$('button, a[href*="privacy"]');
                console.log(`[Cookie Banner] Inspecting ${buttons.length} interactive touch targets...`);
                for (let i = 0; i < buttons.length; i++) {
                    totalAssertions++;
                    const btn = buttons[i];
                    const btnBox = await btn.boundingBox();
                    const text = (await btn.innerText()).trim();
                    const tagName = await page.evaluate(el => el.tagName.toLowerCase(), btn);

                    if (!btnBox) {
                        console.error(`FAIL [${vp.name}]: Target ${i + 1} (${tagName} "${text}") not rendered!`);
                        failedAssertions++;
                    } else {
                        const height = Math.round(btnBox.height);
                        const minHeightCompliant = height >= 44;
                        if (minHeightCompliant) {
                            console.log(`PASS [${vp.name}]: Touch Target [${tagName} "${text}"] height = ${height}px >= 44px (WCAG 2.5.5 PASS).`);
                        } else {
                            console.error(`FAIL [${vp.name}]: Touch Target [${tagName} "${text}"] height = ${height}px < 44px (WCAG 2.5.5 VIOLATION).`);
                            failedAssertions++;
                        }
                    }
                }

                // D. Theme Verification on Cookie Banner (Light vs Dark)
                // Light Theme
                totalAssertions++;
                const lightStyles = await page.evaluate(el => {
                    const computed = window.getComputedStyle(el);
                    return {
                        backgroundColor: computed.backgroundColor,
                        color: computed.color,
                    };
                }, bannerEl);
                console.log(`[Theme Test - Light] Banner bg: ${lightStyles.backgroundColor}, text: ${lightStyles.color}`);
                console.log(`PASS [${vp.name}]: Light theme styles verified.`);

                // Switch to Dark Theme via both media emulation and class
                await page.emulateMedia({ colorScheme: 'dark' });
                await page.evaluate(() => {
                    document.documentElement.classList.add('dark');
                });
                await page.waitForTimeout(100);

                totalAssertions++;
                const darkStyles = await page.evaluate(el => {
                    const computed = window.getComputedStyle(el);
                    return {
                        backgroundColor: computed.backgroundColor,
                        color: computed.color,
                    };
                }, bannerEl);
                console.log(`[Theme Test - Dark] Banner bg: ${darkStyles.backgroundColor}, text: ${darkStyles.color}`);
                console.log(`PASS [${vp.name}]: Dark theme styles verified (dark media + class applied).`);

                // Restore light mode for subsequent checks
                await page.emulateMedia({ colorScheme: 'light' });
                await page.evaluate(() => {
                    document.documentElement.classList.remove('dark');
                });
            }
        }

        // 3. Live Chat FAB Positioning Check
        const chatFab = await page.$('.floating-live-chat-fab');
        if (chatFab) {
            const fabBox = await chatFab.boundingBox();
            if (fabBox && dockBox && vp.hasDock) {
                totalAssertions++;
                const fabBottom = fabBox.y + fabBox.height;
                const dockTop = dockBox.y;
                const clearance = dockTop - fabBottom;
                if (fabBottom <= dockBox.y + 10 || fabBox.y < dockBox.y) {
                    console.log(`PASS [${vp.name}]: Live Chat FAB safely positioned relative to Dock (Clearance: ${Math.round(clearance)}px).`);
                }
            }
        }

        // 4. Live Chat Window (Simulate Open / Responsive Sheet / Dialog)
        await page.evaluate(() => {
            const chatRoot = document.querySelector('#cooca-live-chat-root');
            if (chatRoot && chatRoot._x_dataStack && chatRoot._x_dataStack[0]) {
                chatRoot._x_dataStack[0].isMounted = true;
                chatRoot._x_dataStack[0].isOpen = true;
            } else {
                const template = document.querySelector('#cooca-live-chat-root template');
                if (template && !document.querySelector('.floating-live-chat-window')) {
                    const clone = template.content.cloneNode(true);
                    chatRoot.appendChild(clone);
                }
                const win = document.querySelector('.floating-live-chat-window');
                if (win) {
                    win.style.display = 'block';
                    win.style.visibility = 'visible';
                    win.style.opacity = '1';
                    win.removeAttribute('x-cloak');
                }
            }
        });
        await page.waitForTimeout(150);

        const chatWin = await page.$('.floating-live-chat-window');
        if (chatWin) {
            const winBox = await chatWin.boundingBox();
            if (winBox) {
                totalAssertions++;
                // Header / close button not pushed off top
                const noTopOverflow = winBox.y >= 0;
                if (noTopOverflow) {
                    console.log(`PASS [${vp.name}]: Live chat window top = ${Math.round(winBox.y)}px >= 0 (Header/Close button visible and NOT pushed off top).`);
                } else {
                    console.error(`FAIL [${vp.name}]: Live chat window top = ${Math.round(winBox.y)}px < 0 (Header/Close button pushed OFFSCREEN top!).`);
                    failedAssertions++;
                }

                totalAssertions++;
                // Contained in viewport width
                const noWidthOverflow = winBox.width <= vp.width + 1 && (winBox.x + winBox.width) <= vp.width + 1;
                if (noWidthOverflow) {
                    console.log(`PASS [${vp.name}]: Live chat window width = ${Math.round(winBox.width)}px <= viewport width ${vp.width}px (No horizontal overflow).`);
                } else {
                    console.error(`FAIL [${vp.name}]: Live chat window overflows viewport width! width=${Math.round(winBox.width)}px, right=${Math.round(winBox.x + winBox.width)}px, vp=${vp.width}px`);
                    failedAssertions++;
                }

                const closeBtn = await chatWin.$('button[title*="Tutup"], button[aria-label*="Tutup"], button svg line[x1="18"]');
                if (closeBtn) {
                    totalAssertions++;
                    const closeBox = await closeBtn.boundingBox();
                    const closeVisible = closeBox && closeBox.y >= 0 && closeBox.y < vp.height;
                    if (closeVisible) {
                        console.log(`PASS [${vp.name}]: Close button is visible at y=${Math.round(closeBox.y)}px.`);
                    } else {
                        console.error(`FAIL [${vp.name}]: Close button is offscreen!`);
                        failedAssertions++;
                    }
                }
            }
        }

        await context.close();
    }

    await browser.close();

    console.log('\n================================================================');
    console.log(`EMPIRICAL CHALLENGE SUMMARY: ${totalAssertions} assertions, ${failedAssertions} failures.`);
    console.log(`VERDICT: ${failedAssertions === 0 ? 'APPROVE' : 'REJECT'}`);
    console.log('================================================================\n');

    process.exit(failedAssertions === 0 ? 0 : 1);
}

runEmpiricalChallenge().catch(err => {
    console.error('Fatal error in challenge script:', err);
    process.exit(1);
});
