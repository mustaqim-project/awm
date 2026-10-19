<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Faktur Penjualan {{ $invoice->invoice_number }} - {{ $business->name }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 14mm 12mm 14mm;
        }
        * {
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        body {
            font-size: 11px;
            color: #1e293b;
            line-height: 1.45;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table {
            margin-bottom: 16px;
            border-bottom: 2px solid #007aff;
            padding-bottom: 12px;
        }
        .brand-logo {
            font-size: 22px;
            font-weight: 800;
            color: #007aff;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .brand-sub {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
            line-height: 1.35;
        }
        .doc-title-main {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            text-align: right;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-number {
            font-size: 12px;
            font-weight: 700;
            color: #007aff;
            text-align: right;
            margin-top: 2px;
        }
        .status-badge {
            display: inline-block;
            margin-top: 5px;
            padding: 3px 10px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }
        .status-unpaid { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .status-partial { background-color: #fefce8; color: #854d0e; border: 1px solid #fef08a; }
        .status-paid { background-color: #ecfdf5; color: #047857; border: 1.5px solid #059669; }
        .status-overdue { background-color: #7f1d1d; color: #ffffff; border: 1px solid #991b1b; }
        .status-draft { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        .meta-table {
            margin-bottom: 18px;
        }
        .meta-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            vertical-align: top;
        }
        .meta-title {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 800;
            color: #64748b;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 3px;
        }
        .customer-name {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 3px;
        }

        .items-table {
            margin-top: 8px;
            margin-bottom: 16px;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            text-align: left;
        }
        .items-table td {
            padding: 9px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
            vertical-align: middle;
        }
        .items-table tr:nth-child(even) td {
            background-color: #fafafa;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .summary-table {
            margin-top: 6px;
            margin-bottom: 18px;
        }
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
        }
        .summary-row {
            padding: 4px 0;
            font-size: 11px;
        }
        .summary-total {
            border-top: 2px solid #007aff;
            margin-top: 6px;
            padding-top: 6px;
            font-size: 13px;
            font-weight: 800;
            color: #007aff;
        }
        .summary-balance {
            border-top: 1px solid #e2e8f0;
            margin-top: 4px;
            padding-top: 4px;
            font-size: 12px;
            font-weight: 800;
            color: #b91c1c;
        }

        .payment-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 10px;
            color: #166534;
            margin-bottom: 16px;
        }

        .sign-table {
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .sign-box {
            text-align: center;
            vertical-align: top;
            width: 38%;
        }
        .sign-space {
            height: 60px;
        }
        .sign-line {
            border-bottom: 1px solid #334155;
            margin: 0 20px;
        }

        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- 1. HEADER SECTION -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <h1 class="brand-logo">{{ $business->name }}</h1>
                <div class="brand-sub">
                    @if($business->address) {{ $business->address }}<br> @endif
                    @if($business->phone) Telp / WA: {{ $business->phone }} &bull; @endif
                    @if($business->email) Email: {{ $business->email }} @endif
                </div>
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div class="doc-title-main">Faktur Penjualan (Invoice)</div>
                <div class="doc-number">No: {{ $invoice->invoice_number }}</div>
                <div>
                    @php
                        $paymentStatus = $invoice->payment_status ?? ($invoice->status === 'paid' ? 'paid' : 'unpaid');
                    @endphp
                    <span class="status-badge status-{{ $paymentStatus }}">
                        {{ strtoupper($paymentStatus === 'paid' ? 'LUNAS (PAID)' : ($paymentStatus === 'partial' ? 'DIBAYAR SEBAGIAN' : 'BELUM DIBAYAR')) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- 2. META & CLIENT INFO -->
    <table class="meta-table">
        <tr>
            <!-- Kepada Pembeli -->
            <td class="meta-card" style="width: 52%;">
                <div class="meta-title">Ditagihkan Kepada</div>
                <div class="customer-name">{{ $invoice->customer?->name ?? 'Pelanggan Umum' }}</div>
                @if($invoice->customer?->company)
                    <div style="font-weight: 600; color: #475569;">{{ $invoice->customer->company }}</div>
                @endif
                @if($invoice->customer?->address)
                    <div style="color: #64748b; margin-top: 2px;">{{ $invoice->customer->address }}</div>
                @endif
                @if($invoice->customer?->phone)
                    <div style="color: #64748b; margin-top: 2px;">Telp: {{ $invoice->customer->phone }}</div>
                @endif
            </td>
            <td style="width: 4%;"></td>
            <!-- Detail Tanggal & Referensi -->
            <td class="meta-card" style="width: 44%;">
                <div class="meta-title">Rincian Faktur</div>
                <table style="width: 100%; font-size: 11px;">
                    <tr>
                        <td style="color: #64748b; padding: 2px 0;">Tanggal Faktur:</td>
                        <td style="font-weight: 700; text-align: right;">{{ $invoice->invoice_date?->format('d/m/Y') ?? date('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; padding: 2px 0;">Jatuh Tempo:</td>
                        <td style="font-weight: 700; text-align: right; color: #b91c1c;">
                            {{ $invoice->due_date?->format('d/m/Y') ?? 'Sesuai Ketentuan' }}
                        </td>
                    </tr>
                    @if($invoice->salesOrder)
                    <tr>
                        <td style="color: #64748b; padding: 2px 0;">Ref. No. SO:</td>
                        <td style="font-weight: 700; text-align: right; color: #007aff;">{{ $invoice->salesOrder->so_number }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color: #64748b; padding: 2px 0;">Mata Uang:</td>
                        <td style="font-weight: 700; text-align: right;">IDR (Rupiah)</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- 3. ITEMS TABLE -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 45%;">Deskripsi Produk / Jasa</th>
                <th style="width: 18%;" class="text-right">Harga Satuan</th>
                <th style="width: 12%;" class="text-center">Kuantitas</th>
                <th style="width: 20%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $idx => $item)
            <tr>
                <td class="text-center" style="color: #64748b;">{{ $idx + 1 }}</td>
                <td>
                    <div style="font-weight: 700; color: #0f172a;">{{ $item->product_name }}</div>
                    @if($item->product?->code || $item->product?->sku)
                        <div style="font-size: 9.5px; color: #64748b;">SKU: {{ $item->product->code ?? $item->product->sku }}</div>
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-center" style="font-weight: 600;">{{ $item->quantity + 0 }}</td>
                <td class="text-right" style="font-weight: 700;">Rp {{ number_format($item->total_price ?? ($item->quantity * $item->unit_price), 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="padding: 16px; color: #94a3b8;">Tidak ada item rincian faktur.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. TOTALS & NOTES -->
    <table class="summary-table">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 16px;">
                @if($invoice->notes)
                <div style="margin-bottom: 12px;">
                    <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #64748b; margin-bottom: 3px;">Catatan Penagihan:</div>
                    <div style="font-size: 10.5px; color: #334155; line-height: 1.4;">{{ $invoice->notes }}</div>
                </div>
                @endif

                <div class="payment-box">
                    <strong style="display: block; margin-bottom: 3px; text-transform: uppercase;">Informasi Rekening Pembayaran:</strong>
                    <div>Silakan transfer ke rekening resmi <strong>{{ $business->name }}</strong></div>
                    <div style="margin-top: 3px; font-size: 10.5px;">BCA / Mandiri / BNI a.n <strong>{{ $business->name }}</strong></div>
                    <div style="margin-top: 3px; font-size: 9.5px; color: #15803d;">Sertakan No. Faktur ({{ $invoice->invoice_number }}) pada berita transfer.</div>
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <div class="summary-box">
                    <table style="width: 100%;">
                        <tr>
                            <td class="summary-row" style="color: #64748b;">Subtotal:</td>
                            <td class="summary-row text-right" style="font-weight: 700;">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if((float) $invoice->discount_amount > 0)
                        <tr>
                            <td class="summary-row" style="color: #64748b;">Diskon:</td>
                            <td class="summary-row text-right" style="font-weight: 700; color: #16a34a;">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if((float) $invoice->tax_amount > 0)
                        <tr>
                            <td class="summary-row" style="color: #64748b;">Pajak (PPN):</td>
                            <td class="summary-row text-right" style="font-weight: 700;">+ Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="summary-total">Total Tagihan:</td>
                            <td class="summary-total text-right">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        @php
                            $paid = (float) ($invoice->paid_amount ?? ($paymentStatus === 'paid' ? $invoice->total_amount : 0));
                            $balance = max(0, (float)$invoice->total_amount - $paid);
                        @endphp
                        <tr>
                            <td class="summary-row" style="color: #047857;">Telah Dibayar:</td>
                            <td class="summary-row text-right" style="font-weight: 700; color: #047857;">Rp {{ number_format($paid, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="summary-balance">Sisa Tagihan:</td>
                            <td class="summary-balance text-right">Rp {{ number_format($balance, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- 5. SIGNATURE SECTION -->
    <table class="sign-table">
        <tr>
            <td class="sign-box">
                <div style="font-size: 10px; color: #64748b; margin-bottom: 4px;">Penerima / Pembeli:</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-size: 10.5px; font-weight: 700; margin-top: 4px;">( {{ $invoice->customer?->name ?? '....................................' }} )</div>
                <div style="font-size: 9px; color: #94a3b8;">Tanda Tangan & Nama Terang</div>
            </td>
            <td style="width: 24%;"></td>
            <td class="sign-box">
                <div style="font-size: 10px; color: #64748b; margin-bottom: 4px;">Bagian Keuangan,</div>
                <div style="font-size: 11px; font-weight: 700;">{{ $business->name }}</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-size: 10.5px; font-weight: 700; margin-top: 4px;">( Finance Department )</div>
                <div style="font-size: 9px; color: #94a3b8;">Authorized Signature</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Faktur ini sah dan diakui sebagai bukti tagihan resmi dari {{ $business->name }} &bull; Dicetak: {{ date('d/m/Y H:i') }} WIB
    </div>

</body>
</html>
