{{--
    COOCA Privacy & Cookie Consent Banner
    Compliant with Indonesian Law (UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi / UU PDP)
--}}
<div x-data="{
        showBanner: false,
        init() {
            var consent = localStorage.getItem('cooca_consent_status');
            if (!consent) {
                setTimeout(() => { this.showBanner = true; }, 1500);
            }
        },
        acceptAll() {
            localStorage.setItem('cooca_consent_status', 'accepted');
            this.showBanner = false;
            if (typeof window.initMarketingPixels === 'function') {
                window.initMarketingPixels();
            }
        },
        rejectOptional() {
            localStorage.setItem('cooca_consent_status', 'essential');
            this.showBanner = false;
        }
     }"
     x-show="showBanner"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-6"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-6"
     x-cloak
     class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-6 sm:bottom-6 z-50 max-w-md bg-white/95 dark:bg-[#181B26]/95 backdrop-blur-xl border border-slate-200/80 dark:border-white/10 shadow-[0_16px_40px_rgba(0,0,0,0.2)] rounded-2xl p-4 sm:p-5 text-slate-800 dark:text-slate-100 print:hidden">

    <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-5h2v5h-2zm0-7v-2h2v2h-2z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white mb-1">
                {{ __('public_marketing.cookie_title') }}
            </h4>
            <p class="text-[11.5px] sm:text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-3">
                {{ __('public_marketing.cookie_desc') }}
            </p>
            <div class="flex items-center gap-2">
                <button type="button"
                        @click="acceptAll()"
                        class="px-3.5 py-1.5 rounded-lg bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs tracking-tight shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-all min-h-[34px]">
                    {{ __('public_marketing.cookie_accept') }}
                </button>
                <button type="button"
                        @click="rejectOptional()"
                        class="px-3.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/15 text-slate-700 dark:text-slate-200 font-medium text-xs tracking-tight transition-all min-h-[34px]">
                    {{ __('public_marketing.cookie_decline') }}
                </button>
                <a href="{{ route('public.privacy') }}"
                   class="text-[11px] text-slate-500 dark:text-slate-400 hover:text-[#007AFF] underline ml-auto">
                    {{ __('public_marketing.footer_privacy') }}
                </a>
            </div>
        </div>
    </div>
</div>

