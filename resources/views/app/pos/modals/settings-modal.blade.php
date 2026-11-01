<!-- ===================================================== -->
    <!-- 5B. MODAL: POS SETTINGS & HARDWARE HUB (Bento Apple Sheet) -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showPosSettingsModal" x-cloak
            role="dialog"
            aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showPosSettingsModal = false">
            <!-- Standalone Backdrop -->
            <div x-show="showPosSettingsModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showPosSettingsModal = false"></div>

            <!-- Centered Dialog Wrapper -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showPosSettingsModal = false">
                <div class="pos-modal-panel relative w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[90vh] text-black dark:text-white"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95">

            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.02] dark:bg-white/[0.02]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-base sm:text-lg text-black dark:text-white leading-tight">{{ __('pos.pos_settings_devices_title') }}</h3>
                        <p class="text-xs text-black/50 dark:text-white/50">{{ __('pos.pos_settings_devices_subtitle') }}</p>
                    </div>
                </div>
                <button type="button" @click="showPosSettingsModal = false"
                    class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/20 active:scale-95 flex items-center justify-center text-black/60 dark:text-white/60 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Bento Grid Content -->
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    
                    <!-- 1. Pengaturan Printer Thermal -->
                    <a href="{{ route('pos.printers.index') }}"
                        class="group p-4 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 transition-all flex flex-col justify-between hover:scale-[1.01] active:scale-[0.99]">
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <div class="w-9 h-9 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.75A2.25 2.25 0 0015 1.5H9a2.25 2.25 0 00-2.25 2.25v3.456" />
                                    </svg>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#007AFF]/15 text-[#007AFF] uppercase tracking-wide">Hardware</span>
                            </div>
                            <h4 class="font-semibold text-sm text-black dark:text-white group-hover:text-[#007AFF] transition">{{ __('pos.thermal_printer_settings_title') }}</h4>
                            <p class="text-xs text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                {{ __('pos.thermal_printer_settings_desc') }}
                            </p>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs text-[#007AFF] font-medium">
                            <span>{{ __('pos.open_configuration_link') }}</span>
                            <span class="group-hover:translate-x-0.5 transition">→</span>
                        </div>
                    </a>

                    <!-- 2. Buka Laci Kas (Cash Drawer) Manual Pop -->
                    <button type="button" @click="showPosSettingsModal = false; promptManualDrawerPop()"
                        class="group p-4 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 transition-all flex flex-col justify-between text-left hover:scale-[1.01] active:scale-[0.99]">
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <div class="w-9 h-9 rounded-xl bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                    </svg>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#34C759]/15 text-[#34C759] uppercase tracking-wide">{{ __('pos.badge_quick_action') }}</span>
                            </div>
                            <h4 class="font-semibold text-sm text-black dark:text-white group-hover:text-[#34C759] transition">{{ __('pos.open_cash_drawer_card_title') }}</h4>
                            <p class="text-xs text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                {{ __('pos.open_cash_drawer_card_desc') }}
                            </p>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs text-[#34C759] font-medium">
                            <span>{{ __('pos.open_drawer_now_btn') }}</span>
                            <i data-lucide="zap" class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition"></i>
                        </div>
                    </button>

                    <!-- 3. Kitchen Display System (KDS) -->
                    @if (\App\Support\Context::hasPermission('pos.kitchen') && (isset($business) && $business->hasDineInFeature()))
                        <a href="{{ route('pos.kitchen.index') }}" target="_blank"
                            class="group p-4 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 transition-all flex flex-col justify-between hover:scale-[1.01] active:scale-[0.99]">
                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3" />
                                        </svg>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#FF9500]/15 text-[#FF9500] uppercase tracking-wide">{{ __('pos.badge_kitchen_bar') }}</span>
                                </div>
                                <h4 class="font-semibold text-sm text-black dark:text-white group-hover:text-[#FF9500] transition">{{ __('pos.kitchen_display_bar_card_title') }}</h4>
                                <p class="text-xs text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                    {{ __('pos.kitchen_display_bar_card_desc') }}
                                </p>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs text-[#FF9500] font-medium">
                                <span>{{ __('pos.open_kds_screen_link') }}</span>
                                <span class="group-hover:translate-x-0.5 transition">↗</span>
                            </div>
                        </a>
                    @endif

                    <!-- 3.1 Mode Paperless Auto KDS -->
                    @if ($business && ($business->isFoodIndustry() || $business->hasDineInFeature()))
                        <div class="p-4 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693l-1.57-.393m15.6 0l1.4 3.5a2.25 2.25 0 01-2.09 3.085H5.89a2.25 2.25 0 01-2.09-3.085l1.4-3.5" />
                                        </svg>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide"
                                        :class="posAutoSendKds ? 'bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50'"
                                        x-text="posAutoSendKds ? '{{ __('pos.active') }}' : '{{ __('pos.inactive') }}'"></span>
                                </div>
                                <h4 class="font-semibold text-sm text-black dark:text-white">{{ __('pos.auto_send_kds_card_title') }}</h4>
                                <p class="text-xs text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                    {{ __('pos.auto_send_kds_card_desc') }}
                                </p>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                                <span class="text-xs font-medium text-black/70 dark:text-white/70" x-text="posAutoSendKds ? '{{ __('pos.paperless_feature_active') }}' : '{{ __('pos.paperless_feature_inactive') }}'"></span>
                                <button type="button" @click="toggleAutoKds()"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                    :class="posAutoSendKds ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20'">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="posAutoSendKds ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- 4. Rekap Shift Kasir -->
                    <a href="{{ route('pos.shifts.index') }}"
                        class="group p-4 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 transition-all flex flex-col justify-between hover:scale-[1.01] active:scale-[0.99]">
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <div class="w-9 h-9 rounded-xl bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#5856D6]/15 text-[#5856D6] uppercase tracking-wide">{{ __('pos.badge_reconciliation') }}</span>
                            </div>
                            <h4 class="font-semibold text-sm text-black dark:text-white group-hover:text-[#5856D6] transition">{{ __('pos.shift_recap_card_title') }}</h4>
                            <p class="text-xs text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                {{ __('pos.shift_recap_card_desc') }}
                            </p>
                        </div>
                        <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs text-[#5856D6] font-medium">
                            <span>{{ __('pos.view_shift_history_link') }}</span>
                            <span class="group-hover:translate-x-0.5 transition">→</span>
                        </div>
                    </a>

                    <!-- 5. Riwayat Transaksi & Order -->
                    @if (\App\Support\Context::hasPermission('pos.orders'))
                        <a href="{{ route('pos.orders.index') }}"
                            class="group p-4 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 transition-all flex flex-col justify-between hover:scale-[1.01] active:scale-[0.99]">
                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#AF52DE]/15 text-[#AF52DE] uppercase tracking-wide">{{ __('pos.badge_transaction_audit') }}</span>
                                </div>
                                <h4 class="font-semibold text-sm text-black dark:text-white group-hover:text-[#AF52DE] transition">{{ __('pos.transaction_void_list_title') }}</h4>
                                <p class="text-xs text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                    {{ __('pos.transaction_void_list_desc') }}
                                </p>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs text-[#AF52DE] font-medium">
                                <span>{{ __('pos.open_transactions_link') }}</span>
                                <span class="group-hover:translate-x-0.5 transition">→</span>
                            </div>
                        </a>
                    @endif

                    <!-- 6. Denah Meja & QR Self-Order -->
                    @if (\App\Support\Context::hasPermission('pos.tables') && (isset($business) && $business->hasDineInFeature()))
                        <a href="{{ route('pos.tables.index') }}"
                            class="group p-4 rounded-2xl bg-black/[0.03] dark:bg-white/[0.04] hover:bg-black/[0.06] dark:hover:bg-white/[0.08] border border-black/5 dark:border-white/10 transition-all flex flex-col justify-between hover:scale-[1.01] active:scale-[0.99]">
                            <div>
                                <div class="flex items-center justify-between mb-2.5">
                                    <div class="w-9 h-9 rounded-xl bg-[#00C7BE]/10 text-[#00C7BE] flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                        </svg>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#00C7BE]/15 text-[#00C7BE] uppercase tracking-wide">{{ __('pos.badge_fnb_dine_in') }}</span>
                                </div>
                                <h4 class="font-semibold text-sm text-black dark:text-white group-hover:text-[#00C7BE] transition">{{ __('pos.table_mgmt_qr_title') }}</h4>
                                <p class="text-xs text-black/60 dark:text-white/60 mt-1 leading-relaxed">
                                    {{ __('pos.table_mgmt_qr_desc') }}
                                </p>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-xs text-[#00C7BE] font-medium">
                                <span>{{ __('pos.open_table_qr_btn') }}</span>
                                <span class="group-hover:translate-x-0.5 transition">→</span>
                            </div>
                        </a>
                    @endif

                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3.5 border-t border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.02] dark:bg-white/[0.02]">
                <div class="flex items-center gap-2 text-xs text-black/50 dark:text-white/50">
                    <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                    <span>{{ __('pos.cashier_label_colon') }} <strong class="text-black/70 dark:text-white/70">{{ auth()->user()->name ?? __('pos.cashier') }}</strong></span>
                </div>
                <button type="button" @click="showPosSettingsModal = false"
                    class="h-9 px-4 rounded-xl bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] active:scale-95 text-xs font-semibold transition">
                    {{ __('pos.close') }}
                </button>
            </div>
        </div>
    </div>
</template>
