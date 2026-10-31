@php
    $firstOrder = $orders->first();
    $firstRawChannel = strtolower(trim((string) ($firstOrder?->channel ?: ($firstOrder?->account?->channel ?? ''))));
    $firstBuyer = strtolower((string) ($firstOrder?->buyer_name ?? ''));
    $firstProvider = strtolower((string) ($firstOrder?->shipping_provider ?? ''));

    $pageChannelName = 'Shopee';
    if (str_contains($firstRawChannel, 'tiktok') || str_contains($firstBuyer, 'tiktok') || str_contains($firstProvider, 'tiktok')) {
        $pageChannelName = 'TikTok Shop';
    } elseif (str_contains($firstRawChannel, 'tokopedia') || str_contains($firstRawChannel, 'toped') || str_contains($firstBuyer, 'tokopedia')) {
        $pageChannelName = 'Tokopedia';
    } elseif (str_contains($firstRawChannel, 'storefront') || str_contains($firstRawChannel, 'cooca')) {
        $pageChannelName = 'COOCA Storefront';
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Pengiriman Resmi {{ $pageChannelName }} - {{ $business->name }}</title>
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
            .shopee-logo-path {
                fill: #000000 !important;
            }
            .tiktok-note-cyan, .tiktok-note-red {
                display: none !important;
            }
            .tiktok-note-white {
                fill: #000000 !important;
            }
            .tiktok-shop-box {
                fill: #000000 !important;
                stroke: #000000 !important;
            }
            .tiktok-shop-text {
                fill: #ffffff !important;
            }
            .tokopedia-path {
                fill: #000000 !important;
            }
            .tokopedia-text {
                fill: #000000 !important;
            }
            .cooca-brand-pill {
                background: #000000 !important;
                color: #ffffff !important;
                border: 1px solid #000000 !important;
            }
            .cooca-official-img {
                filter: contrast(160%) brightness(85%);
            }
        }

        /* 1. Header Section: Multi-Channel Official Branding + Courier Badge */
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
            flex-direction: column;
            justify-content: center;
            gap: 1.5px;
        }
        .shopee-official-logo {
            height: 7.8mm;
            width: auto;
            max-width: 32mm;
            display: block;
        }
        .tiktok-official-logo {
            height: 7.8mm;
            width: auto;
            max-width: 38mm;
            display: block;
        }
        .tokopedia-official-logo {
            height: 7.8mm;
            width: auto;
            max-width: 38mm;
            display: block;
        }
        .cooca-official-brand {
            display: flex;
            align-items: center;
            gap: 2mm;
        }
        .cooca-official-img {
            height: 7.5mm;
            width: auto;
            max-width: 26mm;
            object-fit: contain;
            display: block;
        }
        .cooca-brand-pill {
            font-size: 7.5px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 1px 4px;
            border-radius: 3px;
            background: #007AFF;
            color: #ffffff;
            line-height: 1.1;
            display: inline-block;
        }
        .shopee-brand-sub {
            font-size: 7px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #4b5563;
            line-height: 1;
            margin-top: 1px;
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
            <span><strong>{{ $orders->count() }}</strong> Label {{ $pageChannelName }} Siap Cetak</span>
        </div>
        <button type="button" onclick="window.print()">
            <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zM7 9V5a2 2 0 012-2h6a2 2 0 012 2v4"/>
            </svg>
            <span>Cetak Label Thermal (100x150)</span>
        </button>
        <a href="javascript:history.back()">{{ __('marketplace.kembali_ke_pesanan') }}</a>
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
                            @php
                                $rawChannel = strtolower(trim((string) ($order->channel ?: ($order->account?->channel ?? ''))));
                                $buyerStr = strtolower((string) ($order->buyer_name ?? ''));
                                $carrierStr = strtolower((string) ($order->shipping_provider ?? ''));

                                $isTikTok = str_contains($rawChannel, 'tiktok') || str_contains($buyerStr, 'tiktok') || str_contains($carrierStr, 'tiktok');
                                $isTokopedia = str_contains($rawChannel, 'tokopedia') || str_contains($rawChannel, 'toped') || str_contains($buyerStr, 'tokopedia');
                                $isStorefront = str_contains($rawChannel, 'storefront') || str_contains($rawChannel, 'cooca');

                                $channelLabel = $isTikTok ? 'TikTok Shop' : ($isTokopedia ? 'Tokopedia' : ($isStorefront ? 'COOCA Storefront' : 'Shopee'));
                            @endphp
                            @if ($isTikTok)
                                <!-- Official TikTok Shop Vector Logo -->
                                <svg class="tiktok-official-logo" viewBox="0 0 160 40" xmlns="http://www.w3.org/2000/svg">
                                    <g transform="translate(0, 4)">
                                        <path class="tiktok-note-cyan" d="M22.5 12.8c-1.3-.8-2.3-2-2.8-3.4-.4-1.2-.5-2.5-.5-3.8h-4.6v19.4c0 2.2-1.8 4-4 4s-4-1.8-4-4 1.8-4 4-4c.4 0 .9.1 1.3.2V16c-.4-.1-.9-.1-1.3-.1-4.7 0-8.6 3.8-8.6 8.6s3.8 8.6 8.6 8.6 8.6-3.8 8.6-8.6V15.5c2.4 1.7 5.2 2.6 8.1 2.6v-4.6c-1.7-.1-3.4-.7-4.8-1.7z" fill="#00F2FE"/>
                                        <path class="tiktok-note-red" d="M24.5 14.8c-1.3-.8-2.3-2-2.8-3.4-.4-1.2-.5-2.5-.5-3.8h-4.6v19.4c0 2.2-1.8 4-4 4s-4-1.8-4-4 1.8-4 4-4c.4 0 .9.1 1.3.2V18c-.4-.1-.9-.1-1.3-.1-4.7 0-8.6 3.8-8.6 8.6s3.8 8.6 8.6 8.6 8.6-3.8 8.6-8.6V17.5c2.4 1.7 5.2 2.6 8.1 2.6v-4.6c-1.7-.1-3.4-.7-4.8-1.7z" fill="#FE2C55" style="mix-blend-mode: multiply;"/>
                                        <path class="tiktok-note-white" d="M23.5 13.8c-1.3-.8-2.3-2-2.8-3.4-.4-1.2-.5-2.5-.5-3.8h-4.6v19.4c0 2.2-1.8 4-4 4s-4-1.8-4-4 1.8-4 4-4c.4 0 .9.1 1.3.2V17c-.4-.1-.9-.1-1.3-.1-4.7 0-8.6 3.8-8.6 8.6s3.8 8.6 8.6 8.6 8.6-3.8 8.6-8.6V16.5c2.4 1.7 5.2 2.6 8.1 2.6v-4.6c-1.7-.1-3.4-.7-4.8-1.7z" fill="#000000"/>
                                    </g>
                                    <text x="35" y="27" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-weight="900" font-size="20" fill="#000000" letter-spacing="-0.5">TikTok</text>
                                    <rect class="tiktok-shop-box" x="103" y="10" width="52" height="20" rx="4" fill="#FE2C55"/>
                                    <text class="tiktok-shop-text" x="129" y="24.5" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-weight="800" font-size="12" fill="#FFFFFF" text-anchor="middle" letter-spacing="0.3">Shop</text>
                                </svg>
                                <div class="shopee-brand-sub">TikTok Shop Logistics</div>
                            @elseif ($isTokopedia)
                                <!-- Official Tokopedia Vector Logo -->
                                <svg class="tokopedia-official-logo" viewBox="0 0 660 175" xmlns="http://www.w3.org/2000/svg">
                                    <g class="tokopedia-path" fill="#03AC0E">
                                        <path d="M128.2 87.5c0 40.2-32.6 72.8-72.8 72.8S-17.4 127.7-17.4 87.5s32.6-72.8 72.8-72.8 72.8 32.6 72.8 72.8z"/>
                                        <circle cx="35" cy="80" r="22" fill="#FFFFFF"/>
                                        <circle cx="75" cy="80" r="22" fill="#FFFFFF"/>
                                        <circle cx="35" cy="80" r="10" fill="#03AC0E"/>
                                        <circle cx="75" cy="80" r="10" fill="#03AC0E"/>
                                        <circle cx="38" cy="77" r="3.5" fill="#FFFFFF"/>
                                        <circle cx="78" cy="77" r="3.5" fill="#FFFFFF"/>
                                        <polygon points="55,87 48,100 62,100" fill="#FFA000"/>
                                    </g>
                                    <text class="tokopedia-text" x="135" y="115" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-weight="800" font-size="82" fill="#03AC0E" letter-spacing="-2.5">tokopedia</text>
                                </svg>
                                <div class="shopee-brand-sub">Tokopedia Logistics</div>
                            @elseif ($isStorefront)
                                <!-- Official COOCA Storefront Logo -->
                                <div class="cooca-official-brand">
                                    <img src="{{ asset('assets/image/cooca-logo-landscape.png') }}" alt="COOCA" class="cooca-official-img" />
                                    <span class="cooca-brand-pill">STOREFRONT</span>
                                </div>
                                <div class="shopee-brand-sub">Pesanan Toko Online Resmi</div>
                            @else
                                <!-- Official Shopee Vector Logo (Default) -->
                                <svg class="shopee-official-logo" viewBox="0 0 1000 320" xmlns="http://www.w3.org/2000/svg">
                                    <g style="fill-rule:evenodd">
                                        <path class="shopee-logo-path" d="M35.67174 44.953764c-.333349 2.751051-2.000311 4.954341-4.582384 6.057598-1.437971.614592-3.36871.946386-4.896954.842163-2.384027-.09111-4.623787-.670894-6.688335-1.730742-.737553-.378855-1.837052-1.135276-2.68131-1.843776-.213839-.179005-.239235-.293758-.09774-.494467.0764-.115182.217254-.322983.528622-.779199.45158-.661654.507921-.744602.558713-.822178.14448-.221769.379233-.241109.610785-.05888.02433.01891.02433.01891.04268.03331.03799.02944.03799.02944.12762.09907.0907.0707.14448.112389.166248.12872 2.226529 1.743851 4.819699 2.749547 7.437625 2.850117 3.642304-.04964 6.261511-1.687335 6.730804-4.202004.516031-2.767598-1.656504-5.158274-5.907033-6.490821-1.329344-.416676-4.689518-1.761687-5.309053-2.12507-2.909447-1.707104-4.269736-3.943058-4.076384-6.704854.296216-3.828306 3.850167-6.683579 8.340785-6.702705 2.008208-.0041 4.012147.413238 5.937338 1.224457.681638.28731 1.898727.949608 2.318936 1.263351.242009.177716.289813.384872.151095.60836-.07747.12958-.205515.335017-.475482.763298l-.003.0047c-.355331.564092-.366428.581714-.447952.713657-.140852.214463-.306459.234448-.56042.07328-2.060067-1.383907-4.34379-2.080157-6.855437-2.130442-3.126914.06189-5.470605 1.922856-5.624689 4.45794-.04097 2.289677 1.676352 3.961324 5.385881 5.23585 7.529819 2.419687 10.411309 5.25648 9.869029 9.729248M26.372522 5.4266937c4.902289 0 8.898217 4.6522033 9.085166 10.4757833H17.287569c.186949-5.82358 4.182877-10.4757833 9.084953-10.4757833M51.743379 16.997353c0-.604706-.487007-1.094876-1.087549-1.094876H38.87847c-.28896-7.6892751-5.777492-13.8205815-12.505948-13.8205815-6.728244 0-12.216776 6.1313064-12.505736 13.8205815l-11.7942195.000215c-.5913649.01075-1.0674873.496831-1.0674873 1.094661 0 .02858.00107.05695.0032.08488h-.008323l1.6812616 37.061399c.0002134.103148.00405.207156.011738.311809.00171.02364.00363.04706.00555.07048l.00363.07822.00405.0041c.2554543 2.578923 2.1270784 4.656071 4.6720177 4.751913l.00576.0056H44.796175c.01771.000215.03543.00043.05314.00043.01771 0 .03543-.000215.05314-.00043h.0796l.0017-.0015c2.589329-.0707 4.686743-2.176859 4.908265-4.787585l.0013-.0013.0017-.03503c.0021-.02751.0041-.0548.0058-.0823.0041-.06576.0068-.1313.0079-.196412l1.83449-37.207738h-.0013c.0011-.0187.0015-.03761.0015-.05652m124.72208 24.15454c.72084-2.35125 2.90042-3.918678 5.44373-3.918678 2.42769 0 4.73911 1.64869 5.5376 3.914199l.0548.155697h-11.08266zm13.50267 3.406368c.0149.00064.0149.00064.0369.000853.022-.000213.022-.000213.0444-.000853.53055-.02432.9506-.476691.9506-1.027179 0-.02666-.00085-.04969-.003-.08659.00043-.0068.00043-.02026.00043-.06356 0-5.192627-4.07075-9.400732-9.09145-9.400732-5.02049 0-9.09124 4.208105-9.09124 9.400732 0 .387111.0224.773157.0678 1.156856l.003.02048.0141.10131c.25002 1.868373 1.04723 3.583181 2.3063 4.970811-.00064-.00064.00064.00064.007.0079 1.39603 1.536289 3.29146 2.583303 5.39361 2.974894l.0275.0053v-.0028l.13653.02282c.0702.01194.14421.02367.2434.03903 2.76688.332724 5.22124-.06612 7.29949-1.112706.51178-.257861.97193-.542383 1.37064-.842901.12821-.09683.24362-.190463.34346-.278123.0514-.04522.0924-.08318.11413-.105149.4689-.48309.49812-.654357.21525-1.09543-.31146-.495673-.58623-.917977-.82175-1.26755-.0823-.122425-.15402-.226722-.21439-.313315-.0333-.04756-.0333-.04756-.0544-.07764-.008-.01173-.008-.01173-.0139-.0192l-.003-.0038c-.2562-.318861-.43156-.348081-.71593-.09705-.0303.02687-.13162.105149-.14997.124558-1.99932 1.775381-4.72951 2.346557-7.45585 1.776021-.50773-.136289-.9826-.309476-1.41992-.518495-1.70813-.856551-2.91835-2.382602-3.26757-4.149025l-.0275-.139488zm-35.13617-3.406368c.72083-2.35125 2.90039-3.918678 5.44367-3.918678 2.42766 0 4.73906 1.64869 5.53775 3.914199l.0546.155697h-11.08253zm13.50252 3.406368c.0151.00064.0151.00064.0371.000853.0218-.000213.0218-.000213.0442-.000853.53055-.02432.95059-.476691.95059-1.027179 0-.02666-.00086-.04969-.003-.08659.00042-.0068.00042-.02026.00042-.06356 0-5.192627-4.0707-9.400732-9.09134-9.400732-5.02022 0-9.09134 4.208105-9.09134 9.400732 0 .387111.0228.773157.068 1.156856l.003.02048.0141.10131c.25002 1.868373 1.04722 3.583181 2.30627 4.970811-.00064-.00064.00064.00064.007.0079 1.39601 1.536289 3.29141 2.583303 5.39353 2.974894l.0275.0053v-.0028l.13653.02282c.0702.01194.14421.02367.24362.03903 2.76685.332724 5.22117-.06612 7.29919-1.112706.51177-.257861.97191-.542383 1.37062-.842901.12821-.09683.24362-.190463.34346-.278123.0514-.04522.0924-.08318.11413-.105149.46889-.48309.49811-.654357.21524-1.09543-.31145-.495673-.58622-.917977-.82173-1.26755-.0823-.122425-.15402-.226722-.21418-.313315-.0335-.04756-.0335-.04756-.0546-.07764-.008-.01173-.008-.01173-.0139-.0192l-.002-.0038c-.25641-.318861-.43177-.348081-.71613-.09705-.0303.02687-.13163.105149-.14997.124558-1.99909 1.775381-4.72945 2.346557-7.45577 1.776021-.50772-.136289-.98258-.309476-1.4199-.518495-1.70811-.856551-2.9181-2.382602-3.26731-4.149025l-.0277-.139488zm-30.01304 5.177529c-3.3813 0-6.14681-2.680852-6.23169-6.04042v-.316217c.084-3.354187 2.85039-6.035469 6.23169-6.035469 3.44242 0 6.23321 2.77406 6.23321 6.196053 0 3.421994-2.79079 6.196053-6.23321 6.196053m.002-15.679132c-2.21777 0-4.33683.755349-6.0399 2.140764l-.19352.157356v-1.733922c0-.462379-.0994-.564198-.5674-.564198h-2.17652c-.47409 0-.56761.09428-.56761.564198v27.640072c0 .453984.10583.564197.56761.564197h2.17652c.46351 0 .5674-.107845.5674-.564197v-11.52676l.19352.157355c1.70329 1.385631 3.82235 2.140979 6.0399 2.140979 5.27141 0 9.54474-4.247947 9.54474-9.488029 0-5.239867-4.27333-9.487815-9.54474-9.487815m-22.41552 15.467371c-3.44946 0-6.24581-2.749695-6.24581-6.142585 0-3.392891 2.79656-6.142799 6.24581-6.142799 3.44882 0 6.24538 2.749908 6.24538 6.142799 0 3.392677-2.79635 6.142585-6.24538 6.142585m.002-15.543831c-5.28187 0-9.56302 4.21129-9.56302 9.405941 0 5.194437 4.28115 9.40594 9.56302 9.40594 5.28166 0 9.56239-4.211503 9.56239-9.40594 0-5.194651-4.28052-9.405941-9.56239-9.405941m-21.317654.208896c-1.928131 0-3.79389.619899-5.341772 1.765605l-.188189.13931V23.257417c0-.425468-.139582-.564348-.564997-.564348h-2.278269c-.460042 0-.565213.110028-.565213.564348v29.283415c0 .443339.113559.564779.565213.564779h2.278269c.422619 0 .564997-.145771.564997-.564779v-9.56484c.02366-3.011002 2.493128-5.441292 5.529961-5.441292 3.044575 0 5.516844 2.442132 5.529744 5.463039v9.543093c0 .484465.0806.564563.56521.564563h2.27268c.48176 0 .56521-.08247.56521-.564563v-9.571084c-.0181-4.828068-4.044017-8.780654-8.932844-8.780654M62.846335 47.793806l-.0053.0082c-.324866.492119-.460922.699135-.536959.817981-.256091.38121-.224267.551113.166812.881695.91266.735818 2.085897 1.508535 2.877453 1.895537 2.202302 1.076912 4.581027 1.646045 7.101788 1.697531 1.628392.08216 3.673494-.305054 5.196374-.984238 2.756989-1.229868 4.513106-3.626962 4.820886-6.57946.498513-4.784107-2.614313-7.774791-10.632179-10.184971l-.0021-.000644c-3.735648-1.204768-5.490484-2.806407-5.491125-5.042608.109998-2.471535 2.40158-4.317946 5.49326-4.433145 2.490432.0062 4.692307.667599 6.855736 2.059862.456223.276736.666607.22568.973319-.172263.03524-.05878.133279-.201224.543367-.790093l.0013-.0019c.382963-.55004.50898-.733673.540377-.787948.258441-.486326.22149-.67382-.244985-1.004617-.459427-.32908-1.753554-1.002472-2.493636-1.297872-2.058343-.821199-4.186317-1.22-6.304252-1.178811-4.819818.104688-8.578747 3.239317-8.826509 7.351534-.1572 2.970303 1.350301 5.358817 4.500078 7.124567.882971.466161 4.111562 1.68659 5.618423 2.127866 4.284781 1.254753 6.518694 3.563035 6.057131 6.286421-.419272 2.474323-3.011799 4.119939-6.649837 4.232565-2.638234-.05492-5.296332-1.02178-7.60436-2.756209-.01153-.0084-.07006-.05192-.177919-.132361-.151647-.113055-.151647-.113055-.174287-.130002-.470534-.32479-.747343-.29776-1.034619.130216-.0346.05299-.391933.596378-.568143.863246" fill="#EE4D2D" transform="matrix(5.26316 0 0 5.26316 -5.2629262 -10.957342)"/>
                                    </g>
                                </svg>
                                <div class="shopee-brand-sub">Standard Express</div>
                            @endif
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
                                        <td colspan="4" style="text-align: center; padding: 4px; color: #4b5563;">1x Paket Pesanan {{ $channelLabel }}</td>
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
