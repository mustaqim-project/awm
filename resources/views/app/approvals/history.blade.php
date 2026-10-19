@extends('layouts.app', [
    'title' => __('approvals.history_page_title'),
    'headerTitle' => __('approvals.history_header_title'),
    'headerSubtitle' => __('approvals.history_header_subtitle')
])

@section('content')
<div class="max-w-[1440px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{ selectedTicket: null, detailModalOpen: false }">

    <!-- Top Header Bar -->
    <header class="rounded-[20px] backdrop-blur-2xl bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                <i data-lucide="history" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                    {{ __('approvals.badge_audit_archive') }}
                </div>
                <h1 class="text-xl font-bold text-black dark:text-white tracking-tight">
                    {{ __('approvals.history_title') }}
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('approvals.inbox') }}" class="min-h-[44px] px-4 rounded-[12px] text-xs font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all flex items-center gap-1.5">
                <i data-lucide="inbox" class="w-4 h-4"></i>
                <span>{{ __('approvals.btn_back_inbox') }}</span>
            </a>
        </div>
    </header>

    <!-- Filter Bar -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar">
        <a href="{{ route('approvals.history') }}"
            class="px-4 py-2.5 rounded-full font-semibold transition-all shrink-0 flex items-center gap-1.5 {{ empty($filterType) ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-white dark:bg-[#1C1C1E] text-black/70 dark:text-white/70 border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/5' }}">
            <span>{{ __('approvals.filter_all_history') }}</span>
        </a>
        <a href="{{ route('approvals.history', ['type' => 'purchase_order']) }}"
            class="px-4 py-2.5 rounded-full font-semibold transition-all shrink-0 flex items-center gap-1.5 {{ $filterType === 'purchase_order' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-white dark:bg-[#1C1C1E] text-black/70 dark:text-white/70 border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/5' }}">
            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
            <span>{{ __('approvals.filter_po') }}</span>
        </a>
        <a href="{{ route('approvals.history', ['type' => 'expense']) }}"
            class="px-4 py-2.5 rounded-full font-semibold transition-all shrink-0 flex items-center gap-1.5 {{ $filterType === 'expense' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-white dark:bg-[#1C1C1E] text-black/70 dark:text-white/70 border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/5' }}">
            <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
            <span>{{ __('approvals.filter_expense') }}</span>
        </a>
        <a href="{{ route('approvals.history', ['type' => 'supplier_invoice']) }}"
            class="px-4 py-2.5 rounded-full font-semibold transition-all shrink-0 flex items-center gap-1.5 {{ $filterType === 'supplier_invoice' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-white dark:bg-[#1C1C1E] text-black/70 dark:text-white/70 border border-black/[0.06] dark:border-white/[0.08] hover:bg-black/5' }}">
            <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
            <span>{{ __('approvals.filter_supplier_invoice') }}</span>
        </a>
    </div>

    <!-- History Container -->
    <div class="rounded-[22px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-sm">
        
        <!-- Desktop / Tablet Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.03] border-b border-black/[0.06] dark:border-white/[0.08] text-black/60 dark:text-white/60 font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">{{ __('approvals.th_document') }}</th>
                        <th class="px-5 py-3.5">{{ __('approvals.th_maker') }}</th>
                        <th class="px-5 py-3.5 text-right">{{ __('approvals.th_amount') }}</th>
                        <th class="px-5 py-3.5 text-center">{{ __('approvals.th_final_status') }}</th>
                        <th class="px-5 py-3.5">{{ __('approvals.th_completed_time') }}</th>
                        <th class="px-5 py-3.5">{{ __('approvals.th_audit_log') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06] text-black dark:text-white">
                    @forelse ($historyTickets as $ticket)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-bold text-black dark:text-white">{{ $ticket->getDocumentTypeLabel() }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 font-mono mt-0.5">ID: {{ substr($ticket->document_id, 0, 8) }}...</div>
                            </td>
                            <td class="px-5 py-4 text-black/80 dark:text-white/80 font-medium">
                                {{ $ticket->requester->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-right font-bold text-black dark:text-white tabular-nums">
                                Rp {{ number_format($ticket->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if ($ticket->isApproved())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('approvals.status_approved') }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">
                                        <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('approvals.status_rejected') }}</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-black/60 dark:text-white/60 tabular-nums">
                                {{ ($ticket->approved_at ?? $ticket->rejected_at)?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="space-y-1">
                                    @foreach ($ticket->logs as $log)
                                        <div class="text-[11px] text-black/70 dark:text-white/70">
                                            <strong>Level {{ $log->level }}:</strong> {{ $log->approver->name ?? 'User' }}
                                            ({{ ucfirst($log->action) }})
                                            @if($log->notes)
                                                · <span class="italic text-black/50 dark:text-white/50">"{{ $log->notes }}"</span>
                                            @endif
                                        </div>
                                    @endforeach
                                    @if ($ticket->rejection_reason)
                                        <div class="text-[11px] text-[#FF3B30] font-medium">
                                            {{ __('approvals.rejection_reason_prefix') }} "{{ $ticket->rejection_reason }}"
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-black/40 dark:text-white/40">
                                {{ __('approvals.empty_history') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List View -->
        <div class="block md:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06] p-3 space-y-3">
            @forelse ($historyTickets as $ticket)
                <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="text-xs font-bold text-black dark:text-white block">{{ $ticket->getDocumentTypeLabel() }}</span>
                            <span class="text-[11px] text-black/45 dark:text-white/45 font-mono">ID: {{ substr($ticket->document_id, 0, 8) }}...</span>
                        </div>
                        @if ($ticket->isApproved())
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759] shrink-0">
                                <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                                <span>{{ __('approvals.status_approved') }}</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] shrink-0">
                                <i data-lucide="x-circle" class="w-3 h-3"></i>
                                <span>{{ __('approvals.status_rejected') }}</span>
                            </span>
                        @endif
                    </div>

                    <div class="flex justify-between items-baseline text-xs">
                        <span class="text-black/50 dark:text-white/50">{{ __('approvals.amount_label') }}</span>
                        <strong class="text-black dark:text-white font-bold tabular-nums text-sm">Rp {{ number_format($ticket->amount, 0, ',', '.') }}</strong>
                    </div>

                    <div class="flex justify-between text-[11px] text-black/60 dark:text-white/60">
                        <span>Maker: <strong>{{ $ticket->requester->name ?? '-' }}</strong></span>
                        <span class="tabular-nums">{{ ($ticket->approved_at ?? $ticket->rejected_at)?->format('d/m/Y H:i') ?? '-' }}</span>
                    </div>

                    @if($ticket->logs->isNotEmpty())
                        <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] space-y-1 text-[11px]">
                            @foreach ($ticket->logs as $log)
                                <div class="text-black/70 dark:text-white/70">
                                    <strong>L{{ $log->level }}:</strong> {{ $log->approver->name ?? 'User' }} ({{ ucfirst($log->action) }})
                                    @if($log->notes)
                                        <span class="italic text-black/50 dark:text-white/50 block pl-2">"{{ $log->notes }}"</span>
                                    @endif
                                </div>
                            @endforeach
                            @if ($ticket->rejection_reason)
                                <div class="text-[#FF3B30] font-medium pl-2">
                                    {{ __('approvals.rejection_reason_prefix') }} "{{ $ticket->rejection_reason }}"
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-8 text-center text-xs text-black/40 dark:text-white/40">
                    {{ __('approvals.empty_history') }}
                </div>
            @endforelse
        </div>

    </div>

</div>
@endsection
