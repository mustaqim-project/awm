<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Penawaran #{{ $quotation->quotation_number }} - {{ $business->name }}</title>
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

        /* 2. Metadata Surat & Tanggal */
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
            width: 85px;
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
        .official-table tr {
            page-break-inside: avoid;
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
        .terms-box {
            font-size: 9px;
            color: #334155;
            line-height: 1.45;
        }
        .terms-box ol {
            margin: 4px 0 0 16px;
            padding: 0;
        }
        .terms-box li {
            margin-bottom: 3px;
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

        /* 8. Running Footer Resmi */
        .doc-footer {
            position: fixed;
            bottom: -12mm;
            left: 0;
            right: 0;
            height: 8mm;
            border-top: 0.5px solid #cbd5e1;
            padding-top: 3px;
            font-size: 8px;
            color: #64748b;
        }
        .doc-footer-table {
            width: 100%;
        }
        .doc-footer-table td {
            padding: 0;
            font-size: 8px;
            color: #64748b;
        }
        .doc-footer-page:after {
            content: "Halaman " counter(page);
        }
    </style>
</head>
<body>

    <!-- RUNNING FOOTER RESMI (Semua Halaman) -->
    <div class="doc-footer">
        <table class="doc-footer-table">
            <tr>
                <td style="text-align: left;">
                    Dokumen Resmi &bull; {{ $business->name }}
                </td>
                <td style="text-align: right;" class="doc-footer-page"></td>
            </tr>
        </table>
    </div>

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

    <!-- NOMOR SURAT & TANGGAL RESMI -->
    <table class="meta-section-table">
        <tr>
            <td style="width: 58%; vertical-align: top;">
                <table class="letter-ref-table">
                    <tr>
                        <td class="ref-label">Nomor</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val">{{ $quotation->quotation_number }}</td>
                    </tr>
                    <tr>
                        <td class="ref-label">Lampiran</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val">-</td>
                    </tr>
                    <tr>
                        <td class="ref-label">Perihal</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val" style="font-weight: bold;">Penawaran Harga Produk &amp; Layanan</td>
                    </tr>
                </table>
            </td>
            <td style="width: 42%; vertical-align: top; text-align: right;">
                <div class="letter-date-box">
                    {{ !empty($business->city) ? $business->city . ', ' : '' }}{{ $quotation->date ? \Carbon\Carbon::parse($quotation->date)->locale('id')->translatedFormat('d F Y') : date('d F Y') }}
                </div>
                @if($quotation->expiry_date)
                <div style="font-size: 9px; color: #475569; margin-top: 3px;">
                    Masa Berlaku s/d: <strong>{{ \Carbon\Carbon::parse($quotation->expiry_date)->locale('id')->translatedFormat('d F Y') }}</strong>
                </div>
                @endif
            </td>
        </tr>
    </table>

    <!-- TUJUAN SURAT (KEPADA YTH.) -->
    @php
        $recipientExplicit = trim((string) ($quotation->recipient_name ?? ''));
        $custName = $recipientExplicit ?: trim((string) ($quotation->customer?->name ?? ''));
        $custCompany = trim((string) ($quotation->customer?->company_name ?? ($quotation->customer?->company ?? '')));
        $hasDiffCompany = $custCompany !== '' && strcasecmp($custName, $custCompany) !== 0;
        $custAddress = trim((string) ($quotation->customer?->address ?? ($quotation->customer?->billing_address ?? '')));
        $custPhone = trim((string) ($quotation->customer?->phone ?? ''));
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
        Sehubungan dengan kebutuhan pengadaan produk dan layanan pada perusahaan Bapak/Ibu, bersama ini kami sampaikan rincian penawaran harga resmi dengan perincian sebagai berikut:
    </div>

    <!-- TABEL RINCIAN ITEM PENAWARAN -->
    <table class="official-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No.</th>
                <th style="width: 45%;">Nama Barang / Layanan</th>
                <th style="width: 10%; text-align: center;">Satuan</th>
                <th style="width: 10%; text-align: center;">Jumlah</th>
                <th style="width: 15%; text-align: right;">Harga Satuan (Rp)</th>
                <th style="width: 15%; text-align: right;">Total Harga (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quotation->items as $idx => $item)
            <tr>
                <td style="text-align: center; color: #475569;">{{ $idx + 1 }}.</td>
                <td>
                    <div class="item-name">{{ $item->item_name ?? ($item->product_name ?? $item->product?->name) }}</div>
                    @if($item->sku ?? ($item->product?->code ?? $item->product?->sku))
                        <div class="item-meta">Kode: {{ $item->sku ?? ($item->product?->code ?? $item->product?->sku) }}</div>
                    @endif
                    @if(!empty($item->description))
                        <div class="item-meta">{{ $item->description }}</div>
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
                    {{ number_format((float) ($item->total_price ?? ($item->quantity * $item->unit_price)), 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 18px; color: #64748b;">Tidak ada rincian item penawaran.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- RINGKASAN BIAYA & SYARAT KETENTUAN -->
    <table class="summary-section-table">
        <tr>
            <td style="width: 54%; vertical-align: top; padding-right: 18px;">
                <div class="terbilang-box">
                    <span class="terbilang-label">Terbilang:</span><br>
                    <em># {{ \App\Support\Math\FinancialMath::terbilang($quotation->total_amount) }} #</em>
                </div>

                @if($quotation->notes)
                <div style="font-size: 9px; margin-bottom: 6px;">
                    <strong>Catatan Khusus:</strong><br>
                    {!! nl2br(e($quotation->notes)) !!}
                </div>
                @endif

                <div class="terms-box">
                    <strong>Syarat &amp; Ketentuan Penawaran:</strong>
                    @if($quotation->terms_and_conditions)
                        <div>{!! nl2br(e($quotation->terms_and_conditions)) !!}</div>
                    @else
                        <ol>
                            <li>Harga penawaran berlaku sampai dengan tanggal masa berlaku yang tertera.</li>
                            <li>Pemesanan dianggap sah setelah surat penawaran ini ditandatangani atau diterbitkan Surat Pesanan (PO) resmi.</li>
                            <li>Pembayaran dilakukan melalui transfer ke rekening resmi perusahaan.</li>
                        </ol>
                    @endif
                </div>
            </td>
            <td style="width: 46%; vertical-align: top;">
                <table class="totals-table">
                    <tr>
                        <td class="total-lbl">Subtotal</td>
                        <td class="total-sep">:</td>
                        <td class="total-val">Rp {{ number_format((float) $quotation->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if((float) $quotation->discount_amount > 0)
                    <tr>
                        <td class="total-lbl">Diskon Khusus</td>
                        <td class="total-sep">:</td>
                        <td class="total-val" style="color: #b91c1c;">- Rp {{ number_format((float) $quotation->discount_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if((float) $quotation->tax_amount > 0)
                    <tr>
                        <td class="total-lbl">Pajak (PPN)</td>
                        <td class="total-sep">:</td>
                        <td class="total-val">+ Rp {{ number_format((float) $quotation->tax_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total-row">
                        <td class="total-lbl">Total Penawaran</td>
                        <td class="total-sep">:</td>
                        <td class="total-val">Rp {{ number_format((float) $quotation->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- PENUTUP SURAT RESMI -->
    <div class="letter-closing">
        Demikian surat penawaran harga ini kami sampaikan. Besar harapan kami untuk dapat menjalin kerja sama yang baik dengan perusahaan Bapak/Ibu. Atas perhatian dan kerjasamanya, kami mengucapkan terima kasih.
    </div>

    <!-- TANDA TANGAN RESMI -->
    @php
        $senderSignatory = trim((string) ($quotation->sender_name ?? ''));
        if (!$senderSignatory) {
            $senderSignatory = trim((string) ($quotation->creator?->name ?? ($business->owner?->name ?? '')));
        }
    @endphp
    <table class="signature-table">
        <tr>
            <td class="sig-box">
                <div class="sig-title">Menyetujui / Diterima oleh,</div>
                <div class="sig-company">{{ $custCompany ?: ($custName ?: 'Pihak Pemesan') }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">( {{ ($custName && $hasDiffCompany) ? $custName : ($recipientExplicit ?: '..................................................') }} )</div>
                <div class="sig-caption">{{ ($custName && $hasDiffCompany) || $recipientExplicit ? 'Tanda Tangan & Cap Perusahaan' : 'Nama Terang & Cap Perusahaan' }}</div>
            </td>
            <td style="width: 10%;"></td>
            <td class="sig-box">
                <div class="sig-title">Hormat kami,</div>
                <div class="sig-company">{{ $business->name }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">( {{ $senderSignatory ?: '..................................................' }} )</div>
                <div class="sig-caption">Bagian Penjualan &amp; Kemitraan</div>
            </td>
        </tr>
    </table>

</body>
</html>
