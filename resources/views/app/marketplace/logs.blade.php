@extends('layouts.app', [
    'title' => __('marketplace.logs_page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.logs_header_title'),
    'headerSubtitle' => __('marketplace.logs_page_subtitle'),
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{
    detailModal: false,
    selectedLog: null,
    viewDetail(log) {
        this.selectedLog = log;
        this.detailModal = true;
    },
    maskSensitiveData(data) {
        if (!data) return 'null';
        try {
            const clone = typeof data === 'string' ? JSON.parse(data) : JSON.parse(JSON.stringify(data));
            const sensitiveKeys = ['access_token', 'refresh_token', 'secret', 'password', 'token', 'authorization', 'signature', 'client_secret', 'key'];
            
            const maskObject = (obj) => {
                if (!obj || typeof obj !== 'object') return;
                for (const k in obj) {
                    if (typeof obj[k] === 'string' && sensitiveKeys.some(sk => k.toLowerCase().includes(sk))) {
                        const val = obj[k];
                        obj[k] = val.length > 8 ? val.substring(0, 4) + '••••••••' + val.slice(-4) : '••••••••';
                    } else if (typeof obj[k] === 'object') {
                        maskObject(obj[k]);
                    }
                }
            };
            maskObject(clone);
            return JSON.stringify(clone, null, 2);
        } catch (e) {
            return typeof data === 'string' ? data : JSON.stringify(data, null, 2);
        }
    }
}">

    {{-- MODULE HEADER & PERSISTENT MARKETPLACE TABS --}}
    <x-module-header
        module="marketplace"
        :title="__('marketplace.logs_header_title')"
        :subtitle="__('marketplace.logs_module_subtitle')">
        <x-slot:actions>
            <a href="{{ route('marketplace-hub.index') }}"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] flex items-center justify-center gap-1.5 cursor-pointer">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>{{ __('marketplace.back_to_hub') }}</span>
            </a>
        </x-slot:actions>
    </x-module-header>

    <x-module-tabs module="marketplace" />

    <!-- ===================================================== -->
    <!-- 2. FILTER BAR                                         -->
    <!-- ===================================================== -->
    <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-3.5 sm:p-4 backdrop-blur-md flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
        <form method="GET" action="{{ route('marketplace-hub.logs') }}" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <!-- Channel Filter -->
            <select name="channel" onchange="this.form.submit()"
                class="h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-[13px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                <option value="">{{ __('marketplace.all_channels_select') }}</option>
                <option value="shopee" {{ request('channel') === 'shopee' ? 'selected' : '' }}>Shopee</option>
                <option value="tiktok_shop" {{ request('channel') === 'tiktok_shop' ? 'selected' : '' }}>TikTok Shop</option>
                <option value="tokopedia" {{ request('channel') === 'tokopedia' ? 'selected' : '' }}>Tokopedia</option>
            </select>

            <!-- Status Filter -->
            <select name="status" onchange="this.form.submit()"
                class="h-10 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-[13px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                <option value="">{{ __('marketplace.filter_status_all') }}</option>
                <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>{{ __('marketplace.status_success') }}</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('marketplace.status_failed') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('marketplace.status_pending') }}</option>
            </select>

            @if(request()->filled('channel') || request()->filled('status'))
                <a href="{{ route('marketplace-hub.logs') }}" class="h-10 px-3 rounded-[12px] text-[12.5px] font-medium text-black/50 hover:text-black dark:hover:text-white flex items-center">
                    {{ __('marketplace.reset_filter') }}
                </a>
            @endif
        </form>

        <div class="text-[12px] text-black/60 dark:text-white/60">
            <span>{!! __('marketplace.total_logs_count', ['count' => $logs->total()]) !!}</span>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. BENTO LOGS AUDIT TABLE                             -->
    <!-- ===================================================== -->
    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] text-black/60 dark:text-white/60 font-semibold text-[11.5px] uppercase tracking-wider">
                        <th class="py-3.5 px-4 sm:px-6">{{ __('marketplace.th_activity_event') }}</th>
                        <th class="py-3.5 px-4">{{ __('marketplace.th_channel') }}</th>
                        <th class="py-3.5 px-4">{{ __('marketplace.th_status_notes') }}</th>
                        <th class="py-3.5 px-4 text-center">{{ __('marketplace.th_entity') }}</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right">{{ __('marketplace.th_execution_time') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($logs as $log)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors cursor-pointer"
                            @click="viewDetail(@js($log))">
                            <!-- Col 1: Action / Activity -->
                            <td class="py-4 px-4 sm:px-6">
                                <div class="font-bold text-black dark:text-white text-[13.5px]">
                                    {{ ucwords(str_replace('_', ' ', $log->action ?? $log->entity_type)) }}
                                </div>
                                @if($log->entity_id)
                                    <div class="text-[11px] text-black/50 dark:text-white/50 font-mono mt-0.5">
                                        ID: {{ $log->entity_id }}
                                    </div>
                                @endif
                            </td>

                            <!-- Col 2: Channel -->
                            <td class="py-4 px-4">
                                @if($log->channel === 'shopee')
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-[#EE4D2D]/15 text-[#EE4D2D]">SHOPEE</span>
                                @elseif($log->channel === 'tiktok_shop')
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-black text-white dark:bg-white dark:text-black">TIKTOK</span>
                                @elseif($log->channel === 'tokopedia')
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-[#00AA5B]/15 text-[#00AA5B]">TOKPED</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-black/10">{{ strtoupper($log->channel ?? 'SYSTEM') }}</span>
                                @endif
                            </td>

                            <!-- Col 3: Status & Message -->
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2">
                                    @if($log->status === 'success')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            <span>{{ __('marketplace.status_success') }}</span>
                                        </span>
                                    @elseif($log->status === 'failed')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">
                                            <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                            <span>{{ __('marketplace.status_failed') }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/15 text-[#FF9500]">
                                            <span>{{ __('marketplace.status_pending') }}</span>
                                        </span>
                                    @endif
                                    <span class="text-[12px] text-black/70 dark:text-white/70 truncate max-w-[280px]">
                                        {{ $log->error_message ?? ($log->status === 'success' ? __('marketplace.log_op_completed') : __('marketplace.status_pending')) }}
                                    </span>
                                </div>
                            </td>

                            <!-- Col 4: Entity Type -->
                            <td class="py-4 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-[6px] text-[11px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70">
                                    {{ strtoupper($log->entity_type) }}
                                </span>
                            </td>

                            <!-- Col 5: Timestamp -->
                            <td class="py-4 px-4 sm:px-6 text-right">
                                <div class="text-[12px] font-medium text-black dark:text-white">
                                    {{ $log->created_at->format('d M Y, H:i:s') }}
                                </div>
                                <div class="text-[10.5px] text-black/45 dark:text-white/45">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-black/50 dark:text-white/50">
                                <div class="w-12 h-12 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="clipboard-list" class="w-6 h-6 text-black/40 dark:text-white/40"></i>
                                </div>
                                <div class="font-bold text-[14px]">{{ __('marketplace.empty_logs_title') }}</div>
                                <p class="text-[12px] mt-0.5">{{ __('marketplace.empty_logs_desc') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 4. MODAL DETAIL PAYLOAD & AUDIT TRACE (BENTO XXL)     -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="detailModal" x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-4 overflow-y-auto">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                x-show="detailModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="detailModal = false"
                aria-hidden="true"></div>

            <div @click.away="detailModal = false"
                @keydown.escape.window="detailModal = false"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-detail-log-title"
                x-show="detailModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative z-10 w-full max-w-4xl rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-8 space-y-6 shadow-2xl max-h-[90vh] overflow-y-auto my-auto">

                <!-- Header -->
                <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-[16px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="file-code" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 id="modal-detail-log-title" class="text-[17px] font-bold text-black dark:text-white" x-text="selectedLog ? (selectedLog.action || selectedLog.entity_type) : '{{ __('marketplace.detail_log_title') }}'"></h3>
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] inline-flex items-center gap-1">
                                    <i data-lucide="shield-check" class="w-3 h-3"></i>
                                    <span>{{ __('marketplace.detail_log_zero_masked') }}</span>
                                </span>
                            </div>
                            <p class="text-[12px] text-black/50 dark:text-white/50" x-text="selectedLog ? ((selectedLog.channel || 'SYSTEM').toUpperCase() + ' • ID #' + (selectedLog.id ? selectedLog.id.substring(0, 8) : '')) : ''"></p>
                        </div>
                    </div>
                    <button type="button" @click="detailModal = false" class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white transition-all cursor-pointer" aria-label="{{ __('marketplace.close') }}">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Content -->
                <template x-if="selectedLog">
                    <div class="space-y-5 text-[13px]">
                        <!-- Bento Metadata Summary -->
                        <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 block">{{ __('marketplace.detail_log_execution_status') }}</span>
                                <span class="font-bold text-[13px] uppercase mt-0.5 inline-block" :class="selectedLog.status === 'success' ? 'text-[#34C759]' : 'text-[#FF3B30]'" x-text="selectedLog.status"></span>
                            </div>
                            <div>
                                <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 block">{{ __('marketplace.detail_log_entity_ref') }}</span>
                                <span class="font-mono text-[12.5px] text-black dark:text-white font-medium mt-0.5 block truncate" x-text="selectedLog.entity_type + (selectedLog.entity_id ? ' (' + selectedLog.entity_id + ')' : '')"></span>
                            </div>
                            <div>
                                <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 block">{{ __('marketplace.detail_log_recorded_at') }}</span>
                                <span class="text-[12.5px] text-black/80 dark:text-white/80 font-medium mt-0.5 block" x-text="selectedLog.created_at ? new Date(selectedLog.created_at).toLocaleString() : '-'"></span>
                            </div>
                            <template x-if="selectedLog.error_message">
                                <div class="sm:col-span-3 text-[#FF3B30] pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                                    <span class="text-[11px] font-bold uppercase tracking-wider block">{{ __('marketplace.detail_log_error_trace') }}</span>
                                    <p class="text-[12px] mt-1 font-mono p-2.5 rounded-[10px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 leading-relaxed" x-text="selectedLog.error_message"></p>
                                </div>
                            </template>
                        </div>

                        <!-- JSON Payloads in 2 Columns or Stacked -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-[12px] font-bold text-black/70 dark:text-white/70">{{ __('marketplace.detail_log_request_payload') }}</label>
                                    <span class="text-[10px] font-mono text-black/40 dark:text-white/40">{{ __('marketplace.json_format') }}</span>
                                </div>
                                <pre class="p-3.5 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.08] text-[11px] font-mono overflow-x-auto max-h-64 text-black dark:text-white leading-relaxed" x-text="maskSensitiveData(selectedLog.payload)"></pre>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-[12px] font-bold text-black/70 dark:text-white/70">{{ __('marketplace.detail_log_response_data') }}</label>
                                    <span class="text-[10px] font-mono text-black/40 dark:text-white/40">{{ __('marketplace.json_format') }}</span>
                                </div>
                                <pre class="p-3.5 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.04] dark:border-white/[0.08] text-[11px] font-mono overflow-x-auto max-h-64 text-black dark:text-white leading-relaxed" x-text="maskSensitiveData(selectedLog.response)"></pre>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="detailModal = false"
                        class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] transition-all cursor-pointer">
                        {{ __('marketplace.close') }}
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>
@endsection
