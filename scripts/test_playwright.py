import asyncio
from playwright.async_api import async_playwright

async def main():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page(viewport={"width": 1600, "height": 1000})
        await page.set_content("<html><body style='background:#0B142F;color:white;display:flex;align-items:center;justify-content:center;height:100vh;'><h1 style='color:#38BDF8;'>COOCA UI RENDER TEST</h1></body></html>")
        await page.screenshot(path="scripts/test_playwright.png")
        await browser.close()
    print("Screenshot saved to scripts/test_playwright.png")

if __name__ == "__main__":
    asyncio.run(main())
