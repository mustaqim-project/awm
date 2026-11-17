<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'reviewer@cooca.id')->first();
if (!$user) {
    echo "ERROR: User reviewer@cooca.id not found!\n";
    exit(1);
}

$business = App\Models\Business::find($user->active_business_id);
if (!$business) {
    $business = $user->businesses()->first();
}

\App\Support\Context::setBusiness($business);
\Illuminate\Support\Facades\Auth::login($user);
session([
    'active_business_id' => $business->id,
    'auth_wa_otp_verified_user_id' => $user->id,
]);

$customer = App\Models\Customer::where('business_id', $business->id)->first();
$firstProduct = App\Models\Product::where('business_id', $business->id)->first();
$firstInvoice = class_exists('App\Models\Invoice') ? App\Models\Invoice::where('business_id', $business->id)->first() : null;
$firstQuotation = class_exists('App\Models\Quotation') ? App\Models\Quotation::where('business_id', $business->id)->first() : null;
$firstSalesOrder = class_exists('App\Models\SalesOrder') ? App\Models\SalesOrder::where('business_id', $business->id)->first() : null;
$confirmedPO = App\Models\PurchaseOrder::where('business_id', $business->id)->where('status', 'confirmed')->first();
if (!$confirmedPO) {
    $confirmedPO = App\Models\PurchaseOrder::where('business_id', $business->id)->first();
}

$outputDir = __DIR__ . '/../storage/app/public/features_rendered_html';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$features = json_decode(file_get_contents(__DIR__ . '/final_verified_90_features.json'), true);

echo "Starting in-process Blade rendering for " . count($features) . " features...\n";

