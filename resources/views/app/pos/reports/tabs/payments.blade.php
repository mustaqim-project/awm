{{-- TAB 7: PAYMENT METHODS & SETTLEMENT --}}
@php
    $totalTx = (int) $paymentMethods->sum(fn($pm) => $pm->count ?? $pm->tx_count ?? 0);
    $totalGross = (float) $paymentMethods->sum(fn($pm) => $pm->amount ?? $pm->total_amount ?? 0);
    $totalFee = (float) $paymentMethods->sum(fn($pm) => $pm->fee ?? $pm->fee_amount ?? $pm->total_fee ?? 0);
    $totalNet = (float) $paymentMethods->sum(fn($pm) => $pm->net_amount ?? $pm->net_settlement ?? ($pm->amount ?? 0));
    $topMethod = $paymentMethods->sortByDesc(fn($pm) => $pm->amount ?? $pm->total_amount ?? 0)->first();

    // Helper color & icon mapper per payment method
    $getMethodMeta = function($raw) {
        return match (strtolower((string) $raw)) {
            'cash' => [
                'icon' => 'banknote',
                'color' => '#34C759',
                'badge' => 'bg-[#34C759]/10 text-[#34C759] border-[#34C759]/20',
                'bar_color' => 'bg-[#34C759]',
                'label_type' => 'Tunai',
            ],
            'qris', 'qris_dynamic' => [
                'icon' => 'qr-code',
                'color' => '#007AFF',
                'badge' => 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/20',
                'bar_color' => 'bg-[#007AFF]',
                'label_type' => 'QRIS Digital',
            ],
            'edc_debit' => [
                'icon' => 'credit-card',
                'color' => '#5856D6',
                'badge' => 'bg-[#5856D6]/10 text-[#5856D6] border-[#5856D6]/20',
                'bar_color' => 'bg-[#5856D6]',
                'label_type' => 'EDC Debit',
            ],
            'edc_credit' => [
                'icon' => 'credit-card',
                'color' => '#AF52DE',
                'badge' => 'bg-[#AF52DE]/10 text-[#AF52DE] border-[#AF52DE]/20',
                'bar_color' => 'bg-[#AF52DE]',
                'label_type' => 'Kartu Kredit',
            ],
            'transfer' => [
                'icon' => 'building-2',
                'color' => '#FF9500',
                'badge' => 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/20',
                'bar_color' => 'bg-[#FF9500]',
                'label_type' => 'Transfer Bank',
            ],
            'customer_credit' => [
                'icon' => 'user-check',
                'color' => '#32ADE6',
                'badge' => 'bg-[#32ADE6]/10 text-[#32ADE6] border-[#32ADE6]/20',
                'bar_color' => 'bg-[#32ADE6]',
                'label_type' => 'Kasbon / Piutang',
            ],
            'loyalty_points' => [
                'icon' => 'award',
                'color' => '#FFCC00',
                'badge' => 'bg-[#FFCC00]/15 text-[#B28900] dark:text-[#FFD60A] border-[#FFCC00]/30',
                'bar_color' => 'bg-[#FFCC00]',
                'label_type' => 'Poin Member',
            ],
            default => [
                'icon' => 'wallet',
                'color' => '#8E8E93',
                'badge' => 'bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 border-black/10 dark:border-white/10',
                'bar_color' => 'bg-slate-400',
                'label_type' => 'Lainnya',
            ],
        };
    };
@endphp

