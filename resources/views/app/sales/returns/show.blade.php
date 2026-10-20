@extends('layouts.app', ['title' => __('sales.returns.detail_title', ['number' => $return->return_number])])

@section('content')
    <div class="max-w-[1100px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="{
        approveModalOpen: false,
        completeModalOpen: false,
    
        promptApprove() {
            this.approveModalOpen = true;
        },
        submitApprove() {
            this.approveModalOpen = false;
            document.getElementById('approve-form').submit();
        },
    
        promptComplete() {
            this.completeModalOpen = true;
        },
        submitComplete() {
            this.completeModalOpen = false;
            document.getElementById('complete-form').submit();
        },

        promptDelete() {
            this.deleteModalOpen = true;
        },
        submitDelete() {
            this.deleteModalOpen = false;
            document.getElementById('form-delete-return-show').submit();
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
                    <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.returns.breadcrumb_dashboard') }}</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <a href="{{ route('sales.returns.index') }}" class="hover:text-[#007AFF] transition-colors">{{ __('sales.returns.breadcrumb_sales_returns') }}</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                    <span class="text-black dark:text-white font-mono font-medium">{{ $return->return_number }}</span>
                </nav>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight font-mono">
                        {{ $return->return_number }}</h1>
                    @if ($return->status === 'completed')
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> {{ __('sales.returns.status_completed_restocked') }}
                        </span>
                    @elseif($return->status === 'approved')
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF]"></span> {{ __('sales.returns.status_approved_badge') }}
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> {{ __('sales.returns.status_draft_waiting') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- State-driven action triggers -->
                @if (\App\Support\Context::hasPermission('sales.returns') || \App\Support\Context::hasPermission('sales.pipeline'))
                    @if ($return->status === 'draft')
                        <!-- Edit Draft Return Button -->
                        <a href="{{ route('sales.returns.edit', $return) }}"
                            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition flex items-center gap-1.5">
                            <i data-lucide="edit-3" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                            <span>Edit Retur</span>
                        </a>

                        <button type="button" @click="promptApprove()"
                            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>{{ __('sales.returns.btn_approve_return') }}</span>
                        </button>

                        <form id="approve-form" method="POST" action="{{ route('sales.returns.approve', $return) }}"
                            class="hidden">
                            @csrf
                        </form>

                        <!-- Delete Draft Return Button -->
                        <button type="button" @click="promptDelete()"
                            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.97] transition flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="trash-2" class="w-4 h-4 text-[#FF3B30]"></i>
                            <span>Hapus Draf</span>
                        </button>

                        <form id="form-delete-return-show" action="{{ route('sales.returns.destroy', $return) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @elseif($return->status === 'approved')
                        <button type="button" @click="promptComplete()"
                            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#30B350] active:scale-[0.97] active:opacity-80 transition-all flex items-center gap-1.5 shadow-[0_1px_2px_rgba(52,199,89,0.25)]">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>{{ __('sales.returns.btn_complete_restock') }}</span>
                        </button>

                        <form id="complete-form" method="POST" action="{{ route('sales.returns.complete', $return) }}"
                            class="hidden">
                            @csrf
                        </form>
                    @endif
                @endif

                <!-- Real Server-Rendered DomPDF Download -->
                <a href="{{ route('sales.returns.pdf', $return) }}" target="_blank"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] shadow-[0_1px_2px_rgba(0,122,255,0.25)] transition flex items-center gap-1.5"
                    title="{{ __('sales.returns.btn_print_pdf_tooltip') }}">
                    <i data-lucide="printer" class="w-4 h-4 text-white"></i>
                    <span>{{ __('sales.returns.btn_print_pdf') }} (PDF)</span>
                </a>

                <!-- Back Link -->
                <a href="{{ route('sales.returns.index') }}"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                    <span>{{ __('sales.returns.btn_back') }}</span>
                </a>
            </div>
        </header>

        <!-- Formal Print-Only Header (Kop Surat & Judul Dokumen) -->
        <div class="hidden print:block pb-5 mb-5 border-b-2 border-slate-900">
            <div class="flex justify-between items-start">
                <div class="space-y-1 max-w-md">
                    @if ($business->logo_url)
                        <img src="{{ $business->logo_url }}" alt="{{ $business->name }}" class="max-h-12 max-w-[140px] object-contain mb-1">
                    @endif
                    <h1 class="text-base font-bold text-slate-950 uppercase tracking-wide leading-tight">{{ $business->legal_name ?: $business->name }}</h1>
                    <p class="text-xs text-slate-600">{{ $business->address ?? __('sales.returns.header_kop_office_address') }}</p>
                    <p class="text-xs text-slate-600">
                        @if ($business->email) Email: {{ $business->email }} @endif
                        @if ($business->phone) | Telp: {{ $business->phone }} @endif
                    </p>
                    @if ($business->tax_identification_number)
                        <p class="text-xs text-slate-600 tabular-nums">NPWP: {{ $business->tax_identification_number }}</p>
                    @endif
                </div>
                <div class="text-right space-y-0.5">
                    <div class="text-lg font-bold tracking-wider text-slate-950 uppercase">{{ __('sales.returns.header_doc_title') }}</div>
                    <div class="text-xs text-slate-500 font-semibold tracking-wide uppercase">{{ __('sales.returns.header_doc_subtitle') }}</div>
                    <div class="text-sm font-bold text-slate-800 font-mono mt-1">{{ $return->return_number }}</div>
                    <div class="text-[11px] font-semibold text-slate-600 uppercase tracking-wider mt-1">
                        {{ __('sales.invoices.print_status') }} {{ strtoupper($return->status) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Notifications (Apple Banner Style) -->
        @if (session('success'))
            <div
                class="rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/20 px-4 py-3 text-[13px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
                <div class="flex-1 font-medium">{{ session('success') }}</div>
            </div>
        @endif
        @if ($errors->any())
            <div
                class="rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 px-4 py-3 text-[13px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-3">
                <span class="w-2 h-2 rounded-full bg-[#FF3B30] shrink-0"></span>
                <div class="flex-1 font-medium">
                    @foreach ($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Workflow Progress Stepper (Apple HIG Style) -->
        <div
            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 print:hidden">
            <span class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider block mb-3">
                {{ __('sales.returns.stepper_title') }}
            </span>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- Step 1: Draft -->
                <div
                    class="rounded-[10px] p-3.5 border {{ $return->status === 'draft' ? 'bg-[#FF9500]/8 border-[#FF9500]/20' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/5 dark:border-white/5' }} flex items-start gap-3">
                    <div
                        class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ in_array($return->status, ['draft', 'approved', 'completed']) ? 'bg-[#FF9500] text-white' : 'bg-black/10 dark:bg-white/10 text-black/40' }} font-bold text-[11px]">
                        @if (in_array($return->status, ['approved', 'completed']))
                            ✓
                        @else
                            1
                        @endif
                    </div>
                    <div>
                        <h3 class="text-[13px] font-semibold text-black dark:text-white">{{ __('sales.returns.step_1_title') }}</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">{{ __('sales.returns.step_1_desc') }}</p>
                        <span
                            class="text-[10px] tabular-nums text-black/40 dark:text-white/40 block mt-1">{{ $return->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>

                <!-- Step 2: Approved -->
                <div
                    class="rounded-[10px] p-3.5 border {{ $return->status === 'approved' ? 'bg-[#007AFF]/8 border-[#007AFF]/20' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/5 dark:border-white/5' }} flex items-start gap-3">
                    <div
                        class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ in_array($return->status, ['approved', 'completed']) ? 'bg-[#007AFF] text-white' : 'bg-black/10 dark:bg-white/10 text-black/40' }} font-bold text-[11px]">
                        @if ($return->status === 'completed')
                            ✓
                        @else
                            2
                        @endif
                    </div>
                    <div>
                        <h3 class="text-[13px] font-semibold text-black dark:text-white">{{ __('sales.returns.step_2_title') }}</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">{{ __('sales.returns.step_2_desc') }}</p>
                        <span class="text-[10px] tabular-nums text-black/40 dark:text-white/40 block mt-1">
                            {{ $return->approved_at ? $return->approved_at->format('d M Y, H:i') : ($return->status === 'approved' ? __('sales.returns.step_2_verifying') : __('sales.returns.step_2_waiting')) }}
                        </span>
                    </div>
                </div>

                <!-- Step 3: Completed -->
                <div
                    class="rounded-[10px] p-3.5 border {{ $return->status === 'completed' ? 'bg-[#34C759]/8 border-[#34C759]/20' : 'bg-black/[0.02] dark:bg-white/[0.03] border-black/5 dark:border-white/5' }} flex items-start gap-3">
                    <div
                        class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ $return->status === 'completed' ? 'bg-[#34C759] text-white' : 'bg-black/10 dark:bg-white/10 text-black/40' }} font-bold text-[11px]">
                        @if ($return->status === 'completed')
                            ✓
                        @else
                            3
                        @endif
                    </div>
                    <div>
                        <h3 class="text-[13px] font-semibold text-black dark:text-white">{{ __('sales.returns.step_3_title') }}</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">{{ __('sales.returns.step_3_desc') }}</p>
                        <span class="text-[10px] tabular-nums text-black/40 dark:text-white/40 block mt-1">
                            {{ $return->completed_at ? $return->completed_at->format('d M Y, H:i') : __('sales.returns.step_3_waiting') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Grid: Origin Reference + Compensation -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Card 1: Sumber Transaksi & Pelanggan -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-2.5">
                    <h3 class="text-[13px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                        {{ __('sales.returns.card_origin_customer_title') }}
                    </h3>
                    @if ($return->invoice)
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                            {{ __('sales.returns.source_invoice_btn') }}
                        </span>
                    @elseif($return->posOrder)
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-[#5856D6]/10 text-[#5856D6]">
                            {{ __('sales.returns.source_pos_badge') }}
                        </span>
                    @else
                        <span
                            class="px-2 py-0.5 rounded text-[10px] font-semibold bg-black/5 dark:bg-white/5 text-black/50 dark:text-white/50">
                            {{ __('sales.returns.source_manual_badge') }}
                        </span>
                    @endif
                </div>

                <div class="space-y-2 text-[13px]">
                    <div
                        class="flex justify-between items-center py-1 border-b border-black/[0.04] dark:border-white/[0.04]">
                        <span class="text-black/50 dark:text-white/50">{{ __('sales.returns.label_origin_doc_number') }}</span>
                        <span class="font-semibold tabular-nums text-black dark:text-white">
                            @if ($return->invoice)
                                {{ $return->invoice->invoice_number }}
                            @elseif($return->posOrder)
                                {{ $return->posOrder->order_number }}
                            @else
                                -
                            @endif
                        </span>
                    </div>

                    <div
                        class="flex justify-between items-center py-1 border-b border-black/[0.04] dark:border-white/[0.04]">
                        <span class="text-black/50 dark:text-white/50">{{ __('sales.returns.label_customer_name') }}</span>
                        <span class="font-semibold text-black dark:text-white">
                            @if ($return->invoice)
                                {{ $return->invoice->customer?->name ?? __('sales.returns.general_customer') }}
                            @elseif($return->posOrder)
                                {{ $return->posOrder->customer?->name ?? ($return->posOrder->customer_name_guest ?: __('sales.returns.guest_customer')) }}
                            @else
                                {{ $return->customer?->name ?? __('sales.returns.general_customer') }}
                            @endif
                        </span>
                    </div>

                    @if ($return->location)
                        <div
                            class="flex justify-between items-center py-1 border-b border-black/[0.04] dark:border-white/[0.04]">
                            <span class="text-black/50 dark:text-white/50">{{ __('sales.returns.label_restock_warehouse') }}</span>
                            <span class="font-medium text-black dark:text-white">{{ $return->location->name }}</span>
                        </div>
                    @endif

                    <div class="pt-1">
                        <span class="text-black/50 dark:text-white/50 block text-[12px] mb-1">{{ __('sales.returns.label_customer_return_reason') }}</span>
                        <div
                            class="p-3 rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03] text-black/80 dark:text-white/80 italic text-[12px]">
                            &ldquo;{{ $return->reason ?: __('sales.returns.no_specific_note') }}&rdquo;
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Kompensasi & Nilai Retur -->
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/5 pb-2.5">
                    <h3 class="text-[13px] font-semibold uppercase tracking-wider text-black/50 dark:text-white/50">
                        {{ __('sales.returns.card_compensation_title') }}
                    </h3>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-[#FF3B30]/10 text-[#FF3B30]">
                        {{ __('sales.returns.badge_refund') }}
                    </span>
                </div>

                <div class="space-y-3 text-[13px]">
                    <div>
                        <span class="text-[12px] text-black/45 dark:text-white/45 block">{{ __('sales.returns.label_total_refund_comp') }}</span>
                        <div class="text-[24px] font-bold tabular-nums text-[#FF3B30] mt-0.5">
                            Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                        </div>
                    </div>

                    <div
                        class="p-3 rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="text-black/50 dark:text-white/50">{{ __('sales.returns.label_comp_method') }}</span>
                            @if (($return->refund_method ?? 'cash_refund') === 'cash_refund')
                                <span class="font-semibold text-black dark:text-white">{{ __('sales.returns.comp_cash_refund_val') }}</span>
                            @elseif(($return->refund_method ?? '') === 'credit_note')
                                <span class="font-semibold text-[#007AFF]">{{ __('sales.returns.comp_credit_note_val') }}</span>
                            @else
                                <span class="font-semibold text-[#FF9500]">{{ __('sales.returns.comp_store_credit_val') }}</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-black/45 dark:text-white/45 leading-relaxed">
                            @if (($return->refund_method ?? 'cash_refund') === 'cash_refund')
                                {{ __('sales.returns.comp_cash_desc') }}
                            @elseif(($return->refund_method ?? '') === 'credit_note')
                                {{ __('sales.returns.comp_credit_note_desc') }}
                            @else
                                {{ __('sales.returns.comp_store_credit_desc') }}
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center justify-between pt-1 text-[12px]">
                        <span class="text-black/50 dark:text-white/50">{{ __('sales.returns.label_physical_restock_status') }}</span>
                        @if ($return->status === 'completed')
                            <span class="text-[#34C759] dark:text-[#30D158] font-semibold flex items-center gap-1">
                                <span>✓</span> {{ __('sales.returns.status_restocked_success') }}
                            </span>
                        @else
                            <span class="text-[#FF9500] font-medium">{{ __('sales.returns.status_restock_waiting') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table Card -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="p-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-black dark:text-white">
                    {{ __('sales.returns.card_items_title') }}
                </h3>
                <span class="text-[12px] tabular-nums text-black/50 dark:text-white/50">
                    {{ __('sales.returns.total_item_kinds', ['count' => $return->items->count()]) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr
                            class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 text-[11px] font-semibold uppercase tracking-wide">
                            <th class="py-2.5 px-4 w-12 text-center">#</th>
                            <th class="py-2.5 px-4">{{ __('sales.returns.col_item_product') }}</th>
                            <th class="py-2.5 px-4 text-center">{{ __('sales.returns.col_return_qty') }}</th>
                            <th class="py-2.5 px-4 text-right">{{ __('sales.returns.col_unit_price_origin') }}</th>
                            <th class="py-2.5 px-4 text-right">{{ __('sales.returns.col_return_subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($return->items as $idx => $item)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="py-3 px-4 text-center tabular-nums text-black/40 dark:text-white/40">
                                    {{ $idx + 1 }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-black dark:text-white">
                                        {{ $item->item_name }}
                                    </div>
                                    @if ($item->product?->sku)
                                        <div class="text-[11px] font-mono text-black/40 dark:text-white/40">
                                            SKU: {{ $item->product->sku }}
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center tabular-nums font-semibold text-black dark:text-white">
                                    {{ number_format($item->quantity, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums text-black/60 dark:text-white/60">
                                    Rp {{ number_format($item->unit_price ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums font-semibold text-[#FF3B30]">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-black/40 dark:text-white/40 italic">
                                    {{ __('sales.returns.no_items_in_return') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t border-black/5 dark:border-white/10">
                        <tr>
                            <td colspan="4" class="py-3 px-4 text-right font-semibold text-black dark:text-white">
                                {{ __('sales.returns.grand_total_return_value') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold tabular-nums text-[#FF3B30] text-[15px]">
                                Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Print Signatures (Visible on Print Only) -->
        <div class="hidden print:grid grid-cols-3 gap-6 pt-12 text-center text-xs text-black/70">
            <div class="space-y-16">
                <p class="font-semibold">{{ __('sales.returns.sig_submitted_by_customer') }}</p>
                <div>
                    <p class="font-bold border-b border-black/30 pb-1 mx-8">
                        {{ $return->invoice?->customer?->name ?? ($return->posOrder?->customer?->name ?? ($return->customer?->name ?? __('sales.returns.label_buyer_customer_plain'))) }}
                    </p>
                    <p class="text-[10px] text-black/50 mt-1">{{ __('sales.returns.sig_customer_sign_name') }}</p>
                </div>
            </div>

            <div class="space-y-16">
                <p class="font-semibold">{{ __('sales.returns.sig_received_warehouse') }}</p>
                <div>
                    <p class="font-bold border-b border-black/30 pb-1 mx-8">( ........................................ )
                    </p>
                    <p class="text-[10px] text-black/50 mt-1">{{ __('sales.returns.sig_warehouse_checker') }}</p>
                </div>
            </div>

            <div class="space-y-16">
                <p class="font-semibold">{{ __('sales.returns.sig_approved_by') }}</p>
                <div>
                    <p class="font-bold border-b border-black/30 pb-1 mx-8">( ........................................ )
                    </p>
                    <p class="text-[10px] text-black/50 mt-1">{{ __('sales.returns.sig_authorized_signature') }}</p>
                </div>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- APPLE ALERT DIALOG: SETUJUI RETUR                     -->
        <!-- ===================================================== -->
        <template x-teleport="body">
            <div x-show="approveModalOpen" x-cloak
                class="fixed inset-0 z-[220] overflow-y-auto"
                @keydown.escape.window="approveModalOpen = false"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="sales-return-approve-dialog-title">

                <!-- Backdrop -->
                <div x-show="approveModalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

                <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="approveModalOpen = false">
                    <div class="relative w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
                        @click.away="approveModalOpen = false"
                        x-show="approveModalOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95">

                        <div class="px-4 pt-5 pb-4">
                            <p id="sales-return-approve-dialog-title" class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.returns.dialog_approve_title') }}</p>
                            <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                                {{ __('sales.returns.dialog_approve_desc') }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                            <button type="button" @click="approveModalOpen = false"
                                class="min-h-[44px] py-3 text-black/60 dark:text-white/60 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                {{ __('sales.returns.btn_cancel') }}
                            </button>
                            <button type="button" @click="submitApprove()"
                                class="min-h-[44px] py-3 text-[#007AFF] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                {{ __('sales.returns.dialog_approve_btn') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- ===================================================== -->
        <!-- APPLE ALERT DIALOG: SELESAIKAN & RESTOCK              -->
        <!-- ===================================================== -->
        <template x-teleport="body">
            <div x-show="completeModalOpen" x-cloak
                class="fixed inset-0 z-[220] overflow-y-auto"
                @keydown.escape.window="completeModalOpen = false"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="sales-return-complete-dialog-title">

                <!-- Backdrop -->
                <div x-show="completeModalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

                <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="completeModalOpen = false">
                    <div class="relative w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
                        @click.away="completeModalOpen = false"
                        x-show="completeModalOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95">

                        <div class="px-4 pt-5 pb-4">
                            <p id="sales-return-complete-dialog-title" class="text-[17px] font-semibold text-black dark:text-white">{{ __('sales.returns.dialog_complete_title') }}</p>
                            <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                                {{ __('sales.returns.dialog_complete_desc') }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                            <button type="button" @click="completeModalOpen = false"
                                class="min-h-[44px] py-3 text-black/60 dark:text-white/60 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                {{ __('sales.returns.btn_cancel') }}
                            </button>
                            <button type="button" @click="submitComplete()"
                                class="min-h-[44px] py-3 text-[#34C759] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                {{ __('sales.returns.dialog_complete_btn') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Apple HIG Confirmation Modal: Delete Draft Sales Return -->
        <template x-teleport="body">
            <div x-show="deleteModalOpen" x-cloak
                class="fixed inset-0 z-[220] overflow-y-auto"
                @keydown.escape.window="deleteModalOpen = false"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="return-delete-confirm-dialog-title">

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
                            <p id="return-delete-confirm-dialog-title" class="text-[17px] font-semibold text-black dark:text-white">Hapus Retur Penjualan?</p>
                            <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                                Apakah Anda yakin ingin menghapus draf retur <span class="font-medium text-black dark:text-white">{{ $return->return_number }}</span>? Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                            <button type="button" @click="deleteModalOpen = false"
                                class="min-h-[44px] py-3 text-black/60 dark:text-white/60 border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="button" @click="submitDelete()"
                                class="min-h-[44px] py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors cursor-pointer">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>
@endsection

@push('styles')
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 12mm 14mm 12mm 14mm;
            }
            body, html {
                background-color: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            aside, nav, #mobile-nav, #bottom-nav, .no-print, header, [role="navigation"] {
                display: none !important;
            }
            .print\:hidden {
                display: none !important;
            }
            .print\:block {
                display: block !important;
            }
            .print\:grid {
                display: grid !important;
            }
            .max-w-\[1100px\] {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .rounded-\[14px\], .rounded-\[24px\], .rounded-\[10px\] {
                border-radius: 4px !important;
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
                background-color: #ffffff !important;
            }
            table {
                width: 100% !important;
                page-break-inside: auto !important;
            }
            tr {
                page-break-inside: avoid !important;
            }
        }
    </style>
@endpush