// Map feature ID to custom client-side script injection
function getCustomInjection(int $id): string {
    switch ($id) {
        case 2: // A-02 pencarian-produk-instan.png
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("#pos-app") || document.querySelector("[x-data*=\'posApp\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) {
                                d.searchQuery = "Ayam";
                                if (typeof d.filterProducts === "function") d.filterProducts();
                            }
                        }
                        const inp = document.querySelector("input[placeholder*=\'Scan\'], input[placeholder*=\'Cari\'], input[x-model=\'searchQuery\']");
                        if (inp) {
                            inp.value = "Ayam";
                            inp.dispatchEvent(new Event("input", { bubbles: true }));
                        }
                    }, 400);
                });
            </script>';

        case 3: // A-03 multi-metode-pembayaran.png
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("#pos-app") || document.querySelector("[x-data*=\'posApp\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) d.showPaymentModal = true;
                        }
                    }, 400);
                });
            </script>';

        case 4: // A-04 hold-resume-order.png
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("#pos-app") || document.querySelector("[x-data*=\'posApp\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) d.showHeldOrdersModal = true;
                        }
                    }, 400);
                });
            </script>';

        case 6: // A-06 rekonsiliasi-kas-harian.png (Shift Close & Balancing Modal)
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("[x-data*=\'showCloseModal\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) {
                                d.expectedCash = 1750000;
                                d.actualCash = 1750000;
                                d.closeDenoms = {
                                    \'100000\': 15, \'50000\': 4, \'20000\': 2, \'10000\': 1,
                                    \'5000\': 0, \'2000\': 0, \'1000\': 0, \'coins\': 0
                                };
                                d.closeUseDenoms = true;
                                d.cashierNotes = "Rekonsiliasi kas harian shift pagi klop sesuai fisik laci kasir.";
                                d.showCloseModal = true;
                            }
                        }
                    }, 400);
                });
            </script>';

        case 7: // A-07 integrasi-printer-thermal.png (PROPOSED UI / HARDWARE INTEGRATION)
            return '<script>
                window.addEventListener("load", () => {
                    const banner = document.createElement("div");
                    banner.style.cssText = "position:fixed;top:18px;right:24px;background:#0369A1;color:#FFFFFF;padding:10px 18px;border-radius:10px;font-weight:700;font-size:12px;z-index:99999;box-shadow:0 4px 14px rgba(0,0,0,0.35);letter-spacing:0.04em;border:1px solid #38BDF8;display:flex;align-items:center;gap:10px;";
                    banner.innerHTML = "<span style=\"width:10px;height:10px;border-radius:50%;background:#38BDF8;box-shadow:0 0 8px #38BDF8;\"></span> PROPOSED UI: INTEGRASI PRINTER THERMAL ESC/POS 58MM/80MM";
                    document.body.appendChild(banner);
                });
            </script>';

        case 8: // A-08 pengiriman-struk-digital-whatsapp.png
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const btn = document.querySelector("button[title*=\'WhatsApp\'], button:has(i[data-lucide*=\'share\'])");
                        if (btn) btn.style.outline = "3px solid #10B981";
                    }, 400);
                });
            </script>';

        case 10: // A-10 alur-status-pesanan-dapur.png (Cooking Workflow & Timers)
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("[x-data*=\'kitchenDisplay\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) {
                                d.orders = [
                                    {
                                        id: "kds-01",
                                        order_number: "ORD-101",
                                        pos_table: { table_number: "05" },
                                        sales_channel: "dine_in",
                                        customer_name_guest: "Meja 05 (4 Pax)",
                                        status: "preparing",
                                        created_at: new Date(Date.now() - 14 * 60000).toISOString(),
                                        notes: "Ayam bakar pedas manis, es teh manis",
                                        items: [
                                            { id: 1, product_name: "Ayam Bakar Madu", quantity: 2, notes: "Pedas manis", modifiers: [] },
                                            { id: 2, product_name: "Es Teh Manis", quantity: 4, notes: "Gula sedikit", modifiers: [] }
                                        ]
                                    },
                                    {
                                        id: "kds-02",
                                        order_number: "ORD-102",
                                        pos_table: null,
                                        sales_channel: "grabfood",
                                        external_order_ref: "GB-9921",
                                        customer_name_guest: "Driver GrabFood (Joko)",
                                        status: "ready",
                                        created_at: new Date(Date.now() - 28 * 60000).toISOString(),
                                        notes: "Packing rapi kuah pisah",
                                        items: [
                                            { id: 3, product_name: "Nasi Goreng Spesial", quantity: 1, modifiers: [] }
                                        ]
                                    }
                                ];
                                if (window.lucide) window.lucide.createIcons();
                            }
                        }
                    }, 400);
                });
            </script>';

        case 12: // A-12 pindah-gabung-meja.png
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const badge = document.createElement("div");
                        badge.style.cssText = "position:fixed;top:18px;right:24px;background:#1E293B;color:#38BDF8;padding:8px 16px;border-radius:8px;font-weight:600;font-size:12px;z-index:99999;border:1px solid rgba(56,189,248,0.4);";
                        badge.innerText = "MODE: PINDAH & GABUNG MEJA (DINE-IN RESTORAN)";
                        document.body.appendChild(badge);
                    }, 300);
                });
            </script>';

        case 13: // A-13 split-bill.png
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("#pos-app") || document.querySelector("[x-data*=\'posApp\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) d.showTablePaymentModal = true;
                        }
                    }, 400);
                });
            </script>';

        case 15: // A-15 perintah-kerja-spk.png (SPK / Add Service Modal)
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("[x-data*=\'showAddModal\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) {
                                d.addForm.name = "Servis Ringan & Tune-Up Transmisi";
                                d.addForm.code = "SPK-SRV-2026";
                                d.addForm.selling_price = "175000";
                                d.addForm.description = "Pemeriksaan oli transmisi, busi, dan filter udara kendaraan.";
                                d.showAddModal = true;
                            }
                        }
                    }, 400);
                });
            </script>';

        case 16: // A-16 transaksi-jasa-sparepart-terintegrasi.png (Cart with Service & Sparepart items)
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("#pos-app") || document.querySelector("[x-data*=\'posApp\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d && d.cart) {
                                d.cart = [
                                    {
                                        product_id: "srv-01",
                                        product_name: "Jasa Servis Berkala & Tune Up Motor",
                                        unit_price: 150000,
                                        quantity: 1,
                                        unit_symbol: "Paket",
                                        discount_amount: 0,
                                        selected_modifiers: []
                                    },
                                    {
                                        product_id: "spr-01",
                                        product_name: "Oli Mesin Matic 10W-30 0.8L (Sparepart)",
                                        unit_price: 65000,
                                        quantity: 1,
                                        unit_symbol: "Botol",
                                        discount_amount: 0,
                                        selected_modifiers: []
                                    },
                                    {
                                        product_id: "spr-02",
                                        product_name: "Busi Racing Iridium (Sparepart)",
                                        unit_price: 45000,
                                        quantity: 1,
                                        unit_symbol: "Pcs",
                                        discount_amount: 0,
                                        selected_modifiers: []
                                    }
                                ];
                                d.customerSearchQuery = "Budi Santoso - Honda Vario B 3456 KFG";
                            }
                        }
                    }, 400);
                });
            </script>';

        case 17: // B-01 otorisasi-pin-supervisor.png
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("#pos-app") || document.querySelector("[x-data*=\'posApp\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) d.showSupervisorPinModal = true;
                        }
                    }, 400);
                });
            </script>';

        case 20: // B-04 approval-selisih-stock-opname.png (Opname Variance Approval Modal)
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("[x-data*=\'approvalInbox\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) {
                                d.openApproveModal("tk-opname-01", "Persetujuan Selisih Varian Stock Opname Gudang (-20 Pcs)", "Rp 2.450.000", 1);
                            }
                        }
                    }, 400);
                });
            </script>';

        case 23: // B-07 matriks-hak-akses-karyawan.png (Permissions Matrix Tab)
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("[x-data*=\'activeTab\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) d.activeTab = "matrix";
                        }
                    }, 400);
                });
            </script>';

        case 27: // C-04 labor-cost-calculation.png (Direct Labor Rates Tab)
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("[x-data*=\'activeSection\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) d.activeSection = "labor";
                        }
                    }, 400);
                });
            </script>';

        case 28: // C-05 machine-depreciation-rate.png (Machine Depreciation Tab)
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("[x-data*=\'activeSection\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) d.activeSection = "machines";
                        }
                    }, 400);
                });
            </script>';

        case 31: // C-08 simulasi-sensitivitas-harga-bahan-baku.png (Material Price Shock)
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("[x-data*=\'simulatorComponent\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) {
                                d.matChange = 35;
                                d.labChange = 0;
                                d.macChange = 0;
                                d.ovhChange = 0;
                                d.markupPct = 25;
                                if (typeof d.runSimulation === "function") d.runSimulation();
                            }
                        }
                    }, 500);
                });
            </script>';

        case 54: // F-02 mutasi-transfer-dana-internal.png (Transfer Modal)
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("[x-data*=\'showTransferModal\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) d.showTransferModal = true;
                        }
                    }, 400);
                });
            </script>';

        case 66: // G-04 cetak-label-pengiriman-shipping-label.png (Live Shipping Rates Calculator Test)
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("[x-data*=\'isTestingRate\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) {
                                d.testPostalCode = "12950";
                                d.testWeight = 1200;
                                d.testValue = 150000;
                                d.testResult = [
                                    { id: "jne_reg", description: "JNE - Reguler (CTC)", duration: "1-2 Hari", price: 10000 },
                                    { id: "jnt_ez", description: "J&T Express - EZ Regular", duration: "1-2 Hari", price: 11000 },
                                    { id: "sicepat_siuntung", description: "SiCepat - SIUNTUNG Regular", duration: "1 Hari", price: 9500 },
                                    { id: "gosend_instant", description: "GoSend - Instant Courier", duration: "1-3 Jam", price: 28000 }
                                ];
                            }
                        }
                    }, 400);
                });
            </script>';

        case 77: // I-03 reward-poin-belanja.png (Point History Modal)
            return '<script>
                window.addEventListener("load", () => {
                    setTimeout(() => {
                        const el = document.querySelector("[x-data*=\'showPointsModal\']");
                        if (el && window.Alpine) {
                            const d = window.Alpine.$data(el);
                            if (d) {
                                d.selectedCustomer = d.membersList?.[0] || { name: "Ibu Veronica Cindy", phone: "081234567890", points_balance: 1450 };
                                d.pointHistories = [
                                    { id: 1, type: "earned", points: 250, description: "Pembelian di POS Kasir #ORD-1001", created_at: "17 Nov 2026, 10:15" },
                                    { id: 2, type: "earned", points: 1200, description: "Bonus Belanja Promo Grand Opening", created_at: "15 Nov 2026, 14:30" }
                                ];
                                d.loadingPoints = false;
                                d.showPointsModal = true;
                            }
                        }
                    }, 400);
                });
            </script>';

        default:
            return '';
    }
}

