import { chromium } from 'playwright';
import fs from 'fs';

async function testPage(url, pageName) {
    const browser = await chromium.launch({ headless: true });
    
    // Test Light Mode
    {
        const context = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            colorScheme: 'light'
        });
        const page = await context.newPage();
        
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 15000 });
        await page.waitForTimeout(1000);
        await page.evaluate(() => {
            localStorage.setItem('cooca-theme', 'light');
            document.documentElement.classList.remove('dark');
            document.documentElement.setAttribute('data-theme', 'light');
        });
        await page.waitForTimeout(500);
        
        const screenshotPath = `scripts/rendered_${pageName}_light.png`;
        await page.screenshot({ path: screenshotPath, fullPage: true });
        console.log(`Saved ${screenshotPath}`);
        
        // Inspect text & bg colors
        const info = await page.evaluate(() => {
            const bodyBg = window.getComputedStyle(document.body).backgroundColor;
            const h1 = document.querySelector('h1');
            const h1Color = h1 ? window.getComputedStyle(h1).color : null;
            const h2 = document.querySelector('h2');
            const h2Color = h2 ? window.getComputedStyle(h2).color : null;
            const p = document.querySelector('p');
            const pColor = p ? window.getComputedStyle(p).color : null;
            const articleProse = document.querySelector('.article-prose');
            const proseP = articleProse ? articleProse.querySelector('p') : null;
            const proseColor = proseP ? window.getComputedStyle(proseP).color : null;
            return { bodyBg, h1Color, h2Color, pColor, proseColor };
        });
        console.log(`Light Mode [${pageName}]:`, info);
        await context.close();
    }

    // Test Dark Mode
    {
        const context = await browser.newContext({
            viewport: { width: 1440, height: 900 },
            colorScheme: 'dark'
        });
        const page = await context.newPage();
        
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 15000 });
        await page.waitForTimeout(1000);
        await page.evaluate(() => {
            localStorage.setItem('cooca-theme', 'dark');
            document.documentElement.classList.add('dark');
            document.documentElement.setAttribute('data-theme', 'dark');
        });
        await page.waitForTimeout(500);
        
        const screenshotPath = `scripts/rendered_${pageName}_dark.png`;
        await page.screenshot({ path: screenshotPath, fullPage: true });
        console.log(`Saved ${screenshotPath}`);
        
        const info = await page.evaluate(() => {
            const bodyBg = window.getComputedStyle(document.body).backgroundColor;
            const h1 = document.querySelector('h1');
            const h1Color = h1 ? window.getComputedStyle(h1).color : null;
            const h2 = document.querySelector('h2');
            const h2Color = h2 ? window.getComputedStyle(h2).color : null;
            const p = document.querySelector('p');
            const pColor = p ? window.getComputedStyle(p).color : null;
            const articleProse = document.querySelector('.article-prose');
            const proseP = articleProse ? articleProse.querySelector('p') : null;
            const proseColor = proseP ? window.getComputedStyle(proseP).color : null;
            return { bodyBg, h1Color, h2Color, pColor, proseColor };
        });
        console.log(`Dark Mode [${pageName}]:`, info);
        await context.close();
    }

    await browser.close();
}

async function run() {
    console.log("Testing Landing Page...");
    await testPage('http://127.0.0.1:8000/', 'landing');
    console.log("\nTesting Blog Show...");
    await testPage('http://127.0.0.1:8000/blog/cara-menghitung-hpp-kopi', 'blog_show');
}

run().catch(console.error);
