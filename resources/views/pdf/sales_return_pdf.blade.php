<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>NOTA RETUR #{{ $return->return_number }} - {{ $business->name }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 14mm 12mm 14mm;
        }
        * {
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        body {
            font-size: 10.5px;
            color: #1e293b;
            line-height: 1.4;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* 1. Header / Kop Surat */
        .header-table {
            width: 100%;
            margin-bottom: 14px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
        }
        .brand-logo-img {
            max-height: 44px;
            max-width: 150px;
            object-fit: contain;
            margin-bottom: 4px;
        }
        .brand-name {
            font-size: 14px;
            font-weight: 800;
            color: #020617;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }
        .brand-meta {
            font-size: 9.5px;
            color: #475569;
            margin-top: 2px;
            line-height: 1.35;
        }
        .doc-title {
            font-size: 17px;
            font-weight: 800;
            color: #020617;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 0;
        }
        .doc-number {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            text-align: right;
            margin-top: 2px;
        }
        .doc-ref {
            font-size: 9.5px;
            color: #64748b;
            text-align: right;
            margin-top: 1px;
        }
        .status-badge-container {
            text-align: right;
            margin-top: 4px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            border-radius: 3px;
            letter-spacing: 0.5px;
        }
        .status-draft { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .status-approved { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .status-completed { background-color: #ecfdf5; color: #047857; border: 1px solid #059669; }
        .status-rejected { background-color: #fef2f2; color: #b91c1c; border: 1px solid #f87171; }

        /* 2. Meta Grid (2 Kolom) */
        .meta-table {
            width: 100%;
            margin-bottom: 16px;
        }
        .meta-label {
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 3px;
        }
        .client-name {
            font-size: 12px;
            font-weight: 800;
            color: #020617;
            line-height: 1.3;
        }
        .client-company {
            font-size: 10px;
            font-weight: 700;
            color: #334155;
            margin-top: 1px;
        }
        .client-detail {
            font-size: 9.5px;
            color: #475569;
            margin-top: 2px;
            line-height: 1.35;
        }
        .info-kv-table td {
            padding: 1.5px 0;
            font-size: 10px;
        }
        .info-kv-label {
            color: #64748b;
            width: 48%;
        }
        .info-kv-value {
            text-align: right;
            font-weight: 700;
            color: #0f172a;
            width: 52%;
        }

        /* 3. Items Table */
        .items-table {
            width: 100%;
            margin-bottom: 14px;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 8px;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            text-align: left;
        }
        .items-table td {
            padding: 7px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10.5px;
            vertical-align: top;
        }
        .item-name {
            font-weight: 700;
            color: #020617;
            line-height: 1.3;
        }
        .item-meta {
            font-size: 9px;
            color: #64748b;
            margin-top: 1px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* 4. Financial Calculation & Notes */
        .bottom-table {
            width: 100%;
            border-top: 2px solid #0f172a;
            padding-top: 8px;
            margin-bottom: 16px;
        }
        .notes-heading {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .notes-content {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.35;
            margin-bottom: 8px;
        }
        .reason-box {
            background-color: #f8fafc;
            border-left: 3px solid #64748b;
            padding: 7px 10px;
            font-size: 9.5px;
            color: #334155;
            line-height: 1.4;
        }
        .reason-box-title {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin-bottom: 3px;
        }
        .totals-table {
            width: 100%;
        }
        .totals-table td {
            padding: 2.5px 0;
            font-size: 10.5px;
        }
        .total-label {
            color: #475569;
            text-align: left;
        }
        .total-val {
            font-weight: 700;
            color: #0f172a;
            text-align: right;
        }
        .grand-total-row td {
            border-top: 2px solid #0f172a;
            padding-top: 6px;
            margin-top: 4px;
            font-size: 12.5px;
            font-weight: 800;
            color: #020617;
        }

        /* 5. Signature Section */
        .sign-table {
            width: 100%;
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .sign-box {
            width: 42%;
            text-align: center;
            vertical-align: top;
        }
        .sign-title {
            font-size: 9.5px;
            color: #64748b;
            margin-bottom: 2px;
        }
        .sign-entity {
            font-size: 10.5px;
            font-weight: 700;
            color: #0f172a;
        }
        .sign-space {
            height: 52px;
        }
        .sign-line {
            border-top: 1px solid #64748b;
            width: 75%;
            margin: 0 auto 3px auto;
        }
        .sign-caption {
            font-size: 9px;
            color: #475569;
            font-weight: 600;
        }
        .sign-sub {
            font-size: 8.5px;
            color: #94a3b8;
        }

        /* 6. Footer */
        .footer {
            margin-top: 18px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 8.5px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- 1. HEADER SECTION (KOP SURAT) -->
    <table class="header-table">
        <tr>
            <td style="width: 56%; vertical-align: top;">
                @if(!empty($business->pdf_logo))
                    <div>
                        <img src="{{ $business->pdf_logo }}" alt="{{ $business->name }}" class="brand-logo-img">
                    </div>
                @endif
                <div class="brand-name">{{ $business->name }}</div>
                <div class="brand-meta">
                    @if($business->address){{ $business->address }}<br>@endif
                    @if($business->phone)Telp / WA: {{ $business->phone }} &bull; @endif
                    @if($business->email)Email: {{ $business->email }}@endif
                    @if($business->tax_identification_number)<br>NPWP: {{ $business->tax_identification_number }}@endif
                </div>
            </td>
            <td style="width: 44%; vertical-align: top; text-align: right;">
                <div class="doc-title">NOTA RETUR PENJUALAN</div>
                <div class="doc-number">No: {{ $return->return_number }}</div>
                @if($return->invoice)
                    <div class="doc-ref">Ref. Faktur: {{ $return->invoice->invoice_number }}</div>
                @elseif($return->posOrder)
                    <div class="doc-ref">Ref. Kasir POS: {{ $return->posOrder->order_number }}</div>
                @endif
                <div class="status-badge-container">
                    <span class="status-badge status-{{ $return->status }}">
                        {{ strtoupper($return->status) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- 2. DETAIL RETUR & PELANGGAN (2 KOLOM) -->
    <table class="meta-table">
        <tr>
            <!-- Pihak Pelanggan -->
            <td style="width: 52%; vertical-align: top; padding-right: 14px;">
                <div class="meta-label">Pihak Yang Meretur:</div>
                <div class="client-name">{{ $return->customer?->name ?? 'Pelanggan Umum' }}</div>
                @if($return->customer?->company_name || $return->customer?->company)
                    <div class="client-company">{{ $return->customer->company_name ?? $return->customer->company }}</div>
                @endif
                <div class="client-detail">
                    @if($return->customer?->billing_address || $return->customer?->address)
                        {{ $return->customer->billing_address ?? $return->customer->address }}<br>
                    @endif
                    @if($return->customer?->phone)Telp: {{ $return->customer->phone }} @endif
                    @if($return->customer?->email)&bull; Email: {{ $return->customer->email }} @endif
                </div>
            </td>

            <td style="width: 4%;"></td>

            <!-- Rincian Tanggal & Kompensasi -->
            <td style="width: 44%; vertical-align: top;">
                <div class="meta-label">Informasi Retur:</div>
                <table class="info-kv-table">
                    <tr>
                        <td class="info-kv-label">Tanggal Retur:</td>
                        <td class="info-kv-value">{{ $return->return_date?->format('d/m/Y') ?? date('d/m/Y') }}</td>
                    </tr>
                    @if($return->invoice)
                    <tr>
                        <td class="info-kv-label">Ref. Faktur:</td>
                        <td class="info-kv-value">{{ $return->invoice->invoice_number }}</td>
                    </tr>
                    @elseif($return->posOrder)
                    <tr>
                        <td class="info-kv-label">Ref. Order Kasir:</td>
                        <td class="info-kv-value">{{ $return->posOrder->order_number }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="info-kv-label">Kompensasi:</td>
                        <td class="info-kv-value">
                            @if($return->refund_method === 'cash_refund') Pengembalian Tunai
                            @elseif($return->refund_method === 'credit_note') Nota Kredit
                            @elseif($return->refund_method === 'store_credit') Saldo Toko
                            @elseif($return->refund_method === 'exchange') Tukar Barang
                            @else {{ ucfirst($return->refund_method ?? 'Kredit') }} @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="info-kv-label">Mata Uang:</td>
                        <td class="info-kv-value">{{ $business->currency_symbol ?? 'IDR' }} (Rupiah)</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- 3. TABEL RINCIAN ITEM RETUR -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 43%;">Barang Yang Diretur</th>
                <th style="width: 9%;" class="text-center">Satuan</th>
                <th style="width: 11%;" class="text-right">Qty Retur</th>
                <th style="width: 16%;" class="text-right">Harga Satuan</th>
                <th style="width: 16%;" class="text-right">Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($return->items as $idx => $item)
            <tr>
                <td class="text-center" style="color: #64748b;">{{ $idx + 1 }}</td>
                <td>
                    <div class="item-name">{{ $item->item_name ?? ($item->product_name ?? $item->product?->name) }}</div>
                    @if($item->sku ?? ($item->product?->code ?? $item->product?->sku))
                        <div class="item-meta">SKU: {{ $item->sku ?? ($item->product?->code ?? $item->product?->sku) }}</div>
                    @endif
                </td>
                <td class="text-center" style="text-transform: uppercase; color: #475569;">
                    {{ $item->product?->outputUnit?->code ?? ($item->product?->unit ?? 'pcs') }}
                </td>
                <td class="text-right" style="font-weight: 700; color: #b91c1c;">
                    {{ (float) $item->quantity == (int) $item->quantity ? number_format((float) $item->quantity, 0, ',', '.') : rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }}
                </td>
                <td class="text-right" style="color: #334155;">
                    Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                </td>
                <td class="text-right" style="font-weight: 700; color: #020617;">
                    Rp {{ number_format((float) ($item->subtotal ?? ($item->quantity * $item->unit_price)), 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 16px; color: #94a3b8;">Tidak ada item yang diretur.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. TOTALS & ALASAN RETUR -->
    <table class="bottom-table">
        <tr>
            <td style="width: 54%; vertical-align: top; padding-right: 18px;">
                <div class="reason-box">
                    <div class="reason-box-title">Alasan Retur / Kerusakan Barang:</div>
                    <div>{{ $return->reason ?? 'Tidak ada alasan khusus dicatat.' }}</div>
                </div>
            </td>

            <td style="width: 46%; vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td class="total-label">Total Barang Diretur:</td>
                        <td class="total-val">{{ $return->items->sum('quantity') }} unit</td>
                    </tr>
                    <tr class="grand-total-row">
                        <td>Total Nilai Retur:</td>
                        <td class="text-right">Rp {{ number_format((float) $return->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- 5. TANDA TANGAN OTORISASI -->
    <table class="sign-table">
        <tr>
            <td class="sign-box">
                <div class="sign-title">Pihak Yang Meretur,</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div class="sign-caption">( {{ $return->customer?->company_name ?? ($return->customer?->name ?? '....................................') }} )</div>
                <div class="sign-sub">Pelanggan / Pembeli</div>
            </td>
            <td style="width: 16%;"></td>
            <td class="sign-box">
                <div class="sign-title">{{ $business->name }},</div>
                <div class="sign-entity">Gudang & Pengendalian Kualitas</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div class="sign-caption">( QC & Warehouse Dept )</div>
                <div class="sign-sub">Authorized Signature</div>
            </td>
        </tr>
    </table>

    <!-- 6. FOOTER RESMI -->
    <div class="footer">
        Bukti Retur Resmi COOCA ERP &bull; Dicetak otomatis pada {{ date('d/m/Y H:i') }} WIB
    </div>

</body>
</html>
