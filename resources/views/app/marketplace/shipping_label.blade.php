<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Pengiriman Marketplace - {{ $business->name }}</title>
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
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f3f4f6;
            color: #111827;
            font-size: 11px;
            line-height: 1.25;
        }
        .screen-toolbar {
            position: fixed;
            top: 12px;
            right: 12px;
            z-index: 9999;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            padding: 8px 14px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
        }
        .screen-toolbar button {
            background: #007AFF;
            color: #ffffff;
            border: none;
            padding: 7px 14px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,122,255,0.3);
        }
        .screen-toolbar button:hover {
            background: #0062cc;
        }
        .screen-toolbar a {
            color: #4b5563;
            text-decoration: none;
            font-weight: 500;
        }
        .label-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            gap: 20px;
        }
        .label-page {
            width: 100mm;
            height: 148mm;
            background: #ffffff;
            border: 2px solid #000000;
            padding: 3mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            page-break-after: always;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
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
            }
        }

        /* Label Structure */
        .header-section {
            border-bottom: 2px solid #000000;
            padding-bottom: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .channel-badge {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .carrier-badge {
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
            padding: 2px 6px;
            border: 2px solid #000000;
            border-radius: 4px;
        }
        .barcode-section {
            text-align: center;
            padding: 4px 0;
            border-bottom: 1.5px dashed #000000;
        }
        .barcode-mock {
            display: inline-flex;
            gap: 1.5px;
            height: 38px;
            align-items: flex-end;
            margin: 2px 0;
        }
        .barcode-line {
            background: #000000;
            height: 100%;
        }
        .awb-code {
            font-family: monospace;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 2px;
        }
        .order-sn-text {
            font-size: 9.5px;
            color: #374151;
            font-family: monospace;
        }
        .route-section {
            display: flex;
            border-bottom: 2px solid #000000;
            background: #f9fafb;
            font-weight: bold;
        }
        .route-col {
            flex: 1;
            padding: 4px;
            border-right: 1.5px solid #000000;
            text-align: center;
        }
        .route-col:last-child {
            border-right: none;
        }
        .route-title {
            font-size: 8px;
            text-transform: uppercase;
            color: #6b7280;
        }
        .route-value {
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .address-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1.5px solid #000000;
            min-height: 85px;
        }
        .addr-box {
            padding: 4px;
        }
        .addr-box.recipient {
            border-right: 1.5px solid #000000;
        }
        .addr-label {
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 2px;
            display: block;
        }
        .addr-name {
            font-size: 11px;
            font-weight: 800;
            margin-bottom: 2px;
        }
        .addr-detail {
            font-size: 9.5px;
            line-height: 1.25;
            color: #1f2937;
        }
        .items-section {
            flex-grow: 1;
            padding: 4px 0;
            overflow: hidden;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .items-table th {
            border-bottom: 1px solid #000000;
            text-align: left;
            padding: 2px;
            font-size: 8px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 2.5px 2px;
            border-bottom: 0.5px dotted #9ca3af;
            vertical-align: top;
        }
        .footer-section {
            border-top: 2px solid #000000;
            padding-top: 3px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 9px;
        }
        .cod-badge {
            font-size: 11px;
            font-weight: 900;
            padding: 2px 6px;
            border: 1.5px solid #000000;
            border-radius: 4px;
            background: #ffffff;
        }
        .cod-badge.is-cod {
            background: #000000;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <!-- On-screen Toolbar -->
    <div class="screen-toolbar">
        <span><strong>{{ $orders->count() }}</strong> Label Siap Dicetak</span>
        <button type="button" onclick="window.print()">Cetak Label (Thermal 100x150)</button>
        <a href="javascript:history.back()">Kembali</a>
    </div>

    <div class="label-container">
        @foreach($orders as $order)
            @php
                $recipient = $order->raw_payload['recipient_address'] ?? [];
                $city = $recipient['city'] ?? 'KOTA TUJUAN';
                $district = $recipient['district'] ?? ($recipient['state'] ?? 'KECAMATAN');
                $trackingNo = $order->tracking_number ?: ($order->external_order_sn ?: $order->external_order_id);
                $isCod = str_contains(strtolower((string) ($order->raw_payload['payment_method'] ?? '')), 'cod');
                $items = is_array($order->items_summary) ? $order->items_summary : [];
            @endphp

            <div class="label-page">
                <div>
                    <!-- 1. Header: Channel & Courier -->
                    <div class="header-section">
                        <div class="channel-badge">
                            @if($order->channel === 'shopee')
                                <span style="color: #EE4D2D;">SHOPEE</span>
                            @elseif($order->channel === 'tiktok_shop')
                                <span>TIKTOK SHOP</span>
                            @elseif($order->channel === 'tokopedia')
                                <span style="color: #03AC0E;">TOKOPEDIA</span>
                            @else
                                <span>{{ strtoupper($order->channel) }}</span>
                            @endif
                        </div>
                        <div class="carrier-badge">
                            {{ $order->shipping_provider ?: 'REGULER' }}
                        </div>
                    </div>

                    <!-- 2. Barcode & Tracking Number -->
                    <div class="barcode-section">
                        <div class="barcode-mock">
                            @for($i = 0; $i < 48; $i++)
                                <div class="barcode-line" style="width: {{ ($i % 3 === 0) ? '3px' : (($i % 2 === 0) ? '1.5px' : '2px') }};"></div>
                            @endfor
                        </div>
                        <div class="awb-code">{{ $trackingNo }}</div>
                        <div class="order-sn-text">No. Pesanan: {{ $order->external_order_sn ?: $order->external_order_id }}</div>
                    </div>

                    <!-- 3. Sort Code / Destination Area -->
                    <div class="route-section">
                        <div class="route-col">
                            <div class="route-title">Kota / Kabupaten</div>
                            <div class="route-value">{{ $city }}</div>
                        </div>
                        <div class="route-col">
                            <div class="route-title">Kecamatan / Area</div>
                            <div class="route-value">{{ $district }}</div>
                        </div>
                    </div>

                    <!-- 4. Address Section: Recipient & Sender -->
                    <div class="address-section">
                        <div class="addr-box recipient">
                            <span class="addr-label">Penerima:</span>
                            <div class="addr-name">{{ $order->buyer_name }}</div>
                            <div style="font-weight: 700; margin-bottom: 2px;">{{ $order->buyer_phone ?: '-' }}</div>
                            <div class="addr-detail">
                                {{ $recipient['full_address'] ?? ($recipient['address'] ?? ($recipient['city'] ?? 'Alamat tujuan pesanan')) }}
                            </div>
                        </div>

                        <div class="addr-box">
                            <span class="addr-label">Pengirim:</span>
                            <div class="addr-name">{{ $order->account->shop_name ?? $business->name }}</div>
                            <div style="font-weight: 700; margin-bottom: 2px;">{{ $business->phone ?: '-' }}</div>
                            <div class="addr-detail">
                                {{ $business->address ?: ($business->city ?? 'Indonesia') }}
                            </div>
                        </div>
                    </div>

                    <!-- 5. Items List Summary -->
                    <div class="items-section">
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th style="width: 14px;">No</th>
                                    <th>Nama Produk / SKU</th>
                                    <th style="width: 55px;">Variasi</th>
                                    <th style="width: 25px; text-align: center;">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(array_slice($items, 0, 5) as $idx => $item)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>
                                            <div style="font-weight: 700; max-height: 24px; overflow: hidden;">{{ $item['item_name'] ?? ($item['name'] ?? 'Produk') }}</div>
                                            @if(!empty($item['sku']))
                                                <div style="font-size: 8px; color: #4b5563;">SKU: {{ $item['sku'] }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $item['model_name'] ?? '-' }}</td>
                                        <td style="text-align: center; font-weight: 800;">{{ $item['quantity'] ?? 1 }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 8px; color: #6b7280;">1x Paket Marketplace</td>
                                    </tr>
                                @endforelse
                                @if(count($items) > 5)
                                    <tr>
                                        <td colspan="4" style="text-align: center; font-size: 8px; font-weight: 700;">+ {{ count($items) - 5 }} produk lainnya...</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 6. Footer: COD Status & Date -->
                <div class="footer-section">
                    <div class="cod-badge {{ $isCod ? 'is-cod' : '' }}">
                        {{ $isCod ? 'COD (BAYAR DI TEMPAT)' : 'NON-COD (LUNAS)' }}
                    </div>
                    <div style="text-align: right;">
                        <div>Tgl: {{ $order->placed_at ? $order->placed_at->format('d/m/Y H:i') : date('d/m/Y H:i') }}</div>
                        <div style="font-size: 7.5px; color: #6b7280;">Dicetak via COOCA Platform</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if(!empty($autoPrint))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.print();
                }, 400);
            });
        </script>
    @endif
</body>
</html>
