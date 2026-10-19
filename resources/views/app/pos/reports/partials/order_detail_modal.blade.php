{{-- SLIDE-OVER QUICK-VIEW DETAIL TRANSAKSI POS (BENTO APPLE HIG - 20 INDUSTRI & OJOL DELIVERY) --}}
<div x-data="posOrderDetailModal()"
     @open-pos-order-detail.window="openModal($event.detail.orderId)"
     @keydown.escape.window="closeModal()">

    <template x-teleport="body">
        <div x-show="isOpen"
             class="fixed inset-0 z-[200]"
             aria-labelledby="slide-over-title"
             role="dialog"
             aria-modal="true"
             x-cloak>

            {{-- Backdrop Blur Overlay --}}
            <div x-show="isOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeModal()"
                 class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

            {{-- Slide-Over Panel Container --}}
            <div class="fixed inset-0 z-10 overflow-hidden pointer-events-none">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-6 sm:pl-10">
                <div x-show="isOpen"
                     x-transition:enter="transform transition ease-in-out duration-300 sm:duration-400"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-200 sm:duration-300"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="pointer-events-auto w-screen max-w-2xl bg-white dark:bg-[#1C1C1E] shadow-2xl flex flex-col border-l border-black/10 dark:border-white/10">

                    {{-- 1. MODAL HEADER --}}
                    <div class="p-5 sm:p-6 border-b border-black/10 dark:border-white/10 bg-black/[0.015] dark:bg-white/[0.015] flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] text-white flex items-center justify-center shrink-0 shadow-xs"
                                 :style="'background-color: ' + (orderData?.channel_meta?.badge_bg || '#007AFF')">
                                <i data-lucide="receipt" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-[17px] font-bold text-black dark:text-white font-mono tracking-tight" x-text="orderData?.order_number || '{{ __('pos.reports_modal_loading_bill') }}'"></h2>

                                    {{-- Status Badge --}}
                                    <template x-if="orderData?.status">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                              :class="{
                                                  'bg-[#34C759]/10 text-[#34C759]': orderData.status === 'completed',
                                                  'bg-[#FF9500]/10 text-[#FF9500]': orderData.status === 'partial_refund',
                                                  'bg-[#FF3B30]/10 text-[#FF3B30]': orderData.status === 'refunded' || orderData.status === 'voided',
                                                  'bg-black/5 dark:bg-white/10 text-black/60': orderData.status === 'draft_held' || orderData.status === 'pending'
                                              }"
                                              x-text="orderData.status">
                                        </span>
                                    </template>

                                    {{-- Channel Pill Badge --}}
                                    <template x-if="orderData?.channel_meta?.channel_name">
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold text-white shadow-xs"
                                              :style="'background-color: ' + (orderData.channel_meta.badge_bg || '#007AFF')"
                                              x-text="orderData.channel_meta.channel_name">
                                        </span>
                                    </template>
                                </div>
                                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                                    <span x-text="orderData?.order_date || '-'"></span> • <span x-text="orderData?.created_at || ''"></span>
                                    <template x-if="orderData?.channel_meta?.external_order_ref">
                                        <span class="text-[#007AFF] font-mono ml-1 font-semibold" x-text="'(Ref: ' + orderData.channel_meta.external_order_ref + ')'"></span>
                                    </template>
                                </p>
                            </div>
                        </div>

                        <button type="button"
                                @click="closeModal()"
                                class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/20 text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center transition-all cursor-pointer"
                                title="{{ __('pos.reports_modal_close_title') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    {{-- 2. MODAL BODY (SCROLLABLE BENTO CARDS) --}}
                    <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-5">

                        {{-- Loading Skeleton State --}}
                        <div x-show="isLoading" class="space-y-4 animate-pulse">
                            <div class="grid grid-cols-3 gap-3">
                                <div class="h-20 bg-black/5 dark:bg-white/5 rounded-[14px]"></div>
                                <div class="h-20 bg-black/5 dark:bg-white/5 rounded-[14px]"></div>
                                <div class="h-20 bg-black/5 dark:bg-white/5 rounded-[14px]"></div>
                            </div>
                            <div class="h-28 bg-black/5 dark:bg-white/5 rounded-[16px]"></div>
                            <div class="h-48 bg-black/5 dark:bg-white/5 rounded-[16px]"></div>
                        </div>

                        {{-- Error Alert State --}}
                        <div x-show="errorMessage && !isLoading" class="p-4 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-[13px] flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                                <span x-text="errorMessage"></span>
                            </div>
                            <button type="button" @click="fetchOrderDetail(currentOrderId)" class="px-3 py-1 rounded-[8px] bg-[#FF3B30] text-white text-[11px] font-bold">{{ __('pos.reports_modal_retry') }}</button>
                        </div>

                        {{-- Main Order Content --}}
                        <div x-show="!isLoading && orderData" class="space-y-5">

                            {{-- BENTO ROW 1: QUICK KEY METRICS --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="p-4 rounded-[14px] bg-[#007AFF]/5 border border-[#007AFF]/15">
                                    <span class="text-[11px] font-semibold text-[#007AFF] uppercase tracking-wider">{{ __('pos.reports_modal_net_bill') }}</span>
                                    <div class="text-[20px] font-bold text-black dark:text-white tabular-nums mt-1" x-text="formatCurrency(orderData?.financial?.total_amount)"></div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5" x-text="'{{ __('pos.reports_modal_subtotal_prefix') }} ' + formatCurrency(orderData?.financial?.subtotal)"></div>
                                </div>

                                <div class="p-4 rounded-[14px] bg-[#34C759]/5 border border-[#34C759]/15">
                                    <span class="text-[11px] font-semibold text-[#34C759] uppercase tracking-wider">{{ __('pos.reports_modal_gross_profit') }}</span>
                                    <div class="text-[20px] font-bold text-[#34C759] tabular-nums mt-1" x-text="formatCurrency(orderData?.financial?.gross_profit)"></div>
                                    <div class="text-[11px] text-[#34C759]/80 font-medium mt-0.5" x-text="'{{ __('pos.reports_modal_margin_prefix') }} ' + (orderData?.financial?.gross_margin_percent || 0) + '%'"></div>
                                </div>

                                <div class="p-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5">
                                    <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">{{ __('pos.reports_modal_cogs') }}</span>
                                    <div class="text-[20px] font-bold text-black dark:text-white tabular-nums mt-1" x-text="formatCurrency(orderData?.financial?.total_hpp)"></div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5" x-text="(orderData?.items?.length || 0) + ' {{ __('pos.reports_modal_item_types') }}'"></div>
                                </div>
                            </div>

                            {{-- BENTO ROW 2: ONLINE FOOD DELIVERY FINANCIALS (JIKA MITRA OJOL / STOREFRONT) --}}
                            <template x-if="orderData?.channel_meta?.channel_type === 'online_delivery' || (orderData?.channel_meta?.platform_fee_percent || 0) > 0">
                                <div class="p-4 sm:p-5 rounded-[16px] bg-[#EE4D2D]/5 border border-[#EE4D2D]/20 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-3 h-3 rounded-full bg-[#EE4D2D]"></span>
                                            <h3 class="text-[13px] font-bold text-black dark:text-white uppercase tracking-wider">
                                                {{ __('pos.reports_modal_commission_breakdown') }} <span x-text="orderData?.channel_meta?.channel_name"></span>
                                            </h3>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-[#EE4D2D] text-white" x-text="'MDR ' + orderData.channel_meta.platform_fee_percent + '%'"></span>
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-[12px] pt-1">
                                        <div>
                                            <div class="text-black/50 dark:text-white/50 text-[11px]">{{ __('pos.reports_modal_clean_sales') }}</div>
                                            <div class="font-bold text-black dark:text-white mt-0.5 tabular-nums" x-text="formatCurrency(orderData?.financial?.total_amount)"></div>
                                        </div>
                                        <div>
                                            <div class="text-[#FF3B30] text-[11px]">{{ __('pos.reports_modal_commission_cut') }}</div>
                                            <div class="font-bold text-[#FF3B30] mt-0.5 tabular-nums" x-text="'-' + formatCurrency(orderData?.channel_meta?.platform_fee_amount)"></div>
                                        </div>
                                        <div>
                                            <div class="text-[#34C759] text-[11px] font-semibold">{{ __('pos.reports_modal_payout_disbursed') }}</div>
                                            <div class="font-bold text-[#34C759] mt-0.5 tabular-nums" x-text="formatCurrency(orderData?.channel_meta?.net_merchant_payout)"></div>
                                        </div>
                                        <div>
                                            <div class="text-[#AF52DE] text-[11px] font-semibold">{{ __('pos.reports_modal_real_profit') }}</div>
                                            <div class="font-bold text-[#AF52DE] mt-0.5 tabular-nums" x-text="formatCurrency(orderData?.channel_meta?.real_gross_profit) + ' (' + orderData?.channel_meta?.real_margin_percent + '%)'"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- BENTO ROW 3: INFORMASI KONTEKSTUAL 20 INDUSTRI (AUTO-HIDING) --}}
                            <template x-if="orderData?.industry_meta?.has_industry_data">
                                <div class="p-4 sm:p-5 rounded-[16px] bg-[#007AFF]/5 border border-[#007AFF]/20 space-y-3">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="layers" class="w-4 h-4 text-[#007AFF]"></i>
                                        <h3 class="text-[13px] font-bold text-black dark:text-white uppercase tracking-wider">{{ __('pos.reports_modal_industry_info') }}</h3>
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-[12px]">
                                        {{-- Bengkel Otomotif --}}
                                        <template x-if="orderData?.industry_meta?.vehicle_license_plate">
                                            <div>
                                                <div class="text-black/50 dark:text-white/50 text-[11px]">{{ __('pos.reports_modal_license_plate') }}</div>
                                                <div class="font-mono font-bold text-black dark:text-white mt-0.5" x-text="orderData.industry_meta.vehicle_license_plate"></div>
                                                <template x-if="orderData?.industry_meta?.vehicle_model">
                                                    <div class="text-[10px] text-black/50 dark:text-white/50" x-text="orderData.industry_meta.vehicle_model"></div>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="orderData?.industry_meta?.vehicle_mileage">
                                            <div>
                                                <div class="text-black/50 dark:text-white/50 text-[11px]">{{ __('pos.reports_modal_odometer') }}</div>
                                                <div class="font-bold text-black dark:text-white mt-0.5 tabular-nums" x-text="orderData.industry_meta.vehicle_mileage.toLocaleString('id-ID') + ' km'"></div>
                                            </div>
                                        </template>

                                        <template x-if="orderData?.industry_meta?.technician_name">
                                            <div>
                                                <div class="text-black/50 dark:text-white/50 text-[11px]">{{ __('pos.reports_modal_mechanic') }}</div>
                                                <div class="font-semibold text-black dark:text-white mt-0.5" x-text="orderData.industry_meta.technician_name"></div>
                                            </div>
                                        </template>

                                        {{-- Laundry Kiloan --}}
                                        <template x-if="orderData?.industry_meta?.laundry_weight_kg">
                                            <div>
                                                <div class="text-black/50 dark:text-white/50 text-[11px]">{{ __('pos.reports_modal_laundry_weight') }}</div>
                                                <div class="font-bold text-[#007AFF] mt-0.5 tabular-nums" x-text="orderData.industry_meta.laundry_weight_kg + ' kg'"></div>
                                            </div>
                                        </template>

                                        <template x-if="orderData?.industry_meta?.rack_location">
                                            <div>
                                                <div class="text-black/50 dark:text-white/50 text-[11px]">{{ __('pos.reports_modal_laundry_rack') }}</div>
                                                <div class="font-mono font-bold text-[#FF9500] mt-0.5" x-text="'{{ __('pos.reports_modal_rack_prefix') }} ' + orderData.industry_meta.rack_location"></div>
                                            </div>
                                        </template>

                                        <template x-if="orderData?.industry_meta?.estimated_completion_at">
                                            <div>
                                                <div class="text-black/50 dark:text-white/50 text-[11px]">{{ __('pos.reports_modal_pickup_est') }}</div>
                                                <div class="font-medium text-black dark:text-white mt-0.5" x-text="orderData.industry_meta.estimated_completion_at"></div>
                                            </div>
                                        </template>
                                    </div>

                                    <template x-if="orderData?.industry_meta?.service_notes || orderData?.industry_meta?.notes">
                                        <div class="pt-2 border-t border-black/5 dark:border-white/5 text-[11px] text-black/70 dark:text-white/70">
                                            <span class="font-bold">{{ __('pos.reports_modal_work_notes') }}</span>
                                            <span x-text="orderData.industry_meta.service_notes || orderData.industry_meta.notes"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            {{-- BENTO ROW 4: TRANSACTION & CUSTOMER PROFILE --}}
                            <div class="p-4 sm:p-5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-3">
                                <h3 class="text-[13px] font-bold text-black dark:text-white uppercase tracking-wider">{{ __('pos.reports_modal_trx_profile') }}</h3>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-[12px]">
                                    <div>
                                        <div class="text-black/40 dark:text-white/40 text-[11px]">{{ __('pos.reports_modal_cashier_officer') }}</div>
                                        <div class="font-semibold text-black dark:text-white mt-0.5" x-text="orderData?.cashier?.name || '-'"></div>
                                    </div>
                                    <div>
                                        <div class="text-black/40 dark:text-white/40 text-[11px]">{{ __('pos.reports_modal_branch_outlet') }}</div>
                                        <div class="font-semibold text-black dark:text-white mt-0.5" x-text="orderData?.location?.name || '-'"></div>
                                    </div>
                                    <div>
                                        <div class="text-black/40 dark:text-white/40 text-[11px]">{{ __('pos.reports_modal_customer') }}</div>
                                        <div class="font-semibold text-black dark:text-white mt-0.5" x-text="orderData?.customer?.name || '-'"></div>
                                    </div>
                                    <div>
                                        <div class="text-black/40 dark:text-white/40 text-[11px]">{{ __('pos.reports_modal_table_ref_channel') }}</div>
                                        <div class="font-semibold text-black dark:text-white mt-0.5" x-text="orderData?.table_or_reference || orderData?.channel_meta?.channel_name || '-'"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- BENTO ROW 5: PURCHASED ITEMS TABLE (WITH TOPPINGS & MEDICAL/SERIAL DATA) --}}
                            <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-xs">
                                <div class="p-4 border-b border-black/10 dark:border-white/10 flex items-center justify-between">
                                    <h3 class="text-[13px] font-bold text-black dark:text-white">{{ __('pos.reports_modal_items_detail') }} (<span x-text="orderData?.items?.length || 0"></span>)</h3>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-[12px] border-collapse">
                                        <thead>
                                            <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[10px] font-bold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                                <th class="py-2.5 px-3">{{ __('pos.reports_modal_col_item') }}</th>
                                                <th class="py-2.5 px-3 text-center">{{ __('pos.reports_modal_col_qty') }}</th>
                                                <th class="py-2.5 px-3 text-right">{{ __('pos.reports_modal_col_price') }}</th>
                                                <th class="py-2.5 px-3 text-right">{{ __('pos.reports_modal_col_disc') }}</th>
                                                <th class="py-2.5 px-3 text-right">{{ __('pos.reports_modal_col_subtotal') }}</th>
                                                <th class="py-2.5 px-3 text-right">{{ __('pos.reports_modal_col_hpp_margin') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-black/5 dark:divide-white/5">
                                            <template x-for="item in orderData?.items || []" :key="item.id">
                                                <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015]">
                                                    <td class="py-2.5 px-3">
                                                        <div class="font-semibold text-black dark:text-white" x-text="item.name"></div>
                                                        <div class="text-[10px] text-black/40 dark:text-white/40 flex flex-wrap items-center gap-1.5 mt-0.5">
                                                            <span x-text="item.code"></span>
                                                            <span>•</span>
                                                            <span class="uppercase" x-text="item.type === 'service' ? '{{ __('pos.reports_modal_type_service') }}' : '{{ __('pos.reports_modal_type_goods') }}'"></span>
                                                            <template x-if="item.batch_number">
                                                                <span class="text-[#007AFF] font-mono" x-text="'Batch: ' + item.batch_number"></span>
                                                            </template>
                                                            <template x-if="item.expired_date">
                                                                <span class="text-[#FF9500]" x-text="'Exp: ' + item.expired_date"></span>
                                                            </template>
                                                            <template x-if="item.serial_number">
                                                                <span class="text-[#AF52DE] font-mono" x-text="'SN: ' + item.serial_number"></span>
                                                            </template>
                                                        </div>
                                                        <template x-if="item.dosage_instructions">
                                                            <div class="text-[10px] text-[#007AFF] font-medium mt-0.5" x-text="'💊 {{ __('pos.reports_modal_dosage_prefix') }}: ' + item.dosage_instructions"></div>
                                                        </template>
                                                        <template x-if="item.modifiers && item.modifiers.length > 0">
                                                            <div class="mt-1 flex flex-wrap gap-1">
                                                                <template x-for="mod in item.modifiers" :key="mod.id">
                                                                    <span class="px-1.5 py-0.5 rounded-[4px] bg-black/5 dark:bg-white/10 text-[9px] font-medium" x-text="'+ ' + mod.name + (mod.price > 0 ? ' (' + formatCurrency(mod.price) + ')' : '')"></span>
                                                                </template>
                                                            </div>
                                                        </template>
                                                        <template x-if="item.notes">
                                                            <div class="text-[10px] italic text-[#FF9500] mt-0.5" x-text="'{{ __('pos.reports_modal_note_prefix') }}: ' + item.notes"></div>
                                                        </template>
                                                    </td>
                                                    <td class="py-2.5 px-3 text-center font-bold tabular-nums text-black dark:text-white" x-text="item.quantity"></td>
                                                    <td class="py-2.5 px-3 text-right tabular-nums text-black/70 dark:text-white/70" x-text="formatCurrency(item.unit_price)"></td>
                                                    <td class="py-2.5 px-3 text-right tabular-nums text-[#FF3B30]" x-text="item.discount_amount > 0 ? '-' + formatCurrency(item.discount_amount) : '-'"></td>
                                                    <td class="py-2.5 px-3 text-right font-bold tabular-nums text-black dark:text-white" x-text="formatCurrency(item.total_price)"></td>
                                                    <td class="py-2.5 px-3 text-right tabular-nums">
                                                        <div class="text-black/60 dark:text-white/60 text-[11px]" x-text="'{{ __('pos.reports_modal_hpp_prefix') }}: ' + formatCurrency(item.total_hpp)"></div>
                                                        <div class="text-[#34C759] font-semibold text-[10px]" x-text="item.margin_percent + '% {{ __('pos.reports_modal_margin_suffix') }}'"></div>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- BENTO ROW 6: PAYMENTS & FINANCIAL SUMMARY --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                {{-- Split Payment Breakdown --}}
                                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-3">
                                    <h3 class="text-[13px] font-bold text-black dark:text-white uppercase tracking-wider">{{ __('pos.reports_modal_payment_methods') }}</h3>
                                    <div class="space-y-2">
                                        <template x-for="pmt in orderData?.payments || []" :key="pmt.id">
                                            <div class="p-2.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 flex items-center justify-between text-[12px]">
                                                <div>
                                                    <div class="font-semibold text-black dark:text-white" x-text="pmt.method_label"></div>
                                                    <div class="text-[10px] text-black/40 dark:text-white/40" x-text="pmt.reference_number ? 'Ref: ' + pmt.reference_number : (pmt.paid_at || '-')"></div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="font-bold tabular-nums text-black dark:text-white" x-text="formatCurrency(pmt.amount)"></div>
                                                    <template x-if="pmt.fee_amount > 0">
                                                        <div class="text-[10px] text-black/40" x-text="'Fee: ' + formatCurrency(pmt.fee_amount)"></div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="pt-2 border-t border-black/5 dark:border-white/5 flex justify-between text-[12px]">
                                        <span class="text-black/50 dark:text-white/50">{{ __('pos.reports_modal_money_received') }}</span>
                                        <span class="font-semibold tabular-nums text-black dark:text-white" x-text="formatCurrency(orderData?.financial?.paid_amount)"></span>
                                    </div>
                                    <div class="flex justify-between text-[12px]">
                                        <span class="text-black/50 dark:text-white/50">{{ __('pos.reports_modal_cash_change') }}</span>
                                        <span class="font-bold tabular-nums text-[#34C759]" x-text="formatCurrency(orderData?.financial?.change_amount)"></span>
                                    </div>
                                </div>

                                {{-- Accounting & Tax Breakdown --}}
                                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-2 text-[12px]">
                                    <h3 class="text-[13px] font-bold text-black dark:text-white uppercase tracking-wider pb-1">{{ __('pos.reports_modal_fin_calc') }}</h3>

                                    <div class="flex justify-between text-black/70 dark:text-white/70">
                                        <span>{{ __('pos.reports_modal_summary_subtotal') }}</span>
                                        <span class="tabular-nums" x-text="formatCurrency(orderData?.financial?.subtotal)"></span>
                                    </div>

                                    <template x-if="orderData?.financial?.order_discount > 0">
                                        <div class="flex justify-between text-[#FF3B30]">
                                            <span>{{ __('pos.reports_modal_summary_order_disc') }}</span>
                                            <span class="tabular-nums" x-text="'-' + formatCurrency(orderData?.financial?.order_discount)"></span>
                                        </div>
                                    </template>

                                    <template x-if="orderData?.financial?.voucher_discount > 0">
                                        <div class="flex justify-between text-[#FF3B30]">
                                            <span x-text="'{{ __('pos.reports_modal_summary_voucher') }} (' + (orderData?.financial?.voucher_code || 'Promo') + '):'"></span>
                                            <span class="tabular-nums" x-text="'-' + formatCurrency(orderData?.financial?.voucher_discount)"></span>
                                        </div>
                                    </template>

                                    <template x-if="orderData?.financial?.points_discount > 0">
                                        <div class="flex justify-between text-[#FF3B30]">
                                            <span>{{ __('pos.reports_modal_summary_points_disc') }}</span>
                                            <span class="tabular-nums" x-text="'-' + formatCurrency(orderData?.financial?.points_discount)"></span>
                                        </div>
                                    </template>

                                    <div class="flex justify-between text-black/70 dark:text-white/70">
                                        <span x-text="'{{ __('pos.reports_modal_summary_tax') }} (' + (orderData?.financial?.tax_percentage || 0) + '%):'"></span>
                                        <span class="tabular-nums" x-text="formatCurrency(orderData?.financial?.tax_amount)"></span>
                                    </div>

                                    <template x-if="orderData?.financial?.service_charge_amount > 0">
                                        <div class="flex justify-between text-black/70 dark:text-white/70">
                                            <span x-text="'{{ __('pos.reports_modal_summary_service_charge') }} (' + (orderData?.financial?.service_charge_percentage || 0) + '%):'"></span>
                                            <span class="tabular-nums" x-text="formatCurrency(orderData?.financial?.service_charge_amount)"></span>
                                        </div>
                                    </template>

                                    <template x-if="orderData?.financial?.rounding_amount != 0">
                                        <div class="flex justify-between text-black/70 dark:text-white/70">
                                            <span>{{ __('pos.reports_modal_summary_rounding') }}</span>
                                            <span class="tabular-nums" x-text="formatCurrency(orderData?.financial?.rounding_amount)"></span>
                                        </div>
                                    </template>

                                    <div class="pt-2 border-t border-black/10 dark:border-white/10 flex justify-between text-[14px] font-bold text-black dark:text-white">
                                        <span>{{ __('pos.reports_modal_summary_final') }}</span>
                                        <span class="text-[#007AFF] tabular-nums" x-text="formatCurrency(orderData?.financial?.total_amount)"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- BENTO ROW 7: AUDIT TRAIL & ANTI-FRAUD LOGS --}}
                            <div class="p-4 rounded-[14px] bg-black/[0.015] dark:bg-white/[0.015] border border-black/5 dark:border-white/5 space-y-2 text-[11px]">
                                <div class="font-bold text-black/60 dark:text-white/60 uppercase tracking-wider">{{ __('pos.reports_modal_audit_log') }}</div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-black/70 dark:text-white/70">
                                    <div>
                                        <span class="text-black/40">{{ __('pos.reports_modal_info_receipt_print') }}</span>
                                        <span class="font-bold" :class="{'text-[#FF3B30]': (orderData?.audit?.printed_count || 0) > 1}" x-text="(orderData?.audit?.printed_count || 0) + ' {{ __('pos.reports_modal_printed_times') }}'"></span>
                                    </div>
                                    <div>
                                        <span class="text-black/40">{{ __('pos.reports_modal_gateway_status') }}</span>
                                        <span class="font-medium" x-text="orderData?.audit?.sync_status || '-'"></span>
                                    </div>
                                    <div>
                                        <span class="text-black/40">{{ __('pos.reports_modal_info_shift_reg') }}</span>
                                        <span class="font-medium" x-text="orderData?.shift?.shift_number || '-'"></span>
                                    </div>
                                </div>

                                <template x-if="orderData?.audit?.void_reason">
                                    <div class="mt-2 p-2 rounded-[8px] bg-[#FF3B30]/10 text-[#FF3B30]">
                                        <span class="font-bold">{{ __('pos.reports_modal_info_void_reason') }}</span> <span x-text="orderData?.audit?.void_reason"></span>
                                        <span class="text-[10px] ml-1" x-text="'({{ __('pos.reports_modal_by_prefix') }}: ' + (orderData?.audit?.voided_by || '{{ __('pos.reports_modal_fallback_supervisor') }}') + ' @ ' + (orderData?.audit?.voided_at || '') + ')'"></span>
                                    </div>
                                </template>

                                <template x-if="orderData?.audit?.refund_reason">
                                    <div class="mt-2 p-2 rounded-[8px] bg-[#FF9500]/10 text-[#FF9500]">
                                        <span class="font-bold">{{ __('pos.reports_modal_info_refund_reason') }}</span> <span x-text="orderData?.audit?.refund_reason"></span>
                                        <span class="text-[10px] ml-1" x-text="'({{ __('pos.reports_modal_by_prefix') }}: ' + (orderData?.audit?.refunded_by || '{{ __('pos.reports_modal_fallback_supervisor') }}') + ' @ ' + (orderData?.audit?.refunded_at || '') + ')'"></span>
                                    </div>
                                </template>
                            </div>

                        </div>
                    </div>

                    {{-- 3. MODAL FOOTER ACTIONS --}}
                    <div class="p-4 sm:p-5 border-t border-black/10 dark:border-white/10 bg-black/[0.015] dark:bg-white/[0.015] flex flex-wrap items-center justify-between gap-3">
                        <button type="button"
                                @click="closeModal()"
                                class="px-4 py-2 rounded-[10px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/20 transition-all cursor-pointer">
                            {{ __('pos.reports_modal_close') }}
                        </button>

                        <div class="flex items-center gap-2">
                            <template x-if="orderData?.id">
                                <a :href="'/pos/orders/' + orderData.id"
                                   target="_blank"
                                   class="px-4 py-2 rounded-[10px] text-[13px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 transition-all flex items-center gap-1.5">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                    <span>{{ __('pos.reports_modal_full_order_page') }}</span>
                                </a>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
        </div>
    </template>
</div>

<script>
function posOrderDetailModal() {
    return {
        isOpen: false,
        isLoading: false,
        errorMessage: '',
        currentOrderId: null,
        orderData: null,

        openModal(orderId) {
            if (!orderId) return;
            this.currentOrderId = orderId;
            this.isOpen = true;
            this.fetchOrderDetail(orderId);
            document.body.classList.add('overflow-hidden');
        },

        closeModal() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        async fetchOrderDetail(orderId) {
            this.isLoading = true;
            this.errorMessage = '';
            this.orderData = null;

            try {
                const response = await fetch(`/pos/reports/orders/${orderId}/detail`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('{{ __('pos.reports_modal_error_fetch') }}');
                }

                const result = await response.json();
                if (result.success && result.data) {
                    this.orderData = result.data;
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                } else {
                    this.errorMessage = result.message || '{{ __('pos.reports_modal_err_invalid') }}';
                }
            } catch (err) {
                this.errorMessage = err.message || '{{ __('pos.reports_modal_err_network') }}';
            } finally {
                this.isLoading = false;
            }
        },

        formatCurrency(value) {
            const num = parseFloat(value) || 0;
            return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }
    };
}
</script>
