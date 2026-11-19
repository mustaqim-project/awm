@extends('layouts.app', ['title' => __('finance.general_ledger_page_title')])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-5 pb-28 sm:pb-32 lg:pb-10">

        {{-- ========================================================== --}}
        {{-- ZONA 1: TOOLBAR / PAGE HEADER                               --}}
        {{-- ========================================================== --}}
        <x-module-header
            title="{{ __('finance.general_ledger_page_title') }}"
            subtitle="{{ __('finance.general_ledger_subtitle') }}">
            <a href="{{ route('finance.coa.index') }}"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5 whitespace-nowrap">
                <i data-lucide="list-tree" class="w-4 h-4 shrink-0 text-[#007AFF]"></i>
                <span>{{ __('finance.btn_chart_of_accounts') }}</span>
            </a>
            @can('accounting.view')
            <a id="btnGeneralLedgerExportExcel" href="{{ route('finance.general-ledger.export-excel', array_filter(['account_id' => $accountId, 'start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()])) }}"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-semibold text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-xs whitespace-nowrap"
                title="{{ __('finance.export_general_ledger_tooltip') }}">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                <span>{{ __('common.export_excel') }}</span>
            </a>
            @endcan
            <button type="button" onclick="window.print()"
                class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-colors flex items-center justify-center gap-1.5 whitespace-nowrap cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4 shrink-0"></i>
                <span>{{ __('common.print') }}</span>
            </button>
        </x-module-header>

        {{-- ========================================================== --}}
        {{-- MODULE TABS (SSOT)                                         --}}
        {{-- ========================================================== --}}
        <x-module-tabs module="accounting" />

        {{-- ========================================================== --}}
        {{-- ZONA 1: ACCOUNT & DATE FILTER TOOLBAR                       --}}
        {{-- ========================================================== --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 shadow-xs">
            <form method="GET" action="{{ route('finance.general-ledger') }}" id="glFilterForm" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3.5">
                {{-- Account Selector --}}
                <div class="flex-1 min-w-[260px]">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 mb-1">
                        {{ __('finance.kpi_selected_account') }}
                    </label>
                    <div class="relative">
                        <x-searchable-select>
                            <select name="account_id" onchange="this.form.submit()"
                            class="sr-only">
                            @foreach($ledger['accounts'] as $acc)
                                <option value="{{ $acc->id }}" @selected($ledger['selected_account'] && $ledger['selected_account']->id === $acc->id)>
                                    {{ $acc->code }} – {{ $acc->name }} ({{ $acc->getTypeLabel() }})
                                </option>
                            @endforeach
                            </select>
                        </x-searchable-select>
                    </div>
                </div>

                {{-- Date Range Picker --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 mb-1">
                            {{ __('finance.col_date') }}
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="date" name="start_date" id="glStartDate" value="{{ $startDate->toDateString() }}"
                                class="h-11 sm:h-9.5 px-3 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] text-[15px] sm:text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <span class="text-black/30 dark:text-white/30 text-[12px] font-bold">–</span>
                            <input type="date" name="end_date" id="glEndDate" value="{{ $endDate->toDateString() }}"
                                class="h-11 sm:h-9.5 px-3 bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] text-[15px] sm:text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div class="sm:pt-5 flex items-center gap-2">
                        <button type="submit"
                            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9.5 px-5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold transition-all flex items-center justify-center gap-1.5 whitespace-nowrap cursor-pointer shadow-xs active:scale-[0.98]">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                            <span>{{ __('finance.btn_display') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ========================================================== --}}
        {{-- ZONA 2: FINANCIAL POSITION SUMMARY CARDS (ADAPTIVE BENTO)   --}}
        {{-- ========================================================== --}}
        @if($ledger['selected_account'])
            <div class="flex gap-3 overflow-x-auto snap-x snap-mandatory no-scrollbar -mx-4 px-4 pb-1 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:overflow-visible sm:mx-0 sm:px-0 sm:pb-0">
                {{-- Card 1: Selected Account --}}
                <div class="snap-start shrink-0 w-[240px] sm:w-auto rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-4.5 shadow-xs space-y-1">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">{{ __('finance.kpi_selected_account') }}</p>
                    <p class="text-[15px] sm:text-[16px] font-bold text-black dark:text-white truncate">{{ $ledger['selected_account']->name }}</p>
                    <div class="flex items-center gap-1.5 pt-0.5">
                        <span class="text-[11.5px] font-mono font-bold text-[#007AFF]">{{ $ledger['selected_account']->code }}</span>
                        <span class="inline-flex items-center uppercase px-1.5 py-0.2 rounded-full text-[9.5px] font-extrabold bg-[#007AFF]/10 text-[#007AFF]">
                            {{ strtoupper($ledger['selected_account']->normal_balance) }}
                        </span>
                    </div>
                </div>

                {{-- Card 2: Beginning Balance --}}
                <div class="snap-start shrink-0 w-[240px] sm:w-auto rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-4.5 shadow-xs space-y-1">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">{{ __('finance.kpi_beginning_balance') }}</p>
                    <p class="text-[18px] sm:text-[20px] font-bold tabular-nums font-mono text-black dark:text-white">
                        Rp {{ number_format($ledger['beginning_balance'], 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 truncate">{{ __('finance.before_date', ['date' => $startDate->translatedFormat('d M Y')]) }}</p>
                </div>

                {{-- Card 3: Period Mutation --}}
                <div class="snap-start shrink-0 w-[240px] sm:w-auto rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-4.5 shadow-xs space-y-1">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">{{ __('finance.kpi_period_mutation') }}</p>
                    <div class="flex items-center justify-between text-[13px] sm:text-[13.5px] tabular-nums font-mono pt-0.5">
                        <span class="text-[#34C759] dark:text-[#30D158] font-bold">+{{ number_format($ledger['total_debit'], 0, ',', '.') }}</span>
                        <span class="text-[#FF3B30] font-bold">-{{ number_format($ledger['total_credit'], 0, ',', '.') }}</span>
                    </div>
                    <p class="text-[11px] text-black/40 dark:text-white/40 truncate">{{ __('finance.debit_vs_credit') }}</p>
                </div>

                {{-- Card 4: Ending Balance --}}
                <div class="snap-start shrink-0 w-[240px] sm:w-auto rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-4.5 shadow-xs space-y-1">
                    <p class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">{{ __('finance.kpi_ending_balance') }}</p>
                    <p class="text-[18px] sm:text-[20px] font-bold tabular-nums font-mono text-[#007AFF] dark:text-[#0A84FF]">
                        Rp {{ number_format($ledger['ending_balance'], 0, ',', '.') }}
                    </p>
                    <p class="text-[11px] text-black/40 dark:text-white/40 truncate">{{ __('finance.as_of_date', ['date' => $endDate->translatedFormat('d M Y')]) }}</p>
                </div>
            </div>
        @endif

        {{-- ========================================================== --}}
        {{-- ZONA 3: GENERAL LEDGER DRILLDOWN TABLE                      --}}
        {{-- ========================================================== --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[760px]">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                            <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 whitespace-nowrap">{{ __('finance.col_date') }}</th>
                            <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 whitespace-nowrap">{{ __('finance.col_journal_no') }}</th>
                            <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 whitespace-nowrap">{{ __('finance.col_ref_doc') }}</th>
                            <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 min-w-[180px]">{{ __('finance.col_desc_notes') }}</th>
                            <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right whitespace-nowrap">{{ __('finance.col_debit') }} (Rp)</th>
                            <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right whitespace-nowrap">{{ __('finance.col_credit') }} (Rp)</th>
                            <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right whitespace-nowrap">{{ __('finance.col_running_balance') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        {{-- Baris Saldo Awal (Beginning Balance Header Line) --}}
                        <tr class="bg-black/[0.015] dark:bg-white/[0.015] font-medium text-black/70 dark:text-white/70">
                            <td class="px-4 sm:px-5 py-3 tabular-nums whitespace-nowrap font-semibold">{{ $startDate->translatedFormat('d M Y') }}</td>
                            <td class="px-4 sm:px-5 py-3 font-mono text-black/30 dark:text-white/30">–</td>
                            <td class="px-4 sm:px-5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                    {{ __('finance.beginning_balance_badge') }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-5 py-3 font-medium text-black/80 dark:text-white/80">{{ __('finance.beginning_balance_desc') }}</td>
                            <td class="px-4 sm:px-5 py-3 text-right font-mono text-black/30 dark:text-white/30">–</td>
                            <td class="px-4 sm:px-5 py-3 text-right font-mono text-black/30 dark:text-white/30">–</td>
                            <td class="px-4 sm:px-5 py-3 text-right tabular-nums font-mono font-bold text-black dark:text-white whitespace-nowrap">
                                Rp {{ number_format($ledger['beginning_balance'], 0, ',', '.') }}
                            </td>
                        </tr>

                        {{-- Baris Transaksi Buku Besar --}}
                        @forelse($ledger['lines'] as $line)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-4 sm:px-5 py-3 tabular-nums text-black/70 dark:text-white/70 whitespace-nowrap">
                                    {{ $line['entry_date'] }}
                                </td>
                                <td class="px-4 sm:px-5 py-3 tabular-nums font-mono font-bold text-[#007AFF] dark:text-[#0A84FF] whitespace-nowrap">
                                    #{{ $line['entry_number'] }}
                                </td>
                                <td class="px-4 sm:px-5 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-[5px] text-[10.5px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/75 dark:text-white/75">
                                            {{ $line['reference_type_label'] }}
                                        </span>
                                        @if($line['source_url'])
                                            <a href="{{ $line['source_url'] }}" target="_blank"
                                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#007AFF] hover:underline"
                                                title="{{ __('finance.open_original_doc') }}">
                                                <span>{{ __('common.view') }}</span>
                                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 sm:px-5 py-3 text-black/85 dark:text-white/85">
                                    <span class="font-medium">{{ $line['description'] }}</span>
                                    @if($line['notes'])
                                        <span class="block text-[11px] text-black/45 dark:text-white/45 mt-0.5">{{ $line['notes'] }}</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-5 py-3 text-right tabular-nums font-mono whitespace-nowrap">
                                    @if($line['debit'] > 0)
                                        <span class="font-semibold text-black dark:text-white">{{ number_format($line['debit'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-black/20 dark:text-white/20 font-sans">–</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-5 py-3 text-right tabular-nums font-mono whitespace-nowrap">
                                    @if($line['credit'] > 0)
                                        <span class="font-semibold text-[#007AFF] dark:text-[#0A84FF]">{{ number_format($line['credit'], 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-black/20 dark:text-white/20 font-sans">–</span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-5 py-3 text-right tabular-nums font-mono font-bold text-black dark:text-white whitespace-nowrap">
                                    Rp {{ number_format($line['running_balance'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-black/40 dark:text-white/40">
                                    <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                                    <p class="font-medium">{{ __('finance.empty_gl_range_desc') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-black/[0.08] dark:border-white/[0.1] bg-black/[0.03] dark:bg-white/[0.03] font-bold text-[13px]">
                            <td colspan="4" class="px-4 sm:px-5 py-3.5 text-black dark:text-white">{{ __('finance.total_this_period') }}</td>
                            <td class="px-4 sm:px-5 py-3.5 text-right tabular-nums font-mono text-black dark:text-white whitespace-nowrap">
                                Rp {{ number_format($ledger['total_debit'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3.5 text-right tabular-nums font-mono text-[#007AFF] dark:text-[#0A84FF] whitespace-nowrap">
                                Rp {{ number_format($ledger['total_credit'], 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-5 py-3.5 text-right tabular-nums font-mono text-[#34C759] dark:text-[#30D158] whitespace-nowrap">
                                Rp {{ number_format($ledger['ending_balance'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
@endsection
