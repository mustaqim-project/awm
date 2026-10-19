{{-- TAB 8: DISCOUNTS & PROMOTIONS AUDIT --}}
<div class="space-y-6">
    {{-- Top Cards: Discount Breakdown Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('pos.reports_discounts_title') }}</span>
            <div class="text-[22px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A] mt-2">
                Rp {{ number_format($discountAnalytics->totalDiscountAmount, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-1">
                {{ __('pos.reports_discounts_ratio', ['ratio' => number_format($discountAnalytics->overallDiscountRatePct, 1)]) }}
            </div>
        </div>

        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('pos.reports_discounts_item_title') }}</span>
            <div class="text-[22px] font-bold tabular-nums text-black dark:text-white mt-2">
                Rp {{ number_format($discountAnalytics->itemDiscountAmount, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-1">{{ __('pos.reports_discounts_item_desc') }}</div>
        </div>

        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('pos.reports_discounts_cart_title') }}</span>
            <div class="text-[22px] font-bold tabular-nums text-black dark:text-white mt-2">
                Rp {{ number_format($discountAnalytics->orderDiscountAmount, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-1">{{ __('pos.reports_discounts_cart_desc') }}</div>
        </div>

        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 sm:p-5 shadow-xs">
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('pos.reports_discounts_voucher_title') }}</span>
            <div class="text-[22px] font-bold tabular-nums text-[#007AFF] mt-2">
                Rp {{ number_format($discountAnalytics->voucherDiscountAmount + $discountAnalytics->pointsDiscountAmount, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40 mt-1">{{ __('pos.reports_discounts_voucher_desc') }}</div>
        </div>
    </div>

    {{-- Cashier Manual Discount Rankings --}}
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-black/5 dark:border-white/5">
            <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('pos.reports_discounts_audit_title') }}</h3>
            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('pos.reports_discounts_audit_subtitle') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                        <th class="py-3 px-4">{{ __('pos.reports_col_cashier_name') }}</th>
                        <th class="py-3 px-4 text-center">{{ __('pos.reports_col_total_transactions') }}</th>
                        <th class="py-3 px-4 text-center">{{ __('pos.reports_col_discounted_orders') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('pos.reports_col_discounts_given') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('pos.reports_col_manual_discount') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('pos.reports_col_discount_ratio') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse ($discountAnalytics->cashierDiscountRankings as $cDisc)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors">
                            <td class="py-3 px-4 font-semibold text-black dark:text-white">
                                {{ $cDisc['cashier_name'] }}
                            </td>
                            <td class="py-3 px-4 text-center tabular-nums text-black/70 dark:text-white/70">
                                {{ number_format($cDisc['total_orders'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center font-medium tabular-nums text-[#FF3B30]">
                                {{ number_format($cDisc['discounted_orders_count'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">
                                Rp {{ number_format($cDisc['total_discount_given'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold tabular-nums text-black dark:text-white">
                                Rp {{ number_format($cDisc['manual_discount_amount'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold tabular-nums {{ $cDisc['discount_to_sales_ratio'] > 10 ? 'text-[#FF3B30]' : 'text-black/70 dark:text-white/70' }}">
                                {{ number_format($cDisc['discount_to_sales_ratio'], 1) }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-black/40 dark:text-white/40 text-[13px]">
                                {{ __('pos.reports_discounts_empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
