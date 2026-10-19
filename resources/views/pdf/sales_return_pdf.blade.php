<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Nota Retur #{{ $return->return_number }} - {{ $business->name }}</title>
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
            line-height: 1.5;
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
            line-height: 1.4;
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

        /* 2. Metadata Retur & Tanggal */
        .meta-section-table {
            width: 100%;
            margin-bottom: 14px;
        }
        .letter-ref-table td {
            font-size: 10px;
            padding: 2px 0;
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

        .letter-ref-table-right td {
            font-size: 10px;
            padding: 2px 0;
            vertical-align: top;
            text-align: right;
        }
        .ref-label-right {
            color: #475569;
        }
        .ref-val-right {
            color: #0f172a;
        }

        /* 3. Kepada Yth. & Detail Pelanggan */
        .recipient-box {
            margin-top: 4px;
            margin-bottom: 14px;
            line-height: 1.45;
        }
        .recipient-greeting {
            font-size: 10px;
            color: #334155;
        }
        .recipient-name {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
        }
        .recipient-attn {
            font-size: 10px;
            font-weight: 600;
            color: #334155;
        }
        .recipient-address {
            font-size: 9.5px;
            color: #475569;
            margin-top: 1px;
        }
        .recipient-phone {
            font-size: 9px;
            color: #475569;
            margin-top: 1px;
        }

        /* 4. Pembuka Surat */
        .letter-opening {
            font-size: 10px;
            color: #1e293b;
            margin-bottom: 12px;
            line-height: 1.5;
            text-align: justify;
        }

        /* 5. Tabel Rincian Resmi */
        .official-table {
            width: 100%;
            margin-bottom: 14px;
            border: 1px solid #334155;
        }
        .official-table th {
            background-color: #f8fafc;
            color: #0f172a;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 8px 8px;
            border: 1px solid #334155;
        }
        .official-table td {
            padding: 8px 8px;
            border: 1px solid #cbd5e1;
            font-size: 9.5px;
            color: #1e293b;
            vertical-align: top;
        }
        .item-name {
            font-weight: bold;
            color: #0f172a;
            font-size: 10px;
        }
        .item-meta {
            font-size: 8.5px;
            color: #475569;
            margin-top: 2px;
        }

        /* 6. Ringkasan & Terbilang */
        .summary-section-table {
            width: 100%;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }
        .terbilang-box {
            border-left: 3px solid #0f172a;
            background: #f8fafc;
            padding: 7px 10px;
            font-size: 9.5px;
            color: #1e293b;
            line-height: 1.4;
            margin-bottom: 10px;
        }
        .terbilang-label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            font-weight: bold;
        }
        .reason-box {
            border: 1px solid #94a3b8;
            padding: 8px 10px;
            background: #ffffff;
            font-size: 9.5px;
            line-height: 1.45;
        }
        .reason-box-title {
            font-weight: bold;
            font-size: 9px;
            color: #0f172a;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .totals-table td {
            padding: 4px 0;
            font-size: 10px;
        }
        .total-lbl {
            width: 52%;
            color: #475569;
        }
        .total-sep {
            width: 6%;
            color: #475569;
            text-align: center;
        }
        .total-val {
            width: 42%;
            text-align: right;
            font-weight: 600;
            color: #0f172a;
        }
        .grand-total-row td {
            border-top: 1.5px solid #0f172a;
            border-bottom: 2.5px double #0f172a;
            padding: 6px 0;
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
        }

        /* 7. Penutup & Tanda Tangan */
        .letter-closing {
            font-size: 10px;
            color: #1e293b;
            line-height: 1.5;
            margin-bottom: 16px;
            text-align: justify;
        }
        .signature-table {
            width: 100%;
            margin-top: 10px;
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
            height: 65px;
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
            margin-top: 22px;
            padding-top: 8px;
            border-top: 0.5px solid #cbd5e1;
            font-size: 8px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI -->
    @php
        $businessContacts = array_filter([
            !empty($business->phone) ? 'Telp / WhatsApp: ' . $business->phone : null,
            !empty($business->email) ? 'Email: ' . $business->email : null,
        ]);
    @endphp
    <table class="kop-table">
        <tr>
            @if(!empty($business->pdf_logo))
            <td class="kop-logo-cell">
                <img src="{{ $business->pdf_logo }}" alt="{{ $business->name }}" class="kop-logo">
            </td>
            @endif
            <td class="kop-text-cell">
                <div class="kop-title">{{ $business->name }}</div>
                <div class="kop-meta">
                    @if(!empty($business->address)){{ $business->address }}<br>@endif
                    @if(count($businessContacts) > 0){!! implode(' &bull; ', $businessContacts) !!}@endif
                    @if(!empty($business->tax_identification_number))<br>NPWP: {{ $business->tax_identification_number }}@endif
                </div>
            </td>
        </tr>
    </table>

    <div class="kop-divider">
        <div class="kop-line-thick"></div>
        <div class="kop-line-thin"></div>
    </div>

    <!-- NOMOR RETUR & TANGGAL RESMI -->
    <table class="meta-section-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <table class="letter-ref-table">
                    <tr>
                        <td class="ref-label">No. Retur</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val">{{ $return->return_number }}</td>
                    </tr>
                    @if($return->invoice)
                    <tr>
                        <td class="ref-label">Ref. Faktur</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val">{{ $return->invoice->invoice_number }}</td>
                    </tr>
                    @elseif($return->posOrder)
                    <tr>
                        <td class="ref-label">Ref. Order Kasir</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val">{{ $return->posOrder->order_number }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="ref-label">Perihal</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val" style="font-weight: bold;">Nota Retur Penjualan</td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table class="letter-ref-table-right">
                    <tr>
                        <td class="ref-label-right">Tanggal Retur</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val-right"><strong>{{ $return->return_date ? \Carbon\Carbon::parse($return->return_date)->locale('id')->translatedFormat('d F Y') : date('d F Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="ref-label-right">Metode Kompensasi</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val-right"><strong>
                            @if($return->refund_method === 'cash_refund') Pengembalian Tunai
                            @elseif($return->refund_method === 'credit_note') Nota Kredit
                            @elseif($return->refund_method === 'store_credit') Saldo Toko
                            @elseif($return->refund_method === 'exchange') Tukar Barang
                            @else {{ ucfirst(str_replace('_', ' ', $return->refund_method ?? 'Kredit')) }} @endif
                        </strong></td>
                    </tr>
                    @if($return->location)
                    <tr>
                        <td class="ref-label-right">Lokasi / Gudang</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val-right">{{ $return->location->name }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- TUJUAN / PIHAK PELANGGAN (KEPADA YTH.) -->
    @php
        $custName = trim((string) ($return->customer?->name ?? ''));
        $custCompany = trim((string) ($return->customer?->company_name ?? ($return->customer?->company ?? '')));
        $hasDiffCompany = $custCompany !== '' && strcasecmp($custName, $custCompany) !== 0;
        $custAddress = trim((string) ($return->customer?->billing_address ?? ($return->customer?->address ?? '')));
        $custPhone = trim((string) ($return->customer?->phone ?? ''));
    @endphp
    <div class="recipient-box">
        <div class="recipient-greeting">Kepada Yth.</div>
        @if($custCompany && !$custName)
            <div class="recipient-name">{{ $custCompany }}</div>
        @elseif($custName && !$custCompany)
            <div class="recipient-name">{{ $custName }}</div>
        @elseif($custName && $custCompany && !$hasDiffCompany)
            <div class="recipient-name">{{ $custCompany }}</div>
        @else
            <div class="recipient-name">{{ $custCompany }}</div>
            <div class="recipient-attn">u.p. {{ $custName }}</div>
        @endif
        @if($custAddress)
            <div class="recipient-address">{{ $custAddress }}</div>
        @endif
        @if($custPhone)
            <div class="recipient-phone">Telp / Kontak: {{ $custPhone }}</div>
        @endif
    </div>

    <!-- PEMBUKA SURAT RESMI -->
    <div class="letter-opening">
        Dengan hormat,<br>
        Bersama nota ini kami sampaikan bukti penerimaan dan verifikasi pengembalian barang (retur) dengan rincian sebagai berikut:
    </div>

    <!-- TABEL RINCIAN ITEM RETUR -->
    <table class="official-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No.</th>
                <th style="width: 45%;">Nama Barang Yang Diretur</th>
                <th style="width: 10%; text-align: center;">Satuan</th>
                <th style="width: 10%; text-align: center;">Qty Retur</th>
                <th style="width: 15%; text-align: right;">Harga Satuan (Rp)</th>
                <th style="width: 15%; text-align: right;">Total Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($return->items as $idx => $item)
            @php
                $condMap = [
                    'damaged' => 'Rusak / Cacat Fisik',
                    'opened' => 'Segel Terbuka',
                    'wrong_item' => 'Salah Kirim',
                    'expired' => 'Kedaluwarsa',
                    'good' => 'Kondisi Baik',
                ];
                $condRaw = strtolower((string) ($item->condition ?? ''));
                $condText = $condMap[$condRaw] ?? ($item->condition ? ucfirst((string) $item->condition) : null);
            @endphp
            <tr>
                <td style="text-align: center; color: #475569;">{{ $idx + 1 }}.</td>
                <td>
                    <div class="item-name">{{ $item->item_name ?? ($item->product_name ?? $item->product?->name) }}</div>
                    @if($item->sku ?? ($item->product?->code ?? $item->product?->sku))
                        <div class="item-meta">Kode: {{ $item->sku ?? ($item->product?->code ?? $item->product?->sku) }}</div>
                    @endif
                    @if($condText)
                        <div class="item-meta">Kondisi: {{ $condText }}</div>
                    @endif
                </td>
                <td style="text-align: center;">
                    {{ $item->product?->outputUnit?->code ?? ($item->product?->unit ?? 'Pcs') }}
                </td>
                <td style="text-align: center; font-weight: bold; color: #0f172a;">
                    {{ (float) $item->quantity == (int) $item->quantity ? number_format((float) $item->quantity, 0, ',', '.') : rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }}
                </td>
                <td style="text-align: right;">
                    {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                </td>
                <td style="text-align: right; font-weight: bold;">
                    {{ number_format((float) ($item->subtotal ?? ($item->quantity * $item->unit_price)), 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 18px; color: #64748b;">Tidak ada rincian item yang diretur.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- RINGKASAN RETUR & ALASAN -->
    @php
        $totalRetur = (float) ($return->total_refund_amount ?? $return->total_amount);
    @endphp
    <table class="summary-section-table">
        <tr>
            <td style="width: 54%; vertical-align: top; padding-right: 18px;">
                <div class="terbilang-box">
                    <span class="terbilang-label">Terbilang Nilai Retur:</span><br>
                    <em># {{ \App\Support\Math\FinancialMath::terbilang($totalRetur) }} #</em>
                </div>

                <div class="reason-box">
                    <div class="reason-box-title">Alasan Pengembalian / Kerusakan Barang:</div>
                    <div>{{ $return->reason ?? 'Pemeriksaan fisik barang sesuai kesepakatan retur.' }}</div>
                    @if($return->notes)
                        <div style="margin-top: 4px; font-size: 8.5px; color: #475569;"><strong>Catatan Tambahan:</strong> {{ $return->notes }}</div>
                    @endif
                </div>
            </td>
            <td style="width: 46%; vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td class="total-lbl">Total Kuantitas Diretur</td>
                        <td class="total-sep">:</td>
                        <td class="total-val">{{ $return->items->sum('quantity') }}</td>
                    </tr>
                    <tr class="grand-total-row">
                        <td class="total-lbl">Total Nilai Retur</td>
                        <td class="total-sep">:</td>
                        <td class="total-val">Rp {{ number_format($totalRetur, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- PENUTUP SURAT RESMI -->
    <div class="letter-closing">
        Demikian nota retur penjualan ini dibuat dengan sebenar-benarnya sebagai bukti sah penerimaan kembali barang untuk diproses kompensasi sesuai ketentuan yang berlaku.
    </div>

    <!-- TANDA TANGAN RESMI -->
    <table class="signature-table">
        <tr>
            <td class="sig-box">
                <div class="sig-title">Pihak Pelanggan / Pengirim,</div>
                <div class="sig-company">{{ $custCompany ?: ($custName ?: 'Pihak Pelanggan') }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">( {{ ($custName && $hasDiffCompany) ? $custName : '..................................................' }} )</div>
                <div class="sig-caption">{{ ($custName && $hasDiffCompany) ? 'Tanda Tangan & Cap Perusahaan' : 'Nama Terang & Cap Perusahaan' }}</div>
            </td>
            <td style="width: 10%;"></td>
            <td class="sig-box">
                <div class="sig-title">Diterima &amp; Diperiksa oleh,</div>
                <div class="sig-company">{{ $business->name }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">( {{ $return->creator?->name ?: '..................................................' }} )</div>
                <div class="sig-caption">Bagian Gudang &amp; Logistik</div>
            </td>
        </tr>
    </table>

    <!-- FOOTER RESMI -->
    <div class="official-footer">
        Nota Retur ini sah dan diterbitkan secara resmi oleh {{ $business->name }}.
    </div>

</body>
</html>
