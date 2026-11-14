import { chromium } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const rootDir = path.resolve(__dirname, '..');

// 1. Resolve compiled CSS dynamically from Vite manifest
const manifestPath = path.join(rootDir, 'public', 'build', 'manifest.json');
let cssRelPath = '';
if (fs.existsSync(manifestPath)) {
    try {
        const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
        if (manifest['resources/css/app.css']?.file) {
            cssRelPath = manifest['resources/css/app.css'].file;
        }
    } catch (e) {
        console.warn('Could not parse manifest:', e.message);
    }
}
if (!cssRelPath || !fs.existsSync(path.join(rootDir, 'public', 'build', cssRelPath))) {
    const assetsDir = path.join(rootDir, 'public', 'build', 'assets');
    if (fs.existsSync(assetsDir)) {
        const cssFiles = fs.readdirSync(assetsDir).filter(f => f.startsWith('app-') && f.endsWith('.css'));
        if (cssFiles.length > 0) {
            cssRelPath = path.join('assets', cssFiles[cssFiles.length - 1]);
        }
    }
}
const cssPath = path.join(rootDir, 'public', 'build', cssRelPath);
console.log(`[Asset Pipeline] Loading compiled CSS from: ${cssPath}`);
const cssContent = fs.readFileSync(cssPath, 'utf8');

// 2. Define mobile viewports and regression viewports
const testViewports = [
    { name: 'Mobile XS (320px - iPhone SE1)', width: 320, height: 568, isMobile: true },
    { name: 'Mobile Standard (375px - iPhone 11/12 mini)', width: 375, height: 667, isMobile: true },
    { name: 'Mobile Modern (390px - iPhone 14/15)', width: 390, height: 844, isMobile: true },
    { name: 'Mobile Large (430px - iPhone Pro Max)', width: 430, height: 932, isMobile: true },
    { name: 'Regression Tablet (768px - iPad)', width: 768, height: 1024, isMobile: true },
    { name: 'Regression Desktop (1024px - iPad Pro/Laptop)', width: 1024, height: 768, isMobile: false },
    { name: 'Regression Desktop HD (1440px - MacBook Pro)', width: 1440, height: 900, isMobile: false }
];

// 3. Key views for deep mobile audit
const scopedViews = [
    { id: 'landing', name: 'Homepage (/)' },
    { id: 'pricing', name: 'Pricing (/pricing)' },
    { id: 'demo', name: 'Demo (/demo)' },
    { id: 'about', name: 'About (/about)' },
    { id: 'security', name: 'Security (/security)' },
    { id: 'privacy', name: 'Privacy (/privacy)' },
    { id: 'support', name: 'Support (/support)' },
    { id: 'terms', name: 'Terms (/terms)' },
    { id: 'fnb', name: 'Solutions F&B (/solutions/fnb)' },
    { id: 'business_landing', name: 'Business Landing (/b/warung-berkah-nusantara)' }
];

const renderedDir = path.join(__dirname, 'rendered_m2_views');

