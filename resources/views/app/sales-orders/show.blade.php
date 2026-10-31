@extends('layouts.app', [
    'title' => __('sales.sales_orders.show_page_title', ['number' => $salesOrder->so_number]),
    'headerTitle' => __('sales.sales_orders.show_header_title'),
    'headerSubtitle' => __('sales.sales_orders.show_header_subtitle'),
])

@section('content')
    <div class="max-w-[1100px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{
        invoiceModalOpen: false,
        deleteModalOpen: false,
        promptInvoice() {
            this.invoiceModalOpen = true;
        },
        submitInvoice() {
            this.invoiceModalOpen = false;
            document.getElementById('form-generate-invoice').submit();
        },
        promptDelete() {
            this.deleteModalOpen = true;
        },
        submitDelete() {
            this.deleteModalOpen = false;
            document.getElementById('form-delete-sales-order-show').submit();
        }
    }">

        <!-- ===================================================== -->
        <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
        <!-- ===================================================== -->
        <header
            class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 print:hidden">
            <div>
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1"
                    aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.sales_orders.breadcrumb_dashboard') }}</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <a href="{{ route('sales.orders.index') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.sales_orders.breadcrumb_sales_orders') }}</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <span class="text-black dark:text-white font-mono font-medium">{{ $salesOrder->so_number }}</span>
                </nav>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight font-mono">
                        {{ $salesOrder->so_number }}</h1>
                    @if ($salesOrder->status === 'fulfilled')
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('sales.sales_orders.status_fulfilled') }}
                        </span>
                    @elseif($salesOrder->status === 'partially_fulfilled')
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> {{ __('sales.sales_orders.status_partially_fulfilled') }}
                        </span>
                    @elseif($salesOrder->status === 'draft')
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-black/40 dark:bg-white/40"></span> Draf
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span> {{ __('sales.sales_orders.status_confirmed') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- 1-Click Generate Invoice Button -->
                @if ($salesOrder->status !== 'fulfilled' && \App\Support\Context::hasPermission('invoices.create'))
                    <button type="button" @click="promptInvoice()"
                        class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span>{{ __('sales.sales_orders.btn_generate_invoice') }}</span>
                    </button>

                    <form id="form-generate-invoice" action="{{ route('sales.orders.generate-invoice', $salesOrder) }}"
                        method="POST" class="hidden">
                        @csrf
                    </form>
                @endif

                <!-- Edit Button -->
                @if (\App\Support\Context::hasPermission('sales.pipeline') || \App\Support\Context::hasPermission('sales.view'))
                    <a href="{{ route('sales.orders.edit', $salesOrder) }}"
                        class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition flex items-center gap-1.5">
                        <i data-lucide="edit-3" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                        <span>{{ __('sales.edit_pesanan_4') }}</span>
                    </a>
                @endif

                <!-- Real Server-Rendered DomPDF Print & Download -->
                <a href="{{ route('sales.orders.pdf', $salesOrder) }}" target="_blank"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition flex items-center gap-1.5">
                    <i data-lucide="printer" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                    <span>{{ __('sales.sales_orders.btn_print') }} (PDF)</span>
                </a>

                @feature('logistics.delivery_order')
                <!-- Surat Jalan (Delivery Order) Button -->
                <a href="{{ route('sales.orders.delivery-order', $salesOrder) }}" target="_blank"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition flex items-center gap-1.5">
                    <i data-lucide="truck" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Surat Jalan (DO)</span>
                </a>
                @endfeature

                <!-- Delete Draft Sales Order Button -->
                @if ($salesOrder->status === 'draft' && \App\Support\Context::hasPermission('sales.pipeline'))
                    <button type="button" @click="promptDelete()"
                        class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="trash-2" class="w-4 h-4 text-[#FF3B30]"></i>
                        <span>{{ __('sales.hapus_draf') }}</span>
                    </button>

                    <form id="form-delete-sales-order-show" action="{{ route('sales.orders.destroy', $salesOrder) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif

                <!-- Back Link -->
                <a href="{{ route('sales.orders.index') }}"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                    <span>{{ __('sales.sales_orders.btn_back') }}</span>
                </a>
            </div>
        </header>

        <!-- Top Summary Banner -->
        <div
            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
            <div>
                <span class="text-[12px] font-medium text-black/45 dark:text-white/45">{{ __('sales.sales_orders.summary_customer_label') }}</span>
                <h2 class="text-[17px] font-semibold text-black dark:text-white mt-0.5">{{ $salesOrder->customer->name }}
                </h2>
                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                    {{ __('sales.sales_orders.summary_issued_on', ['date' => $salesOrder->order_date->format('d M Y')]) }} &bull; {{ __('sales.sales_orders.summary_eta', ['date' => $salesOrder->expected_delivery_date ? $salesOrder->expected_delivery_date->format('d M Y') : __('sales.sales_orders.summary_eta_default')]) }}
                </p>
            </div>

            <div class="text-left sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-black/5 dark:border-white/5">
                <span class="text-[12px] font-medium text-black/45 dark:text-white/45">{{ __('sales.sales_orders.total_order_value_label') }}</span>
                <div class="text-[22px] sm:text-[26px] font-bold tabular-nums text-[#007AFF] mt-0.5">
                    Rp {{ number_format($salesOrder->total_amount, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Linked Quotation / Invoices Callouts -->
        @if ($salesOrder->quotation || $salesOrder->invoices->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 print:hidden">
                @if ($salesOrder->quotation)
                    <div
                        class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 flex items-center justify-between text-[13px]">
                        <div>
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45">{{ __('sales.sales_orders.origin_quotation_label') }}</span>
                            <p class="font-semibold tabular-nums text-black dark:text-white">
                                {{ $salesOrder->quotation->quotation_number }}</p>
                        </div>
                        <a href="{{ route('sales.quotations.show', $salesOrder->quotation) }}"
                            class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition flex items-center">
                            {{ __('sales.sales_orders.btn_view_quotation') }}
                        </a>
                    </div>
                @endif

                @if ($salesOrder->invoices->count() > 0)
                    <div
                        class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3.5 flex items-center justify-between text-[13px]">
                        <div>
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45">{{ __('sales.sales_orders.invoice_issued_label') }}</span>
                            <p class="font-semibold tabular-nums text-[#34C759]">
                                {{ $salesOrder->invoices->first()->invoice_number }}</p>
                        </div>
                        <a href="{{ route('invoices.show', $salesOrder->invoices->first()) }}"
                            class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#34C759] bg-[#34C759]/10 hover:bg-[#34C759]/15 transition flex items-center">
                            {{ __('sales.sales_orders.btn_open_invoice') }}
                        </a>
                    </div>
                @endif
            </div>
        @endif

        <!-- ===================================================== -->
        <!-- OFFICIAL PRINTABLE SALES ORDER DOCUMENT CARD           -->
        <!-- ===================================================== -->
        <div
            class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 sm:p-8 space-y-6 print:p-0 print:border-none print:shadow-none">
            <!-- Letterhead Header -->
            <div
                class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-black/5 dark:border-white/10 pb-6">
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5">
                        @if ($business->logo_url)
                            <img src="{{ $business->logo_url }}" alt="{{ $business->name }}"
                                class="h-9 w-auto object-contain">
                        @endif
                        <span
                            class="text-[20px] font-bold text-black dark:text-white tracking-tight">{{ $business->name }}</span>
                    </div>
                    <p class="text-[13px] text-black/55 dark:text-white/55 leading-relaxed">
                        {{ $business->address ?? __('sales.sales_orders.doc_default_address') }}</p>
                    <p class="text-[12px] text-black/50 dark:text-white/50 tabular-nums">{{ $business->phone ?? '' }}</p>
                </div>

                <div class="text-left sm:text-right space-y-1">
                    <h2 class="text-[18px] font-bold uppercase tracking-wider text-[#007AFF]">{{ __('sales.sales_orders.doc_heading_title') }}</h2>
                    <p class="text-[15px] font-semibold tabular-nums text-black dark:text-white">
                        {{ $salesOrder->so_number }}</p>
                    <div class="text-[12px] text-black/50 dark:text-white/50 space-y-0.5 pt-1">
                        <p>{{ __('sales.sales_orders.col_order_date') }}: <span
                                class="font-medium tabular-nums text-black dark:text-white">{{ $salesOrder->order_date->format('d M Y') }}</span>
                        </p>
                        @if ($salesOrder->expected_delivery_date)
                            <p>{{ __('sales.sales_orders.col_expected_delivery') }}: <span
                                    class="font-medium tabular-nums text-black dark:text-white">{{ $salesOrder->expected_delivery_date->format('d M Y') }}</span>
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Addresses Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div
                    class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-1 text-[13px]">
                    <span
                        class="text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">{{ __('sales.sales_orders.doc_buyer_label') }}</span>
                    <h3 class="font-semibold text-black dark:text-white text-[15px]">{{ $salesOrder->customer->name }}</h3>
                    @if ($salesOrder->customer->company)
                        <p class="text-black/60 dark:text-white/60">{{ $salesOrder->customer->company }}</p>
                    @endif
                    <p class="text-black/50 dark:text-white/50 tabular-nums">{{ $salesOrder->customer->phone }}</p>
                    <p class="text-black/55 dark:text-white/55">{{ $salesOrder->customer->address }}</p>
                </div>

                <div
                    class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-1 text-[13px]">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">{{ __('sales.sales_orders.doc_shipping_address_label') }}</span>
                    @if ($salesOrder->shipping_address)
                        <p class="text-black/70 dark:text-white/70 leading-relaxed">{{ $salesOrder->shipping_address }}</p>
                    @else
                        <p class="text-black/40 dark:text-white/40 italic">{{ __('sales.sales_orders.doc_same_as_customer') }}</p>
                    @endif
                </div>
            </div>

            <!-- Items Table -->
            <div class="overflow-x-auto rounded-[10px] border border-black/5 dark:border-white/10">
                <table class="w-full text-left text-[13px] min-w-[650px]">
                    <thead>
                        <tr
                            class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 text-[11px] font-semibold uppercase tracking-wide">
                            <th class="py-2.5 px-3 w-10 text-center">{{ __('sales.sales_orders.doc_col_num') }}</th>
                            <th class="py-2.5 px-3">{{ __('sales.sales_orders.doc_col_desc') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('sales.sales_orders.doc_col_unit_price') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('sales.sales_orders.doc_col_qty') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('sales.sales_orders.doc_col_fulfilled') }}</th>
                            <th class="py-2.5 px-3 text-right">{{ __('sales.sales_orders.doc_col_subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach ($salesOrder->items as $idx => $item)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="py-3 px-3 text-center tabular-nums text-black/40 dark:text-white/40">
                                    {{ $idx + 1 }}</td>
                                <td class="py-3 px-3 font-medium text-black dark:text-white">
                                    {{ $item->product_name }}
                                </td>
                                <td class="py-3 px-3 text-right tabular-nums text-black/60 dark:text-white/60">
                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right tabular-nums font-semibold text-black dark:text-white">
                                    {{ (float) $item->quantity }}
                                </td>
                                <td class="py-3 px-3 text-right tabular-nums font-semibold text-[#007AFF]">
                                    {{ (float) $item->fulfilled_quantity }}
                                </td>
                                <td class="py-3 px-3 text-right tabular-nums font-semibold text-black dark:text-white">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Notes & Financial Breakdown -->
            <div class="pt-2 flex flex-col sm:flex-row justify-between items-start gap-6">
                <div class="w-full sm:w-1/2 space-y-2">
                    @if ($salesOrder->notes)
                        <div
                            class="rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-1 text-[13px]">
                            <span
                                class="text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40">{{ __('sales.sales_orders.doc_notes_label') }}</span>
                            <p class="text-black/70 dark:text-white/70 leading-relaxed">{{ $salesOrder->notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- Financial Summary Box -->
                <div
                    class="w-full sm:w-80 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-4 space-y-2.5 text-[13px]">
                    <div class="flex justify-between text-black/60 dark:text-white/60">
                        <span>{{ __('sales.sales_orders.doc_subtotal_label') }}</span>
                        <span class="font-semibold tabular-nums text-black dark:text-white">
                            Rp {{ number_format($salesOrder->items->sum('subtotal'), 0, ',', '.') }}
                        </span>
                    </div>

                    @if ($salesOrder->discount_amount > 0)
                        <div class="flex justify-between text-[#FF3B30]">
                            <span>{{ __('sales.sales_orders.doc_discount_label') }}</span>
                            <span class="tabular-nums">- Rp
                                {{ number_format($salesOrder->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if ($salesOrder->tax_amount > 0)
                        <div class="flex justify-between text-black/60 dark:text-white/60">
                            <span>{{ __('sales.sales_orders.doc_tax_label', ['percent' => number_format($salesOrder->tax_percentage, 1)]) }}</span>
                            <span class="tabular-nums text-[#34C759]">+ Rp
                                {{ number_format($salesOrder->tax_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="pt-2.5 border-t border-black/5 dark:border-white/10 flex justify-between items-baseline">
                        <span class="font-semibold text-black dark:text-white">{{ __('sales.sales_orders.doc_grand_total_label') }}</span>
                        <span class="text-[18px] font-bold tabular-nums text-[#007AFF]">Rp
                            {{ number_format($salesOrder->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Signature Authorization Block for Print -->
            <div class="hidden print:grid grid-cols-3 gap-6 pt-12 text-center text-xs text-black/70">
                <div class="space-y-12">
                    <p class="font-semibold">{{ __('sales.sales_orders.sig_created_by') }}</p>
                    <p class="border-t border-black/30 pt-1">{{ __('sales.sales_orders.sig_sales_dept') }}</p>
                </div>
                <div class="space-y-12">
                    <p class="font-semibold">{{ __('sales.sales_orders.sig_approved_by') }}</p>
                    <p class="border-t border-black/30 pt-1">{{ __('sales.sales_orders.sig_supervisor') }}</p>
                </div>
                <div class="space-y-12">
                    <p class="font-semibold">{{ __('sales.sales_orders.sig_received_by') }}</p>
                    <p class="border-t border-black/30 pt-1">{{ $salesOrder->customer->name }}</p>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- APPLE ALERT DIALOG: TERBITKAN FAKTUR TAGIHAN          -->
        <!-- ===================================================== -->
        <template x-teleport="body">
            <div x-show="invoiceModalOpen" x-cloak
                class="fixed inset-0 z-[220] overflow-y-auto"
                @keydown.escape.window="invoiceModalOpen = false"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="so-invoice-confirm-dialog-title">

                <!-- Backdrop -->
                <div x-show="invoiceModalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

                <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="invoiceModalOpen = false">
                    <div class="relative w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
                        @click.away="invoiceModalOpen = false"
                        x-show="invoiceModalOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95">

                        <div class="px-4 pt-5 pb-4">
                            <p id="so-invoice-confirm-dialog-title" class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.sales_orders.modal_invoice_title') }}</p>
                            <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                                {{ __('sales.sales_orders.modal_invoice_prompt') }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                            <button type="button" @click="invoiceModalOpen = false"
                                class="min-h-[44px] py-3 text-black/60 dark:text-white/60 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                {{ __('sales.sales_orders.modal_btn_cancel') }}
                            </button>
                            <button type="button" @click="submitInvoice()"
                                class="min-h-[44px] py-3 text-[#007AFF] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                {{ __('sales.sales_orders.modal_btn_issue') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Apple HIG Confirmation Modal: Delete Draft Sales Order -->
        <template x-teleport="body">
            <div x-show="deleteModalOpen" x-cloak
                class="fixed inset-0 z-[220] overflow-y-auto"
                @keydown.escape.window="deleteModalOpen = false"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="so-delete-confirm-dialog-title">

                <!-- Backdrop -->
                <div x-show="deleteModalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

                <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="deleteModalOpen = false">
                    <div class="relative w-[320px] rounded-[18px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
                        @click.away="deleteModalOpen = false"
                        x-show="deleteModalOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95">

                        <div class="px-4 pt-5 pb-4">
                            <p id="so-delete-confirm-dialog-title" class="text-[17px] font-semibold text-black dark:text-white">Hapus Pesanan Penjualan?</p>
                            <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                                Apakah Anda yakin ingin menghapus draf pesanan <span class="font-medium text-black dark:text-white">{{ $salesOrder->so_number }}</span>? Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                            <button type="button" @click="deleteModalOpen = false"
                                class="min-h-[44px] py-3 text-black/60 dark:text-white/60 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">{{ __('common.cancel') }}</button>
                            <button type="button" @click="submitDelete()"
                                class="min-h-[44px] py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">{{ __('common.delete') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>
@endsection
