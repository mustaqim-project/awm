<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Faktur Penjualan #{{ $invoice->invoice_number }} - {{ $business->name }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 18mm 20mm 18mm 20mm;
        }
        * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }
        body {
            font-size: 10px;
            color: #111827;
            line-height: 1.45;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* 1. Kop Surat Resmi Indonesia */
        .kop-table {
            width: 100%;
            margin-bottom: 0px;
        }
        .kop-logo-cell {
            width: 75px;
            vertical-align: middle;
            padding-right: 14px;
        }
        .kop-logo {
            max-height: 55px;
            max-width: 105px;
            object-fit: contain;
        }
        .kop-text-cell {
            vertical-align: middle;
            text-align: left;
        }
        .kop-title {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .kop-meta {
            font-size: 9px;
            color: #334155;
            margin-top: 3px;
            line-height: 1.35;
        }
        .kop-divider {
            margin-top: 8px;
            margin-bottom: 14px;
        }
        .kop-line-thick {
            border-top: 2.5px solid #0f172a;
            margin-bottom: 1.5px;
        }
        .kop-line-thin {
            border-top: 0.75px solid #0f172a;
        }

        /* 2. Metadata Faktur & Tanggal */
        .meta-section-table {
            width: 100%;
            margin-bottom: 12px;
        }
        .letter-ref-table td {
            font-size: 10px;
            padding: 1.5px 0;
            vertical-align: top;
        }
        .ref-label {
            width: 90px;
            color: #475569;
        }
        .ref-sep {
            width: 12px;
            color: #475569;
            text-align: center;
        }
        .ref-val {
            color: #0f172a;
            font-weight: 600;
        }
        .letter-date-box {
            text-align: right;
            font-size: 10px;
            color: #0f172a;
        }

        /* 3. Kepada Yth. & Detail Penagihan */
        .recipient-box {
            margin-top: 8px;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        .recipient-greeting {
            font-size: 10px;
            color: #334155;
        }
        .recipient-name {
            font-size: 10.5px;
            font-weight: bold;
            color: #0f172a;
        }
        .recipient-company {
            font-size: 10px;
            font-weight: 600;
            color: #334155;
        }
        .recipient-address {
            font-size: 9.5px;
            color: #475569;
        }

        /* 4. Tabel Rincian Resmi */
        .official-table {
            width: 100%;
            margin-bottom: 12px;
            border: 1px solid #334155;
        }
        .official-table th {
            background-color: #f8fafc;
            color: #0f172a;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 6px 7px;
            border: 1px solid #334155;
        }
        .official-table td {
            padding: 6px 7px;
            border: 1px solid #cbd5e1;
            font-size: 9.5px;
            color: #1e293b;
            vertical-align: top;
        }
        .item-name {
            font-weight: bold;
            color: #0f172a;
        }
        .item-meta {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 1px;
        }

        /* 5. Summary Section */
        .summary-section-table {
            width: 100%;
            margin-bottom: 12px;
        }
        .terbilang-box {
            background-color: #f8fafc;
            border-left: 3px solid #0f172a;
            padding: 6px 9px;
            margin-bottom: 8px;
            font-size: 9.5px;
            color: #1e293b;
            line-height: 1.4;
        }
        .terbilang-label {
            font-size: 8.5px;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
        }
        .bank-box {
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            padding: 7px 10px;
            font-size: 9px;
            color: #334155;
            line-height: 1.4;
        }
        .bank-box-title {
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .totals-table td {
            padding: 2.5px 0;
            font-size: 10px;
        }
        .total-lbl {
            width: 50%;
            color: #475569;
        }
        .total-sep {
            width: 6%;
            color: #475569;
            text-align: center;
        }
        .total-val {
            width: 44%;
            text-align: right;
            font-weight: 600;
            color: #0f172a;
        }
        .grand-total-row td {
            border-top: 1.5px solid #0f172a;
            border-bottom: 1px solid #cbd5e1;
            padding: 5px 0;
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
        }
        .balance-due-row td {
            border-top: 1px solid #cbd5e1;
            border-bottom: 1.5px solid #0f172a;
            padding: 5px 0;
            font-size: 11px;
            font-weight: bold;
            color: #b91c1c;
        }

        /* 6. Tanda Tangan */
        .signature-table {
            width: 100%;
            margin-top: 14px;
            page-break-inside: avoid;
        }
        .sig-box {
            width: 45%;
            text-align: center;
            vertical-align: top;
        }
        .sig-title {
            font-size: 9.5px;
            color: #334155;
        }
        .sig-company {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 1px;
        }
        .sig-space {
            height: 55px;
        }
        .sig-name {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
        }
        .sig-caption {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 2px;
        }

        .official-footer {
            margin-top: 18px;
            padding-top: 6px;
            border-top: 0.5px solid #cbd5e1;
            font-size: 8px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    <table class="kop-table">
        <tr>
            @if(!empty($business->pdf_logo))
            <td class="kop-logo-cell">
                <img src="{{ $business->pdf_logo }}" alt="{{ $business->name }}" class="kop-logo">
            </td>
            @endif
            <td class="kop-text-cell">
                <div class="kop-title">{{ $business->name }}</div>
                @if($invoice->sender_name)
                    <div style="font-size: 9.5px; font-weight: bold; color: #334155;">Pengirim: {{ $invoice->sender_name }}</div>
                @endif
                <div class="kop-meta">
                    @if($business->address){{ $business->address }}<br>@endif
                    @if($business->phone)Telp / WhatsApp: {{ $business->phone }} &bull; @endif
                    @if($business->email)Email: {{ $business->email }}@endif
                    @if($business->tax_identification_number)<br>NPWP: {{ $business->tax_identification_number }}@endif
                </div>
            </td>
        </tr>
    </table>

    <div class="kop-divider">
        <div class="kop-line-thick"></div>
        <div class="kop-line-thin"></div>
    </div>

    <!-- NOMOR FAKTUR & TANGGAL RESMI -->
    <table class="meta-section-table">
        <tr>
            <td style="width: 58%; vertical-align: top;">
                <table class="letter-ref-table">
                    <tr>
                        <td class="ref-label">No. Faktur</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val">{{ $invoice->invoice_number }}</td>
                    </tr>
                    @if($invoice->purchaseOrder)
                    <tr>
                        <td class="ref-label">Ref. No. PO</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val">{{ $invoice->purchaseOrder->po_number }}</td>
                    </tr>
                    @elseif($invoice->salesOrder)
                    <tr>
                        <td class="ref-label">Ref. No. SO</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val">{{ $invoice->salesOrder->so_number }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="ref-label">Perihal</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val" style="font-weight: bold;">Faktur Penjualan (Commercial Invoice)</td>
                    </tr>
                </table>
            </td>
            <td style="width: 42%; vertical-align: top; text-align: right;">
                <div class="letter-date-box">
                    Tanggal Faktur: <strong>{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->locale('id')->translatedFormat('d F Y') : date('d F Y') }}</strong>
                </div>
                <div style="font-size: 9px; color: #b91c1c; margin-top: 3px;">
                    Jatuh Tempo: <strong>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->locale('id')->translatedFormat('d F Y') : 'Sesuai Ketentuan' }}</strong>
                </div>
                @if($invoice->payment_terms)
                <div style="font-size: 9px; color: #475569; margin-top: 2px;">
                    Syarat Pembayaran: {{ $invoice->payment_terms }}
                </div>
                @endif
            </td>
        </tr>
    </table>

    <!-- TUJUAN PENAGIHAN (KEPADA YTH.) -->
    <div class="recipient-box">
        <div class="recipient-greeting">Kepada Yth.</div>
        <div class="recipient-name">{{ $invoice->recipient_name ?: ($invoice->customer?->name ?? 'Pelanggan / Pembeli') }}</div>
        @if($invoice->customer?->company_name || $invoice->customer?->company)
            <div class="recipient-company">{{ $invoice->customer->company_name ?? $invoice->customer->company }}</div>
        @endif
        @if($invoice->customer?->billing_address || $invoice->customer?->address)
            <div class="recipient-address">{{ $invoice->customer->billing_address ?? $invoice->customer->address }}</div>
        @endif
        @if($invoice->customer?->phone)
            <div style="font-size: 9px; color: #475569;">Telp: {{ $invoice->customer->phone }}</div>
        @endif
        @if($invoice->customer?->tax_identification_number)
            <div style="font-size: 9px; color: #475569;">NPWP: {{ $invoice->customer->tax_identification_number }}</div>
        @endif
    </div>

    <!-- TABEL RINCIAN BARANG / JASA -->
    <table class="official-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No.</th>
                <th style="width: 45%;">Deskripsi Produk / Jasa</th>
                <th style="width: 9%; text-align: center;">Satuan</th>
                <th style="width: 9%; text-align: right;">Jumlah</th>
                <th style="width: 16%; text-align: right;">Harga Satuan (Rp)</th>
                <th style="width: 16%; text-align: right;">Subtotal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $idx => $item)
            <tr>
                <td style="text-align: center; color: #475569;">{{ $idx + 1 }}.</td>
                <td>
                    <div class="item-name">{{ $item->item_name ?? $item->product_name }}</div>
                    @if($item->sku || $item->product?->sku || $item->product?->code)
                        <div class="item-meta">Kode: {{ $item->sku ?? ($item->product?->sku ?? $item->product?->code) }}</div>
                    @endif
                    @if($item->description)
                        <div class="item-meta">{{ $item->description }}</div>
                    @endif
                </td>
                <td style="text-align: center;">
                    {{ $item->unit?->code ?? ($item->unit?->symbol ?? 'Pcs') }}
                </td>
                <td style="text-align: right; font-weight: bold;">
                    {{ (float) $item->quantity == (int) $item->quantity ? number_format((float) $item->quantity, 0, ',', '.') : rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }}
                </td>
                <td style="text-align: right;">
                    {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                </td>
                <td style="text-align: right; font-weight: bold;">
                    {{ number_format((float) ($item->total_price ?? $item->subtotal ?? ($item->quantity * $item->unit_price)), 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 16px; color: #64748b;">Tidak ada rincian item faktur.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- RINGKASAN PEMBAYARAN & REKENING BANK -->
    <table class="summary-section-table">
        <tr>
            <td style="width: 54%; vertical-align: top; padding-right: 18px;">
                <div class="terbilang-box">
                    <span class="terbilang-label">Terbilang:</span><br>
                    <em># {{ \App\Support\Math\FinancialMath::terbilang($invoice->total_amount) }} #</em>
                </div>

                @if($invoice->notes)
                <div style="font-size: 9px; margin-bottom: 6px;">
                    <strong>Catatan Penagihan:</strong><br>
                    {!! nl2br(e($invoice->notes)) !!}
                </div>
                @endif

                <div class="bank-box">
                    <div class="bank-box-title">Instruksi Pembayaran Transfer Bank:</div>
                    <div>Pembayaran dapat ditransfer ke rekening resmi&nbsp;<strong>{{ $business->name }}</strong>:</div>
                    <div style="margin-top: 3px;">
                        Bank: <strong>{{ str_starts_with(strtolower($business->bank_name ?? ''), 'bank') ? $business->bank_name : 'Bank ' . ($business->bank_name ?? 'BCA / Mandiri / BNI') }}</strong><br>
                        No. Rekening: <strong>{{ $business->bank_account_number ?? '-' }}</strong><br>
                        Atas Nama: <strong>{{ $business->bank_account_holder ?? $business->name }}</strong>
                    </div>
                    <div style="font-size: 8.5px; color: #64748b; margin-top: 3px;">
                        * Mohon cantumkan No. Faktur (<strong>{{ $invoice->invoice_number }}</strong>) pada keterangan transfer.
                    </div>
                </div>
            </td>
            <td style="width: 46%; vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td class="total-lbl">Subtotal</td>
                        <td class="total-sep">:</td>
                        <td class="total-val">Rp {{ number_format((float) $invoice->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if((float) $invoice->discount_amount > 0)
                    <tr>
                        <td class="total-lbl">Diskon Khusus</td>
                        <td class="total-sep">:</td>
                        <td class="total-val" style="color: #b91c1c;">- Rp {{ number_format((float) $invoice->discount_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if((float) $invoice->tax_amount > 0)
                    <tr>
                        <td class="total-lbl">Pajak (PPN {{ $invoice->tax_percentage ?? 11 }}%)</td>
                        <td class="total-sep">:</td>
                        <td class="total-val">+ Rp {{ number_format((float) $invoice->tax_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if((float) $invoice->shipping_cost > 0)
                    <tr>
                        <td class="total-lbl">Ongkos Kirim</td>
                        <td class="total-sep">:</td>
                        <td class="total-val">+ Rp {{ number_format((float) $invoice->shipping_cost, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total-row">
                        <td class="total-lbl">Total Tagihan</td>
                        <td class="total-sep">:</td>
                        <td class="total-val">Rp {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    @php
                        $paid = (float) ($invoice->paid_amount ?? ($invoice->status === 'paid' ? $invoice->total_amount : 0));
                        $balance = max(0, (float)$invoice->total_amount - $paid);
                    @endphp
                    <tr>
                        <td class="total-lbl" style="color: #047857; padding-top: 4px;">Telah Dibayar</td>
                        <td class="total-sep" style="color: #047857; padding-top: 4px;">:</td>
                        <td class="total-val" style="color: #047857; padding-top: 4px;">Rp {{ number_format($paid, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="balance-due-row">
                        <td class="total-lbl">Sisa Tagihan</td>
                        <td class="total-sep">:</td>
                        <td class="total-val">Rp {{ number_format($balance, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- TANDA TANGAN RESMI -->
    <table class="signature-table">
        <tr>
            <td class="sig-box">
                <div class="sig-title">Penerima / Pembeli,</div>
                <div class="sig-company">{{ $invoice->customer?->company_name ?? ($invoice->customer?->name ?? 'Pihak Pembeli') }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">( {{ $invoice->recipient_name ?: ($invoice->customer?->name ?? '..................................................') }} )</div>
                <div class="sig-caption">Tanda Tangan &amp; Cap Pembeli</div>
            </td>
            <td style="width: 10%;"></td>
            <td class="sig-box">
                <div class="sig-title">Hormat kami,</div>
                <div class="sig-company">{{ $business->name }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">( {{ $invoice->sender_name ?: '..................................................' }} )</div>
                <div class="sig-caption">Bagian Keuangan &amp; Administrasi</div>
            </td>
        </tr>
    </table>

    <!-- FOOTER RESMI -->
    <div class="official-footer">
        Faktur ini sah dan diakui sebagai bukti penagihan resmi dari {{ $business->name }}.
    </div>

</body>
</html>
