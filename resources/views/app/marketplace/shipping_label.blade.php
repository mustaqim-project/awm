<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Pengiriman Resmi Shopee - {{ $business->name }}</title>
    <style>
        @page {
            size: 100mm 150mm;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            background-color: #e5e7eb;
            color: #000000;
            font-size: 10.5px;
            line-height: 1.25;
        }
        .screen-toolbar {
            position: fixed;
            top: 14px;
            right: 14px;
            z-index: 9999;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            padding: 10px 16px;
            border-radius: 14px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12.5px;
            border: 1px solid rgba(0, 0, 0, 0.08);
        }
        .screen-toolbar button {
            background: #EE4D2D;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 9px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(238, 77, 45, 0.35);
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }
        .screen-toolbar button:hover {
            background: #d43f21;
            transform: translateY(-1px);
        }
        .screen-toolbar a {
            color: #4b5563;
            text-decoration: none;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 8px;
        }
        .screen-toolbar a:hover {
            background: rgba(0, 0, 0, 0.05);
            color: #111827;
        }
        .label-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 0;
            gap: 24px;
        }
        .label-page {
            width: 100mm;
            height: 148mm;
            background: #ffffff;
            border: 2px solid #000000;
            padding: 2.5mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            page-break-after: always;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            overflow: hidden;
        }
        @media print {
            body {
                background: #ffffff;
            }
            .screen-toolbar {
                display: none !important;
            }
            .label-container {
                padding: 0;
                gap: 0;
            }
            .label-page {
                box-shadow: none;
                border: 1.5px solid #000000;
                page-break-after: always;
                margin: 0;
                width: 100mm;
                height: 148mm;
            }
        }

        /* 1. Header Section: Shopee Branding + Courier Badge */
        .shopee-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 2mm;
            border-bottom: 2px solid #000000;
            height: 14mm;
        }
        .shopee-brand {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .shopee-logo-svg {
            width: 26px;
            height: 28px;
        }
        .shopee-brand-text {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: -0.8px;
            color: #000000;
            line-height: 1;
        }
        .shopee-brand-sub {
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #4b5563;
        }
        .carrier-box {
            text-align: right;
            max-width: 55%;
        }
        .carrier-tag {
            display: inline-block;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            padding: 3px 8px;
            border: 2px solid #000000;
            border-radius: 4px;
            background: #ffffff;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .carrier-service-sub {
            font-size: 8px;
            font-weight: 700;
            color: #4b5563;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* 2. Main Barcode Section */
        .barcode-section {
            text-align: center;
            padding: 2.5mm 0 2mm 0;
            border-bottom: 2px solid #000000;
            height: 32mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .barcode-svg-container {
            width: 90mm;
            height: 17mm;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        .barcode-svg-container svg {
            width: 100%;
            height: 100%;
        }
        .tracking-number-text {
            font-family: "Courier New", Courier, monospace, sans-serif;
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 2px;
            margin-top: 1.5mm;
            color: #000000;
        }
        .order-meta-row {
            font-size: 9px;
            font-weight: 700;
            color: #1f2937;
            margin-top: 1mm;
            display: flex;
            justify-content: center;
            gap: 8px;
        }

        /* 3. Sorting & Routing Block */
        .routing-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            border-bottom: 2px solid #000000;
            height: 14mm;
            background: #fdfdfd;
        }
        .routing-cell {
            padding: 1.5mm 2mm;
            border-right: 1.5px solid #000000;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .routing-cell:last-child {
            border-right: none;
        }
        .routing-label {
            font-size: 7.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: #4b5563;
            margin-bottom: 1px;
        }
        .routing-value {
            font-size: 11.5px;
            font-weight: 900;
            text-transform: uppercase;
            color: #000000;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* 4. Address Block (Penerima & Pengirim) */
        .address-grid {
            display: grid;
            grid-template-columns: 1.25fr 0.95fr;
            border-bottom: 2px solid #000000;
            height: 38mm;
        }
        .address-col {
            padding: 2mm 2.5mm;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            overflow: hidden;
        }
        .address-col.recipient {
            border-right: 1.5px solid #000000;
        }
        .address-badge {
            font-size: 8px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            color: #000000;
        }
        .person-name {
            font-size: 11px;
            font-weight: 900;
            color: #000000;
            line-height: 1.15;
            margin-bottom: 1.5px;
        }
        .person-phone {
            font-size: 10px;
            font-weight: 800;
            color: #000000;
            margin-bottom: 2px;
        }
        .address-full-text {
            font-size: 9px;
            line-height: 1.25;
            color: #111827;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
        }
        .sender-store {
            font-size: 10.5px;
            font-weight: 900;
            color: #000000;
            line-height: 1.15;
            margin-bottom: 1px;
        }

        /* 5. Items Summary Table */
        .items-section {
            border-bottom: 2px solid #000000;
            height: 30mm;
            padding: 1.5mm 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }
        .items-table th {
            border-bottom: 1px solid #000000;
            padding: 1.5px 2px;
            text-align: left;
            font-size: 7.5px;
            font-weight: 900;
            text-transform: uppercase;
            background: #fafafa;
        }
        .items-table td {
            padding: 2px 2px;
            border-bottom: 0.5px dotted #9ca3af;
            vertical-align: top;
            line-height: 1.2;
        }
        .item-title-box {
            font-weight: 700;
            color: #000000;
            max-height: 14px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .item-sku-text {
            font-size: 7.5px;
            color: #4b5563;
            font-family: monospace;
        }
        .items-footer-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 8px;
            font-weight: 700;
            padding: 1.5px 2px 0 2px;
            color: #374151;
        }

        /* 6. Footer Section (COD + Bukti Serah Terima) */
        .shopee-footer {
            height: 20mm;
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            padding-top: 1.5mm;
            gap: 2mm;
        }
        .cod-box-wrapper {
            flex: 1.1;
            border: 2px solid #000000;
            border-radius: 4px;
            padding: 2mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .cod-box-wrapper.is-cod {
            background: #000000;
            color: #ffffff;
        }
        .cod-title-main {
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.5px;
            line-height: 1.1;
        }
        .cod-amount-text {
            font-size: 12px;
            font-weight: 900;
            margin-top: 1px;
        }
        .cod-instruction-sub {
            font-size: 7.5px;
            font-weight: 700;
            margin-top: 1.5px;
            opacity: 0.9;
        }
        .signature-box-wrapper {
            flex: 1;
            border: 1.5px solid #000000;
            border-radius: 4px;
            padding: 1.5mm 2mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 8px;
        }
        .signature-title {
            font-size: 7.5px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .signature-line {
            border-bottom: 1px dashed #000000;
            height: 8mm;
        }
        .signature-sub {
            font-size: 7px;
            color: #4b5563;
            text-align: center;
        }
    </style>
    <!-- JsBarcode for Real Scannable Thermal Barcodes -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
</head>
<body>

    <!-- On-screen Toolbar (Hidden when Printed) -->
    <div class="screen-toolbar">
        <div style="display: flex; align-items: center; gap: 8px;">
            <svg style="width: 20px; height: 20px; color: #EE4D2D;" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 8h-1V3H6v5H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zM8 5h8v3H8V5zm8 14H8v-4h8v4zm4-4h-2v-2H4v2H2v-4c0-.55.45-1 1-1h18c.55 0 1 .45 1 1v4z"/>
            </svg>
            <span><strong>{{ $orders->count() }}</strong> Label Shopee Siap Cetak</span>
        </div>
        <button type="button" onclick="window.print()">
            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zM7 9V5a2 2 0 012-2h6a2 2 0 012 2v4"/>
            </svg>
            <span>Cetak Label Thermal (100x150)</span>
        </button>
        <a href="javascript:history.back()">Kembali ke Pesanan</a>
    </div>

    <div class="label-container">
        @foreach($orders as $idx => $order)
            @php
                $recipient = $order->raw_payload['recipient_address'] ?? [];
                $city = !empty($recipient['city']) ? $recipient['city'] : 'KOTA JAKARTA';
                $district = !empty($recipient['district']) ? $recipient['district'] : (!empty($recipient['state']) ? $recipient['state'] : 'KECAMATAN');
                $zipcode = !empty($recipient['zipcode']) ? $recipient['zipcode'] : '10000';
                
                $trackingNo = $order->tracking_number ?: ($order->external_order_sn ?: $order->external_order_id);
                $orderSn = $order->external_order_sn ?: $order->external_order_id;
                
                $payMethod = strtolower((string) ($order->raw_payload['payment_method'] ?? ''));
                $isCod = str_contains($payMethod, 'cod') || str_contains($payMethod, 'cash on delivery') || str_contains($payMethod, 'bayar di tempat');
                
                // Clean Courier Provider Display Name
                $rawCarrier = (string) ($order->shipping_provider ?: 'REGULER');
                $isSandboxCarrier = str_contains($rawCarrier, 'SANDBOX');
                $cleanCarrier = str_ireplace(['SANDBOX-', '(DON\'T MODIFY)', 'DON\'T MODIFY'], '', $rawCarrier);
                $cleanCarrier = trim(strtoupper($cleanCarrier)) ?: 'SPX STANDARD';

                $items = is_array($order->items_summary) ? $order->items_summary : (json_decode($order->items_summary ?? '[]', true) ?: []);
                $totalQty = 0;
                foreach ($items as $it) {
                    $totalQty += (int) ($it['quantity'] ?? 1);
                }
                if ($totalQty === 0) $totalQty = 1;

                // Estimated weight
                $weight = $order->raw_payload['actual_shipping_fee'] ?? 1;
                $weightKg = '1.00';
            @endphp

            <div class="label-page">
                <div>
                    <!-- ============================================== -->
                    <!-- 1. HEADER: SHOPEE OFFICIAL BRANDING & COURIER  -->
                    <!-- ============================================== -->
                    <div class="shopee-header">
                        <div class="shopee-brand">
                            <!-- Official Shopee Bag Vector Logo -->
                            <svg class="shopee-logo-svg" viewBox="0 0 100 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M50 0C38.9543 0 30 8.95431 30 20V25H20C14.4772 25 10 29.4772 10 35L5 95C5 100.523 9.47715 105 15 105H85C90.5228 105 95 100.523 95 95L90 35C90 29.4772 85.5228 25 80 25H70V20C70 8.95431 61.0457 0 50 0ZM38 20C38 13.3726 43.3726 8 50 8C56.6274 8 62 13.3726 62 20V25H38V20ZM51.5 45C62.5 45 68 50 68 56C68 62 61 65 52 68C44 70.5 42 72.5 42 75.5C42 79 46 81.5 53 81.5C60 81.5 65.5 78.5 68.5 77L70.5 83C66.5 85.5 59.5 87.5 52 87.5C40 87.5 34 82 34 75C34 68.5 40.5 65.5 50 62.5C58 60 60 58 60 55C60 52 56.5 50 50.5 50C44 50 38.5 53 35.5 55L33.5 49C37.5 46.5 44 45 51.5 45Z" fill="#EE4D2D"/>
                            </svg>
                            <div>
                                <div class="shopee-brand-text">Shopee</div>
                                <div class="shopee-brand-sub">Standard Express</div>
                            </div>
                        </div>

                        <div class="carrier-box">
                            <div class="carrier-tag" title="{{ $cleanCarrier }}">
                                {{ $cleanCarrier }}
                            </div>
                            @if($isSandboxCarrier)
                                <div class="carrier-service-sub">Mode Pengujian Sandbox</div>
                            @else
                                <div class="carrier-service-sub">Layanan Ekspedisi Resmi</div>
                            @endif
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- 2. SCANNABLE BARCODE & AWB TRACKING NUMBER     -->
                    <!-- ============================================== -->
                    <div class="barcode-section">
                        <div class="barcode-svg-container">
                            <svg id="barcode-{{ $idx }}"></svg>
                        </div>
                        <div class="tracking-number-text">{{ $trackingNo }}</div>
                        <div class="order-meta-row">
                            <span>No. Pesanan: <strong>{{ $orderSn }}</strong></span>
                            <span>•</span>
                            <span>Berat: <strong>{{ $weightKg }} Kg</strong></span>
                            <span>•</span>
                            <span>Tgl: {{ $order->placed_at ? $order->placed_at->format('d/m/Y') : date('d/m/Y') }}</span>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- 3. SORTING CODE & DESTINATION ROUTING HUB      -->
                    <!-- ============================================== -->
                    <div class="routing-grid">
                        <div class="routing-cell">
                            <span class="routing-label">Kota / Hub Tujuan</span>
                            <span class="routing-value">{{ $city }}</span>
                        </div>
                        <div class="routing-cell">
                            <span class="routing-label">Kecamatan / Drop Point</span>
                            <span class="routing-value">{{ $district }}</span>
                        </div>
                        <div class="routing-cell">
                            <span class="routing-label">Kode Pos / Area</span>
                            <span class="routing-value">{{ $zipcode }}</span>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- 4. ADDRESS SECTION: PENERIMA & PENGIRIM       -->
                    <!-- ============================================== -->
                    <div class="address-grid">
                        <!-- Penerima -->
                        <div class="address-col recipient">
                            <span class="address-badge">Penerima (Kepada):</span>
                            <div class="person-name">{{ $order->buyer_name }}</div>
                            <div class="person-phone">{{ $order->buyer_phone ?: '-' }}</div>
                            <div class="address-full-text">
                                {{ $recipient['full_address'] ?? ($recipient['address'] ?? ($city . ', ' . $district . ' ' . $zipcode)) }}
                            </div>
                        </div>

                        <!-- Pengirim -->
                        <div class="address-col">
                            <span class="address-badge">Pengirim (Dari):</span>
                            <div class="sender-store">{{ $order->account->shop_name ?? $business->name }}</div>
                            <div class="person-phone">{{ $business->phone ?: '-' }}</div>
                            <div class="address-full-text">
                                {{ $business->address ?: ($business->city ?? 'Indonesia') }}
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- 5. ORDER ITEMS & PRODUCT SUMMARY TABLE        -->
                    <!-- ============================================== -->
                    <div class="items-section">
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th style="width: 14px; text-align: center;">#</th>
                                    <th>Nama Produk / SKU</th>
                                    <th style="width: 50px;">Variasi</th>
                                    <th style="width: 25px; text-align: center;">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(array_slice($items, 0, 4) as $itemIdx => $it)
                                    <tr>
                                        <td style="text-align: center; font-weight: 800;">{{ $itemIdx + 1 }}</td>
                                        <td>
                                            <div class="item-title-box">{{ $it['item_name'] ?? ($it['name'] ?? 'Paket Produk') }}</div>
                                            @if(!empty($it['sku']))
                                                <div class="item-sku-text">SKU: {{ $it['sku'] }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $it['model_name'] ?? '-' }}</td>
                                        <td style="text-align: center; font-weight: 900;">{{ $it['quantity'] ?? 1 }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 4px; color: #4b5563;">1x Paket Pesanan Shopee</td>
                                    </tr>
                                @endforelse
                                @if(count($items) > 4)
                                    <tr>
                                        <td colspan="4" style="text-align: center; font-size: 7.5px; font-weight: 800; color: #EE4D2D;">
                                            + {{ count($items) - 4 }} produk lainnya dalam paket ini...
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <div class="items-footer-bar">
                            <span>Total Jumlah Barang: <strong>{{ $totalQty }} Pcs</strong></span>
                            <span>Wajib video unboxing untuk klaim komplain</span>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- 6. FOOTER: COD STATUS & BUKTI SERAH TERIMA     -->
                <!-- ============================================== -->
                <div class="shopee-footer">
                    <!-- COD / NON-COD Box -->
                    <div class="cod-box-wrapper {{ $isCod ? 'is-cod' : '' }}">
                        @if($isCod)
                            <div class="cod-title-main">COD (BAYAR DI TEMPAT)</div>
                            <div class="cod-amount-text">Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</div>
                            <div class="cod-instruction-sub">Tagih tunai ke pembeli sebelum paket diserahkan</div>
                        @else
                            <div class="cod-title-main">NON-COD (LUNAS)</div>
                            <div class="cod-instruction-sub">Pembeli telah membayar lunas secara online</div>
                            <div style="font-size: 7.5px; font-weight: 800; margin-top: 1px; color: #EE4D2D;">KURIR TIDAK MENAGIH BIAYA APAPUN</div>
                        @endif
                    </div>

                    <!-- Bukti Tanda Terima Kurir -->
                    <div class="signature-box-wrapper">
                        <div class="signature-title">Bukti Penerimaan:</div>
                        <div class="signature-line"></div>
                        <div class="signature-sub">Tanda Tangan & Nama Terang</div>
                    </div>
                </div>
            </div>

            <!-- Barcode Generator Script per Order -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (window.JsBarcode) {
                        try {
                            JsBarcode("#barcode-{{ $idx }}", "{{ $trackingNo }}", {
                                format: "CODE128",
                                width: 2.1,
                                height: 48,
                                displayValue: false,
                                margin: 0
                            });
                        } catch (e) {
                            console.warn("Barcode rendering fallback:", e);
                        }
                    }
                });
            </script>
        @endforeach
    </div>

    @if(!empty($autoPrint))
        <script>
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 600);
            });
        </script>
    @endif
</body>
</html>
