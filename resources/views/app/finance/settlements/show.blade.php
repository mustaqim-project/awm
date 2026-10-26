@extends('layouts.app', ['title' => __('finance.settlement_detail_page_title', ['number' => $settlement->settlement_number])])

@section('content')
<div x-data="{ showProofModal: false }" class="max-w-[1000px] mx-auto space-y-6 pb-16">

    {{-- ========================================================== --}}
    {{-- TOOLBAR / PAGE HEADER                                      --}}
    {{-- ========================================================== --}}
    <header class="rounded-[20px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 px-5 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('finance.settlements.index') }}" class="hover:text-black dark:hover:text-white transition">{{ __('finance.breadcrumb_gateway_recon') }}</a>
                <i data-lucide="chevron-right" class="w-3 h-3 opacity-40"></i>
                <span class="text-black dark:text-white font-medium">{{ __('finance.breadcrumb_settlement_detail') }}</span>
            </nav>
            <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight flex flex-wrap items-center gap-2.5">
                <span>Settlement #{{ $settlement->settlement_number }}</span>
                @if($settlement->isCompleted())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] inline-flex items-center gap-1">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span>{{ __('finance.status_transferred_completed') }}</span>
                    </span>
                @elseif($settlement->isPending())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FF9500]/15 text-[#D97706] dark:text-[#F59E0B] inline-flex items-center gap-1">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        <span>{{ __('finance.status_waiting_admin_transfer') }}</span>
                    </span>
                @elseif($settlement->isRejected())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FF3B30]/15 text-[#FF3B30] inline-flex items-center gap-1">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        <span>{{ __('finance.status_rejected') }}</span>
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#007AFF]/15 text-[#007AFF]">
                        {{ ucfirst($settlement->status) }}
                    </span>
                @endif
            </h1>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                {{ __('finance.submitted_on', ['date' => \Carbon\Carbon::parse($settlement->settlement_date)->translatedFormat('d F Y')]) }}
                @if($settlement->reconciledBy)
                    {{ __('finance.by_user', ['name' => $settlement->reconciledBy->name]) }}
                @endif
            </p>
        </div>
        <div>
            <a href="{{ route('finance.settlements.index') }}"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition flex items-center justify-center gap-1.5 active:scale-[0.98]">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>{{ __('common.back_to_list') }}</span>
            </a>
        </div>
    </header>

    {{-- ========================================================== --}}
    {{-- STATUS SPECIFIC BENTO CARD (BUKTI BAYAR / PENDING / REJECT) --}}
    {{-- ========================================================== --}}
    @if($settlement->isCompleted() && $settlement->proof_image_path)
        {{-- BUKTI TRANSFER ADMIN TERSEDIA --}}
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-[#34C759]/25 p-5 sm:p-6 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[14px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center font-bold">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight">{{ __('finance.proof_transfer_title') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('finance.proof_transfer_subtitle') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="showProofModal = true"
                        class="min-h-[44px] sm:min-h-0 h-10 sm:h-9 px-3.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12.5px] font-semibold flex items-center gap-1.5 transition active:scale-[0.98] shadow-xs">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                        <span>{{ __('finance.btn_view_photo_proof') }}</span>
                    </button>
                    <a href="{{ $settlement->proof_image_url }}" download="bukti-transfer-{{ $settlement->settlement_number }}.jpg" target="_blank"
                        class="min-h-[44px] sm:min-h-0 h-10 sm:h-9 px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] text-black dark:text-white text-[12.5px] font-semibold flex items-center gap-1.5 transition active:scale-[0.98]">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>{{ __('finance.btn_download_receipt') }}</span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-start">
                {{-- Preview Thumbnail --}}
                <div class="md:col-span-4 cursor-pointer group" @click="showProofModal = true">
                    <div class="relative rounded-[16px] overflow-hidden border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] aspect-4/3 flex items-center justify-center">
                        <img src="{{ $settlement->proof_image_url }}" alt="Bukti Transfer Settlement" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold gap-1.5">
                            <i data-lucide="maximize-2" class="w-4 h-4"></i>
                            <span>{{ __('finance.click_to_enlarge') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Detail Transfer Admin --}}
                <div class="md:col-span-8 space-y-3 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                        <div>
                            <span class="text-black/45 dark:text-white/45 block text-[11px] font-medium uppercase tracking-wider">{{ __('finance.transfer_time') }}</span>
                            <span class="text-[13px] font-bold text-black dark:text-white tabular-nums block mt-0.5">
                                @datetime($settlement->transferred_at, 'd F Y, H:i')
                            </span>
                        </div>
                        <div>
                            <span class="text-black/45 dark:text-white/45 block text-[11px] font-medium uppercase tracking-wider">{{ __('finance.verified_and_sent_by') }}</span>
                            <span class="text-[13px] font-bold text-black dark:text-white block mt-0.5">
                                {{ $settlement->admin?->name ?? 'Admin COOCA Platform' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-black/45 dark:text-white/45 block text-[11px] font-medium uppercase tracking-wider">{{ __('finance.store_destination_account') }}</span>
                            <span class="text-[13px] font-bold text-black dark:text-white block mt-0.5">
                                {{ $settlement->destination_bank ?? __('finance.store_bank_account') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-black/45 dark:text-white/45 block text-[11px] font-medium uppercase tracking-wider">{{ __('finance.clean_disbursed_nominal') }}</span>
                            <span class="text-[14px] font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums block mt-0.5">
                                {{ $business->currency_symbol }} {{ number_format($settlement->net_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    @if($settlement->admin_notes)
                        <div class="p-3.5 rounded-[14px] bg-[#34C759]/5 border border-[#34C759]/15 text-xs text-[#248A3D] dark:text-[#30D158]">
                            <span class="font-bold">{{ __('finance.admin_notes_prefix') }}:</span>
                            <span class="ml-1">{{ $settlement->admin_notes }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @elseif($settlement->isPending())
        {{-- PENDING BANNER --}}
        <div class="rounded-[20px] bg-[#FF9500]/5 border border-[#FF9500]/20 p-5 sm:p-6 space-y-3">
            <div class="flex items-start gap-3.5">
                <div class="w-9 h-9 rounded-[12px] bg-[#FF9500]/15 text-[#D97706] dark:text-[#F59E0B] flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('finance.waiting_transfer_title') }}</h3>
                    <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                        {{ __('finance.waiting_transfer_desc', ['amount' => $business->currency_symbol . ' ' . number_format($settlement->net_amount, 0, ',', '.'), 'account' => $settlement->destination_bank ?? __('finance.bank_merchant')]) }}
                    </p>
                </div>
            </div>
        </div>
    @elseif($settlement->isRejected())
        {{-- REJECTED BANNER --}}
        <div class="rounded-[20px] bg-[#FF3B30]/5 border border-[#FF3B30]/20 p-5 sm:p-6 space-y-3">
            <div class="flex items-start gap-3.5">
                <div class="w-9 h-9 rounded-[12px] bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-[15px] font-bold text-[#FF3B30]">{{ __('finance.payout_rejected_title') }}</h3>
                    <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                        {{ __('finance.rejection_reason_prefix') }}: <strong class="text-black dark:text-white">{{ $settlement->rejection_reason ?: __('finance.default_rejection_reason') }}</strong>
                    </p>
                    <p class="text-[11.5px] text-black/50 dark:text-white/50">
                        {{ __('finance.rejected_funds_restored_notice') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================== --}}
    {{-- SETTLEMENT SUMMARY BENTO CARDS                             --}}
    {{-- ========================================================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-1 shadow-xs">
            <span class="text-xs text-black/50 dark:text-white/50 block font-medium">{{ __('finance.kpi_total_gross') }}</span>
            <span class="text-xl font-extrabold text-black dark:text-white tabular-nums">
                {{ $business->currency_symbol }} {{ number_format($settlement->gross_amount, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-black/45 dark:text-white/45 block">{{ __('finance.escrow_account_clearing') }}</span>
        </div>

        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-1 shadow-xs">
            <span class="text-xs text-black/50 dark:text-white/50 block font-medium">{{ __('finance.kpi_mdr_fee') }}</span>
            <span class="text-xl font-extrabold text-[#FF3B30] tabular-nums">
                {{ $business->currency_symbol }} {{ number_format($settlement->fee_amount, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-black/45 dark:text-white/45 block">{{ __('finance.mdr_expense_account') }}</span>
        </div>

        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 space-y-1 shadow-xs">
            <span class="text-xs text-black/50 dark:text-white/50 block font-medium">{{ __('finance.kpi_net_received') }}</span>
            <span class="text-xl font-extrabold text-[#34C759] dark:text-[#30D158] tabular-nums">
                {{ $business->currency_symbol }} {{ number_format($settlement->net_amount, 0, ',', '.') }}
            </span>
            <span class="text-[11px] text-black/45 dark:text-white/45 block">{{ $settlement->destination_bank ?? __('finance.store_bank_account') }}</span>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- ALLOCATIONS BREAKDOWN TABLE                                --}}
    {{-- ========================================================== --}}
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
        <div class="px-5 sm:px-6 py-4 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.01]">
            <h2 class="text-[15px] font-bold text-black dark:text-white">{{ __('finance.allocated_transactions_title', ['count' => $settlement->allocations->count()]) }}</h2>
            <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">{{ __('finance.allocated_transactions_subtitle') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold">
                    <tr>
                        <th class="py-3 px-5">{{ __('finance.col_payment_type') }}</th>
                        <th class="py-3 px-5">{{ __('finance.col_payment_ref_id') }}</th>
                        <th class="py-3 px-5 text-right">{{ __('finance.col_allocated_amount') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-black/80 dark:text-white/80">
                    @foreach($settlement->allocations as $allocation)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                            <td class="py-3 px-5">
                                <span class="px-2 py-0.5 rounded-md font-semibold text-[10px]"
                                    :class="'{{ $allocation->payment_type }}' === 'commerce_order' ? 'bg-[#AF52DE]/10 text-[#AF52DE]' : 'bg-[#007AFF]/10 text-[#007AFF]'">
                                    {{ $allocation->payment_type === 'commerce_order' ? __('finance.type_online_store') : ($allocation->payment_type === 'pos_order_payment' ? __('finance.type_pos_qr') : __('finance.type_invoice')) }}
                                </span>
                            </td>
                            <td class="py-3 px-5 font-mono text-[11px] text-black/60 dark:text-white/60">
                                {{ $allocation->payment_id }}
                            </td>
                            <td class="py-3 px-5 text-right font-bold tabular-nums text-black dark:text-white">
                                {{ $business->currency_symbol }} {{ number_format($allocation->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($settlement->notes)
            <div class="p-4 border-t border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.01] text-xs">
                <span class="font-semibold text-black dark:text-white">{{ __('finance.merchant_notes_prefix') }}:</span>
                <span class="text-black/60 dark:text-white/60 ml-1">{{ $settlement->notes }}</span>
            </div>
        @endif
    </div>

    {{-- ========================================================== --}}
    {{-- MODAL PREVIEW BUKTI TRANSFER (APPLE HIG MODAL)             --}}
    {{-- ========================================================== --}}
    @if($settlement->proof_image_url)
        <template x-teleport="body">
            <div x-show="showProofModal"
                x-cloak
                class="fixed inset-0 z-[200] overflow-y-auto"
                @keydown.escape.window="showProofModal = false"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-proof-show-title">

                {{-- Backdrop --}}
                <div x-show="showProofModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                    @click="showProofModal = false"></div>

                {{-- Dialog Container --}}
                <div class="relative z-10 min-h-full flex items-center justify-center p-4 sm:p-6" @click.self="showProofModal = false">
                    <div class="relative bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[24px] max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] flex flex-col"
                        @click.stop
                        x-show="showProofModal"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                            <div>
                                <h4 id="modal-proof-show-title" class="text-base font-bold text-black dark:text-white">{{ __('finance.modal_proof_title') }}</h4>
                                <p class="text-xs text-black/50 dark:text-white/50">Settlement #{{ $settlement->settlement_number }}</p>
                            </div>
                            <button @click="showProofModal = false"
                                class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:bg-black/10 transition cursor-pointer"
                                aria-label="{{ __('common.close_modal') }}">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <div class="flex-1 overflow-auto flex items-center justify-center bg-black/[0.03] dark:bg-white/[0.03] rounded-[16px] p-2">
                            <img src="{{ $settlement->proof_image_url }}" alt="Bukti Transfer" class="max-h-[65vh] w-auto object-contain rounded-[12px] shadow-sm">
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-xs text-black/50 dark:text-white/50">{{ __('finance.transferred_at_prefix') }}: {{ $settlement->transferred_at?->translatedFormat('d M Y, H:i') }} WIB</span>
                            <div class="flex gap-2">
                                <a href="{{ $settlement->proof_image_url }}" download="bukti-transfer-{{ $settlement->settlement_number }}.jpg" target="_blank"
                                    class="h-9 px-4 rounded-[12px] bg-[#007AFF] text-white text-xs font-semibold flex items-center gap-1.5 hover:bg-[#0071E3] transition">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('finance.btn_download_file') }}</span>
                                </a>
                                <button type="button" @click="showProofModal = false" class="h-9 px-4 rounded-[12px] bg-black/5 dark:bg-white/10 text-black dark:text-white text-xs font-semibold hover:bg-black/10 transition">
                                    {{ __('common.close') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    @endif

</div>
@endsection
