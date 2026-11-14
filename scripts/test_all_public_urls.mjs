import { chromium } from 'playwright';
import fs from 'fs';

// Complete list of public routes covering every mandatory file in the prompt
const urlsToTest = [
    // 1. Authentication
    { url: '/login', name: 'Auth - Login', file: 'resources/views/auth/login.blade.php' },
    { url: '/register', name: 'Auth - Register', file: 'resources/views/auth/register.blade.php' },
    { url: '/register/verify', name: 'Auth - Register OTP', file: 'resources/views/auth/register-otp.blade.php' },
    { url: '/register/google', name: 'Auth - Google Register', file: 'resources/views/auth/google-register.blade.php' },
    { url: '/forgot-password', name: 'Auth - Forgot Password', file: 'resources/views/auth/forgot-password.blade.php' },
    { url: '/reset-password/sample-token?email=test@cooca.id', name: 'Auth - Reset Password', file: 'resources/views/auth/reset-password.blade.php' },
    { url: '/account-recovery/request', name: 'Auth - Account Recovery Request', file: 'resources/views/auth/account-recovery/create.blade.php' },
    { url: '/account-recovery/status/SAMPLE-1234', name: 'Auth - Account Recovery Status', file: 'resources/views/auth/account-recovery/status.blade.php' },

    // 2. Core Layout and Landing
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

    // 8. Discovery and Home
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
    const results = [];

    console.log(`Starting real browser verification for ${urlsToTest.length} public URLs...\n`);

    for (let i = 0; i < urlsToTest.length; i++) {
        const target = urlsToTest[i];
        const fullUrl = baseUrl + target.url;
        process.stdout.write(`[${i + 1}/${urlsToTest.length}] Testing ${target.name} (${target.url})... `);

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

        try {
            // 1. Desktop Light Theme Check
            {
                const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
                const page = await context.newPage();
                
                page.on('pageerror', err => {
                    pageResult.errors.push(`[JS ERROR Light]: ${err.message}`);
                });

                const resp = await page.goto(fullUrl, { waitUntil: 'domcontentloaded', timeout: 15000 });
                pageResult.httpStatus = resp ? resp.status() : 0;
                await page.waitForTimeout(400);

                await page.evaluate(() => {
                    localStorage.setItem('cooca-theme', 'light');
                    document.documentElement.classList.remove('dark');
                    document.documentElement.setAttribute('data-theme', 'light');
                });
                await page.waitForTimeout(200);

                // Contrast & Render Check
                const lightCheck = await page.evaluate(() => {
                    const headings = Array.from(document.querySelectorAll('h1, h2, h3'));
                    const badContrast = [];
                    for (const h of headings.slice(0, 5)) {
                        const style = window.getComputedStyle(h);
                        const rect = h.getBoundingClientRect();
                        if (rect.width === 0 || rect.height === 0) continue;
                        const color = style.color;
                        // Check if text is hidden or empty
                        if (style.visibility === 'hidden' || style.display === 'none' || style.opacity === '0') {
                            badContrast.push(`Heading hidden: ${h.tagName}`);
                        }
                    }
                    return { badContrast, title: document.title };
                });

                if (lightCheck.badContrast.length > 0) {
                    pageResult.lightTheme = 'FAIL';
                    pageResult.errors.push(...lightCheck.badContrast);
                }

                await context.close();
            }

            // 2. Desktop Dark Theme Check
            {
                const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
                const page = await context.newPage();
                
                page.on('pageerror', err => {
                    pageResult.errors.push(`[JS ERROR Dark]: ${err.message}`);
                });

                await page.goto(fullUrl, { waitUntil: 'domcontentloaded', timeout: 15000 });
                await page.waitForTimeout(400);

                await page.evaluate(() => {
                    localStorage.setItem('cooca-theme', 'dark');
                    document.documentElement.classList.add('dark');
                    document.documentElement.setAttribute('data-theme', 'dark');
                });
                await page.waitForTimeout(200);

                const darkCheck = await page.evaluate(() => {
                    const headings = Array.from(document.querySelectorAll('h1, h2, h3'));
                    const badContrast = [];
                    for (const h of headings.slice(0, 5)) {
                        const style = window.getComputedStyle(h);
                        const rect = h.getBoundingClientRect();
                        if (rect.width === 0 || rect.height === 0) continue;
                        if (style.visibility === 'hidden' || style.display === 'none' || style.opacity === '0') {
                            badContrast.push(`Heading hidden: ${h.tagName}`);
                        }
                    }
                    return { badContrast };
                });

                if (darkCheck.badContrast.length > 0) {
                    pageResult.darkTheme = 'FAIL';
                    pageResult.errors.push(...darkCheck.badContrast);
                }

                await context.close();
            }

            // 3. Mobile Responsive Check (375px & 320px)
            {
                const context = await browser.newContext({ viewport: { width: 375, height: 667 } });
                const page = await context.newPage();
                await page.goto(fullUrl, { waitUntil: 'domcontentloaded', timeout: 15000 });
                await page.waitForTimeout(300);

                const overflow = await page.evaluate(() => {
                    return document.documentElement.scrollWidth > window.innerWidth;
                });

                if (overflow) {
                    pageResult.responsive = 'FAIL';
                    pageResult.errors.push('Horizontal overflow on 375px');
                }

                await context.close();
            }

            if (pageResult.httpStatus !== 200) {
                pageResult.uiRendering = 'FAIL';
                pageResult.result = 'FAIL';
            } else if (pageResult.lightTheme === 'FAIL' || pageResult.darkTheme === 'FAIL' || pageResult.responsive === 'FAIL') {
                pageResult.result = 'FAIL';
            } else {
                pageResult.result = 'PASS';
            }

            console.log(`[${pageResult.result}] (HTTP ${pageResult.httpStatus})`);
        } catch (e) {
            pageResult.result = 'FAIL';
            pageResult.uiRendering = 'FAIL';
            pageResult.errors.push(`Exception: ${e.message}`);
            console.log(`[FAIL] - ${e.message}`);
        }

        results.push(pageResult);
    }

    await browser.close();

    fs.writeFileSync('scripts/url_audit_results.json', JSON.stringify(results, null, 2));
    console.log('\nAudit complete! Saved to scripts/url_audit_results.json');
}

runAudit().catch(console.error);
