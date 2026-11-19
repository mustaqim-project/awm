<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('production.page_title_print', ['order_number' => $productionOrder->order_number, 'business' => $business->name]) }}</title>

    <!-- System Font & Fallback -->
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
            background-color: #f2f2f7;
            color: #000000;
        }

        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
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

<body class="py-6 px-4 sm:px-6 bg-[#F2F2F7] dark:bg-[#000000]">

    <!-- Floating Top Print Action Bar (macOS Sonoma Floating Toolbar) -->
    <div
        class="no-print max-w-4xl mx-auto mb-6 backdrop-blur-md bg-white/90 dark:bg-[#1C1C1E]/90 border border-black/10 dark:border-white/10 p-4 rounded-[14px] shadow-[0_4px_24px_rgba(0,0,0,0.06)] space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $productionOrder->isCompleted() ? 'bg-[#34C759]' : ($productionOrder->isInProgress() ? 'bg-[#FF9500]' : 'bg-[#007AFF]') }}"></span>
                <span class="text-[13px] font-semibold text-black dark:text-white tracking-tight">{{ __('production.spkp_print_doc_title') }}</span>
                <span class="text-[12px] font-mono font-medium text-black/50 dark:text-white/50">#{{ $productionOrder->order_number }}</span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.history.back()"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all">{{ __('common.back') }}</button>
                <button onclick="window.print()"
                    class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-black/60 dark:text-white/60"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6" />
                        <rect x="6" y="14" width="12" height="8" rx="1" />
                    </svg>
                    <span>{{ __('production.spkp_print_printer_btn') }}</span>
                </button>
                <button id="btnDownloadPdf" onclick="downloadPDF()"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)] flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    <span>{{ __('production.download_pdf_direct') }}</span>
                </button>
            </div>
        </div>
        <div
            class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center gap-2 text-[12px] text-black/50 dark:text-white/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#007AFF] shrink-0" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
            <span>{{ __('production.spkp_print_direct_hint') }}</span>
        </div>
    </div>

    <!-- Paper Sheet (Standard A4 Flat Container) -->
    <div id="printArea"
        class="print-sheet max-w-4xl mx-auto bg-white p-8 sm:p-10 border border-black/10 shadow-sm rounded-[14px] text-black space-y-5">

        <!-- 1. Header / Kop Surat -->
        <div class="flex justify-between items-start border-b-2 border-black pb-4">
            <div class="space-y-1 max-w-md">
                @if ($business->logo_url)
                    <div class="mb-1">
                        <img src="{{ $business->logo_url }}" alt="{{ $business->name }}"
                            class="max-h-12 max-w-[140px] object-contain">
                    </div>
                @endif
                <div class="space-y-0.5">
                    <h1 class="text-[16px] font-bold text-black uppercase tracking-wide leading-tight">
                        {{ $business->name }}
                    </h1>
                    <p class="text-[12px] text-black/70">{{ $business->address ?? 'Alamat Kantor / Pabrik' }}</p>
                    <p class="text-[12px] text-black/70">
                        @if ($business->email)
                            Email: {{ $business->email }}
                        @endif
                        @if ($business->phone)
                            | Telp: {{ $business->phone }}
                        @endif
                    </p>
                    @if ($business->tax_identification_number)
                        <p class="text-[12px] text-black/70 tabular-nums">NPWP:
                            {{ $business->tax_identification_number }}</p>
                    @endif
                </div>
            </div>

            <div class="text-right space-y-1">
                <div class="text-[16px] font-bold tracking-wider text-black uppercase">
                    {{ __('production.spkp_print_doc_title') }}
                </div>
                <div class="text-[14px] font-bold tabular-nums font-mono text-black/90">{{ $productionOrder->order_number }}</div>
                <div class="text-[11px] text-black/60">
                    {{ __('production.print_info_order_date') }}: <span class="font-semibold text-black">{{ $productionOrder->created_at?->translatedFormat('d F Y') }}</span>
                </div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-black/70 mt-1">
                    STATUS: <span class="font-bold text-black px-1.5 py-0.5 rounded border border-black/20 text-[10.5px]">
                        {{ strtoupper(str_replace('_', ' ', $productionOrder->status)) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Informasi Produk Target & Rencana Produksi -->
        <div class="rounded-lg border border-black/15 bg-black/[0.015] p-3.5 space-y-2">
            <div class="flex items-center justify-between border-b border-black/10 pb-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-black/60">{{ __('production.print_info_production_plan') }}</span>
                <span class="text-[11px] text-black/50">{{ __('production.print_doc_header_subtitle') }}</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-[12px]">
                <!-- Target Product -->
                <div class="col-span-2">
                    <span class="text-black/50 block text-[10.5px] uppercase font-semibold">{{ __('production.print_info_target_product') }}</span>
                    <span class="text-[15px] font-bold text-black block leading-snug">
                        {{ $productionOrder->product?->name ?? __('production.product_deleted') }}
                    </span>
                    <span class="text-[11px] text-black/60 font-mono">
                        {{ __('production.print_info_sku') }}: {{ $productionOrder->product?->code ?? '-' }}
                    </span>
                </div>

                <!-- Planned Quantity -->
                <div>
                    <span class="text-black/50 block text-[10.5px] uppercase font-semibold">{{ __('production.print_info_planned_qty') }}</span>
                    <span class="text-[16px] font-bold tabular-nums text-black block">
                        {{ number_format((float) $productionOrder->planned_quantity, 0, ',', '.') }}
                        <span class="text-[12px] font-normal text-black/70">{{ $productionOrder->product?->outputUnit?->name ?? 'pcs' }}</span>
                    </span>
                </div>

                <!-- Planned Start Date -->
                <div>
                    <span class="text-black/50 block text-[10.5px] uppercase font-semibold">{{ __('production.print_info_start_date') }}</span>
                    <span class="text-[13px] font-semibold tabular-nums text-black block">
                        {{ $productionOrder->planned_start_date ? \Carbon\Carbon::parse($productionOrder->planned_start_date)->translatedFormat('d F Y') : '-' }}
                    </span>
                </div>

                <!-- Source Warehouse -->
                <div>
                    <span class="text-black/50 block text-[10.5px] uppercase font-semibold">{{ __('production.print_info_source_wh') }}</span>
                    <span class="text-[12.5px] font-medium text-black block">
                        {{ $productionOrder->sourceLocation?->name ?? '-' }}
                    </span>
                </div>

                <!-- Target Warehouse -->
                <div>
                    <span class="text-black/50 block text-[10.5px] uppercase font-semibold">{{ __('production.print_info_target_wh') }}</span>
                    <span class="text-[12.5px] font-medium text-black block">
                        {{ $productionOrder->targetLocation?->name ?? '-' }}
                    </span>
                </div>

                <!-- Batch Number -->
                <div>
                    <span class="text-black/50 block text-[10.5px] uppercase font-semibold">{{ __('production.print_info_batch_no') }}</span>
                    <span class="text-[12px] font-mono font-medium text-black block">
                        {{ $productionOrder->batch_number ?: '-' }}
                    </span>
                </div>

                <!-- Expiry Date -->
                <div>
                    <span class="text-black/50 block text-[10.5px] uppercase font-semibold">{{ __('production.print_info_expiry') }}</span>
                    <span class="text-[12px] font-medium text-black block">
                        {{ $productionOrder->expiry_date ? \Carbon\Carbon::parse($productionOrder->expiry_date)->translatedFormat('d F Y') : '-' }}
                    </span>
                </div>
            </div>

            @if($productionOrder->notes)
                <div class="pt-2 border-t border-black/10 text-[11.5px]">
                    <span class="text-black/50 font-semibold uppercase text-[10px]">{{ __('production.print_info_notes') }}:</span>
                    <p class="text-black/80 italic mt-0.5">{{ $productionOrder->notes }}</p>
                </div>
            @endif
        </div>

        <!-- 3. Tabel Bahan Baku & Pengeluaran Aktual (WIP) -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <h3 class="text-[12px] font-bold uppercase tracking-wider text-black">
                    {{ __('production.print_table_title') }}
                </h3>
                <span class="text-[11px] text-black/50 tabular-nums">
                    {{ __('production.count_materials', ['count' => $productionOrder->items->count()]) }}
                </span>
            </div>

            <table class="w-full text-left text-[12px] border-collapse">
                <thead>
                    <tr class="border-y-2 border-black bg-black/[0.04]">
                        <th class="py-2.5 px-3 font-bold uppercase text-[10.5px] text-black text-center w-10">
                            {{ __('production.print_table_no') }}
                        </th>
                        <th class="py-2.5 px-3 font-bold uppercase text-[10.5px] text-black">
                            {{ __('production.print_table_material') }}
                        </th>
                        <th class="py-2.5 px-3 font-bold uppercase text-[10.5px] text-black text-right">
                            {{ __('production.print_table_planned') }}
                        </th>
                        <th class="py-2.5 px-3 font-bold uppercase text-[10.5px] text-black text-right">
                            {{ __('production.print_table_actual') }}
                        </th>
                        <th class="py-2.5 px-3 font-bold uppercase text-[10.5px] text-black text-center w-36">
                            {{ __('production.print_table_notes') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10">
                    @forelse($productionOrder->items as $idx => $item)
                        <tr class="border-b border-black/10">
                            <!-- No -->
                            <td class="py-2.5 px-3 text-center tabular-nums text-black/70 font-medium">
                                {{ $idx + 1 }}
                            </td>

                            <!-- Nama Bahan Baku -->
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-black text-[12.5px]">
                                    {{ $item->material?->name ?? __('production.material_deleted') }}
                                </div>
                                @if($item->material?->code)
                                    <div class="text-[10px] text-black/55 font-mono">
                                        {{ $item->material->code }}
                                    </div>
                                @endif
                            </td>

                            <!-- Rencana Standar (+Waste) -->
                            <td class="py-2.5 px-3 text-right tabular-nums font-semibold text-black">
                                {{ number_format((float) $item->planned_quantity, 2, ',', '.') }}
                                <span class="text-[11px] font-normal text-black/60">{{ $item->unit?->name ?? $item->material?->unit?->name ?? 'Unit' }}</span>
                            </td>

                            <!-- Pengeluaran Aktual (WIP) -->
                            <td class="py-2.5 px-3 text-right tabular-nums">
                                @if((float) $item->actual_consumed_quantity > 0)
                                    <span class="font-bold text-black">
                                        {{ number_format((float) $item->actual_consumed_quantity, 2, ',', '.') }}
                                    </span>
                                    <span class="text-[11px] font-normal text-black/60">{{ $item->unit?->name ?? $item->material?->unit?->name ?? 'Unit' }}</span>
                                @else
                                    <span class="no-print text-black/40 text-[11px]">-</span>
                                    <span class="hidden print:inline-block border-b border-black/30 w-24 text-right">&nbsp;</span>
                                @endif
                            </td>

                            <!-- Keterangan / Paraf -->
                            <td class="py-2.5 px-3 text-center text-[11px] text-black/70">
                                @if($item->notes)
                                    <span>{{ $item->notes }}</span>
                                @else
                                    <span class="hidden print:inline-block border-b border-black/20 w-16">&nbsp;</span>
                                    <span class="no-print text-black/30">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-black/40 text-[12px]">
                                {{ __('production.empty_bom_simulation') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- 4. Lembar Pengesahan / Tanda Tangan -->
        <div class="keep-together pt-6 border-t-2 border-black/20">
            <div class="grid grid-cols-3 gap-6 text-center text-[11.5px]">
                <!-- Dibuat Oleh -->
                <div class="space-y-14">
                    <span class="font-bold text-black uppercase block text-[10.5px] tracking-wider">{{ __('production.print_sign_prepared_by') }}</span>
                    <div>
                        <div class="border-b border-black mx-4"></div>
                        <span class="text-[11px] text-black/70 mt-1 block">
                            {{ $productionOrder->creator?->name ?? 'Admin Produksi' }}
                        </span>
                    </div>
                </div>

                <!-- Dikerjakan Oleh -->
                <div class="space-y-14">
                    <span class="font-bold text-black uppercase block text-[10.5px] tracking-wider">{{ __('production.print_sign_executed_by') }}</span>
                    <div>
                        <div class="border-b border-black mx-4"></div>
                        <span class="text-[11px] text-black/70 mt-1 block">Operator Pabrik / Mandor</span>
                    </div>
                </div>

                <!-- Diperiksa Oleh -->
                <div class="space-y-14">
                    <span class="font-bold text-black uppercase block text-[10.5px] tracking-wider">{{ __('production.print_sign_checked_by') }}</span>
                    <div>
                        <div class="border-b border-black mx-4"></div>
                        <span class="text-[11px] text-black/70 mt-1 block">Supervisor QC / Gudang</span>
                    </div>
                </div>
            </div>

            <!-- Footer Timestamp -->
            <div class="mt-8 pt-3 border-t border-black/10 flex items-center justify-between text-[10px] text-black/45">
                <span>{{ __('production.print_footer_timestamp') }} {{ now()->translatedFormat('d F Y H:i:s') }}</span>
                <span class="font-mono">COOCA-SPKP • {{ $productionOrder->order_number }}</span>
            </div>
        </div>
    </div>

    <!-- Client-Side PDF Script -->
    <script>
        function downloadPDF() {
            const btn = document.getElementById('btnDownloadPdf');
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<span>Sedang Membuat PDF...</span>';
            btn.disabled = true;

            const element = document.getElementById('printArea');
            const opt = {
                margin: [10, 10, 10, 10],
                filename: 'SPKP_{{ $productionOrder->order_number }}.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }).catch(err => {
                console.error('PDF generation failed:', err);
                btn.innerHTML = originalContent;
                btn.disabled = false;
                window.print();
            });
        }
    </script>
</body>

</html>
