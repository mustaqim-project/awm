@extends('layouts.app', [
    'title' => __('communication.inbound_title'),
    'headerTitle' => __('communication.module_title'),
    'headerSubtitle' => __('communication.inbound_subtitle')
])

@section('content')
<div class="max-w-[1200px] mx-auto space-y-6 pb-20" x-data="{
    selectedEmail: null,
    viewModalOpen: false,
    isChecking: false,
    checkFeedback: null,
    openBodyModal(email) {
        this.selectedEmail = email;
        this.viewModalOpen = true;
    },
    async triggerCheck() {
        this.isChecking = true;
        this.checkFeedback = null;
        try {
            let res = await fetch('{{ route('communication.inbound.check-now') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            let data = await res.json();
            this.checkFeedback = data;
            if (data.success) {
                setTimeout(() => window.location.reload(), 1200);
            }
        } catch (err) {
            this.checkFeedback = { success: false, message: 'Gagal memeriksa email: ' + err.message };
        } finally {
            this.isChecking = false;
        }
    }
}">
    <!-- 1. NAVIGATION TABS -->
    @include('app.communication.partials.nav-tabs')

    <!-- 2. ACTION BAR & STATS (Bento HIG) -->
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 space-y-4 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                    <i data-lucide="inbox" class="w-5 h-5 text-[#34C759]"></i>
                    <span>{{ __('communication.inbound_title') }}</span>
                </h2>
                <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                    {{ __('communication.inbound_subtitle') }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                        @click="triggerCheck()"
                        :disabled="isChecking"
                        class="min-h-[40px] px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-2 shadow-[0_2px_6px_rgba(0,122,255,0.25)] disabled:opacity-50 cursor-pointer">
                    <i data-lucide="refresh-cw" class="w-4 h-4" :class="isChecking ? 'animate-spin' : ''"></i>
                    <span x-text="isChecking ? '{{ __('communication.checking') }}' : '{{ __('communication.check_inbound_now') }}'"></span>
                </button>
            </div>
        </div>

        <div x-show="checkFeedback" x-transition.opacity class="p-3.5 rounded-[10px] text-[13px]"
             :class="checkFeedback?.success ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]'">
            <p class="font-medium" x-text="checkFeedback?.message"></p>
        </div>

        <!-- Filter pills -->
        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-black/5 dark:border-white/5">
            @php $currentStatus = request()->query('status'); @endphp
            <a href="{{ route('communication.inbound.index') }}"
               class="px-3 py-1 rounded-full text-[12px] font-medium transition-all {{ empty($currentStatus) ? 'bg-black dark:bg-white text-white dark:text-black font-semibold' : 'bg-black/5 dark:bg-white/5 text-black/70 dark:text-white/70 hover:bg-black/10' }}">
                {{ __('communication.inbound_status_all') }}
            </a>
            <a href="{{ route('communication.inbound.index', ['status' => 'matched']) }}"
               class="px-3 py-1 rounded-full text-[12px] font-medium transition-all {{ $currentStatus === 'matched' ? 'bg-[#34C759] text-white font-semibold' : 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/15' }}">
                {{ __('communication.inbound_status_matched') }}
            </a>
            <a href="{{ route('communication.inbound.index', ['status' => 'unprocessed']) }}"
               class="px-3 py-1 rounded-full text-[12px] font-medium transition-all {{ $currentStatus === 'unprocessed' ? 'bg-[#FF9500] text-white font-semibold' : 'bg-[#FF9500]/10 text-[#B25E00] dark:text-[#FF9F0A] hover:bg-[#FF9500]/15' }}">
                {{ __('communication.inbound_status_unprocessed') }}
            </a>
            <a href="{{ route('communication.inbound.index', ['status' => 'ignored']) }}"
               class="px-3 py-1 rounded-full text-[12px] font-medium transition-all {{ $currentStatus === 'ignored' ? 'bg-black/40 text-white font-semibold' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10' }}">
                {{ __('communication.inbound_status_ignored') }}
            </a>
        </div>
    </div>

    <!-- 3. INBOUND EMAILS TABLE (Apple HIG) -->
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
        @if($emails->isEmpty())
            <div class="py-16 px-6 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                    <i data-lucide="inbox" class="w-6 h-6"></i>
                </div>
                <h3 class="text-[15px] font-semibold text-black dark:text-white">{{ __('communication.no_inbound_emails') }}</h3>
                <p class="text-[13px] text-black/50 dark:text-white/50 max-w-md mx-auto leading-relaxed">
                    {{ __('communication.no_inbound_emails_desc') }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.015] dark:bg-white/[0.015] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                            <th class="py-3.5 px-6">{{ __('communication.table_from') }}</th>
                            <th class="py-3.5 px-6">{{ __('communication.table_doc') }}</th>
                            <th class="py-3.5 px-6">{{ __('communication.table_status') }}</th>
                            <th class="py-3.5 px-6">{{ __('communication.table_received_at') }}</th>
                            <th class="py-3.5 px-6 text-right">{{ __('communication.table_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @foreach($emails as $item)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-4 px-6">
                                    <div class="font-semibold text-black dark:text-white">{{ $item->from_name ?: $item->from_address }}</div>
                                    <div class="text-[12px] text-black/50 dark:text-white/50 font-mono">{{ $item->from_address }}</div>
                                    <div class="text-[12px] text-black/80 dark:text-white/80 font-medium mt-1 truncate max-w-md">
                                        {{ $item->subject ?: '(Tanpa Subjek)' }}
                                    </div>
                                </td>

                                <td class="py-4 px-6">
                                    @if($item->detected_document_number)
                                        <div class="space-y-1">
                                            @php
                                                $docRoute = '#';
                                                if ($item->detected_document_type === 'quotation' && $item->detected_document_id) {
                                                    $docRoute = route('sales.quotations.show', $item->detected_document_id);
                                                } elseif ($item->detected_document_type === 'invoice' && $item->detected_document_id) {
                                                    $docRoute = route('invoices.show', $item->detected_document_id);
                                                } elseif ($item->detected_document_type === 'purchase_order' && $item->detected_document_id) {
                                                    $docRoute = route('purchase-orders.show', $item->detected_document_id);
                                                }
                                            @endphp
                                            <a href="{{ $docRoute }}"
                                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] text-[12px] font-semibold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] hover:bg-[#0071E3]/20 transition-all font-mono">
                                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                                <span>{{ $item->detected_document_number }}</span>
                                            </a>
                                            <div class="text-[10px] text-black/40 dark:text-white/40 uppercase font-semibold">
                                                {{ ucfirst($item->detected_document_type) }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-black/40 dark:text-white/40 italic">-</span>
                                    @endif
                                </td>

                                <td class="py-4 px-6">
                                    @php
                                        $badgeClasses = match($item->processing_status) {
                                            'matched' => 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]',
                                            'unprocessed' => 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]',
                                            'failed' => 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]',
                                            default => 'bg-black/5 dark:bg-white/5 text-black/50 dark:text-white/50',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $badgeClasses }}">
                                        {{ __('communication.inbound_status_' . $item->processing_status) }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 text-black/60 dark:text-white/60 tabular-nums text-[12px]">
                                    {{ $item->received_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                                </td>

                                <td class="py-4 px-6 text-right">
                                    <button type="button"
                                            @click="openBodyModal({{ json_encode($item) }})"
                                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('communication.view_body') }}</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($emails->hasPages())
                <div class="p-4 border-t border-black/5 dark:border-white/5">
                    {{ $emails->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- 4. MODAL LIHAT ISI EMAIL -->
    <div x-show="viewModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="viewModalOpen = false"></div>
        <div class="relative w-full max-w-[640px] rounded-[20px] bg-white dark:bg-[#1C1C1E] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-5" @click.stop>
            <div class="flex items-start justify-between border-b border-black/5 dark:border-white/10 pb-4">
                <div class="min-w-0 pr-4">
                    <h3 class="text-[16px] font-bold text-black dark:text-white truncate" x-text="selectedEmail?.subject || '(Tanpa Subjek)'"></h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                        Dari: <span class="font-medium text-black/70 dark:text-white/70" x-text="selectedEmail?.from_name ? (selectedEmail?.from_name + ' <' + selectedEmail?.from_address + '>') : selectedEmail?.from_address"></span>
                    </p>
                </div>
                <button type="button" @click="viewModalOpen = false" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="max-h-[420px] overflow-y-auto space-y-4">
                <div class="p-4 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 text-[13px] text-black/80 dark:text-white/80 whitespace-pre-wrap leading-relaxed font-sans"
                     x-text="selectedEmail?.body_text || '(Email kosong)'"></div>

                <template x-if="selectedEmail?.detected_document_number">
                    <div class="p-3.5 rounded-[10px] bg-[#007AFF]/8 border border-[#007AFF]/20 text-[12px] text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-between">
                        <div>
                            <span class="font-semibold">Dokumen Terkait:</span>
                            <span class="font-mono font-bold ml-1" x-text="selectedEmail?.detected_document_number"></span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-end pt-2">
                <button type="button" @click="viewModalOpen = false" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-all">
                    {{ __('communication.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
