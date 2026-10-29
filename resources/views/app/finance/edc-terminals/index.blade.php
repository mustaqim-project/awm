@extends('layouts.app', ['title' => __('finance.edc_terminals_page_title')])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="edcTerminalApp()">

    {{-- TOOLBAR / PAGE HEADER --}}
    <x-module-header
        title="{{ __('finance.edc_terminals_page_title') }}"
        subtitle="{{ __('finance.edc_terminals_subtitle') }}">
        <button type="button" @click="openAddModal()"
            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>{{ __('finance.btn_add_edc') }}</span>
        </button>
        <a href="{{ route('finance.cash-bank.index') }}"
            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>{{ __('finance.btn_back_to_cash_bank') }}</span>
        </a>
    </x-module-header>

    {{-- MODULE TABS (SSOT) --}}
    <x-module-tabs module="finance" />

    {{-- STATS & FILTER BENTO BAR --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-medium text-black/50 dark:text-white/50">{{ __('finance.kpi_total_edc') }}</p>
                <p class="text-lg font-bold text-black dark:text-white tabular-nums">{{ $terminals->count() }} {{ __('common.unit') }}</p>
            </div>
        </div>

        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-medium text-black/50 dark:text-white/50">{{ __('finance.kpi_active_edc_pos') }}</p>
                <p class="text-lg font-bold text-[#34C759] tabular-nums">{{ $terminals->where('is_active', true)->count() }} {{ __('common.unit') }}</p>
            </div>
        </div>

        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center shrink-0">
                    <i data-lucide="map-pin" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-[11px] font-medium text-black/50 dark:text-white/50">{{ __('finance.filter_branch') }}</p>
                    <form method="GET" action="{{ route('finance.edc-terminals.index') }}" id="branchFilterForm">
                        <select name="location_id" onchange="document.getElementById('branchFilterForm').submit()"
                            class="text-xs font-bold text-black dark:text-white bg-transparent border-0 p-0 focus:ring-0 cursor-pointer">
                            <option value="">{{ __('common.all_branches') }}</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
            <i data-lucide="chevron-down" class="w-4 h-4 text-black/40 dark:text-white/40"></i>
        </div>
    </div>

    {{-- TERMINALS GRID BENTO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($terminals as $term)
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border {{ $term->is_active ? 'border-black/10 dark:border-white/10' : 'border-black/5 dark:border-white/5 opacity-60' }} p-5 shadow-sm flex flex-col justify-between space-y-4 transition-all">
                <div class="space-y-3">
                    {{-- Header Card --}}
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 flex items-center justify-center font-bold text-xs text-black dark:text-white font-mono shrink-0">
                                {{ strtoupper(substr($term->bank_name, 0, 4)) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-black dark:text-white leading-tight">{{ $term->terminal_name }}</h3>
                                <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">{{ $term->bank_name }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $term->is_active ? 'bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50' }}">
                            {{ $term->is_active ? __('finance.active_in_pos') : __('finance.inactive') }}
                        </span>
                    </div>

                    {{-- Terminal Identifiers Bento Box --}}
                    <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-black/50 dark:text-white/50">Terminal ID (TID):</span>
                            <span class="font-mono font-bold text-black dark:text-white tabular-nums">{{ $term->terminal_id_tid }}</span>
                        </div>
                        @if($term->merchant_id_mid)
                            <div class="flex items-center justify-between text-xs border-t border-black/5 dark:border-white/5 pt-1.5">
                                <span class="text-black/50 dark:text-white/50">Merchant ID (MID):</span>
                                <span class="font-mono text-black/80 dark:text-white/80 text-[11px] tabular-nums">{{ $term->merchant_id_mid }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between text-xs border-t border-black/5 dark:border-white/5 pt-1.5">
                            <span class="text-black/50 dark:text-white/50">{{ __('finance.branch_placement') }}:</span>
                            <span class="font-medium text-[#007AFF] text-[11px]">{{ $term->location ? $term->location->name : __('finance.all_branches_hq') }}</span>
                        </div>
                    </div>

                    {{-- MDR Rates & Settlement Target --}}
                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        <div class="p-2 rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                            <span class="text-black/45 dark:text-white/45 block">{{ __('finance.mdr_debit') }}:</span>
                            <span class="font-bold text-black dark:text-white tabular-nums">{{ number_format((float) $term->mdr_debit_percent, 2) }}%</span>
                        </div>
                        <div class="p-2 rounded-[8px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                            <span class="text-black/45 dark:text-white/45 block">{{ __('finance.mdr_credit') }}:</span>
                            <span class="font-bold text-black dark:text-white tabular-nums">{{ number_format((float) $term->mdr_credit_percent, 2) }}%</span>
                        </div>
                    </div>

                    @if($term->settlement_account_info)
                        <div class="text-[11px] text-black/60 dark:text-white/60 bg-black/[0.02] dark:bg-white/[0.03] p-2 rounded-[8px] border border-black/5 dark:border-white/5">
                            <span class="text-black/40 dark:text-white/40 block text-[10px]">{{ __('finance.settlement_account_info_label') }}:</span>
                            <span class="font-medium truncate block">{{ $term->settlement_account_info }}</span>
                        </div>
                    @endif
                </div>

                @if (\App\Support\Context::hasPermission('finance.cash_bank'))
                {{-- Action Bar --}}
                <div class="flex items-center justify-between pt-2 border-t border-black/5 dark:border-white/10 text-xs">
                    <form action="{{ route('finance.edc-terminals.toggle', $term->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="min-h-[36px] sm:min-h-[38px] px-3 rounded-[10px] text-[12px] font-medium {{ $term->is_active ? 'text-[#FF9500] hover:bg-[#FF9500]/10' : 'text-[#34C759] hover:bg-[#34C759]/10' }} transition cursor-pointer">
                            {{ $term->is_active ? __('finance.btn_deactivate') : __('finance.btn_activate_terminal') }}
                        </button>
                    </form>

                    <form action="{{ route('finance.edc-terminals.destroy', $term->id) }}" method="POST"
                        onsubmit="return confirm('{{ __('finance.confirm_delete_edc') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="min-w-[36px] min-h-[36px] sm:min-w-[40px] sm:min-h-[40px] rounded-[10px] text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center justify-center transition cursor-pointer">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
                @endif
            </div>
        @empty
            <div class="col-span-full py-16 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-8 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
                    <i data-lucide="credit-card" class="w-6 h-6"></i>
                </div>
                <h3 class="font-bold text-base text-black dark:text-white">{{ __('finance.empty_edc_title') }}</h3>
                <p class="text-xs text-black/50 dark:text-white/50 max-w-md mx-auto">
                    {{ __('finance.empty_edc_desc') }}
                </p>
                <button type="button" @click="openAddModal()"
                    class="h-10 px-5 rounded-[12px] text-xs font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition inline-flex items-center gap-2 shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('finance.btn_add_edc_now') }}</span>
                </button>
            </div>
        @endforelse
    </div>

    {{-- MODAL TAMBAH MESIN EDC (BENTO SHEET XXL) --}}
    <template x-teleport="body">
        <div x-show="showModal"
            x-cloak
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showModal = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-edc-title">

            {{-- Backdrop --}}
            <div x-show="showModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showModal = false"></div>

            {{-- Dialog Container --}}
            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showModal = false">
                <div class="relative w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden"
                    @click.stop
                    x-show="showModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <div class="px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.02]">
                        <div>
                            <h2 id="modal-edc-title" class="text-[16px] font-bold text-black dark:text-white">{{ __('finance.modal_add_edc_title') }}</h2>
                            <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">{{ __('finance.modal_add_edc_subtitle') }}</p>
                        </div>
                        <button type="button" @click="showModal = false"
                            class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full hover:bg-black/5 dark:hover:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 transition cursor-pointer"
                            aria-label="Tutup modal">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form action="{{ route('finance.edc-terminals.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('finance.field_bank_issuer') }}</label>
                        <select name="bank_name" required
                            class="w-full h-11 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">{{ __('finance.select_bank_edc') }}</option>
                            <option value="BCA">Bank Central Asia (BCA)</option>
                            <option value="Mandiri">Bank Mandiri</option>
                            <option value="BRI">Bank Rakyat Indonesia (BRI)</option>
                            <option value="BNI">Bank Negara Indonesia (BNI)</option>
                            <option value="CIMB Niaga">CIMB Niaga</option>
                            <option value="Bank Permata">Bank Permata</option>
                            <option value="Bank Danamon">Bank Danamon</option>
                            <option value="BSI">Bank Syariah Indonesia (BSI)</option>
                            <option value="Lainnya">Bank / Vendor Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('finance.field_terminal_name') }}</label>
                        <input type="text" name="terminal_name" required placeholder="{{ __('finance.field_terminal_name_placeholder') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('finance.field_tid') }}</label>
                        <input type="text" name="terminal_id_tid" required placeholder="{{ __('finance.field_tid_placeholder') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-mono font-bold text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('finance.field_mid') }}</label>
                        <input type="text" name="merchant_id_mid" placeholder="{{ __('finance.field_mid_placeholder') }}"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-mono font-medium text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('finance.field_branch_placement') }}</label>
                        <select name="location_id"
                            class="w-full h-11 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">{{ __('finance.all_branches_global') }}</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('finance.mdr_debit') }} (%)</label>
                        <input type="number" step="0.01" min="0" max="10" name="mdr_debit_percent" value="0.15"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-mono font-medium text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('finance.mdr_credit') }} (%)</label>
                        <input type="number" step="0.01" min="0" max="10" name="mdr_credit_percent" value="1.50"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-mono font-medium text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('finance.settlement_account_info_label') }} ({{ __('common.optional') ?? 'Opsional' }})</label>
                    <input type="text" name="settlement_account_info" placeholder="{{ __('finance.field_settlement_account_placeholder') }}"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div class="pt-2 flex items-center justify-between border-t border-black/5 dark:border-white/10">
                    <label class="flex items-center gap-2 text-xs text-black/70 dark:text-white/70 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-black/20 text-[#007AFF] focus:ring-0">
                        <span>{{ __('finance.checkbox_activate_edc') }}</span>
                    </label>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="showModal = false"
                            class="h-10 px-4 rounded-[12px] text-xs font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 transition">
                            {{ __('common.cancel') }}
                        </button>
                        <button type="submit"
                            class="h-10 px-5 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition shadow-sm flex items-center gap-1.5">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>{{ __('finance.btn_save_edc') }}</span>
                        </button>
                    </div>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
    function edcTerminalApp() {
        return {
            showModal: false,
            openAddModal() {
                this.showModal = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        };
    }
</script>
@endsection
