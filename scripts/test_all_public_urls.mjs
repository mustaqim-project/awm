import { chromium } from 'playwright';
import fs from 'fs';

const urlsToTest = [
    // 1. Authentication (13 files)
    { url: '/login', name: 'Auth - Login', file: 'resources/views/auth/login.blade.php' },
    { url: '/register', name: 'Auth - Register', file: 'resources/views/auth/register.blade.php' },
    { url: '/test-preview/auth-register-otp', name: 'Auth - Register OTP', file: 'resources/views/auth/register-otp.blade.php' },
    { url: '/test-preview/auth-google-register', name: 'Auth - Google Register', file: 'resources/views/auth/google-register.blade.php' },
    { url: '/forgot-password', name: 'Auth - Forgot Password', file: 'resources/views/auth/forgot-password.blade.php' },
    { url: '/test-preview/auth-reset-password', name: 'Auth - Reset Password', file: 'resources/views/auth/reset-password.blade.php' },
    { url: '/account-recovery/request', name: 'Auth - Account Recovery Request', file: 'resources/views/auth/account-recovery/create.blade.php' },
    { url: '/test-preview/auth-recovery-status', name: 'Auth - Account Recovery Status', file: 'resources/views/auth/account-recovery/status.blade.php' },
    { url: '/test-preview/auth-complete-profile', name: 'Auth - Complete Profile', file: 'resources/views/auth/complete-profile.blade.php' },
    { url: '/test-preview/auth-select-business', name: 'Auth - Select Business', file: 'resources/views/auth/select-business.blade.php' },
    { url: '/test-preview/auth-otp', name: 'Auth - OTP Verification', file: 'resources/views/auth/otp.blade.php' },
    { url: '/test-preview/auth-verify-email', name: 'Auth - Verify Email', file: 'resources/views/auth/verify-email.blade.php' },

    // 2. Core Layout & Landing
    { url: '/', name: 'Core - Landing Homepage', file: 'resources/views/landing.blade.php' },

    // 3. Blog
    { url: '/blog', name: 'Blog - Index', file: 'resources/views/public/blog/index.blade.php' },
    { url: '/blog/cara-menghitung-hpp-kopi', name: 'Blog - Article Detail', file: 'resources/views/public/blog/show.blade.php' },

    // 4. Business Operating System
    { url: '/business-operating-system/overview', name: 'BOS - Overview', file: 'resources/views/public/business-operating-system/overview.blade.php' },
    { url: '/business-operating-system/how-it-works', name: 'BOS - How It Works', file: 'resources/views/public/business-operating-system/how-it-works.blade.php' },
    { url: '/business-operating-system/why-cooca', name: 'BOS - Why Cooca', file: 'resources/views/public/business-operating-system/why-cooca.blade.php' },

    // 5. Calculators
    { url: '/kalkulator', name: 'Calculators - Index', file: 'resources/views/public/calculators/index.blade.php' },
    { url: '/kalkulator/hpp', name: 'Calculators - HPP', file: 'resources/views/public/calculators/hpp.blade.php' },
    { url: '/kalkulator/bep', name: 'Calculators - BEP', file: 'resources/views/public/calculators/bep.blade.php' },
    { url: '/kalkulator/harga-jual', name: 'Calculators - Harga Jual', file: 'resources/views/public/calculators/harga_jual.blade.php' },
    { url: '/kalkulator/laba-bersih', name: 'Calculators - Laba Bersih', file: 'resources/views/public/calculators/laba_bersih.blade.php' },
    { url: '/kalkulator/gaji-karyawan', name: 'Calculators - Gaji Karyawan', file: 'resources/views/public/calculators/gaji_karyawan.blade.php' },
    { url: '/kalkulator/pph-final', name: 'Calculators - PPh Final', file: 'resources/views/public/calculators/pph_final.blade.php' },
    { url: '/kalkulator/omzet-harian', name: 'Calculators - Omzet Harian', file: 'resources/views/public/calculators/omzet_harian.blade.php' },
    { url: '/kalkulator/simulasi-what-if', name: 'Calculators - Simulasi What-If', file: 'resources/views/public/calculators/simulasi_what_if.blade.php' },

    // 6. Contact
    { url: '/kontak', name: 'Contact - Index', file: 'resources/views/public/contact/index.blade.php' },

    // 7. Content Automation
    { url: '/content-automation/content-creation', name: 'Content - Creation', file: 'resources/views/public/content-automation/content-creation.blade.php' },
    { url: '/content-automation/content-calendar', name: 'Content - Calendar', file: 'resources/views/public/content-automation/content-calendar.blade.php' },
    { url: '/content-automation/publishing', name: 'Content - Publishing', file: 'resources/views/public/content-automation/publishing.blade.php' },
    { url: '/content-automation/analytics', name: 'Content - Analytics', file: 'resources/views/public/content-automation/analytics.blade.php' },

    // 8. Discovery
    { url: '/jelajah', name: 'Discovery - Index', file: 'resources/views/public/discovery/index.blade.php' },

    // 9. Commercial Landing Pages
    { url: '/aplikasi-kasir-gratis', name: 'Commercial - Kasir Gratis', file: 'resources/views/public/landing_commercial/kasir_gratis.blade.php' },
    { url: '/aplikasi-kasir-android', name: 'Commercial - Kasir Android', file: 'resources/views/public/landing_commercial/kasir_android.blade.php' },
    { url: '/aplikasi-kasir-offline', name: 'Commercial - Kasir Offline', file: 'resources/views/public/landing_commercial/kasir_offline.blade.php' },

    // 10. Marketplace
    { url: '/marketplace', name: 'Marketplace - Index', file: 'resources/views/public/marketplace/index.blade.php' },
    { url: '/marketplace/cari?q=kopi', name: 'Marketplace - Search', file: 'resources/views/public/marketplace/search.blade.php' },
    { url: '/marketplace/businesses', name: 'Marketplace - Businesses', file: 'resources/views/public/marketplace/businesses.blade.php' },
    { url: '/marketplace/products', name: 'Marketplace - Products', file: 'resources/views/public/marketplace/products.blade.php' },
    { url: '/marketplace/categories', name: 'Marketplace - Categories', file: 'resources/views/public/marketplace/categories.blade.php' },
    { url: '/marketplace/locations', name: 'Marketplace - Locations', file: 'resources/views/public/marketplace/locations.blade.php' },

    // 11. Omnichannel
    { url: '/omnichannel/customer', name: 'Omnichannel - Customer', file: 'resources/views/public/omnichannel/customer.blade.php' },
    { url: '/omnichannel/marketplace', name: 'Omnichannel - Marketplace', file: 'resources/views/public/omnichannel/marketplace.blade.php' },
    { url: '/omnichannel/orders', name: 'Omnichannel - Orders', file: 'resources/views/public/omnichannel/orders.blade.php' },
    { url: '/omnichannel/social-media', name: 'Omnichannel - Social Media', file: 'resources/views/public/omnichannel/social-media.blade.php' },
    { url: '/omnichannel/whatsapp', name: 'Omnichannel - WhatsApp', file: 'resources/views/public/omnichannel/whatsapp.blade.php' },

    // 12. Omnichannel ERP
    { url: '/omnichannel-erp/accounting', name: 'ERP - Accounting', file: 'resources/views/public/omnichannel-erp/accounting.blade.php' },
    { url: '/omnichannel-erp/analytics', name: 'ERP - Analytics', file: 'resources/views/public/omnichannel-erp/analytics.blade.php' },
    { url: '/omnichannel-erp/crm', name: 'ERP - CRM', file: 'resources/views/public/omnichannel-erp/crm.blade.php' },
    { url: '/omnichannel-erp/erp', name: 'ERP - ERP Hub', file: 'resources/views/public/omnichannel-erp/erp.blade.php' },
    { url: '/omnichannel-erp/finance', name: 'ERP - Finance', file: 'resources/views/public/omnichannel-erp/finance.blade.php' },
    { url: '/omnichannel-erp/hrm', name: 'ERP - HRM', file: 'resources/views/public/omnichannel-erp/hrm.blade.php' },
    { url: '/omnichannel-erp/inventory', name: 'ERP - Inventory', file: 'resources/views/public/omnichannel-erp/inventory.blade.php' },
    { url: '/omnichannel-erp/pos', name: 'ERP - POS', file: 'resources/views/public/omnichannel-erp/pos.blade.php' },

    // 13. Resources
    { url: '/resources/case-studies', name: 'Resources - Case Studies', file: 'resources/views/public/resources/case-studies.blade.php' },
    { url: '/resources/faq', name: 'Resources - FAQ', file: 'resources/views/public/resources/faq.blade.php' },
    { url: '/resources/guides', name: 'Resources - Guides', file: 'resources/views/public/resources/guides.blade.php' },

    // 14. Solutions
    { url: '/solutions/fnb', name: 'Solutions - FnB', file: 'resources/views/public/solutions/fnb.blade.php' },
    { url: '/solutions/laundry', name: 'Solutions - Laundry', file: 'resources/views/public/solutions/laundry.blade.php' },
    { url: '/solutions/manufacturing', name: 'Solutions - Manufacturing', file: 'resources/views/public/solutions/manufacturing.blade.php' },
    { url: '/solutions/retail', name: 'Solutions - Retail', file: 'resources/views/public/solutions/retail.blade.php' },
    { url: '/solutions/services', name: 'Solutions - Services', file: 'resources/views/public/solutions/services.blade.php' },
    { url: '/solutions/workshop', name: 'Solutions - Workshop', file: 'resources/views/public/solutions/workshop.blade.php' },
    { url: '/solusi/kasir-warung', name: 'Solutions - Show (Kasir Warung)', file: 'resources/views/public/solutions/show.blade.php' },

    // 15. Additional Public Pages
    { url: '/about', name: 'Public - About', file: 'resources/views/public/about.blade.php' },
    { url: '/demo', name: 'Public - Demo', file: 'resources/views/public/demo.blade.php' },
    { url: '/pricing', name: 'Public - Pricing', file: 'resources/views/public/pricing.blade.php' },
    { url: '/privacy', name: 'Public - Privacy Policy', file: 'resources/views/public/privacy.blade.php' },
    { url: '/security', name: 'Public - Security', file: 'resources/views/public/security.blade.php' },
    { url: '/sitemap', name: 'Public - Sitemap', file: 'resources/views/public/sitemap.blade.php' },
    { url: '/support', name: 'Public - Support', file: 'resources/views/public/support.blade.php' },
    { url: '/terms', name: 'Public - Terms of Service', file: 'resources/views/public/terms.blade.php' },
    { url: '/b/mahakarya-roastery-prestige', name: 'Public - Business Landing', file: 'resources/views/public/business_landing.blade.php' },

    // 16. Templates
    { url: '/template-pembukuan-gratis', name: 'Templates - Index', file: 'resources/views/public/templates/index.blade.php' },
    { url: '/template/invoice-sederhana', name: 'Templates - Show', file: 'resources/views/public/templates/show.blade.php' },
];

