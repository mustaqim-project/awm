{{-- TAB 3: FITUR & MODE TRANSAKSI 20 INDUSTRI --}}
<div class="space-y-6">
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-2.5 pb-4 border-b border-black/5 dark:border-white/10">
            <div class="w-9 h-9 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center font-bold">
                <i data-lucide="layout-grid" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">{{ __('storefront.backoffice.tab_features.industry_modes_title') }}</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('storefront.backoffice.tab_features.industry_modes_desc') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- FEATURE 1: REQUEST ORDER --}}
            <div class="p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <i data-lucide="file-question" class="w-4 h-4 text-[#007AFF]"></i>
                        <span class="text-[14px] font-bold text-black dark:text-white">{{ __('storefront.backoffice.tab_features.request_order_title') }}</span>
                    </div>
                    <p class="text-[12px] text-black/55 dark:text-white/55 leading-relaxed">
                        {{ __('storefront.backoffice.tab_features.request_order_desc') }}
                    </p>
                    <span class="inline-block text-[10.5px] font-semibold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded mt-1">
                        {{ __('storefront.backoffice.tab_features.request_order_match') }}
                    </span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                    <input type="hidden" name="allow_request_order" value="0">
                    <input type="checkbox" name="allow_request_order" value="1" class="sr-only peer"
                        {{ $setting->allow_request_order ?? true ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#007AFF]">
                    </div>
                </label>
            </div>

            {{-- FEATURE 2: PESANAN TERJADWAL --}}
            <div class="p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <i data-lucide="calendar-clock" class="w-4 h-4 text-[#FF9500]"></i>
                        <span class="text-[14px] font-bold text-black dark:text-white">{{ __('storefront.backoffice.tab_features.scheduled_order_title') }}</span>
                    </div>
                    <p class="text-[12px] text-black/55 dark:text-white/55 leading-relaxed">
                        {{ __('storefront.backoffice.tab_features.scheduled_order_desc') }}
                    </p>
                    <span class="inline-block text-[10.5px] font-semibold text-[#FF9500] bg-[#FF9500]/10 px-2 py-0.5 rounded mt-1">
                        {{ __('storefront.backoffice.tab_features.scheduled_order_match') }}
                    </span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                    <input type="hidden" name="allow_scheduled_order" value="0">
                    <input type="checkbox" name="allow_scheduled_order" value="1" class="sr-only peer"
                        {{ $setting->allow_scheduled_order ?? true ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#FF9500]">
                    </div>
                </label>
            </div>

            {{-- FEATURE 3: CUSTOMER PO & BATCH --}}
            <div class="p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <i data-lucide="layers" class="w-4 h-4 text-[#5856D6]"></i>
                        <span class="text-[14px] font-bold text-black dark:text-white">{{ __('storefront.backoffice.tab_features.customer_po_title') }}</span>
                    </div>
                    <p class="text-[12px] text-black/55 dark:text-white/55 leading-relaxed">
                        {{ __('storefront.backoffice.tab_features.customer_po_desc') }}
                    </p>
                    <span class="inline-block text-[10.5px] font-semibold text-[#5856D6] bg-[#5856D6]/10 px-2 py-0.5 rounded mt-1">
                        {{ __('storefront.backoffice.tab_features.customer_po_match') }}
                    </span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                    <input type="hidden" name="allow_customer_po" value="0">
                    <input type="checkbox" name="allow_customer_po" value="1" class="sr-only peer"
                        {{ $setting->allow_customer_po ?? true ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#5856D6]">
                    </div>
                </label>
            </div>

            {{-- FEATURE 4: RESERVASI & BOOKING JADWAL --}}
            <div class="p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <i data-lucide="calendar-check" class="w-4 h-4 text-[#34C759]"></i>
                        <span class="text-[14px] font-bold text-black dark:text-white">{{ __('storefront.backoffice.tab_features.reservation_booking_title') }}</span>
                    </div>
                    <p class="text-[12px] text-black/55 dark:text-white/55 leading-relaxed">
                        {{ __('storefront.backoffice.tab_features.reservation_booking_desc') }}
                    </p>
                    <span class="inline-block text-[10.5px] font-semibold text-[#248A3D] dark:text-[#30D158] bg-[#34C759]/10 px-2 py-0.5 rounded mt-1">
                        {{ __('storefront.backoffice.tab_features.reservation_booking_match') }}
                    </span>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                    <input type="hidden" name="allow_reservation" value="0">
                    <input type="checkbox" name="allow_reservation" value="1" class="sr-only peer"
                        {{ $setting->allow_reservation ?? true ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]">
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- REKOMENDASI PRESET INDUSTRI --}}
    <div class="bg-black/[0.02] dark:bg-white/[0.02] rounded-[22px] border border-black/5 dark:border-white/10 p-5 space-y-3">
        <div class="flex items-center gap-2 text-black/70 dark:text-white/70">
            <i data-lucide="sparkles" class="w-4 h-4 text-[#FF9500]"></i>
            <span class="text-[13px] font-bold">{{ __('storefront.backoffice.tab_features.optimization_guide_title') }}</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-[12px] text-black/60 dark:text-white/60">
            <div class="p-3 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white block">{{ __('storefront.backoffice.tab_features.preset_retail_title') }}</span>
                <p>{{ __('storefront.backoffice.tab_features.preset_retail_desc') }}</p>
            </div>
            <div class="p-3 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white block">{{ __('storefront.backoffice.tab_features.preset_resto_title') }}</span>
                <p>{{ __('storefront.backoffice.tab_features.preset_resto_desc') }}</p>
            </div>
            <div class="p-3 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white block">{{ __('storefront.backoffice.tab_features.preset_service_title') }}</span>
                <p>{{ __('storefront.backoffice.tab_features.preset_service_desc') }}</p>
            </div>
        </div>
    </div>
</div>
