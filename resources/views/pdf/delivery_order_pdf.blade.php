<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Surat Jalan #SJ-{{ $salesOrder->so_number }} - {{ $business->name }}</title>
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

        /* 2. Metadata Surat Jalan */
        .meta-section-table {
            width: 100%;
            margin-bottom: 14px;
        }
        .doc-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
            text-decoration: underline;
        }
        .ref-label {
            width: 110px;
            color: #475569;
            font-size: 10px;
        }
        .ref-sep {
            width: 10px;
            color: #475569;
            text-align: center;
        }
        .ref-val {
            color: #0f172a;
            font-weight: 600;
            font-size: 10px;
        }
        .recipient-box {
            padding: 8px 12px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }

        /* 3. Items Table (No prices on Delivery Orders) */
        .items-table {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 16px;
            border: 1px solid #cbd5e1;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            font-size: 9.5px;
            padding: 7px 8px;
            border-bottom: 1.5px solid #94a3b8;
            border-right: 1px solid #cbd5e1;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 6.5px 8px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #cbd5e1;
            font-size: 9.5px;
            vertical-align: middle;
        }
        .items-table tr:nth-child(even) {
            background-color: #fafafa;
        }

        /* 4. Notes & Signatures */
        .notes-box {
            border: 1px dashed #cbd5e1;
            background-color: #f8fafc;
            padding: 8px 10px;
            margin-bottom: 24px;
            border-radius: 4px;
        }
        .signatures-table {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .sig-col {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }
        .sig-title {
            font-size: 10px;
            color: #475569;
            margin-bottom: 60px;
        }
        .sig-name {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px solid #334155;
            padding-bottom: 2px;
            display: inline-block;
            min-width: 120px;
        }
        .sig-meta {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 3px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT PERUSAHAAN -->
    <table class="kop-table">
        <tr>
            <td class="kop-text-cell">
                <div class="kop-title">{{ $business->name }}</div>
                <div class="kop-meta">
                    @if ($business->address) {{ $business->address }}<br> @endif
                    @if ($business->phone) Telp: {{ $business->phone }} @endif
                    @if ($business->email) | Email: {{ $business->email }} @endif
                </div>
            </td>
            <td style="text-align: right; vertical-align: top;">
                <div style="font-size: 11px; font-weight: bold; color: #0284c7;">SURAT JALAN PENGIRIMAN</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 2px;">Dokumen Pengantar Barang Resmi</div>
            </td>
        </tr>
    </table>

    <div class="kop-divider">
        <div class="kop-line-thick"></div>
        <div class="kop-line-thin"></div>
    </div>

    <div class="doc-title">SURAT JALAN (DELIVERY ORDER)</div>

    <!-- METADATA & RECIPIENT -->
    <table class="meta-section-table">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 14px;">
                <table>
                    <tr>
                        <td class="ref-label">No. Surat Jalan</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val">SJ-{{ $salesOrder->so_number }}</td>
                    </tr>
                    <tr>
                        <td class="ref-label">No. Referensi SO</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val">{{ $salesOrder->so_number }}</td>
                    </tr>
                    <tr>
                        <td class="ref-label">Tanggal Kirim</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val">{{ now()->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="ref-label">Metode Pengiriman</td>
                        <td class="ref-sep">:</td>
                        <td class="ref-val">Armada Sendiri / Kurir Ekspedisi</td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <div class="recipient-box">
                    <div style="font-size: 9px; font-weight: bold; text-transform: uppercase; color: #475569; margin-bottom: 4px;">Tujuan Pengiriman / Penerima:</div>
                    <div style="font-size: 10.5px; font-weight: bold; color: #0f172a;">{{ $salesOrder->customer ? $salesOrder->customer->name : 'Pelanggan Umum' }}</div>
                    @if ($salesOrder->customer && $salesOrder->customer->address)
                        <div style="font-size: 9px; color: #334155; margin-top: 2px;">{{ $salesOrder->customer->address }}</div>
                    @endif
                    @if ($salesOrder->customer && $salesOrder->customer->phone)
                        <div style="font-size: 9px; color: #334155; margin-top: 2px;">Telp/WA: {{ $salesOrder->customer->phone }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- ITEM TABLE -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 32px; text-align: center;">No.</th>
                <th style="width: 90px; text-align: left;">Kode</th>
                <th style="text-align: left;">Deskripsi Barang / Spesifikasi</th>
                <th style="width: 70px; text-align: center;">Satuan</th>
                <th style="width: 80px; text-align: right;">Qty Kirim</th>
                <th style="width: 100px; text-align: left;">Catatan / Batch</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($salesOrder->items as $idx => $item)
                <tr>
                    <td style="text-align: center; color: #64748b;">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace; font-size: 9px;">{{ $item->product ? ($item->product->code ?? '-') : '-' }}</td>
                    <td style="font-weight: 600; color: #0f172a;">
                        {{ $item->product ? $item->product->name : ($item->name ?? 'Barang Dagangan') }}
                    </td>
                    <td style="text-align: center; color: #475569;">
                        {{ $item->product && $item->product->outputUnit ? $item->product->outputUnit->symbol : 'PCS' }}
                    </td>
                    <td style="text-align: right; font-weight: bold; font-size: 10px; color: #0f172a;">
                        {{ number_format((float) $item->quantity, 0, ',', '.') }}
                    </td>
                    <td style="font-size: 8.5px; color: #64748b;">
                        {{ $item->notes ?? 'Baik / Utuh' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 14px; color: #94a3b8;">
                        Tidak ada item barang dalam pesanan ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- NOTES BOX -->
    <div class="notes-box">
        <div style="font-size: 9px; font-weight: bold; color: #334155; margin-bottom: 2px;">Petunjuk & Ketentuan Pengiriman:</div>
        <div style="font-size: 8.5px; color: #64748b; line-height: 1.4;">
            1. Harap barang diperiksa dengan teliti saat serah terima di lokasi penerima.<br>
            2. Klaim kerusakan atau kekurangan barang hanya dapat diproses maksimal 1x24 jam setelah dokumen ini ditandatangani.<br>
            3. Surat jalan ini sah sebagai bukti serah terima fisik barang.
        </div>
    </div>

    <!-- SIGNATURE BLOCKS -->
    <table class="signatures-table">
        <tr>
            <td class="sig-col">
                <div class="sig-title">Yang Menyerahkan (Gudang/Pengirim)</div>
                <div class="sig-name">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                <div class="sig-meta">Tanda Tangan & Nama Jelas</div>
            </td>
            <td class="sig-col">
                <div class="sig-title">Pengemudi / Kurir Ekspedisi</div>
                <div class="sig-name">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                <div class="sig-meta">No. Pol: _________________</div>
            </td>
            <td class="sig-col">
                <div class="sig-title">Yang Menerima (Customer)</div>
                <div class="sig-name">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                <div class="sig-meta">Tanda Tangan, Nama & Cap Toko</div>
            </td>
        </tr>
    </table>

</body>
</html>
