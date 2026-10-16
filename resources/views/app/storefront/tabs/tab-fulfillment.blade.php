{{-- TAB 2: METODE PEMENUHAN & PENGANTARAN --}}
<div class="space-y-6">
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-5">
        <div class="flex items-center gap-2.5 pb-4 border-b border-black/5 dark:border-white/10">
            <div class="w-9 h-9 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold">
                <i data-lucide="package-check" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">{{ __('storefront.backoffice.tab_fulfillment.handover_options_title') }}</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('storefront.backoffice.tab_fulfillment.handover_options_desc') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- OPTION: PICKUP --}}
            <div class="p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <i data-lucide="store" class="w-4 h-4 text-[#007AFF]"></i>
                        <span class="text-[14px] font-bold text-black dark:text-white">{{ __('storefront.backoffice.tab_fulfillment.pickup_title') }}</span>
                    </div>
                    <p class="text-[12px] text-black/55 dark:text-white/55 leading-relaxed">
                        {{ __('storefront.backoffice.tab_fulfillment.pickup_desc') }}
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                    <input type="hidden" name="allow_pickup" value="0">
                    <input type="checkbox" name="allow_pickup" value="1" class="sr-only peer"
                        {{ $setting->allow_pickup ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]">
                    </div>
                </label>
            </div>

            {{-- OPTION: DELIVERY --}}
            <div class="p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <i data-lucide="truck" class="w-4 h-4 text-[#FF9500]"></i>
                        <span class="text-[14px] font-bold text-black dark:text-white">{{ __('storefront.backoffice.tab_fulfillment.delivery_title') }}</span>
                    </div>
                    <p class="text-[12px] text-black/55 dark:text-white/55 leading-relaxed">
                        {{ __('storefront.backoffice.tab_fulfillment.delivery_desc') }}
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-0.5">
                    <input type="hidden" name="allow_delivery" value="0">
                    <input type="checkbox" name="allow_delivery" value="1" class="sr-only peer"
                        {{ $setting->allow_delivery ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-black/20 dark:bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]">
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- INTEGRASI TARIF & ONGKIR PENGIRIMAN --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                    <h4 class="text-[14px] font-bold text-black dark:text-white">{{ __('storefront.backoffice.tab_fulfillment.shipping_rules_title') }}</h4>
                </div>
                <p class="text-[12px] text-black/55 dark:text-white/55 max-w-xl">
                    {{ __('storefront.backoffice.tab_fulfillment.shipping_rules_desc') }}
                </p>
            </div>
            <a href="{{ route('storefront.shipping.index') }}"
                class="h-9 px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-xs font-semibold flex items-center justify-center gap-1.5 transition self-start sm:self-auto shrink-0 active:scale-[0.98]">
                <span>{{ __('storefront.backoffice.tab_fulfillment.manage_shipping_btn') }}</span>
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>
</div>