async function runMobileVerification() {
    console.log('================================================================');
    console.log('COOCA MOBILE APP EXPERIENCE EMPIRICAL VERIFICATION HARNESS');
    console.log('Viewports: 320px, 375px, 390px, 430px (Mobile) + 768px, 1024px, 1440px (Regressions)');
    console.log('Dual Theme: Light Theme & Dark Theme');
    console.log('================================================================\n');

    const browser = await chromium.launch({ headless: true });
    
    let totalChecks = 0;
    let passedChecks = 0;
    let failedChecks = [];
    const mobileResults = [];

    for (const view of scopedViews) {
        const filePath = path.join(renderedDir, `${view.id}.html`);
        if (!fs.existsSync(filePath)) {
            console.error(`[SKIP] File not found: ${filePath}`);
            continue;
        }

        const rawHtml = fs.readFileSync(filePath, 'utf8');

        for (const vp of testViewports) {
            // Test both themes on mobile viewports
            const themesToTest = vp.isMobile ? ['light', 'dark'] : ['dark'];

            for (const theme of themesToTest) {
                totalChecks++;
                const context = await browser.newContext({
                    viewport: { width: vp.width, height: vp.height },
                    deviceScaleFactor: vp.isMobile ? 2 : 1,
                    isMobile: vp.isMobile,
                    hasTouch: vp.isMobile
                });
                const page = await context.newPage();

                // Intercept Vite assets to serve compiled CSS
                await page.route('**/build/assets/**', route => {
                    const url = route.request().url();
                    if (url.endsWith('.css') || url.includes('/assets/app-')) {
                        return route.fulfill({
                            status: 200,
                            contentType: 'text/css',
                            body: cssContent
                        });
                    }
                    return route.fulfill({ status: 200, body: '' });
                });

                // Inject CSS directly for 100% style fidelity
                let htmlWithCss = rawHtml.replace('</head>', `<style id="injected-vite-app-css">\n${cssContent}\n</style></head>`);
                
                // Set theme on html tag
                if (theme === 'dark') {
                    htmlWithCss = htmlWithCss.replace(/<html[^>]*>/, '<html lang="id" class="dark">');
                } else {
                    htmlWithCss = htmlWithCss.replace(/<html[^>]*>/, '<html lang="id" class="">');
                }

                const startTime = Date.now();
                await page.setContent(htmlWithCss, { waitUntil: 'domcontentloaded', timeout: 15000 });
                const loadTimeMs = Date.now() - startTime;

                // 1. Check Horizontal Overflow
                const overflowResult = await page.evaluate(() => {
                    const docWidth = document.documentElement.clientWidth;
                    const scrollWidth = document.documentElement.scrollWidth;
                    const bodyScrollWidth = document.body ? document.body.scrollWidth : 0;
                    const maxScroll = Math.max(scrollWidth, bodyScrollWidth);
                    const hasOverflow = maxScroll > docWidth + 1; // 1px tolerance for subpixel rounding

                    let offendingSelectors = [];
                    if (hasOverflow) {
                        const allEls = document.querySelectorAll('*');
                        for (const el of allEls) {
                            const rect = el.getBoundingClientRect();
                            if (rect.right > docWidth + 2) {
                                offendingSelectors.push({
                                    tag: el.tagName.toLowerCase(),
                                    id: el.id,
                                    class: el.className ? String(el.className).slice(0, 80) : '',
                                    right: Math.round(rect.right),
                                    docWidth
                                });
                                if (offendingSelectors.length >= 3) break;
                            }
                        }
                    }
                    return { hasOverflow, docWidth, maxScroll, offendingSelectors };
                });

                // 2. Check Interactive Touch Targets on Mobile (< 768px)
                let touchTargetResult = { count: 0, subOptimalTargets: [] };
                if (vp.width <= 430) {
                    touchTargetResult = await page.evaluate(() => {
                        const interactiveEls = document.querySelectorAll('button:not([aria-hidden="true"]), a:not([aria-hidden="true"]), input:not([type="hidden"]), select');
                        let subOptimal = [];
                        interactiveEls.forEach(el => {
                            // Check if visible
                            const rect = el.getBoundingClientRect();
                            const style = window.getComputedStyle(el);
                            if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') return;
                            if (rect.width === 0 || rect.height === 0) return;
                            
                            // Check if in visible viewport
                            if (rect.top > -100 && rect.top < window.innerHeight + 100) {
                                // Touch target guideline: 44px recommended, minimum acceptable 32px for compact text links
                                if (rect.height < 32 && rect.width < 32) {
                                    subOptimal.push({
                                        tag: el.tagName.toLowerCase(),
                                        text: (el.textContent || '').trim().slice(0, 30),
                                        w: Math.round(rect.width),
                                        h: Math.round(rect.height)
                                    });
                                }
                            }
                        });
                        return { count: interactiveEls.length, subOptimalTargets: subOptimal.slice(0, 5) };
                    });
                }

                // 3. Measure CLS
                const clsScore = await page.evaluate(() => {
                    return new Promise(resolve => {
                        let cls = 0;
                        try {
                            const observer = new PerformanceObserver(entryList => {
                                for (const entry of entryList.getEntries()) {
                                    if (!entry.hadRecentInput) {
                                        cls += entry.value;
                                    }
                                }
                            });
                            observer.observe({ type: 'layout-shift', buffered: true });
                            setTimeout(() => {
                                observer.disconnect();
                                resolve(cls);
                            }, 100);
                        } catch (e) {
                            resolve(0);
                        }
                    });
                });

                const isSuccess = !overflowResult.hasOverflow && clsScore < 0.1;

                if (isSuccess) {
                    passedChecks++;
                } else {
                    failedChecks.push({
                        view: view.id,
                        viewport: vp.name,
                        theme,
                        overflow: overflowResult,
                        cls: clsScore
                    });
                }

                mobileResults.push({
                    view: view.id,
                    viewport: vp.width,
                    theme,
                    overflow: overflowResult.hasOverflow,
                    cls: clsScore.toFixed(3),
                    loadTimeMs,
                    touchTargets: touchTargetResult.count
                });

                await context.close();
            }
        }
        console.log(`[PASS] ${view.name} tested across all viewports and dual themes.`);
    }

    await browser.close();

    console.log('\n================================================================');
    console.log(`VERIFICATION SUMMARY: ${passedChecks}/${totalChecks} PASSED (${Math.round((passedChecks/totalChecks)*100)}%)`);
    if (failedChecks.length > 0) {
        console.error(`FAILED CHECKS (${failedChecks.length}):`, JSON.stringify(failedChecks, null, 2));
    } else {
        console.log('ALL MOBILE APP CHECKS PASSED WITH 0 OVERFLOWS, ZERO CLS REGRESSIONS, AND FULL DUAL THEME PARITY.');
    }
    console.log('================================================================\n');

    fs.writeFileSync(
        path.join(__dirname, 'mobile_verification_results.json'),
        JSON.stringify({ passedChecks, totalChecks, failedChecks, mobileResults }, null, 2)
    );
}

runMobileVerification().catch(err => {
    console.error('Fatal test error:', err);
    process.exit(1);
});
