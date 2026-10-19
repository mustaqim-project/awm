<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('sales.returns.print_page_title', ['number' => $return->return_number, 'business' => $business->name]) }}</title>

    <!-- Google Fonts / SF Pro Fallback -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- html2pdf.js for Direct Client-Side PDF Download -->
    <script src="{{ asset('vendor/html2pdf.bundle.min.js') }}"></script>
    <script>
        if (typeof html2pdf === 'undefined') {
            document.write(
                '<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"><\/script>'
            );
        }
    </script>

    <style>
        body {
            font-family: -apple-system, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
            background-color: #F2F2F7;
            color: #1C1C1E;
        }

        @page {
            size: A4 portrait;
            margin: 14mm 16mm 14mm 16mm;
        }

        .print-sheet {
            box-shadow: none !important;
            border: none !important;
            border-radius: 0 !important;
            background-color: #ffffff !important;
        }

        @media print {
            html,
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .print-sheet {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
            }

            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                page-break-after: auto !important;
                break-after: auto !important;
            }

            td,
            th {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            thead {
                display: table-header-group !important;
            }

            tfoot {
                display: table-footer-group !important;
            }

            .keep-together {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>

<body class="py-6 px-4 sm:px-6 bg-[#F2F2F7]">

    <!-- Floating Top Print Action Bar (Apple HIG macOS Frosted Toolbar) -->
    <div
        class="no-print max-w-4xl mx-auto mb-6 backdrop-blur-md bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/10 dark:border-white/10 p-4 rounded-[14px] shadow-[0_8px_24px_rgba(0,0,0,0.06)] space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $return->status === 'completed' ? 'bg-[#34C759]' : ($return->status === 'approved' ? 'bg-[#007AFF]' : 'bg-[#FF9500]') }}"></span>
                <span class="text-[13px] font-semibold text-black dark:text-white tracking-tight">
                    {{ __('sales.returns.print_bar_title', ['number' => $return->return_number]) }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('sales.returns.show', $return) }}"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-black/60 dark:text-white/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>{{ __('sales.returns.btn_back') }}</span>
                </a>
                <button onclick="window.print()"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] flex items-center gap-1.5 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-black/60 dark:text-white/60"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6" />
                        <rect x="6" y="14" width="12" height="8" rx="1" />
                    </svg>
                    <span>{{ __('sales.returns.btn_print_printer') }}</span>
                </button>
                <a id="btnDownloadPdf" href="{{ route('sales.returns.pdf', $return) }}" target="_blank"
                    class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex items-center gap-1.5 active:scale-[0.97] transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    <span>{{ __('sales.returns.btn_download_pdf_direct') }}</span>
                </a>
            </div>
        </div>
        <div
            class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center gap-2 text-[12px] text-black/50 dark:text-white/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#007AFF] shrink-0" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
            <span>{!! __('sales.returns.print_bar_instruction') !!}</span>
        </div>
    </div>

    <!-- Paper Sheet (Standard A4 Flat Container) -->
    <div
        class="print-sheet max-w-4xl mx-auto bg-white p-8 sm:p-12 border-0 shadow-none rounded-none text-slate-900 space-y-6">

        <!-- Header / Kop Surat -->
        <div class="flex justify-between items-start border-b-2 border-slate-900 pb-5">
            <div class="space-y-2 max-w-md">
                @if ($business->logo_url)
                    <div class="mb-1">
                        <img src="{{ $business->logo_url }}" alt="{{ $business->name }}"
                            class="max-h-12 max-w-[140px] object-contain">
                    </div>
                @endif
                <div class="space-y-0.5">
                    <h1 class="text-base font-bold text-slate-950 uppercase tracking-wide leading-tight">
                        {{ $business->legal_name ?: $business->name }}
                    </h1>
                    <p class="text-xs text-slate-600">
                        {{ $business->address ?? __('sales.returns.header_kop_office_address') }}
                    </p>
                    <p class="text-xs text-slate-600">
                        @if ($business->email)
                            Email: {{ $business->email }}
                        @endif
                        @if ($business->phone)
                            | Telp: {{ $business->phone }}
                        @endif
                    </p>
                    @if ($business->tax_identification_number)
                        <p class="text-xs text-slate-600 tabular-nums">NPWP: {{ $business->tax_identification_number }}</p>
                    @endif
                </div>
            </div>

            <div class="text-right space-y-0.5">
                <div class="text-lg font-bold tracking-wider text-slate-950 uppercase">{{ __('sales.returns.header_doc_title') }}</div>
                <div class="text-xs text-slate-500 font-semibold tracking-wide uppercase">{{ __('sales.returns.header_doc_subtitle') }}</div>
                <div class="text-sm tabular-nums font-bold text-slate-800 font-mono mt-1">{{ $return->return_number }}</div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-600 mt-1">
                    {{ __('sales.invoices.print_status') }} 
                    @if ($return->status === 'completed')
                        <span class="font-bold text-emerald-700">{{ __('sales.returns.status_completed_badge') }}</span>
                    @elseif ($return->status === 'approved')
                        <span class="font-bold text-sky-700">{{ __('sales.returns.status_approved_badge_print') }}</span>
                    @else
                        <span class="font-bold text-amber-700">{{ __('sales.returns.status_draft_badge_print') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Detail Pihak Pelanggan & Informasi Retur -->
        <div class="grid grid-cols-2 gap-8 text-xs">
            <!-- Kolom Kiri: Pelanggan & Sumber -->
            <div class="space-y-1.5">
                <div class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">{{ __('sales.returns.label_buyer_customer') }}</div>
                <div class="text-sm font-bold text-slate-950">
                    @if ($return->invoice)
                        {{ $return->invoice->customer?->name ?? __('sales.returns.general_customer') }}
                    @elseif($return->posOrder)
                        {{ $return->posOrder->customer?->name ?? ($return->posOrder->customer_name_guest ?: __('sales.returns.guest_customer')) }}
                    @else
                        {{ $return->customer?->name ?? __('sales.returns.general_customer') }}
                    @endif
                </div>

                @php
                    $customerObj = $return->invoice?->customer ?? ($return->posOrder?->customer ?? $return->customer);
                @endphp

                @if ($customerObj?->company_name)
                    <div class="font-semibold text-slate-800">{{ $customerObj->company_name }}</div>
                @endif
                <div class="text-slate-600 whitespace-pre-line">{{ $customerObj?->billing_address ?? ($customerObj?->address ?? '-') }}</div>
                <div class="text-slate-600 tabular-nums">{{ __('sales.returns.label_contact') }} {{ $customerObj?->phone ?? ($customerObj?->email ?? '-') }}</div>
                
                <div class="pt-2 border-t border-slate-200 mt-2 space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-500">{{ __('sales.returns.label_source_doc') }}</span>
                        <span class="font-semibold text-slate-900 font-mono">
                            @if ($return->invoice)
                                {{ __('sales.returns.source_sales_invoice_ref', ['number' => $return->invoice->invoice_number]) }}
                            @elseif ($return->posOrder)
                                {{ __('sales.returns.source_pos_receipt_ref', ['number' => $return->posOrder->order_number]) }}
                            @else
                                {{ __('sales.returns.source_manual_badge') }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Tanggal, Gudang & Kompensasi -->
            <div class="space-y-2 text-right">
                <div class="flex justify-end gap-4">
                    <span class="text-slate-500">{{ __('sales.returns.label_submission_date') }}</span>
                    <span class="tabular-nums font-bold text-slate-900">
                        {{ $return->return_date ? $return->return_date->translatedFormat('d F Y') : $return->created_at->translatedFormat('d F Y') }}
                    </span>
                </div>
                @if ($return->approved_at)
                    <div class="flex justify-end gap-4">
                        <span class="text-slate-500">{{ __('sales.returns.label_approved_date') }}</span>
                        <span class="tabular-nums font-semibold text-slate-800">{{ $return->approved_at->translatedFormat('d F Y, H:i') }}</span>
                    </div>
                @endif
                @if ($return->completed_at)
                    <div class="flex justify-end gap-4">
                        <span class="text-slate-500">{{ __('sales.returns.label_completed_date') }}</span>
                        <span class="tabular-nums font-semibold text-emerald-800">{{ $return->completed_at->translatedFormat('d F Y, H:i') }}</span>
                    </div>
                @endif
                @if ($return->location)
                    <div class="flex justify-end gap-4">
                        <span class="text-slate-500">{{ __('sales.returns.label_receiving_warehouse') }}</span>
                        <span class="font-semibold text-slate-900">{{ $return->location->name }}</span>
                    </div>
                @endif
                <div class="flex justify-end gap-4">
                    <span class="text-slate-500">{{ __('sales.returns.label_comp_method') }}</span>
                    <span class="font-bold text-slate-900">
                        @if (($return->refund_method ?? 'cash_refund') === 'cash_refund')
                            {{ __('sales.returns.comp_cash_refund') }}
                        @elseif (($return->refund_method ?? '') === 'credit_note')
                            {{ __('sales.returns.comp_credit_note') }}
                        @else
                            {{ __('sales.returns.comp_store_credit') }}
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Alasan Retur Box -->
        <div class="p-3 bg-slate-50 border border-slate-200 rounded text-xs space-y-1">
            <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">{{ __('sales.returns.label_return_reason_complaint') }}</span>
            <p class="text-slate-800 italic leading-relaxed">
                &ldquo;{{ $return->reason ?: __('sales.returns.no_specific_complaint') }}&rdquo;
            </p>
        </div>

        <!-- Tabel Rincian Barang yang Diretur -->
        <div class="overflow-visible">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-y-2 border-slate-900 text-slate-950 font-bold uppercase tracking-wider text-[10.5px]">
                        <th class="py-2.5 px-2 text-center w-8">#</th>
                        <th class="py-2.5 px-3">{{ __('sales.returns.col_item_desc') }}</th>
                        <th class="py-2.5 px-3 text-center w-24">{{ __('sales.returns.col_sku_code') }}</th>
                        <th class="py-2.5 px-3 text-center w-24">{{ __('sales.returns.col_return_qty') }}</th>
                        <th class="py-2.5 px-3 text-right w-32">{{ __('sales.returns.col_unit_price_origin') }}</th>
                        <th class="py-2.5 px-3 text-right w-36">{{ __('sales.returns.col_compensation_value') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($return->items as $idx => $item)
                        <tr>
                            <td class="py-2.5 px-2 text-center tabular-nums text-slate-500">{{ $idx + 1 }}</td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-slate-950">{{ $item->item_name }}</div>
                                @if($item->product && $item->product->name !== $item->item_name)
                                    <div class="text-[11px] text-slate-500">{{ $item->product->name }}</div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono text-[11px] text-slate-600">
                                {{ $item->product?->code ?? ($item->product?->sku ?? '-') }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-bold tabular-nums text-slate-900">
                                {{ number_format($item->quantity, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right tabular-nums text-slate-700">
                                Rp {{ number_format($item->unit_price ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold tabular-nums text-slate-950">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400 italic">
                                {{ __('sales.returns.no_items_in_return') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t-2 border-slate-900 font-bold">
                    <tr>
                        <td colspan="5" class="py-3 px-3 text-right uppercase tracking-wider text-slate-900 text-xs">
                            {{ __('sales.returns.total_refund_value_label') }}
                        </td>
                        <td class="py-3 px-3 text-right tabular-nums text-base text-rose-700 font-bold">
                            Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Informasi Kompensasi & Restock -->
        <div class="grid grid-cols-2 gap-6 text-xs keep-together pt-2">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded space-y-1">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">{{ __('sales.returns.terms_compensation_title') }}</span>
                <p class="text-slate-600 leading-relaxed text-[11px]">
                    @if (($return->refund_method ?? 'cash_refund') === 'cash_refund')
                        {{ __('sales.returns.terms_cash_desc') }}
                    @elseif (($return->refund_method ?? '') === 'credit_note')
                        {{ __('sales.returns.terms_credit_note_desc') }}
                    @else
                        {{ __('sales.returns.terms_store_credit_desc') }}
                    @endif
                </p>
            </div>
            <div class="p-3 bg-slate-50 border border-slate-200 rounded space-y-1">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[10px] block">{{ __('sales.returns.status_physical_verify_title') }}</span>
                <div class="text-[11px] font-semibold {{ $return->status === 'completed' ? 'text-emerald-700' : 'text-amber-700' }}">
                    @if ($return->status === 'completed')
                        {{ __('sales.returns.verify_completed_desc') }}
                    @elseif ($return->status === 'approved')
                        {{ __('sales.returns.verify_approved_desc') }}
                    @else
                        {{ __('sales.returns.verify_draft_desc') }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Box Tanda Tangan Resmi (3 Kolom: Pelanggan, Gudang, Authorized) -->
        <div class="pt-8 keep-together">
            <div class="grid grid-cols-3 gap-6 text-center text-xs">
                <!-- Kolom 1: Pelanggan -->
                <div class="space-y-16">
                    <div>
                        <div class="text-slate-500">{{ __('sales.returns.sig_submitted_by') }}</div>
                        <div class="font-bold text-slate-950 mt-1">{{ __('sales.returns.label_buyer_customer_plain') }}</div>
                    </div>
                    <div>
                        <div class="border-t border-slate-400 w-44 mx-auto pt-1 font-semibold text-slate-800">
                            {{ $customerObj?->name ?? __('sales.returns.label_buyer_customer_plain') }}
                        </div>
                        <div class="text-[10px] text-slate-500 mt-0.5">{{ __('sales.returns.sig_customer_sign_name') }}</div>
                    </div>
                </div>

                <!-- Kolom 2: Staff Gudang -->
                <div class="space-y-16">
                    <div>
                        <div class="text-slate-500">{{ __('sales.returns.sig_verified_received_by') }}</div>
                        <div class="font-bold text-slate-950 mt-1">{{ __('sales.returns.sig_warehouse_checker_title') }}</div>
                    </div>
                    <div>
                        <div class="border-t border-slate-400 w-44 mx-auto pt-1 font-semibold text-slate-800">
                            ( ........................................ )
                        </div>
                        <div class="text-[10px] text-slate-500 mt-0.5">{{ __('sales.returns.sig_stamp_date') }}</div>
                    </div>
                </div>

                <!-- Kolom 3: Supervisor / Manajemen -->
                <div class="space-y-16">
                    <div>
                        <div class="text-slate-500">{{ $business->name }},</div>
                        <div class="font-bold text-slate-950 mt-1">{{ __('sales.returns.sig_approved_management') }}</div>
                    </div>
                    <div>
                        <div class="border-t border-slate-400 w-44 mx-auto pt-1 font-semibold text-slate-800">
                            ( ........................................ )
                        </div>
                        <div class="text-[10px] text-slate-500 mt-0.5">{{ __('sales.returns.sig_authorized_signature') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footnote -->
        <div class="pt-6 border-t border-slate-200 text-center text-[10px] text-slate-400">
            {{ __('sales.returns.footnote_system_doc', ['date' => date('d F Y, H:i')]) }}
        </div>

    </div>

    <!-- Client-Side PDF Generation Script -->
    <script>
        function downloadPDF() {
            const btn = document.getElementById('btnDownloadPdf');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-wait');
            btn.innerHTML = `
                <svg class="animate-spin w-4 h-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ __('sales.returns.pdf_generating') }}</span>
            `;

            const element = document.querySelector('.print-sheet');
            const prevPadding = element.style.padding;
            element.style.padding = '0px';

            const filename = 'Nota-Retur-{{ $return->return_number }}.pdf';

            const opt = {
                margin: [14, 16, 14, 16],
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    scrollY: 0
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                },
                pagebreak: {
                    mode: ['avoid-all', 'css', 'legacy']
                }
            };

            if (typeof html2pdf !== 'undefined') {
                html2pdf().set(opt).from(element).save().then(() => {
                    element.style.padding = prevPadding;
                    btn.disabled = false;
                    btn.classList.remove('opacity-75', 'cursor-wait');
                    btn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>{{ __('sales.returns.pdf_download_success') }}</span>
                    `;
                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                    }, 3500);
                }).catch(err => {
                    element.style.padding = prevPadding;
                    console.error('html2pdf error:', err);
                    btn.disabled = false;
                    btn.classList.remove('opacity-75', 'cursor-wait');
                    btn.innerHTML = originalHtml;
                    window.print();
                });
            } else {
                element.style.padding = prevPadding;
                window.print();
            }
        function downloadPDF() {
            window.location.href = "{{ route('sales.returns.pdf', $return) }}";
        }

        window.addEventListener('load', () => {
            const params = new URLSearchParams(window.location.search);
            if (params.get('download') === '1' || params.get('pdf') === '1') {
                window.location.href = "{{ route('sales.returns.pdf', $return) }}";
            } else if (params.get('print') === '1') {
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        });
    </script>
</body>

</html>