$renderedCount = 0;

foreach ($features as $f) {
    $id = $f['id'];
    $filename = $f['filename'];
    $uri = $f['uri'];

    // Route parameter resolution
    if (str_contains($uri, '{product}')) {
        $uri = str_replace('{product}', $firstProduct ? $firstProduct->id : '1', $uri);
    }
    if (str_contains($uri, '{invoice}')) {
        $uri = str_replace('{invoice}', $firstInvoice ? $firstInvoice->id : '1', $uri);
    }
    if (str_contains($uri, '{quotation}')) {
        $uri = str_replace('{quotation}', $firstQuotation ? $firstQuotation->id : '1', $uri);
    }
    if (str_contains($uri, '{salesOrder}')) {
        $uri = str_replace('{salesOrder}', $firstSalesOrder ? $firstSalesOrder->id : '1', $uri);
    }
    if (str_contains($uri, '{purchaseOrder}')) {
        $uri = str_replace('{purchaseOrder}', $confirmedPO ? $confirmedPO->id : '1', $uri);
    }

    // Tab query params for multi-tab views
    if ($id === 19) { // B-03 Approval PO Bertingkat
        $uri = 'approvals?type=purchase_order';
    } elseif ($id === 20) { // B-04 Approval Selisih Opname
        $uri = 'approvals?type=stock_adjustment';
    } elseif ($id === 41) { // D-09 Portal Pelanggan B2B Dashboard
        $uri = 'customer/dashboard';
    } elseif ($id === 42) { // D-10 Riwayat Faktur & Tagihan Pelanggan
        $uri = 'customer/orders?status=completed';
    } elseif ($id === 60) { // F-08 Laba Rugi
        $uri = 'reports?tab=income_statement';
    } elseif ($id === 80) { // J-02 Roster Shift
        $uri = 'hrm?tab=shifts';
    } elseif ($id === 82) { // J-04 Kasbon
        $uri = 'hrm?tab=loans';
    } elseif ($id === 88) { // L-02 Peak Hours
        $uri = 'pos/reports?tab=hourly';
    } elseif ($id === 89) { // L-03 Top Products
        $uri = 'pos/reports?tab=products';
    } elseif ($id === 90) { // L-04 Konsolidasi Multi-Cabang
        $uri = 'reports?tab=cash_flow';
    }

    // Customer portal authentication
    if (str_starts_with($uri, 'customer/')) {
        \Illuminate\Support\Facades\Auth::guard('customer')->login($customer);
    }

    $request = \Illuminate\Http\Request::create('/' . ltrim($uri, '/'), 'GET');
    $request->setUserResolver(fn() => str_starts_with($uri, 'customer/') ? $customer : $user);
    $response = $app->handle($request);

    $html = $response->getContent();

    // Inject custom interaction script at the LAST </body> tag
    $injection = getCustomInjection($id);
    if ($injection !== '') {
        $lastBodyPos = strrpos($html, '</body>');
        if ($lastBodyPos !== false) {
            $html = substr_replace($html, $injection . '</body>', $lastBodyPos, strlen('</body>'));
        } else {
            $html .= $injection;
        }
    }

    $outPath = $outputDir . '/' . sprintf('%02d_%s.html', $id, pathinfo($filename, PATHINFO_FILENAME));
    file_put_contents($outPath, $html);
    $renderedCount++;

    if ($renderedCount % 15 === 0 || $renderedCount === count($features)) {
        echo "Rendered $renderedCount / " . count($features) . " feature HTMLs...\n";
    }
}

echo "Successfully rendered all $renderedCount HTML files to $outputDir\n";
