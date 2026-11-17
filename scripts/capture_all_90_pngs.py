import os
import json
import asyncio
import time
from playwright.async_api import async_playwright
from PIL import Image

BASE_DIR = r"c:\laragon\www\cooca_core"
HTML_DIR = os.path.join(BASE_DIR, "storage", "app", "public", "features_rendered_html")
TARGET_DIR = os.path.join(BASE_DIR, "documentation", "feature-ui-images")
JSON_PATH = os.path.join(BASE_DIR, "scripts", "final_verified_90_features.json")

with open(JSON_PATH, "r", encoding="utf-8") as f:
    features = json.load(f)

# Features that require mobile viewport (9:19.5, 393x852)
MOBILE_FEATURE_IDS = {81, 87} # J-03 (presensi-gps-geofencing), L-01 (dashboard-eksekutif-real-time-ponsel)

async def capture_feature(page, feat, index, total):
    feat_id = feat["id"]
    filename = feat["filename"]
    cat_name = feat["category_name"]
    out_dir = os.path.join(TARGET_DIR, cat_name)
    os.makedirs(out_dir, exist_ok=True)
    out_png_path = os.path.join(out_dir, filename)

    html_file = os.path.join(HTML_DIR, f"{feat_id:02d}_{os.path.splitext(filename)[0]}.html")
    if not os.path.exists(html_file):
        raise FileNotFoundError(f"HTML file not found: {html_file}")

    is_mobile = feat_id in MOBILE_FEATURE_IDS
    viewport = {"width": 393, "height": 852} if is_mobile else {"width": 1600, "height": 1000}
    await page.set_viewport_size(viewport)

    file_url = f"file:///{os.path.abspath(html_file).replace(os.sep, '/')}"
    await page.goto(file_url, wait_until="load")
    
    # Wait for Alpine and icons to render
    await page.wait_for_timeout(400)

    # Specific DOM adjustments per feature if needed
    if feat_id == 2: # A-02 search
        await page.evaluate("""() => {
            const el = document.querySelector('#pos-app') || document.querySelector('[x-data*="posApp"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) {
                    d.searchQuery = 'Ayam';
                    if (typeof d.filterProducts === 'function') d.filterProducts();
                }
            }
            const inp = document.querySelector('input[placeholder*="Scan"], input[placeholder*="Cari"], input[x-model="searchQuery"]');
            if (inp) {
                inp.value = 'Ayam';
                inp.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }""")
        await page.wait_for_timeout(500)
    elif feat_id == 3: # A-03 payment modal
        await page.evaluate("""() => {
            const el = document.querySelector('#pos-app') || document.querySelector('[x-data*="posApp"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) d.showPaymentModal = true;
            }
        }""")
        await page.wait_for_timeout(200)
    elif feat_id == 4: # A-04 held orders
        await page.evaluate("""() => {
            const el = document.querySelector('#pos-app') || document.querySelector('[x-data*="posApp"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) d.showHeldOrdersModal = true;
            }
        }""")
        await page.wait_for_timeout(200)
    elif feat_id == 6: # A-06 Shift Close & Balancing
        await page.evaluate("""() => {
            const el = document.querySelector('[x-data*="showCloseModal"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) {
                    d.expectedCash = 1750000;
                    d.actualCash = 1750000;
                    d.closeDenoms = {
                        '100000': 15, '50000': 4, '20000': 2, '10000': 1,
                        '5000': 0, '2000': 0, '1000': 0, 'coins': 0
                    };
                    d.closeUseDenoms = true;
                    d.cashierNotes = 'Rekonsiliasi kas harian shift pagi klop sesuai fisik laci kasir.';
                    d.showCloseModal = true;
                }
            }
        }""")
        await page.wait_for_timeout(500)
    elif feat_id == 10: # A-10 KDS timers & workflow
        await page.evaluate("""() => {
            const el = document.querySelector('[x-data*="kitchenDisplay"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) {
                    d.orders = [
                        {
                            id: 'kds-01',
                            order_number: 'ORD-101',
                            pos_table: { table_number: '05' },
                            sales_channel: 'dine_in',
                            customer_name_guest: 'Meja 05 (4 Pax)',
                            status: 'preparing',
                            created_at: new Date(Date.now() - 14 * 60000).toISOString(),
                            notes: 'Ayam bakar pedas manis, es teh manis',
                            items: [
                                { id: 1, product_name: 'Ayam Bakar Madu', quantity: 2, notes: 'Pedas manis', modifiers: [] },
                                { id: 2, product_name: 'Es Teh Manis', quantity: 4, notes: 'Gula sedikit', modifiers: [] }
                            ]
                        },
                        {
                            id: 'kds-02',
                            order_number: 'ORD-102',
                            pos_table: null,
                            sales_channel: 'grabfood',
                            external_order_ref: 'GB-9921',
                            customer_name_guest: 'Driver GrabFood (Joko)',
                            status: 'ready',
                            created_at: new Date(Date.now() - 28 * 60000).toISOString(),
                            notes: 'Packing rapi kuah pisah',
                            items: [
                                { id: 3, product_name: 'Nasi Goreng Spesial', quantity: 1, modifiers: [] }
                            ]
                        }
                    ];
                    if (window.lucide) window.lucide.createIcons();
                }
            }
        }""")
        await page.wait_for_timeout(500)
    elif feat_id == 13: # A-13 split bill
        await page.evaluate("""() => {
            const el = document.querySelector('#pos-app') || document.querySelector('[x-data*="posApp"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) d.showTablePaymentModal = true;
            }
        }""")
        await page.wait_for_timeout(200)
    elif feat_id == 15: # A-15 SPK / Add Service Modal
        await page.evaluate("""() => {
            const el = document.querySelector('[x-data*="showAddModal"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) {
                    d.addForm.name = 'Servis Ringan & Tune-Up Transmisi';
                    d.addForm.code = 'SPK-SRV-2026';
                    d.addForm.selling_price = '175000';
                    d.addForm.description = 'Pemeriksaan oli transmisi, busi, dan filter udara kendaraan.';
                    d.showAddModal = true;
                }
            }
        }""")
        await page.wait_for_timeout(500)
    elif feat_id == 16: # A-16 integrated service & sparepart cart
        await page.evaluate("""() => {
            const el = document.querySelector('#pos-app') || document.querySelector('[x-data*="posApp"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d && d.cart) {
                    d.cart = [
                        {
                            product_id: 'srv-01',
                            product_name: 'Jasa Servis Berkala & Tune Up Motor',
                            unit_price: 150000,
                            quantity: 1,
                            unit_symbol: 'Paket',
                            discount_amount: 0,
                            selected_modifiers: []
                        },
                        {
                            product_id: 'spr-01',
                            product_name: 'Oli Mesin Matic 10W-30 0.8L (Sparepart)',
                            unit_price: 65000,
                            quantity: 1,
                            unit_symbol: 'Botol',
                            discount_amount: 0,
                            selected_modifiers: []
                        },
                        {
                            product_id: 'spr-02',
                            product_name: 'Busi Racing Iridium (Sparepart)',
                            unit_price: 45000,
                            quantity: 1,
                            unit_symbol: 'Pcs',
                            discount_amount: 0,
                            selected_modifiers: []
                        }
                    ];
                    d.customerSearchQuery = 'Budi Santoso - Honda Vario B 3456 KFG';
                }
            }
        }""")
        await page.wait_for_timeout(500)
    elif feat_id == 17: # B-01 pin supervisor
        await page.evaluate("""() => {
            const el = document.querySelector('#pos-app') || document.querySelector('[x-data*="posApp"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) d.showSupervisorPinModal = true;
            }
        }""")
        await page.wait_for_timeout(200)
    elif feat_id == 20: # B-04 Opname variance approval modal
        await page.evaluate("""() => {
            const el = document.querySelector('[x-data*="approvalInbox"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) {
                    d.openApproveModal('tk-opname-01', 'Persetujuan Selisih Varian Stock Opname Gudang (-20 Pcs)', 'Rp 2.450.000', 1);
                }
            }
        }""")
        await page.wait_for_timeout(500)
    elif feat_id == 23: # B-07 matrix tab
        await page.evaluate("""() => {
            const el = document.querySelector('[x-data*="activeTab"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) d.activeTab = 'matrix';
            }
        }""")
        await page.wait_for_timeout(500)
    elif feat_id == 27: # C-04 labor cost tab
        await page.evaluate("""() => {
            const el = document.querySelector('[x-data*="activeSection"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) d.activeSection = 'labor';
            }
        }""")
        await page.wait_for_timeout(500)
    elif feat_id == 28: # C-05 machine depreciation tab
        await page.evaluate("""() => {
            const el = document.querySelector('[x-data*="activeSection"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) d.activeSection = 'machines';
            }
        }""")
        await page.wait_for_timeout(500)
    elif feat_id == 31: # C-08 raw material price shock simulation
        await page.evaluate("""() => {
            const el = document.querySelector('[x-data*="simulatorComponent"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) {
                    d.matChange = 35;
                    d.labChange = 0;
                    d.macChange = 0;
                    d.ovhChange = 0;
                    d.markupPct = 25;
                    if (typeof d.runSimulation === 'function') d.runSimulation();
                }
            }
        }""")
        await page.wait_for_timeout(500)
    elif feat_id == 54: # F-02 transfer modal
        await page.evaluate("""() => {
            const el = document.querySelector('[x-data*="showTransferModal"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) d.showTransferModal = true;
            }
        }""")
        await page.wait_for_timeout(500)
    elif feat_id == 66: # G-04 shipping rates test
        await page.evaluate("""() => {
            const el = document.querySelector('[x-data*="isTestingRate"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) {
                    d.testPostalCode = '12950';
                    d.testWeight = 1200;
                    d.testValue = 150000;
                    d.testResult = [
                        { id: 'jne_reg', description: 'JNE - Reguler (CTC)', duration: '1-2 Hari', price: 10000 },
                        { id: 'jnt_ez', description: 'J&T Express - EZ Regular', duration: '1-2 Hari', price: 11000 },
                        { id: 'sicepat_siuntung', description: 'SiCepat - SIUNTUNG Regular', duration: '1 Hari', price: 9500 },
                        { id: 'gosend_instant', description: 'GoSend - Instant Courier', duration: '1-3 Jam', price: 28000 }
                    ];
                }
            }
        }""")
        await page.wait_for_timeout(500)
    elif feat_id == 77: # I-03 points modal
        await page.evaluate("""() => {
            const el = document.querySelector('[x-data*="showPointsModal"]');
            if (el && window.Alpine) {
                const d = window.Alpine.$data(el);
                if (d) {
                    d.selectedCustomer = d.membersList?.[0] || { name: 'Ibu Veronica Cindy', phone: '081234567890', points_balance: 1450 };
                    d.pointHistories = [
                        { id: 1, type: 'earned', points: 250, description: 'Pembelian di POS Kasir #ORD-1001', created_at: '17 Nov 2026, 10:15' },
                        { id: 2, type: 'earned', points: 1200, description: 'Bonus Belanja Promo Grand Opening', created_at: '15 Nov 2026, 14:30' }
                    ];
                    d.loadingPoints = false;
                    d.showPointsModal = true;
                }
            }
        }""")
        await page.wait_for_timeout(500)

    # Take screenshot with retry for Windows transient file locks
    for attempt in range(3):
        try:
            await page.screenshot(path=out_png_path, full_page=False)
            break
        except Exception as e:
            if attempt == 2:
                raise e
            await asyncio.sleep(0.3)

    # Verify screenshot integrity with PIL
    img = Image.open(out_png_path)
    size_bytes = os.path.getsize(out_png_path)
    expected_size = (393, 852) if is_mobile else (1600, 1000)
    assert img.size == expected_size, f"Size mismatch for {filename}: {img.size} vs {expected_size}"
    assert size_bytes > 5000, f"File size too small for {filename}: {size_bytes} bytes"

    print(f"[{index}/{total}] Captured {cat_name}/{filename} ({img.size[0]}x{img.size[1]}, {size_bytes:,} bytes)")

async def main():
    start_time = time.time()
    print(f"Starting Playwright capture for {len(features)} features...")
    
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        context = await browser.new_context(device_scale_factor=1)
        page = await context.new_page()

        for idx, feat in enumerate(features, 1):
            await capture_feature(page, feat, idx, len(features))

        await context.close()
        await browser.close()

    elapsed = time.time() - start_time
    print(f"\nAll {len(features)} screenshots captured successfully in {elapsed:.1f} seconds!")

if __name__ == "__main__":
    asyncio.run(main())
