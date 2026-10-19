{{-- TAB 15: PROFITABILITY & MARGIN DEEP DIVE --}}
<div class="space-y-6">
    {{-- Margin Tier Stratification Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($marginAnalytics->marginTiers as $tierKey => $tier)
            @php
                $tierColors = [
                    'high_margin' => 'text-[#34C759] border-[#34C759]/20 bg-[#34C759]/5',
                    'medium_margin' => 'text-[#007AFF] border-[#007AFF]/20 bg-[#007AFF]/5',
                    'low_margin' => 'text-[#FF9500] border-[#FF9500]/20 bg-[#FF9500]/5',
                    'negative_margin' => 'text-[#FF3B30] border-[#FF3B30]/20 bg-[#FF3B30]/5',
                ];
            @endphp
            <div class="rounded-[16px] border p-4 sm:p-5 shadow-xs {{ $tierColors[$tierKey] ?? 'bg-white' }}">
                <span class="text-[11px] font-bold uppercase tracking-wider">{{ $tier['label'] }}</span>
                <div class="text-[22px] font-bold tabular-nums mt-2">
                    {{ number_format($tier['count'], 0, ',', '.') }} <span class="text-[13px] font-normal opacity-70">{{ __('pos.reports_profitability_product_unit') }}</span>
                </div>
                <div class="text-[11px] opacity-80 mt-1 font-medium">
                    {{ __('pos.reports_profitability_turnover_prefix', ['amount' => number_format($tier['revenue'], 0, ',', '.')]) }} ({{ number_format($tier['share_percent'], 1) }}%)
                </div>
            </div>
        @endforeach
    </div>

    {{-- Zero-COGS Warnings if Any --}}
    @if ($marginAnalytics->zeroCogsItemsCount > 0)
        <div class="rounded-[16px] bg-[#FF9500]/10 border border-[#FF9500]/30 p-5 shadow-xs">
            <div class="flex items-start gap-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-[#FF9500] shrink-0 mt-0.5"></i>
                <div class="flex-1 min-w-0">
                    <h4 class="text-[14px] font-bold text-black dark:text-white">{{ __('pos.reports_profitability_warning_title', ['count' => $marginAnalytics->zeroCogsItemsCount]) }}</h4>
                    <p class="text-[12px] text-black/70 dark:text-white/70 mt-0.5">
                        {{ __('pos.reports_profitability_zero_cogs_desc', ['amount' => number_format($marginAnalytics->zeroCogsRevenue, 0, ',', '.')]) }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($marginAnalytics->zeroCogsWarningProducts->take(6) as $zProd)
                            <span class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 text-[11px] font-medium text-black dark:text-white">
                                {{ $zProd['product_name'] }} (Rp {{ number_format($zProd['gross_revenue'], 0, ',', '.') }})
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Loss Leader / Negative Margin Products Table --}}
    @if ($marginAnalytics->lossLeaderProducts->isNotEmpty())
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-black/5 dark:border-white/5">
                <h3 class="text-[15px] font-bold text-[#FF3B30]">{{ __('pos.reports_profitability_loss_title') }}</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('pos.reports_profitability_loss_subtitle') }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                            <th class="py-3 px-4">{{ __('pos.reports_col_product_name') }}</th>
                            <th class="py-3 px-4">{{ __('pos.reports_col_category') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('pos.reports_col_qty_sold') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('pos.reports_col_sales') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('pos.reports_col_total_cogs') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('pos.reports_col_loss_amount') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('pos.reports_col_margin_pct') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @foreach ($marginAnalytics->lossLeaderProducts as $lossProd)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                                <td class="py-3 px-4 font-semibold text-black dark:text-white">
                                    {{ $lossProd['product_name'] }}
                                </td>
                                <td class="py-3 px-4 text-[12px] text-black/70 dark:text-white/70">
                                    {{ $lossProd['category_name'] }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold tabular-nums text-black dark:text-white">
                                    {{ number_format($lossProd['quantity_sold'], 1) }}
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums text-black dark:text-white">
                                    Rp {{ number_format($lossProd['gross_revenue'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold tabular-nums text-[#FF9500]">
                                    Rp {{ number_format($lossProd['total_cogs'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                                    -Rp {{ number_format($lossProd['loss_amount'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                                    {{ number_format($lossProd['margin_percent'], 1) }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
