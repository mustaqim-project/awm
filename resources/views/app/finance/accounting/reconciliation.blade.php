@extends('layouts.app', ['title' => __('finance.bank_recon_page_title')])

@section('content')
    <div x-data="{ uploadModalOpen: false }" class="max-w-[1360px] mx-auto space-y-5 pb-28 lg:pb-12">

        {{-- ========================================================== --}}
        {{-- TOOLBAR / PAGE HEADER                                      --}}
        {{-- ========================================================== --}}
        <x-module-header
            title="{{ __('finance.bank_recon_page_title') }}"
            subtitle="{{ __('finance.bank_recon_subtitle') }}">
            <a href="{{ route('finance.cash-bank.index') }}"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5">
                <i data-lucide="landmark" class="w-4 h-4 text-[#007AFF]"></i>
                <span>{{ __('finance.cash_bank_page_title') }}</span>
            </a>
            @if (\App\Support\Context::hasPermission('accounting.manage'))
                <button type="button" @click="uploadModalOpen = true"
                    class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.35)]">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    <span>{{ __('finance.btn_upload_statement') }}</span>
                </button>
            @endif
        </x-module-header>

        {{-- ========================================================== --}}
        {{-- MODULE TABS (SSOT)                                         --}}
        {{-- ========================================================== --}}
        <x-module-tabs module="accounting" />

        {{-- ========================================================== --}}
        {{-- SUMMARY & PROGRESS CARD (BENTO)                            --}}
        {{-- ========================================================== --}}
        @if($selectedStatement)
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3.5">
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 shadow-xs">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">{{ __('finance.kpi_bank_account') }}</p>
                    <p class="text-[16px] font-bold text-black dark:text-white mt-1 truncate">{{ $selectedStatement->cashAccount->name }}</p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ $selectedStatement->filename }}</p>
                </div>

                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 shadow-xs">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">{{ __('finance.kpi_total_statement_lines') }}</p>
                    <p class="text-[20px] font-bold tabular-nums text-black dark:text-white mt-1">{{ $selectedStatement->total_lines }}</p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ __('finance.statement_transactions') }}</p>
                </div>

                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 shadow-xs">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">{{ __('finance.kpi_reconciled') }}</p>
                    <p class="text-[20px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] mt-1">{{ $selectedStatement->reconciled_lines }}</p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 mt-0.5">{{ __('finance.from_total_lines', ['total' => $selectedStatement->total_lines]) }}</p>
                </div>

                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4.5 shadow-xs">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">{{ __('finance.kpi_progress') }}</p>
                    <div class="mt-1 flex items-center justify-between">
                        <span class="text-[20px] font-bold tabular-nums text-[#007AFF] dark:text-[#0A84FF]">
                            {{ $selectedStatement->getProgressPercentage() }}%
                        </span>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $selectedStatement->status === 'completed' ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF9500]/12 text-[#C97800] dark:text-[#FF9F0A]' }}">
                            {{ $selectedStatement->status === 'completed' ? __('finance.status_completed') : __('finance.status_in_progress') }}
                        </span>
                    </div>
                    <div class="w-full bg-black/5 dark:bg-white/10 rounded-full h-1.5 mt-2 overflow-hidden">
                        <div class="bg-[#007AFF] h-1.5 rounded-full transition-all duration-300" style="width: {{ $selectedStatement->getProgressPercentage() }}%"></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ========================================================== --}}
        {{-- MAIN INTERFACE: STATEMENTS LIST & LINES MATCHER            --}}
        {{-- ========================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">

            {{-- Sidebar Statement History --}}
            <div class="lg:col-span-1 space-y-3">
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 space-y-3 shadow-xs">
                    <h3 class="text-[12px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('finance.import_history_title') }}</h3>

                    <div class="space-y-1.5">
                        @forelse($statements as $st)
                            <a href="{{ route('finance.reconciliations.index', ['statement_id' => $st->id]) }}"
                                class="block p-3 rounded-[12px] text-[12px] transition-all {{ $selectedStatement && $selectedStatement->id === $st->id ? 'bg-[#007AFF]/10 border border-[#007AFF]/25 text-[#007AFF]' : 'hover:bg-black/[0.04] dark:hover:bg-white/[0.06] text-black/70 dark:text-white/70 border border-transparent' }}">
                                <div class="font-semibold text-[13px] text-black dark:text-white truncate">{{ $st->filename }}</div>
                                <div class="flex items-center justify-between text-[11px] text-black/50 dark:text-white/50 mt-1">
                                    <span>{{ $st->statement_date->translatedFormat('d M Y') }}</span>
                                    <span class="tabular-nums font-medium">{{ $st->reconciled_lines }}/{{ $st->total_lines }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="py-6 text-center text-[12px] text-black/40 dark:text-white/40">
                                {{ __('finance.empty_statements') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Main Statement Lines Table --}}
            <div class="lg:col-span-3 space-y-3">
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-xs">
                    <div class="p-4 sm:px-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.01]">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#34C759]"></span>
                            <h2 class="text-[14px] font-bold text-black dark:text-white">{{ __('finance.matcher_sheet_title') }}</h2>
                        </div>
                        <span class="text-[12px] text-black/40 dark:text-white/40">{{ __('finance.date_tolerance') }}</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                                    <th class="px-4 sm:px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">{{ __('finance.col_date') }}</th>
                                    <th class="px-4 sm:px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">{{ __('finance.col_bank_description') }}</th>
                                    <th class="px-4 sm:px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 text-right">{{ __('finance.col_nominal') }}</th>
                                    <th class="px-4 sm:px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45">{{ __('finance.col_status_system_notes') }}</th>
                                    <th class="px-4 sm:px-5 py-3.5 text-[11px] font-semibold uppercase tracking-wider text-black/45 dark:text-white/45 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                @forelse($lines as $line)
                                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors group">
                                        <td class="px-4 sm:px-5 py-3.5 tabular-nums text-[13px] sm:text-[13.5px] font-medium text-black/75 dark:text-white/75">
                                            {{ $line->transaction_date->translatedFormat('d M Y') }}
                                        </td>
                                        <td class="px-4 sm:px-5 py-3.5 text-[13.5px] sm:text-[14px] font-medium text-black dark:text-white">
                                            {{ $line->description }}
                                            @if($line->reference_number)
                                                <span class="block text-[12px] font-mono text-black/45 dark:text-white/45 mt-0.5">Ref: {{ $line->reference_number }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 sm:px-5 py-3.5 text-right tabular-nums">
                                            <span class="text-[14px] sm:text-[15px] font-bold font-mono tracking-tight {{ $line->type === 'credit' ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black dark:text-white' }}">
                                                {{ $line->type === 'credit' ? '+' : '-' }}{{ number_format($line->amount, 0, ',', '.') }}
                                            </span>
                                            <span class="block text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">{{ $line->type }}</span>
                                        </td>
                                        <td class="px-4 sm:px-5 py-3.5">
                                            @if($line->status === 'reconciled')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                    {{ __('finance.status_reconciled') }}
                                                </span>
                                            @elseif($line->status === 'matched')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-[#007AFF]/12 text-[#007AFF]">
                                                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                                    {{ __('finance.status_auto_matched') }}
                                                </span>
                                                <span class="block text-[12px] text-black/55 dark:text-white/55 mt-1">{{ $line->notes }}</span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[12px] font-semibold bg-black/[0.06] dark:bg-white/[0.08] text-black/55 dark:text-white/55">
                                                    {{ __('finance.status_unmatched') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 sm:px-5 py-3.5 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                            <div class="flex items-center justify-end gap-1">
                                                @if (\App\Support\Context::hasPermission('accounting.manage'))
                                                    @if($line->status === 'reconciled')
                                                        <form method="POST" action="{{ route('finance.reconciliations.unmatch', $line) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" class="min-w-[36px] min-h-[36px] sm:min-w-[40px] sm:min-h-[40px] rounded-[8px] text-black/60 dark:text-white/60 bg-black/[0.04] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center shrink-0 cursor-pointer" title="{{ __('finance.btn_unmatch') }}" aria-label="{{ __('finance.btn_unmatch') }}">
                                                                <i data-lucide="undo" class="w-4 h-4"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form method="POST" action="{{ route('finance.reconciliations.match', $line) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" class="min-w-[36px] min-h-[36px] sm:min-w-[40px] sm:min-h-[40px] rounded-[8px] bg-[#34C759]/10 text-[#34C759] hover:bg-[#34C759]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer" title="{{ __('finance.btn_match') }}" aria-label="{{ __('finance.btn_match') }}">
                                                                <i data-lucide="check" class="w-4 h-4"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-black/30 dark:text-white/30"> </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-12 text-center text-black/40 dark:text-white/40">
                                            {{ __('finance.empty_matcher_prompt') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- ========================================================== --}}
        {{-- MODAL UPLOAD BANK STATEMENT (BENTO APPLE HIG XXL SHEET)    --}}
        {{-- ========================================================== --}}
        @if (\App\Support\Context::hasPermission('accounting.manage'))
        <template x-teleport="body">
            <div x-show="uploadModalOpen" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                x-transition.opacity
                @keydown.escape.window="uploadModalOpen = false">
                <div class="w-full max-w-full sm:max-w-3xl lg:max-w-4xl bg-white dark:bg-[#1C1C1E] rounded-[24px] shadow-[0_25px_70px_rgba(0,0,0,0.35)] border border-black/10 dark:border-white/10 overflow-hidden flex flex-col max-h-[92vh]"
                    @click.outside="uploadModalOpen = false"
                    x-data="{ isSubmitting: false }">
                    <div class="flex items-center justify-between px-6 sm:px-8 py-5 border-b border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02]">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="file-up" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight">{{ __('finance.modal_upload_statement_title') }}</h2>
                                <p class="text-[12.5px] text-black/50 dark:text-white/50">{{ __('finance.modal_upload_statement_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="uploadModalOpen = false" class="w-9 h-9 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('finance.reconciliations.upload') }}" enctype="multipart/form-data" @submit="isSubmitting = true" class="flex-1 overflow-y-auto p-5 sm:p-8 space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                            {{-- Kolom Kiri: Akun Kas & Tanggal (6/12) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.field_target_account') }}</label>
                                    <select name="cash_account_id" required class="w-full h-11 px-3.5 text-[16px] sm:text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                                        @foreach($cashAccounts as $ca)
                                            <option value="{{ $ca->id }}">{{ $ca->name }} ({{ $ca->account_number ?? 'Internal' }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.field_statement_date') }}</label>
                                    <input type="date" name="statement_date" value="{{ now()->toDateString() }}" required
                                        class="w-full h-11 px-3.5 text-[16px] sm:text-[14px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                                </div>

                                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2">
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80">{{ __('finance.field_csv_file') }}</label>
                                    <input type="file" name="statement_file" accept=".csv,.txt"
                                        class="w-full text-[13px] text-black/70 dark:text-white/70 file:mr-3 file:py-2.5 file:px-4 file:rounded-[10px] file:border-0 file:text-[12px] file:font-semibold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/15 cursor-pointer">
                                    <p class="text-[11px] text-black/45 dark:text-white/45">{{ __('finance.file_column_format_hint') }}</p>
                                </div>
                            </div>

                            {{-- Kolom Kanan: Paste Manual CSV (6/12) --}}
                            <div class="lg:col-span-6 space-y-4">
                                <div>
                                    <label class="block text-[13px] font-bold text-black/80 dark:text-white/80 mb-1.5">{{ __('finance.field_paste_csv') }}</label>
                                    <textarea name="manual_entries" rows="6" placeholder="{{ __('finance.csv_paste_placeholder') }}"
                                        class="w-full p-3.5 text-[14px] sm:text-[12.5px] font-mono bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[12px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]"></textarea>
                                </div>
                                <p class="text-[11.5px] text-black/45 dark:text-white/45 leading-relaxed">
                                    {{ __('finance.auto_matcher_hint') }}
                                </p>
                            </div>
                        </div>

                        <div class="pt-4 flex flex-col-reverse sm:flex-row items-center justify-end gap-3 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="uploadModalOpen = false" :disabled="isSubmitting" class="w-full sm:w-auto min-h-[48px] px-6 rounded-[14px] text-[14px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition">{{ __('common.cancel') }}</button>
                            <button type="submit" :disabled="isSubmitting" class="w-full sm:w-auto min-h-[48px] px-8 rounded-[14px] text-[14.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-[0_4px_16px_rgba(0,122,255,0.3)] transition flex items-center justify-center gap-2">
                                <template x-if="isSubmitting">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </template>
                                <span x-text="isSubmitting ? '{{ __('finance.processing') }}' : '{{ __('finance.btn_process_match') }}'">{{ __('finance.btn_process_match') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
        @endif

    </div>
@endsection