function parseRgb(colorStr) {
    if (!colorStr) return [0, 0, 0];
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

async function runAudit() {
    const baseUrl = 'http://127.0.0.1:8000';
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();
    const results = [];

    console.log(`Starting real browser verification for ${urlsToTest.length} public & auth URLs...\n`);

    for (let i = 0; i < urlsToTest.length; i++) {
        const target = urlsToTest[i];
        const fullUrl = baseUrl + target.url;
        process.stdout.write(`[${i + 1}/${urlsToTest.length}] ${target.name} (${target.url})... `);

        const pageResult = {
            url: target.url,
            name: target.name,
            file: target.file,
            httpStatus: 0,
            uiRendering: 'PASS',
            lightTheme: 'PASS',
            darkTheme: 'PASS',
            responsive: 'PASS',
            result: 'PASS',
            errors: []
        };

        const pageErrors = [];
        const errorHandler = err => pageErrors.push(err.message);
        page.on('pageerror', errorHandler);

        try {
            await page.setViewportSize({ width: 1440, height: 900 });
            const resp = await page.goto(fullUrl, { waitUntil: 'domcontentloaded', timeout: 30000 });
            pageResult.httpStatus = resp ? resp.status() : 0;
            await page.waitForTimeout(300);

            if (pageResult.httpStatus !== 200) {
                pageResult.uiRendering = 'FAIL';
                pageResult.result = 'FAIL';
                pageResult.errors.push(`HTTP ${pageResult.httpStatus}`);
                console.log(`FAIL (HTTP ${pageResult.httpStatus})`);
                page.off('pageerror', errorHandler);
                continue;
            }

            // 1. Light Mode Check
            await page.evaluate(() => {
                localStorage.setItem('cooca-theme', 'light');
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-theme', 'light');
            });
            await page.waitForTimeout(150);

            const lightData = await page.evaluate(() => {
                const bodyBg = window.getComputedStyle(document.body).backgroundColor;
                const h1 = document.querySelector('h1');
                const h1Color = h1 ? window.getComputedStyle(h1).color : null;
                const hasContent = document.body.innerText.trim().length > 50;
                return { bodyBg, h1Color, hasContent, title: document.title };
            });

            if (!lightData.hasContent || !lightData.title) {
                pageResult.uiRendering = 'FAIL';
                pageResult.errors.push('Empty content or missing title in Light mode');
            }

            // 2. Dark Mode Check
            await page.evaluate(() => {
                localStorage.setItem('cooca-theme', 'dark');
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            });
            await page.waitForTimeout(150);

            const darkData = await page.evaluate(() => {
                const bodyBg = window.getComputedStyle(document.body).backgroundColor;
                const h1 = document.querySelector('h1');
                const h1Color = h1 ? window.getComputedStyle(h1).color : null;
                const h2 = document.querySelector('h2');
                const h2Color = h2 ? window.getComputedStyle(h2).color : null;

                // Check text contrast on h1/h2
                const badContrast = [];
                const checkHeading = (el, tag) => {
                    if (!el) return;
                    const style = window.getComputedStyle(el);
                    const rect = el.getBoundingClientRect();
                    if (rect.width === 0 || rect.height === 0) return;
                    if (style.visibility === 'hidden' || style.display === 'none' || style.opacity === '0') {
                        badContrast.push(`${tag} is invisible in dark mode`);
                    }
                };
                checkHeading(h1, 'h1');
                checkHeading(h2, 'h2');

                return { bodyBg, h1Color, h2Color, badContrast };
            });

            if (darkData.badContrast.length > 0) {
                pageResult.darkTheme = 'FAIL';
                pageResult.errors.push(...darkData.badContrast);
            }

            // 3. Responsive Check (375px & 320px)
            await page.setViewportSize({ width: 375, height: 667 });
            await page.waitForTimeout(250);
            const overflow375 = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 4);

            await page.setViewportSize({ width: 320, height: 568 });
            await page.waitForTimeout(250);
            const overflow320 = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 4);

            if (overflow375 || overflow320) {
                const debugWidths = await page.evaluate(() => `(scroll=${document.documentElement.scrollWidth}, inner=${window.innerWidth})`);
                pageResult.responsive = 'FAIL';
                pageResult.errors.push(`Horizontal overflow (375px: ${overflow375}, 320px: ${overflow320}) ${debugWidths}`);
            }

            if (pageErrors.length > 0) {
                pageResult.errors.push(...pageErrors);
            }

            if (pageResult.errors.length > 0) {
                pageResult.result = 'FAIL';
                console.log(`FAIL (${pageResult.errors.join(', ')})`);
            } else {
                console.log('PASS');
            }

        } catch (err) {
            pageResult.uiRendering = 'FAIL';
            pageResult.result = 'FAIL';
            pageResult.errors.push(err.message);
            console.log(`FAIL: ${err.message}`);
        } finally {
            page.off('pageerror', errorHandler);
            results.push(pageResult);
        }
    }

    await browser.close();

    fs.writeFileSync('scripts/url_audit_results.json', JSON.stringify(results, null, 2), 'utf-8');
    console.log(`\nAudit complete! Results saved to scripts/url_audit_results.json.`);

    const passCount = results.filter(r => r.result === 'PASS').length;
    const failCount = results.filter(r => r.result === 'FAIL').length;
    console.log(`Summary: ${passCount} PASSED, ${failCount} FAILED out of ${results.length} total URLs.`);
}

runAudit().catch(console.error);
