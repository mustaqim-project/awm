import asyncio
import os
from playwright.async_api import async_playwright

async def main():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page(viewport={"width": 1600, "height": 1000})
        html_path = os.path.abspath("scripts/sample_calc.html")
        await page.goto(f"file:///{html_path.replace(os.sep, '/')}", wait_until="networkidle")
        await page.screenshot(path="scripts/sample_calc.png", full_page=False)
        await browser.close()
    print("Screenshot captured to scripts/sample_calc.png")

if __name__ == "__main__":
    asyncio.run(main())
