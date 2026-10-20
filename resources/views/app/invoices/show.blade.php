@extends('layouts.app', [
    'title' => __('sales.invoices.show_title', ['number' => $invoice->invoice_number]),
    'headerTitle' => __('sales.invoices.show_title', ['number' => $invoice->invoice_number]),
    'headerSubtitle' => __('sales.invoices.show_subtitle')
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{
    showPaymentModal: false,
    showReminderModal: false,
    confirmReleaseModalOpen: false,
    confirmVoidModalOpen: false,
    confirmDeleteModalOpen: false,
    reminderChannel: 'both',
    reminderNotes: '',
    submitRelease() {
        document.getElementById('form-confirm-release').submit();
    },
    submitVoid() {
        document.getElementById('form-confirm-void').submit();
    },
    submitDelete() {
        document.getElementById('form-confirm-delete').submit();
    }
}">

    <x-module-header
        module="sales"
        :title="__('sales.invoices.show_title', ['number' => $invoice->invoice_number])"
        :subtitle="($invoice->customer?->name ?? __('sales.invoices.customer_label')) . ' · ' . __('sales.sales_orders.summary_issued_on', ['date' => $invoice->invoice_date?->translatedFormat('d F Y') ?? ''])"
        :breadcrumbs="[
            ['label' => __('sales.invoices.breadcrumb_dashboard'), 'route' => route('dashboard')],
            ['label' => __('sales.sales_orders.module_title'), 'route' => route('sales.orders.index')],
            ['label' => __('sales.invoices.module_title'), 'route' => route('invoices.index')],
            ['label' => $invoice->invoice_number],
        ]">
        <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
            <a href="{{ route('invoices.index') }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>{{ __('sales.invoices.btn_list') }}</span>
            </a>

            {{-- Tombol Edit Faktur (tampil saat belum lunas dan belum ada pembayaran, bukan void) --}}
            @if(!in_array($invoice->status, ['paid', 'void'], true) && (float) $invoice->paid_amount <= 0 && (\App\Support\Context::hasPermission('invoices.edit') || \App\Support\Context::hasPermission('invoices.create')))
            <a href="{{ route('invoices.edit', $invoice->id) }}" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-[#FF9500] dark:text-[#FF9F0A] bg-[#FF9500]/10 dark:bg-[#FF9500]/20 hover:bg-[#FF9500]/15 dark:hover:bg-[#FF9500]/30 active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
                <span>{{ $invoice->status === 'draft' ? __('sales.invoices.btn_edit_draft') : 'Edit Faktur' }}</span>
            </a>
            @endif

            {{-- Tombol Konfirmasi & Rilis Faktur (hanya tampil saat draft) --}}
            @if($invoice->status === 'draft' && (\App\Support\Context::hasPermission('invoices.create') || \App\Support\Context::hasPermission('invoices.edit')))
            <button type="button" @click="confirmReleaseModalOpen = true" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#2FB350] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(52,199,89,0.25)]">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                <span>{{ __('sales.invoices.btn_confirm_release') }}</span>
            </button>
            <form id="form-confirm-release" method="POST" action="{{ route('invoices.confirm', $invoice->id) }}" class="hidden">
                @csrf
            </form>
            @endif

            {{-- Tombol Catat Pembayaran (hanya saat ada sisa tagihan dan bukan void) --}}
            @if($invoice->balance_due > 0 && $invoice->status !== 'void' && $invoice->status !== 'draft' && \App\Support\Context::hasPermission('invoices.record_payment'))
            <button type="button" @click="showPaymentModal = true" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <i data-lucide="credit-card" class="w-4 h-4"></i>
                <span>{{ __('sales.invoices.btn_record_payment') }}</span>
            </button>
            @endif

            {{-- Tombol Kirim Pengingat Termin (WA & Email) --}}
            @if($invoice->balance_due > 0 && !in_array($invoice->status, ['void', 'draft']) && \App\Support\Context::hasPermission('invoices.view'))
            <button type="button" @click="showReminderModal = true" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-[#5856D6] dark:text-[#BF5AF2] bg-[#5856D6]/10 dark:bg-[#5856D6]/20 hover:bg-[#5856D6]/15 dark:hover:bg-[#5856D6]/30 active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <i data-lucide="bell-ring" class="w-4 h-4"></i>
                <span>{{ __('sales.invoices.btn_send_reminder') }}</span>
            </button>
            @endif

            {{-- Tautan Cepat WhatsApp Web jika ada session wa_url --}}
            @if(session('wa_url'))
            <a href="{{ session('wa_url') }}" target="_blank" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-white bg-[#25D366] hover:bg-[#20BA5A] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(37,211,102,0.25)]">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                <span>{{ __('sales.invoices.btn_open_wa') }}</span>
            </a>
            @endif

            @if(\App\Support\Context::hasPermission('invoices.export') || \App\Support\Context::hasPermission('invoices.view'))
            <a href="{{ route('invoices.pdf', $invoice->id) }}" target="_blank" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="download" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>{{ __('sales.invoices.btn_download_pdf') }}</span>
            </a>
            @endif

            <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="printer" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                <span>{{ __('sales.invoices.btn_print_a4') }}</span>
            </a>

            {{-- Tombol Void (hanya tampil saat bukan draft/void dan belum ada pembayaran) --}}
            @if(!in_array($invoice->status, ['draft','void']) && $invoice->paid_amount == 0 && (\App\Support\Context::hasPermission('invoices.delete') || \App\Support\Context::hasPermission('invoices.edit')))
            <button type="button" @click="confirmVoidModalOpen = true" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <i data-lucide="ban" class="w-4 h-4"></i>
                <span>{{ __('sales.invoices.btn_void_invoice') }}</span>
            </button>
            <form id="form-confirm-void" method="POST" action="{{ route('invoices.void', $invoice->id) }}" class="hidden">
                @csrf
            </form>
            @endif

            {{-- Tombol Hapus Draf (hanya tampil saat status draft dan belum ada pembayaran) --}}
            @if($invoice->status === 'draft' && (float) $invoice->paid_amount <= 0 && \App\Support\Context::hasPermission('invoices.delete'))
            <button type="button" @click="confirmDeleteModalOpen = true" class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <i data-lucide="trash-2" class="w-4 h-4 text-[#FF3B30]"></i>
                <span>Hapus Draf</span>
            </button>
            <form id="form-confirm-delete" method="POST" action="{{ route('invoices.destroy', $invoice->id) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            @endif
        </div>
    </x-module-header>

    <x-module-tabs module="sales" />

    <!-- ===================================================== -->
    <!-- 2. MAIN INVOICE DOCUMENT CARD (Apple Material & Depth) -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 sm:p-8 space-y-6">

        <!-- Document Top Info -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-black/5 dark:border-white/10">
            <div>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-[#007AFF]">{{ __('sales.invoices.doc_badge_commercial') }}</span>
                <h2 class="text-[26px] font-bold text-black dark:text-white tabular-nums tracking-tight mt-1">
                    {{ $invoice->invoice_number }}
                </h2>
                @if($invoice->purchaseOrder)
                    <div class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mt-1">
                        <span>{{ __('sales.invoices.based_on_po') }}</span>
                        <span class="font-medium text-black dark:text-white tabular-nums">{{ $invoice->purchaseOrder->po_number }}</span>
                    </div>
                @endif
            </div>

            <div class="text-left sm:text-right space-y-1 self-start sm:self-auto">
                <div class="text-[13px] text-black/50 dark:text-white/50">
                    {{ __('sales.invoices.invoice_date_label') }}: <span class="font-medium text-black dark:text-white tabular-nums">{{ $invoice->invoice_date?->translatedFormat('d F Y') }}</span>
                </div>
                <div class="text-[13px] text-black/50 dark:text-white/50">
                    {{ __('sales.invoices.due_date_label') }}: <span class="font-medium text-black dark:text-white tabular-nums">{{ $invoice->due_date?->translatedFormat('d F Y') }}</span>
                </div>
                @if($invoice->payment_terms)
                    <div class="text-[12px] text-black/45 dark:text-white/45">
                        {{ __('sales.invoices.payment_terms_col_label') }}: <span class="font-medium text-black/70 dark:text-white/70">{{ $invoice->payment_terms }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Party Information (Two Grouped Inset Blocks) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[13px]">
            <!-- Seller Info -->
            <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('sales.invoices.seller_party_title') }}</span>
                <div class="text-[15px] font-semibold text-black dark:text-white mt-1">{{ $business->name }}</div>
                @if($invoice->sender_name)
                    <div class="text-[12px] font-medium text-[#007AFF]">{{ __('sales.invoices.sender_pic_label') }} {{ $invoice->sender_name }}</div>
                @endif
                <p class="text-black/60 dark:text-white/60 leading-snug">{{ $business->address ?? __('sales.sales_orders.doc_default_address') }}</p>
                <div class="text-black/50 dark:text-white/50 text-[12px] pt-1">
                    {{ __('sales.invoices.currency_info_label') }} <span class="font-medium text-black/70 dark:text-white/70">{{ $business->currency_code }} ({{ $business->currency_symbol }})</span>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('sales.invoices.recipient_party_title') }}</span>
                <div class="text-[15px] font-semibold text-black dark:text-white mt-1">{{ $invoice->customer?->name ?? __('sales.invoices.customer_label') }}</div>
                @if($invoice->recipient_name)
                    <div class="text-[12px] font-medium text-[#34C759] dark:text-[#30D158]">{{ __('sales.invoices.recipient_pic_label') }} {{ $invoice->recipient_name }}</div>
                @endif
                @if($invoice->customer?->company_name)
                    <div class="text-[12px] font-medium text-[#007AFF]">{{ $invoice->customer->company_name }}</div>
                @endif
                <p class="text-black/60 dark:text-white/60 leading-snug">{{ $invoice->customer?->billing_address ?? '-' }}</p>
                <div class="text-black/50 dark:text-white/50 text-[12px] pt-1 flex flex-wrap gap-x-3">
                    <span>{{ __('sales.invoices.contact_label') }} <span class="tabular-nums text-black/70 dark:text-white/70">{{ $invoice->customer?->phone ?? $invoice->customer?->email ?? '-' }}</span></span>
                    @if($invoice->customer?->tax_identification_number)
                        <span>{{ __('sales.invoices.tax_id_label') }} <span class="tabular-nums text-black/70 dark:text-white/70">{{ $invoice->customer->tax_identification_number }}</span></span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Gudang Asal Pengeluaran Barang --}}
        @if($invoice->location)
        <div class="flex items-center gap-2 text-[12px] text-black/55 dark:text-white/55 px-3 py-2 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
            <svg class="w-4 h-4 text-[#007AFF] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009 9c.896 0 1.706-.395 2.25-1.02a2.993 2.993 0 002.25 1.02c.896 0 1.706-.395 2.25-1.02a3.001 3.001 0 003.75.614m-16.5 0L6 3.75h12l2.25 5.6" />
            </svg>
            <span>{{ __('sales.invoices.inventory_out_from') }}</span>
            <span class="font-medium text-black dark:text-white">{{ $invoice->location->name }}</span>
            @if($invoice->location->type)
                <span class="text-black/40 dark:text-white/40">({{ $invoice->location->type }})</span>
            @endif
        </div>
        @endif

        <!-- Items Table (Desktop & Tablet Dense List) -->
        <div class="rounded-[12px] border border-black/5 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] whitespace-nowrap">
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 w-12 text-center">{{ __('sales.invoices.print_col_no') }}</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('sales.invoices.col_item_desc') }}</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center w-24">{{ __('sales.invoices.col_unit') }}</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-20">{{ __('sales.invoices.col_qty') }}</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-32">{{ __('sales.invoices.print_col_unit_price') }}</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-[#FF9500] dark:text-[#FF9F0A] text-right w-28">{{ __('sales.invoices.col_hpp') }}</th>
                            <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right w-36">{{ __('sales.invoices.col_subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @foreach($invoice->items as $idx => $item)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                            <td class="py-3 px-4 text-center tabular-nums text-black/40 dark:text-white/40">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-black dark:text-white">{{ $item->item_name }}</div>
                                @if($item->sku)
                                    <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums mt-0.5">SKU: {{ $item->sku }}</div>
                                @endif
                                @if($item->description)
                                    <div class="text-[12px] text-black/55 dark:text-white/55 mt-0.5">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center text-black/60 dark:text-white/60">
                                {{ $item->unit?->name ?? 'pcs' }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums font-semibold text-black dark:text-white">
                                {{ (float)$item->quantity == (int)$item->quantity ? number_format((float)$item->quantity, 0, ',', '.') : rtrim(rtrim(number_format((float)$item->quantity, 2, ',', '.'), '0'), ',') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-black/70 dark:text-white/70">
                                {{ $business->currency_symbol }} {{ number_format((float)$item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">
                                {{ $business->currency_symbol }} {{ number_format((float)$item->unit_hpp, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums font-semibold text-black dark:text-white">
                                {{ $business->currency_symbol }} {{ number_format((float)$item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Financial Summary Breakdown & Margin -->
        <div class="flex flex-col sm:flex-row justify-between items-start pt-4 border-t border-black/5 dark:border-white/10 gap-6">
            <div class="space-y-3 w-full sm:max-w-md text-[13px]">
                <!-- Profit Analysis Card (Apple System Green Tint) -->
                <div class="p-4 rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 space-y-1.5">
                    <div class="text-[11px] text-[#248A3D] dark:text-[#30D158] font-semibold uppercase tracking-wider flex items-center justify-between">
                        <span>{{ __('sales.invoices.real_gross_profit_title') }}</span>
                        <span class="px-2 py-0.5 rounded-full bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] text-[11px] font-bold tabular-nums">
                            {{ $invoice->subtotal > 0 ? round(($invoice->total_gross_profit / $invoice->subtotal) * 100) : 0 }}% {{ __('sales.invoices.margin_label') }}
                        </span>
                    </div>
                    <div class="text-[22px] font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums">
                        {{ $business->currency_symbol }} {{ number_format((float)$invoice->total_gross_profit, 0, ',', '.') }}
                    </div>
                    <div class="text-[11px] text-black/50 dark:text-white/50">
                        {{ __('sales.invoices.sales_label') }} <span class="tabular-nums">{{ $business->currency_symbol }} {{ number_format((float)$invoice->subtotal, 0, ',', '.') }}</span> · {{ __('sales.invoices.cogs_label') }} <span class="tabular-nums">{{ $business->currency_symbol }} {{ number_format((float)$invoice->total_hpp_cost, 0, ',', '.') }}</span>
                    </div>
                </div>

                @if($invoice->notes)
                <div class="p-3 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <div class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wide mb-0.5">{{ __('sales.invoices.invoice_notes_label') }}</div>
                    <div class="text-black/70 dark:text-white/70 whitespace-pre-line leading-relaxed">{{ $invoice->notes }}</div>
                </div>
                @endif

                @if($invoice->terms_conditions)
                <div class="p-3 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <div class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wide mb-0.5">{{ __('sales.invoices.terms_title') }}</div>
                    <div class="text-black/70 dark:text-white/70 whitespace-pre-line">{{ $invoice->terms_conditions }}</div>
                </div>
                @endif
            </div>

            <!-- Financial Calculation Totals -->
            <div class="w-full sm:w-80 space-y-2 text-[13px]">
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>{{ __('sales.invoices.subtotal_label') }}</span>
                    <span class="tabular-nums font-medium text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format((float)$invoice->subtotal, 0, ',', '.') }}</span>
                </div>

                @if($invoice->discount_amount > 0)
                <div class="flex justify-between text-[#FF3B30] dark:text-[#FF453A]">
                    <span>{{ __('sales.invoices.discount_label') }}</span>
                    <span class="tabular-nums font-medium">- {{ $business->currency_symbol }} {{ number_format((float)$invoice->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif

                @if($invoice->tax_amount > 0)
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>{{ __('sales.invoices.tax_vat_label') }} ({{ $invoice->tax_percentage }}%):</span>
                    <span class="tabular-nums font-medium text-black dark:text-white">+ {{ $business->currency_symbol }} {{ number_format((float)$invoice->tax_amount, 0, ',', '.') }}</span>
                </div>
                @endif

                @if($invoice->shipping_cost > 0)
                <div class="flex justify-between text-black/60 dark:text-white/60">
                    <span>{{ __('sales.invoices.shipping_cost_label') }}</span>
                    <span class="tabular-nums font-medium text-black dark:text-white">+ {{ $business->currency_symbol }} {{ number_format((float)$invoice->shipping_cost, 0, ',', '.') }}</span>
                </div>
                @endif

                <div class="pt-2 border-t border-black/5 dark:border-white/10 flex justify-between items-center text-[15px] font-bold text-black dark:text-white">
                    <span>{{ __('sales.invoices.grand_total_label') }}</span>
                    <span class="tabular-nums text-[18px] text-[#007AFF]">{{ $business->currency_symbol }} {{ number_format((float)$invoice->total_amount, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between text-black/60 dark:text-white/60 pt-1">
                    <span>{{ __('sales.invoices.paid_amount_label') }}</span>
                    <span class="tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">{{ $business->currency_symbol }} {{ number_format((float)$invoice->paid_amount, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between items-center pt-2 border-t border-black/5 dark:border-white/10 font-bold {{ $invoice->balance_due > 0 ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-[#34C759] dark:text-[#30D158]' }}">
                    <span class="text-[13px]">{{ __('sales.invoices.remaining_receivable_label') }}</span>
                    <span class="tabular-nums text-[16px]">{{ $business->currency_symbol }} {{ number_format((float)$invoice->balance_due, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Payments History Section -->
        <div class="pt-6 border-t border-black/5 dark:border-white/10 space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white">{{ __('sales.invoices.history_receipts_title') }}</h3>
                </div>
                @if($invoice->balance_due > 0 && $invoice->status !== 'void' && $invoice->status !== 'draft')
                <button type="button" @click="showPaymentModal = true" class="h-7 px-2.5 rounded-[6px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.97] transition-all flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>{{ __('sales.invoices.btn_add_payment') }}</span>
                </button>
                @endif
            </div>

            <div class="rounded-[12px] border border-black/5 dark:border-white/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('sales.invoices.col_receipt_no') }}</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('sales.invoices.col_date') }}</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('sales.invoices.payment_method_label') }}</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">{{ __('sales.invoices.col_ref_bank') }}</th>
                                <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">{{ __('sales.invoices.col_amount_paid') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                            @forelse($invoice->payments as $payment)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="py-3 px-4 font-semibold text-black dark:text-white tabular-nums">{{ $payment->payment_number }}</td>
                                <td class="py-3 px-4 tabular-nums text-black/60 dark:text-white/60">{{ $payment->payment_date?->translatedFormat('d M Y') }}</td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/70 dark:text-white/70">
                                        {{ str_replace('_', ' ', $payment->payment_method) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-black/50 dark:text-white/50 tabular-nums">{{ $payment->reference_number ?? '-' }}</td>
                                <td class="py-3 px-4 text-right tabular-nums font-semibold text-[#34C759] dark:text-[#30D158]">
                                    {{ $business->currency_symbol }} {{ number_format((float)$payment->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-black/40 dark:text-white/40 text-[13px]">
                                    {{ __('sales.invoices.no_payment_receipts') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- ===================================================== -->
    <!-- 3. APPLE SHEET: CATAT PEMBAYARAN MODAL                -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('invoices.record_payment'))
    <template x-teleport="body">
        <div x-show="showPaymentModal"
            x-cloak
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showPaymentModal = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="invoice-payment-modal-title">

            <!-- Backdrop -->
            <div x-show="showPaymentModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

            <div class="relative z-10 min-h-full flex items-end sm:items-center justify-center p-0 sm:p-4" @click.self="showPaymentModal = false">
                <div class="relative w-full sm:max-w-md rounded-t-[20px] sm:rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]"
                    @click.away="showPaymentModal = false"
                    x-show="showPaymentModal"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                    x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-y-0 sm:scale-100 sm:opacity-100"
                    x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0">

                    <!-- Mobile Grabber Handle -->
                    <div class="sm:hidden w-10 h-1 rounded-full bg-black/20 dark:bg-white/20 mx-auto mb-2"></div>

                    <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                        <h3 id="invoice-payment-modal-title" class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.invoices.modal_payment_record_title') }}</h3>
                        <button type="button" @click="showPaymentModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') ?? 'Close' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('invoices.payments.store', $invoice->id) }}" class="space-y-4 text-[13px]">
                        @csrf

                        <!-- Balance Due Tile -->
                        <div class="p-3.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] flex justify-between items-center">
                            <span class="text-black/60 dark:text-white/60">{{ __('sales.invoices.remaining_receivable_label') }}</span>
                            <span class="font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A] text-[15px]">
                                {{ $business->currency_symbol }} {{ number_format((float)$invoice->balance_due, 0, ',', '.') }}
                            </span>
                        </div>

                        <div>
                            <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('sales.invoices.payment_received_amount_label') }}</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-medium">{{ $business->currency_symbol }}</span>
                                <input type="number" step="any" min="1" max="{{ (float)$invoice->balance_due }}" name="amount" value="{{ (float)$invoice->balance_due }}" required
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-10 pr-3.5 text-[15px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('sales.invoices.payment_date_label') }} *</label>
                                <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>
                            <div>
                                <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('sales.invoices.payment_method_label') }} *</label>
                                <select name="payment_method" required class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <option value="bank_transfer">{{ __('sales.invoices.method_bank_transfer') }}</option>
                                    <option value="cash">{{ __('sales.invoices.method_cash') }}</option>
                                    <option value="qris">{{ __('sales.invoices.method_qris') }}</option>
                                    <option value="credit_card">{{ __('sales.invoices.method_credit_card') }}</option>
                                    <option value="cheque">{{ __('sales.invoices.method_other') }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('sales.invoices.payment_ref_label') }}</label>
                            <input type="text" name="reference_number" placeholder="TRX-BCA-981203"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>

                        <div>
                            <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1">{{ __('sales.invoices.col_payment_notes') }}</label>
                            <input type="text" name="notes" placeholder="{{ __('sales.invoices.payment_notes_placeholder') }}"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="showPaymentModal = false" class="min-h-[44px] h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 active:scale-[0.97] transition-all cursor-pointer">
                                {{ __('sales.invoices.btn_cancel') }}
                            </button>
                            <button type="submit" class="min-h-[44px] h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all shadow-[0_1px_2px_rgba(0,122,255,0.25)] cursor-pointer">
                                {{ __('sales.invoices.btn_confirm_pay') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
    @endif

    <!-- ===================================================== -->
    <!-- 4. APPLE ALERT DIALOG: KONFIRMASI RILIS FAKTUR         -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('invoices.create') || \App\Support\Context::hasPermission('invoices.edit'))
    <template x-teleport="body">
        <div x-show="confirmReleaseModalOpen"
            x-cloak
            class="fixed inset-0 z-[220] overflow-y-auto"
            @keydown.escape.window="confirmReleaseModalOpen = false"
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="invoice-release-dialog-title">

            <!-- Backdrop -->
            <div x-show="confirmReleaseModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="confirmReleaseModalOpen = false">
                <div class="relative w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
                    @click.away="confirmReleaseModalOpen = false"
                    x-show="confirmReleaseModalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">

                    <div class="px-4 pt-5 pb-4">
                        <p id="invoice-release-dialog-title" class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.invoices.dialog_release_title') }}</p>
                        <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                            {{ __('sales.invoices.dialog_release_desc') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                        <button type="button" @click="confirmReleaseModalOpen = false" class="min-h-[44px] py-3 text-black/70 dark:text-white/70 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                            {{ __('sales.invoices.btn_cancel') }}
                        </button>
                        <button type="button" @click="submitRelease()" class="min-h-[44px] py-3 text-[#34C759] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                            {{ __('sales.invoices.dialog_release_confirm_btn') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
    @endif

    <!-- ===================================================== -->
    <!-- 5. APPLE ALERT DIALOG: VOID / BATALKAN FAKTUR          -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('invoices.delete') || \App\Support\Context::hasPermission('invoices.edit'))
    <template x-teleport="body">
        <div x-show="confirmVoidModalOpen"
            x-cloak
            class="fixed inset-0 z-[220] overflow-y-auto"
            @keydown.escape.window="confirmVoidModalOpen = false"
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="invoice-void-dialog-title">

            <!-- Backdrop -->
            <div x-show="confirmVoidModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="confirmVoidModalOpen = false">
                <div class="relative w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
                    @click.away="confirmVoidModalOpen = false"
                    x-show="confirmVoidModalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">

                    <div class="px-4 pt-5 pb-4">
                        <p id="invoice-void-dialog-title" class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.invoices.dialog_void_title') }}</p>
                        <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                            {{ __('sales.invoices.dialog_void_desc') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                        <button type="button" @click="confirmVoidModalOpen = false" class="min-h-[44px] py-3 text-black/70 dark:text-white/70 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                            {{ __('sales.invoices.btn_back') }}
                        </button>
                        <button type="button" @click="submitVoid()" class="min-h-[44px] py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                            {{ __('sales.invoices.dialog_void_btn') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
    @endif

    <!-- ===================================================== -->
    <!-- 5.1 APPLE ALERT DIALOG: HAPUS DRAF FAKTUR             -->
    <!-- ===================================================== -->
    @if($invoice->status === 'draft' && \App\Support\Context::hasPermission('invoices.delete'))
    <template x-teleport="body">
        <div x-show="confirmDeleteModalOpen"
            x-cloak
            class="fixed inset-0 z-[220] overflow-y-auto"
            @keydown.escape.window="confirmDeleteModalOpen = false"
            role="alertdialog"
            aria-modal="true"
            aria-labelledby="invoice-delete-dialog-title">

            <!-- Backdrop -->
            <div x-show="confirmDeleteModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="confirmDeleteModalOpen = false">
                <div class="relative w-[320px] rounded-[18px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
                    @click.away="confirmDeleteModalOpen = false"
                    x-show="confirmDeleteModalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">

                    <div class="px-4 pt-5 pb-4">
                        <p id="invoice-delete-dialog-title" class="text-[17px] font-semibold text-black dark:text-white">Hapus Draf Faktur?</p>
                        <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                            Apakah Anda yakin ingin menghapus draf faktur <span class="font-medium text-black dark:text-white">{{ $invoice->invoice_number }}</span>? Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                        <button type="button" @click="confirmDeleteModalOpen = false" class="min-h-[44px] py-3 text-black/70 dark:text-white/70 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                            {{ __('sales.invoices.btn_cancel') }}
                        </button>
                        <button type="button" @click="submitDelete()" class="min-h-[44px] py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
    @endif

    <!-- ===================================================== -->
    <!-- 6. MODAL: KIRIM PENGINGAT TERMIN (WHATSAPP & EMAIL)   -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showReminderModal"
            x-cloak
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showReminderModal = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="invoice-reminder-modal-title">

            <!-- Backdrop -->
            <div x-show="showReminderModal"
                x-transition:enter="ease-out duration-250"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showReminderModal = false">
                <div class="relative w-full max-w-lg rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_24px_50px_rgba(0,0,0,0.25)] overflow-hidden text-left"
                    @click.away="showReminderModal = false"
                    x-show="showReminderModal"
                    x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-2">

                    <!-- Modal Header -->
                    <div class="px-6 pt-6 pb-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-gradient-to-br from-[#5856D6] to-[#007AFF] text-white flex items-center justify-center shadow-md shadow-[#5856D6]/20 shrink-0">
                                <i data-lucide="bell-ring" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 id="invoice-reminder-modal-title" class="text-[17px] font-bold text-black dark:text-white tracking-tight leading-snug">
                                    {{ __('sales.invoices.modal_reminder_title') }}
                                </h3>
                                <p class="text-[12.5px] text-black/60 dark:text-white/60">
                                    Faktur #{{ $invoice->invoice_number }} · {{ $invoice->customer?->name }}
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="showReminderModal = false"
                            class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') ?? 'Close' }}">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form action="{{ route('invoices.remind', $invoice->id) }}" method="POST">
                        @csrf
                        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                            <!-- Bento Info Tagihan -->
                            <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 space-y-2">
                                <div class="flex justify-between items-center text-[13px]">
                                    <span class="text-black/60 dark:text-white/60">{{ __('sales.invoices.unpaid_balance_label') }}</span>
                                    <span class="font-bold text-[#FF3B30] font-mono text-[15px]">
                                        {{ $business->currency_symbol }} {{ number_format((float) $invoice->balance_due, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-[12.5px]">
                                    <span class="text-black/60 dark:text-white/60">{{ __('sales.invoices.due_date_label') }}:</span>
                                    <span class="font-semibold text-black dark:text-white">
                                        {{ $invoice->due_date?->translatedFormat('d F Y') ?? '-' }}
                                    </span>
                                </div>
                                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex flex-wrap gap-y-1 justify-between text-[11.5px] text-black/50 dark:text-white/50">
                                    <span>WhatsApp: <strong class="text-black/80 dark:text-white/80 font-mono">{{ $invoice->customer?->phone ?: __('sales.invoices.none_label') }}</strong></span>
                                    <span>Email: <strong class="text-black/80 dark:text-white/80">{{ $invoice->customer?->email ?: __('sales.invoices.none_label') }}</strong></span>
                                </div>
                            </div>

                            <!-- Saluran Notifikasi -->
                            <div class="space-y-2">
                                <label class="block text-[13px] font-semibold text-black dark:text-white">
                                    {{ __('sales.invoices.notification_channel_label') }}
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <label class="flex items-center gap-2.5 p-3 rounded-[12px] border border-black/10 dark:border-white/10 hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer text-[12.5px] font-medium transition"
                                        :class="reminderChannel === 'both' ? 'border-[#007AFF] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 text-[#007AFF]' : 'text-black/80 dark:text-white/80'">
                                        <input type="radio" name="channel" value="both" x-model="reminderChannel" class="hidden">
                                        <i data-lucide="send" class="w-4 h-4 shrink-0"></i>
                                        <span>{{ __('sales.invoices.channel_both') }}</span>
                                    </label>

                                    <label class="flex items-center gap-2.5 p-3 rounded-[12px] border border-black/10 dark:border-white/10 hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer text-[12.5px] font-medium transition"
                                        :class="reminderChannel === 'whatsapp' ? 'border-[#25D366] bg-[#25D366]/5 dark:bg-[#25D366]/10 text-[#25D366]' : 'text-black/80 dark:text-white/80'">
                                        <input type="radio" name="channel" value="whatsapp" x-model="reminderChannel" class="hidden">
                                        <i data-lucide="message-circle" class="w-4 h-4 shrink-0"></i>
                                        <span>{{ __('sales.invoices.channel_wa_only') }}</span>
                                    </label>

                                    <label class="flex items-center gap-2.5 p-3 rounded-[12px] border border-black/10 dark:border-white/10 hover:bg-black/[0.02] dark:hover:bg-white/[0.02] cursor-pointer text-[12.5px] font-medium transition"
                                        :class="reminderChannel === 'email' ? 'border-[#007AFF] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 text-[#007AFF]' : 'text-black/80 dark:text-white/80'">
                                        <input type="radio" name="channel" value="email" x-model="reminderChannel" class="hidden">
                                        <i data-lucide="mail" class="w-4 h-4 shrink-0"></i>
                                        <span>{{ __('sales.invoices.channel_email_only') }}</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Catatan Tambahan -->
                            <div class="space-y-1.5">
                                <label class="block text-[13px] font-semibold text-black dark:text-white">
                                    {{ __('sales.invoices.custom_notes_label') }}
                                </label>
                                <textarea name="custom_notes" rows="2"
                                    placeholder="{{ __('sales.invoices.custom_notes_placeholder') }}"
                                    class="w-full px-3.5 py-2.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-1 focus:ring-[#007AFF] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 transition outline-none resize-none"></textarea>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-6 py-4 border-t border-black/[0.06] dark:border-white/[0.08] bg-black/[0.01] dark:bg-white/[0.02] flex items-center justify-end gap-2.5">
                            <button type="button" @click="showReminderModal = false"
                                class="min-h-[44px] px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition cursor-pointer">
                                {{ __('sales.invoices.btn_cancel') }}
                            </button>
                            <button type="submit"
                                class="min-h-[44px] px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-md shadow-[#007AFF]/25 active:scale-[0.97] transition flex items-center justify-center gap-2 cursor-pointer">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                <span>{{ __('sales.invoices.btn_send_now') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

</div>
@endsection