<div class="space-y-6">

    {{-- 1. BENTO KPI SUMMARY CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Penerimaan Kotor --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-medium text-black/55 dark:text-white/55">Penerimaan Kotor</span>
                <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-[22px] font-bold tracking-tight text-black dark:text-white tabular-nums">
                    Rp {{ number_format($totalGross, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-1 flex items-center gap-1.5">
                    <span>Dari {{ number_format($totalTx, 0, ',', '.') }} total transaksi</span>
                </div>
            </div>
        </div>

        {{-- Total Potongan Fee MDR --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-medium text-black/55 dark:text-white/55">Potongan Fee & MDR</span>
                <span class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center">
                    <i data-lucide="percent" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-[22px] font-bold tracking-tight text-[#FF3B30] dark:text-[#FF453A] tabular-nums">
                    {{ $totalFee > 0 ? '-Rp ' . number_format($totalFee, 0, ',', '.') : 'Rp 0' }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">
                    {{ $totalGross > 0 ? number_format(($totalFee / $totalGross) * 100, 2) : 0 }}% rata-rata biaya MDR
                </div>
            </div>
        </div>

        {{-- Penerimaan Bersih (Net Settlement) --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-medium text-black/55 dark:text-white/55">Penerimaan Bersih</span>
                <span class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-[22px] font-bold tracking-tight text-[#34C759] dark:text-[#30D158] tabular-nums">
                    Rp {{ number_format($totalNet, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">
                    Uang kas riil setelah dipotong MDR
                </div>
            </div>
        </div>

        {{-- Metode Utama / Terpopuler --}}
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-medium text-black/55 dark:text-white/55">Metode Terpopuler</span>
                <span class="w-8 h-8 rounded-[10px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-[18px] font-bold tracking-tight text-black dark:text-white truncate">
                    {{ $topMethod ? ($topMethod->method ?? $topMethod->method_label ?? __('common.no_data_yet')) : __('common.no_data_yet') }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-1">
                    @if($topMethod)
                        {{ number_format($topMethod->share ?? $topMethod->contribution_percent ?? 0, 1) }}% dari omzet ({{ number_format($topMethod->count ?? $topMethod->tx_count ?? 0, 0, ',', '.') }} tx)
                    @else
                        Tidak ada transaksi pada periode ini
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 2. KOMPOSISI & DISTRIBUSI VISUAL STACKED BAR --}}
    @if ($totalGross > 0 && $paymentMethods->isNotEmpty())
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 shadow-xs space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-[14px] font-bold text-black dark:text-white">{{ __('pos.payment_channel_composition') }}</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">Proporsi perolehan omzet kasir berdasarkan metode pembayaran</p>
                </div>
                <div class="text-[12px] font-semibold text-black/60 dark:text-white/60 tabular-nums">
                    100.0% Terpetakan
                </div>
            </div>

            {{-- Multi-color Stacked Progress Bar --}}
            <div class="h-3.5 w-full rounded-full overflow-hidden flex bg-black/[0.04] dark:bg-white/[0.06] p-0.5 gap-0.5">
                @foreach ($paymentMethods as $pm)
                    @php
                        $mAmount = (float) ($pm->amount ?? $pm->total_amount ?? 0);
                        $mShare = (float) ($pm->share ?? $pm->contribution_percent ?? 0);
                        $mMeta = $getMethodMeta($pm->raw_method ?? $pm->payment_method ?? '');
                    @endphp
                    @if ($mAmount > 0)
                        <div class="h-full rounded-full {{ $mMeta['bar_color'] }} transition-all"
                            style="width: {{ max(1.5, $mShare) }}%"
                            title="{{ $pm->method ?? $pm->method_label }}: Rp {{ number_format($mAmount, 0, ',', '.') }} ({{ number_format($mShare, 1) }}%)">
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Mini Legend Badges --}}
            <div class="flex flex-wrap items-center gap-3 pt-1">
                @foreach ($paymentMethods as $pm)
                    @php
                        $mAmount = (float) ($pm->amount ?? $pm->total_amount ?? 0);
                        $mShare = (float) ($pm->share ?? $pm->contribution_percent ?? 0);
                        $mMeta = $getMethodMeta($pm->raw_method ?? $pm->payment_method ?? '');
                    @endphp
                    <div class="flex items-center gap-2 text-[12px]">
                        <span class="w-2.5 h-2.5 rounded-full {{ $mMeta['bar_color'] }} shrink-0"></span>
                        <span class="font-medium text-black/75 dark:text-white/75">{{ $pm->method ?? $pm->method_label }}</span>
                        <span class="font-bold tabular-nums text-black dark:text-white">{{ number_format($mShare, 1) }}%</span>
                        <span class="text-[11px] text-black/40 dark:text-white/40 tabular-nums">(Rp {{ number_format($mAmount, 0, ',', '.') }})</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 3. TABEL DETAIL RINCIAN METODE PEMBAYARAN --}}
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('pos.reports_payments_title') }}</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('pos.reports_payments_subtitle') }}</p>
            </div>
            <div class="text-[12px] font-medium text-black/60 dark:text-white/60">
                {{ __('pos.reports_total_prefix') }}: <span class="font-bold text-black dark:text-white">{{ $paymentMethods->count() }}</span> {{ __('pos.reports_payments_unit') }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4">{{ __('pos.reports_col_payment_method') }}</th>
                        <th class="py-3 px-4 text-center">{{ __('pos.reports_col_total_transactions') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('pos.reports_col_total_received') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('pos.reports_col_mdr_fee') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('pos.reports_col_net_receipt') }}</th>
                        <th class="py-3 px-4 text-right w-44">{{ __('pos.reports_col_share_revenue') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse ($paymentMethods as $idx => $pm)
                        @php
                            $mRaw = $pm->raw_method ?? $pm->payment_method ?? '';
                            $mMeta = $getMethodMeta($mRaw);
                            $mCount = (int) ($pm->count ?? $pm->tx_count ?? 0);
                            $mAmount = (float) ($pm->amount ?? $pm->total_amount ?? 0);
                            $mFee = (float) ($pm->fee ?? $pm->fee_amount ?? $pm->total_fee ?? 0);
                            $mNet = (float) ($pm->net_amount ?? $pm->net_settlement ?? ($mAmount - $mFee));
                            $mShare = (float) ($pm->share ?? $pm->contribution_percent ?? 0);
                        @endphp
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                            <td class="py-3.5 px-4 text-[12px] text-center text-black/40 dark:text-white/40 font-mono">
                                {{ $idx + 1 }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-[10px] {{ $mMeta['badge'] }} border flex items-center justify-center shrink-0">
                                        <i data-lucide="{{ $mMeta['icon'] }}" class="w-4 h-4"></i>
                                    </span>
                                    <div>
                                        <div class="font-semibold text-black dark:text-white flex items-center gap-1.5">
                                            <span>{{ $pm->method ?? $pm->method_label }}</span>
                                            <span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded-full {{ $mMeta['badge'] }} border">
                                                {{ $mMeta['label_type'] }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-black/40 dark:text-white/40 font-mono">
                                            id: {{ $mRaw }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[12px] font-bold tabular-nums bg-[#007AFF]/10 text-[#007AFF]">
                                    {{ number_format($mCount, 0, ',', '.') }} tx
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold tabular-nums text-black dark:text-white">
                                Rp {{ number_format($mAmount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                                {{ $mFee > 0 ? '-Rp ' . number_format($mFee, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">
                                Rp {{ number_format($mNet, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums">
                                <div class="font-bold text-black dark:text-white text-[13px]">
                                    {{ number_format($mShare, 1) }}%
                                </div>
                                <div class="w-24 h-1.5 bg-black/[0.05] dark:bg-white/[0.08] rounded-full overflow-hidden ml-auto mt-1">
                                    <div class="h-full {{ $mMeta['bar_color'] }} rounded-full" style="width: {{ min(100, $mShare) }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="w-12 h-12 rounded-full bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center">
                                        <i data-lucide="credit-card" class="w-6 h-6"></i>
                                    </div>
                                    <div class="text-[14px] font-semibold text-black/70 dark:text-white/70">
                                        {{ __('pos.reports_payments_empty') }}
                                    </div>
                                    <p class="text-[12px] text-black/40 dark:text-white/40 max-w-sm">
                                        {{ __('pos.no_completed_payment_transactions_desc') }} Silakan sesuaikan tanggal atau preset filter di atas.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($paymentMethods->isNotEmpty())
                    <tfoot class="border-t-2 border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] font-bold text-black dark:text-white">
                        <tr>
                            <td class="py-4 px-4 text-center"></td>
                            <td class="py-4 px-4 text-[13px]">
                                TOTAL PENERIMAAN KASIR
                            </td>
                            <td class="py-4 px-4 text-center tabular-nums text-[#007AFF]">
                                {{ number_format($totalTx, 0, ',', '.') }} tx
                            </td>
                            <td class="py-4 px-4 text-right tabular-nums text-[14px]">
                                Rp {{ number_format($totalGross, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                                {{ $totalFee > 0 ? '-Rp ' . number_format($totalFee, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-4 px-4 text-right tabular-nums text-[14px] text-[#34C759] dark:text-[#30D158]">
                                Rp {{ number_format($totalNet, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-4 text-right tabular-nums text-[13px]">
                                100.0%
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- 4. INFORMASI REKONSILIASI & SETTLEMENT --}}
    <div class="rounded-[16px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 p-4.5 sm:p-5 flex items-start gap-3.5">
        <span class="w-9 h-9 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0 mt-0.5">
            <i data-lucide="info" class="w-5 h-5"></i>
        </span>
        <div class="space-y-1 text-[12px] leading-relaxed">
            <div class="font-bold text-black dark:text-white text-[13px]">Panduan Rekonsiliasi & Settlement Finansial</div>
            <p class="text-black/70 dark:text-white/70">
                • <strong>Penerimaan Tunai (Cash):</strong> Masuk langsung ke laci kasir dan diverifikasi saat tutup kasir di tab <a href="{{ route('pos.reports.index', array_merge(request()->query(), ['tab' => 'shifts'])) }}" class="text-[#007AFF] font-semibold hover:underline">Rekonsiliasi Shift</a>.<br>
                • <strong>Penerimaan Non-Tunai (QRIS, EDC, Transfer):</strong> Dicatat dengan potongan estimasi biaya MDR. Dana masuk ke rekening penampungan/gateway dan otomatis dicatat pada modul <a href="{{ route('finance.settlements.index') }}" class="text-[#007AFF] font-semibold hover:underline">Settlement Kas & Bank</a>.
            </p>
        </div>
    </div>

</div>
