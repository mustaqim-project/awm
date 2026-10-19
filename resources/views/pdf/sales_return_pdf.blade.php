<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Nota Retur Penjualan {{ $return->return_number }} - {{ $business->name }}</title>
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
        .status-draft { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .status-approved { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .status-completed { background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .status-rejected { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

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

        .reason-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 10.5px;
            color: #334155;
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
                @if($business->logo_url)
                    <div style="margin-bottom: 5px;">
                        <img src="{{ $business->logo_url }}" alt="{{ $business->name }}" style="max-height: 42px; max-width: 140px; object-fit: contain;">
                    </div>
                @endif
                <h1 class="brand-logo">{{ $business->name }}</h1>
                <div class="brand-sub">
                    @if($business->address) {{ $business->address }}<br> @endif
                    @if($business->phone) Telp / WA: {{ $business->phone }} @endif
                    @if($business->email) &bull; Email: {{ $business->email }} @endif
                    @if($business->tax_identification_number) <br>NPWP: {{ $business->tax_identification_number }} @endif
                </div>
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div class="doc-title-main">Nota Retur Penjualan</div>
                <div class="doc-number">No: {{ $return->return_number }}</div>
                <div>
                    <span class="status-badge status-{{ $return->status }}">
                        {{ strtoupper($return->status) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- 2. META & CLIENT INFO -->
    <table class="meta-table">
        <tr>
            <!-- Kepada Pelanggan -->
            <td class="meta-card" style="width: 52%;">
                <div class="meta-title">Pelanggan / Pihak Pengembali</div>
                <div class="customer-name">{{ $return->customer?->name ?? 'Pelanggan Umum' }}</div>
                @if($return->customer?->company)
                    <div style="font-weight: 600; color: #475569;">{{ $return->customer->company }}</div>
                @endif
                @if($return->customer?->address)
                    <div style="color: #64748b; margin-top: 2px;">{{ $return->customer->address }}</div>
                @endif
                @if($return->customer?->phone)
                    <div style="color: #64748b; margin-top: 2px;">Telp: {{ $return->customer->phone }}</div>
                @endif
            </td>
            <td style="width: 4%;"></td>
            <!-- Detail Tanggal & Referensi -->
            <td class="meta-card" style="width: 44%;">
                <div class="meta-title">Informasi Retur</div>
                <table style="width: 100%; font-size: 11px;">
                    <tr>
                        <td style="color: #64748b; padding: 2px 0;">Tanggal Retur:</td>
                        <td style="font-weight: 700; text-align: right;">{{ $return->return_date?->format('d/m/Y') ?? date('d/m/Y') }}</td>
                    </tr>
                    @if($return->invoice)
                    <tr>
                        <td style="color: #64748b; padding: 2px 0;">Ref. Faktur:</td>
                        <td style="font-weight: 700; text-align: right; color: #007aff;">{{ $return->invoice->invoice_number }}</td>
                    </tr>
                    @elseif($return->posOrder)
                    <tr>
                        <td style="color: #64748b; padding: 2px 0;">Ref. Order Kasir:</td>
                        <td style="font-weight: 700; text-align: right; color: #16a34a;">{{ $return->posOrder->order_number }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color: #64748b; padding: 2px 0;">Metode Kompensasi:</td>
                        <td style="font-weight: 700; text-align: right;">
                            @if($return->refund_method === 'cash_refund') Pengembalian Tunai
                            @elseif($return->refund_method === 'credit_note') Nota Kredit (Potong Faktur)
                            @elseif($return->refund_method === 'store_credit') Saldo Deposit Toko
                            @else {{ ucfirst($return->refund_method ?? 'Kredit') }} @endif
                        </td>
                    </tr>
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
                <th style="width: 45%;">Barang Yang Diretur</th>
                <th style="width: 18%;" class="text-right">Harga Satuan</th>
                <th style="width: 12%;" class="text-center">Jumlah Retur</th>
                <th style="width: 20%;" class="text-right">Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($return->items as $idx => $item)
            <tr>
                <td class="text-center" style="color: #64748b;">{{ $idx + 1 }}</td>
                <td>
                    <div style="font-weight: 700; color: #0f172a;">{{ $item->item_name ?? $item->product_name ?? $item->product?->name }}</div>
                    @if($item->sku ?? $item->product?->code ?? $item->product?->sku)
                        <div style="font-size: 9.5px; color: #64748b;">SKU: {{ $item->sku ?? $item->product?->code ?? $item->product?->sku }}</div>
                    @endif
                    @if(!empty($item->notes))
                        <div style="font-size: 9.5px; color: #64748b; font-style: italic; margin-top: 2px;">Catatan: {{ $item->notes }}</div>
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-center" style="font-weight: 600; color: #007aff;">
                    {{ number_format($item->quantity, 0, ',', '.') }} {{ $item->unit ?? $item->product?->unit?->symbol ?? $item->product?->unit?->code ?? 'Unit' }}
                </td>
                <td class="text-right" style="font-weight: 700; color: #0f172a;">
                    Rp {{ number_format($item->subtotal ?? ($item->quantity * $item->unit_price), 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="padding: 16px; color: #94a3b8;">Tidak ada item yang diretur.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. TOTALS & REASON -->
    <table class="summary-table">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 16px;">
                <div class="reason-box">
                    <strong style="display: block; margin-bottom: 3px; text-transform: uppercase; color: #64748b; font-size: 10px;">Alasan Retur / Kerusakan:</strong>
                    <div>{{ $return->reason ?? 'Tidak ada alasan khusus dicatat.' }}</div>
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <div class="summary-box">
                    <table style="width: 100%;">
                        <tr>
                            <td class="summary-row" style="color: #64748b;">Total Barang Diretur:</td>
                            <td class="summary-row text-right" style="font-weight: 700;">{{ $return->items->sum('quantity') }} unit</td>
                        </tr>
                        <tr>
                            <td class="summary-total">Total Pengembalian:</td>
                            <td class="summary-total text-right">Rp {{ number_format($return->total_amount, 0, ',', '.') }}</td>
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
                <div style="font-size: 10px; color: #64748b; margin-bottom: 4px;">Pihak Yang Meretur:</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-size: 10.5px; font-weight: 700; margin-top: 4px;">( {{ $return->customer?->name ?? '....................................' }} )</div>
                <div style="font-size: 9px; color: #94a3b8;">Pelanggan / Pembeli</div>
            </td>
            <td style="width: 24%;"></td>
            <td class="sign-box">
                <div style="font-size: 10px; color: #64748b; margin-bottom: 4px;">Petugas Penerima Barang:</div>
                <div style="font-size: 11px; font-weight: 700;">{{ $business->name }}</div>
                <div class="sign-space"></div>
                <div class="sign-line"></div>
                <div style="font-size: 10.5px; font-weight: 700; margin-top: 4px;">( Gudang & QC )</div>
                <div style="font-size: 9px; color: #94a3b8;">Authorized Staff</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dokumen ini dicetak otomatis oleh Sistem COOCA ERP &bull; Sah tanpa tanda tangan basah &bull; Dicetak pada: {{ date('d/m/Y H:i') }} WIB
    </div>

</body>
</html>
