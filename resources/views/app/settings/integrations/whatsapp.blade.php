{{--
    =============================================================================
    COOCA SETTINGS HUB - INTEGRASI WHATSAPP GATEWAY & STRUK OTOMATIS
    Cluster 4: Unified Settings Hub (Bento Apple HIG Standard)
    =============================================================================
--}}

<div class="space-y-6">

    {{-- 1. HEADER COCKPIT INFORMASI & STATUS LAYANAN --}}
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 transition-colors">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-[16px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20 flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="message-square" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight">
                            {{ __('whatsapp.title') }}
                        </h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                            Cloud API v26.0
                        </span>
                    </div>
                    <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">
                        {{ __('whatsapp.subtitle', ['business' => $business->name]) }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3 py-1.5 rounded-full text-[11.5px] font-bold inline-flex items-center gap-1.5"
                    :class="metaAccount?.is_active ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/55 dark:text-white/55 border border-black/10 dark:border-white/10'">
                    <span class="w-2 h-2 rounded-full" :class="metaAccount?.is_active ? 'bg-[#34C759]' : 'bg-[#FF9500]'"></span>
                    <span x-text="metaAccount?.is_active ? '{{ __('whatsapp.connected_active') }}' : '{{ __('whatsapp.disconnected') }}'"></span>
                </span>
                <a href="{{ route('whatsapp.logs.index') }}"
                    class="min-h-[38px] px-3.5 rounded-[10px] text-[12px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.10] active:scale-[0.98] transition-all flex items-center gap-1.5">
                    <i data-lucide="history" class="w-3.5 h-3.5"></i>
                    <span>{{ __('whatsapp.tab_logs') }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- WABA Architecture & Delivery Banner (Bento Card) -->
    <div class="rounded-[20px] bg-[#34C759]/[0.06] dark:bg-[#34C759]/[0.08] border border-[#34C759]/20 p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-[8px] bg-[#34C759] text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-[14.5px] font-bold text-black dark:text-white">
                        {{ __('settings.waba_channel_architecture_title') }}
                    </h3>
                    <p class="text-[12px] text-black/60 dark:text-white/60">
                        {{ __('settings.waba_channel_architecture_desc') }}
                    </p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25 shrink-0">
                {{ __('settings.waba_engine_badge') }}
            </span>
        </div>
    </div>

    {{-- 2. DUA KOLOM: FORM KONFIGURASI GATEWAY & LIVE PHONE PREVIEW --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- KOLOM KIRI (7 COLS): META CLOUD API & PENGATURAN STRUK --}}
        <div class="lg:col-span-7 space-y-6">

            <!-- 2.1 META CLOUD API OFFICIAL & EMBEDDED SIGNUP CARD -->
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 space-y-4 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3.5 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[13px] bg-[#007AFF]/12 text-[#007AFF] border border-[#007AFF]/20 flex items-center justify-center shrink-0 shadow-xs">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">{{ __('whatsapp.meta_official_title') }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('whatsapp.meta_official_desc') }}</p>
                        </div>
                    </div>

                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] inline-flex items-center gap-1 shrink-0 self-start sm:self-auto">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        <span>1.000 percakapan gratis/bln</span>
                    </span>
                </div>

                <!-- Active Connected Account Details -->
                <template x-if="metaAccount?.is_active">
                    <div class="space-y-4 pt-1">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 text-[12.5px]">
                            <div>
                                <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">{{ __('whatsapp.verified_name_label') }}</span>
                                <p class="font-bold text-black dark:text-white text-[13.5px] mt-0.5" x-text="metaAccount.verified_name || '-'"></p>
                            </div>
                            <div>
                                <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">{{ __('whatsapp.phone_number_label') }}</span>
                                <p class="font-mono font-bold text-[#007AFF] text-[13.5px] mt-0.5 tabular-nums" x-text="metaAccount.display_phone_number || '-'"></p>
                            </div>
                            <div>
                                <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">{{ __('whatsapp.quality_rating_label') }}</span>
                                <p class="font-bold text-[#34C759] mt-0.5 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                                    <span x-text="metaAccount.quality_rating || 'GREEN'"></span>
                                </p>
                            </div>
                            <div>
                                <span class="text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">{{ __('whatsapp.messaging_limit_label') }}</span>
                                <p class="font-bold text-black dark:text-white mt-0.5" x-text="metaAccount.messaging_limit_tier || 'TIER_50'"></p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                            <span class="text-[11.5px] text-black/50 dark:text-white/50 font-mono truncate max-w-[280px]">
                                {{ __('whatsapp.waba_id_label') }}: <span x-text="metaAccount.waba_id"></span>
                            </span>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('whatsapp.broadcast.index') }}"
                                    class="min-h-[38px] px-3.5 rounded-[10px] text-[12px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-sm">
                                    <i data-lucide="megaphone" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('whatsapp.tab_broadcast') }}</span>
                                </a>
                                <button type="button" @click="disconnectMeta()"
                                    class="min-h-[38px] px-3.5 rounded-[10px] text-[12px] font-bold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all flex items-center justify-center cursor-pointer">
                                    <i data-lucide="unlink" class="w-3.5 h-3.5 mr-1"></i>
                                    <span>{{ __('whatsapp.disconnect_btn') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Connect Action (When not connected - 1-Click Embedded Signup) -->
                <template x-if="!metaAccount?.is_active">
                    <div class="space-y-4 pt-1">
                        <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                            {{ __('whatsapp.embedded_signup_instruction') }}
                        </p>

                        @if (\App\Support\Context::hasPermission('whatsapp.manage') || \App\Support\Context::hasPermission('settings.edit'))
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3 pt-0.5">
                                <button type="button" @click="launchEmbeddedSignup()" :disabled="embeddedLoading"
                                    class="min-h-[44px] px-5 rounded-[12px] bg-[#1877F2] hover:bg-[#166FE5] text-white font-semibold text-[13px] inline-flex items-center justify-center gap-2 shadow-sm active:scale-[0.98] transition-all disabled:opacity-50 w-full sm:w-auto cursor-pointer">
                                    <i data-lucide="loader-2" x-show="embeddedLoading" class="w-4 h-4 animate-spin shrink-0"></i>
                                    <i data-lucide="shield-check" x-show="!embeddedLoading" class="w-4 h-4 shrink-0"></i>
                                    <span class="whitespace-nowrap" x-text="embeddedLoading ? '{{ __('whatsapp.connecting_meta') }}' : '{{ __('whatsapp.connect_meta_btn') }}'"></span>
                                </button>
                            </div>
                        @endif

                        <!-- Quiet Informational Stepper -->
                        <div class="pt-3 border-t border-black/5 dark:border-white/10">
                            <div class="flex overflow-x-auto sm:grid sm:grid-cols-3 gap-3 no-scrollbar pb-1 snap-x snap-mandatory">
                                <div class="snap-start flex items-start gap-2.5 shrink-0 w-[220px] sm:w-auto">
                                    <span class="w-5 h-5 rounded-full bg-[#1877F2]/10 text-[#1877F2] text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">1</span>
                                    <div class="space-y-0.5 min-w-0">
                                        <p class="font-semibold text-black dark:text-white text-[12px] leading-tight">{{ __('whatsapp.step_1_title') }}</p>
                                        <p class="text-[11px] text-black/55 dark:text-white/55 leading-normal">{{ __('whatsapp.step_1_desc') }}</p>
                                    </div>
                                </div>
                                <div class="snap-start flex items-start gap-2.5 shrink-0 w-[220px] sm:w-auto">
                                    <span class="w-5 h-5 rounded-full bg-[#1877F2]/10 text-[#1877F2] text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">2</span>
                                    <div class="space-y-0.5 min-w-0">
                                        <p class="font-semibold text-black dark:text-white text-[12px] leading-tight">{{ __('whatsapp.step_2_title') }}</p>
                                        <p class="text-[11px] text-black/55 dark:text-white/55 leading-normal">{{ __('whatsapp.step_2_desc') }}</p>
                                    </div>
                                </div>
                                <div class="snap-start flex items-start gap-2.5 shrink-0 w-[220px] sm:w-auto">
                                    <span class="w-5 h-5 rounded-full bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] text-[11px] font-bold flex items-center justify-center shrink-0 mt-0.5">3</span>
                                    <div class="space-y-0.5 min-w-0">
                                        <p class="font-semibold text-[#248A3D] dark:text-[#30D158] text-[12px] leading-tight">{{ __('whatsapp.step_3_title') }}</p>
                                        <p class="text-[11px] text-black/55 dark:text-white/55 leading-normal">{{ __('whatsapp.step_3_desc') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Feedback Error & Success -->
                        <div x-show="embeddedError" x-cloak x-transition class="p-3 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            <span x-text="embeddedError"></span>
                        </div>

                        <div x-show="embeddedSuccess" x-cloak x-transition class="p-3 rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/30 text-[12px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                            <span x-text="embeddedSuccess"></span>
                        </div>
                    </div>
                </template>

                <!-- Status Layanan WhatsApp Toko (Master Toggle) -->
                <div class="pt-2 border-t border-black/5 dark:border-white/10">
                    <div class="flex items-center justify-between p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                        <div class="pr-2">
                            <p class="text-[13px] font-bold text-black dark:text-white">{{ __('whatsapp.service_status_title') }}</p>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5">{{ __('whatsapp.service_status_desc') }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" name="is_active" value="1" @change="toggleServiceStatus($event)" class="sr-only peer"
                                {{ ($waSession?->is_active ?? true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-black/[0.12] dark:bg-white/[0.15] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- 2.2 KONFIGURASI PENGIRIMAN STRUK & FOOTER NOTA -->
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 space-y-5 transition-colors">
                <div class="border-b border-black/5 dark:border-white/10 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#30B0C7]/12 text-[#30B0C7] dark:text-[#40C8E0] flex items-center justify-center shrink-0">
                            <i data-lucide="receipt" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('whatsapp.receipt_settings_title') }}</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">{{ __('whatsapp.receipt_settings_desc') }}</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_tab" value="whatsapp">

                    <!-- Toggle Kirim Struk Otomatis Pasca Bayar -->
                    <div class="flex items-center justify-between p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                        <div class="pr-2">
                            <p class="text-[13px] font-semibold text-black dark:text-white">{{ __('whatsapp.auto_send_receipt_label') }}</p>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-0.5">{{ __('whatsapp.auto_send_receipt_desc') }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" name="auto_send_receipt" value="1" class="sr-only peer"
                                {{ ($waSession && $waSession->auto_send_receipt) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-black/[0.12] dark:bg-white/[0.15] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                        </label>
                    </div>

                    <!-- Catatan Kaki Struk Digital (pos_receipt_footer_note) -->
                    <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                            <div>
                                <label for="pos_receipt_footer_note" class="block text-[13px] font-semibold text-black dark:text-white">
                                    {{ __('settings.receipt_footer_section_title') }}
                                </label>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50">
                                    {{ __('settings.receipt_footer_section_desc') }}
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-medium bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] shrink-0 self-start sm:self-auto">
                                {{ __('settings.receipt_footer_note_badge') }}
                            </span>
                        </div>
                        <input type="text" id="pos_receipt_footer_note" name="pos_receipt_footer_note"
                            x-model="receiptFooterNote"
                            value="{{ old('pos_receipt_footer_note', $business->pos_receipt_footer_note) }}"
                            placeholder="{{ __('settings.receipt_footer_note_hint') }}"
                            maxlength="500"
                            class="w-full h-11 px-3.5 rounded-[10px] bg-white dark:bg-black/20 border border-black/10 dark:border-white/10 text-[14px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <!-- Format Pesan WhatsApp Struk Kasir Manual (pos_receipt_wa_template) -->
                    <div class="space-y-3 pt-1">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                            <div>
                                <label for="pos_receipt_wa_template" class="block text-[13px] font-semibold text-black dark:text-white">
                                    {{ __('settings.wa_manual_section_title') }}
                                </label>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50">
                                    {{ __('settings.wa_manual_section_desc') }}
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-medium bg-[#007AFF]/10 text-[#007AFF] shrink-0 self-start sm:self-auto">
                                {{ __('settings.wa_manual_template_badge') }}
                            </span>
                        </div>

                        <!-- Dynamic Variable Chips -->
                        <div class="space-y-1.5">
                            <label class="block text-[12px] font-medium text-black/70 dark:text-white/70">
                                {{ __('settings.dynamic_variables') }} <span class="text-[11px] font-normal text-black/40 dark:text-white/40">{{ __('settings.dynamic_variables_hint') }}</span>
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach (['{business_name}', '{customer_name}', '{order_number}', '{date}', '{cashier_name}', '{receipt_link}', '{footer_note}'] as $tag)
                                    <button type="button" @click="insertTag('{{ $tag }}')"
                                        class="px-2.5 py-1 rounded-full text-[11.5px] font-medium bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 active:scale-[0.97] transition flex items-center gap-1 cursor-pointer min-h-[30px]">
                                        <span>+</span>
                                        <span class="font-mono">{{ $tag }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Template Textarea -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[12px] font-medium text-black/70 dark:text-white/70">
                                    {{ __('settings.wa_template_format') }}
                                </span>
                                <span class="text-[11px] text-black/40 dark:text-white/40 font-mono tabular-nums"
                                    x-text="(waTemplateText ? waTemplateText.length : 0) + ' / 1000 karakter'"></span>
                            </div>
                            <textarea id="pos_receipt_wa_template" name="pos_receipt_wa_template" x-ref="waTextarea" x-model="waTemplateText"
                                rows="6" maxlength="1000"
                                class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] p-3.5 text-[14px] font-mono leading-relaxed text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition resize-y"
                                placeholder="{{ __('settings.ph_wa_manual_custom') }}"></textarea>
                        </div>

                        <!-- Markdown Cheatsheet Guide -->
                        <div class="rounded-[10px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 p-3 text-[12px] space-y-1">
                            <div class="font-medium text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>{{ __('settings.wa_markdown_guide') }}</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-black/60 dark:text-white/60 text-[11px]">
                                <div><code>*teks*</code> &rarr; <strong>Tebal</strong></div>
                                <div><code>_teks_</code> &rarr; <em>Miring</em></div>
                                <div><code>~teks~</code> &rarr; <del>Coret</del></div>
                                <div><code>```teks```</code> &rarr; Monospace</div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    @if (\App\Support\Context::hasPermission('settings.edit') || \App\Support\Context::hasPermission('whatsapp.manage'))
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-3 border-t border-black/5 dark:border-white/10">
                            <button type="button" @click="resetToDefaultTemplate()"
                                class="h-10 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition flex items-center justify-center cursor-pointer">
                                {{ __('settings.reset_default_template') }}
                            </button>
                            <button type="submit"
                                class="h-10 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition flex items-center justify-center gap-2 shadow-sm cursor-pointer">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>{{ __('settings.save_wa_template') }}</span>
                            </button>
                        </div>
                    @endif
                </form>
            </div>

        </div>

        {{-- KOLOM KANAN (5 COLS): LIVE PHONE PREVIEW & TEST CONSOLE --}}
        <div class="lg:col-span-5 space-y-6">

            <!-- 2.3 TEST SEND CONSOLE -->
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 space-y-4 transition-colors">
                <div class="flex items-center gap-3 pb-3 border-b border-black/5 dark:border-white/10">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[15px] font-bold text-black dark:text-white">{{ __('whatsapp.test_console_title') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('whatsapp.test_console_desc') }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('whatsapp.test_phone_label') }}</label>
                        <input x-model="testPhone" type="tel" placeholder="{{ __('whatsapp.test_phone_placeholder') }}"
                            class="w-full min-h-[42px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] px-3.5 text-[13.5px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 placeholder:text-black/35 dark:placeholder:text-white/35 transition-colors">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('whatsapp.test_message_label') }}</label>
                        <input x-model="testMessage" type="text" placeholder="{{ __('whatsapp.test_message_placeholder') }}"
                            class="w-full min-h-[42px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 rounded-[10px] px-3.5 text-[13.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 placeholder:text-black/35 dark:placeholder:text-white/35 transition-colors">
                    </div>

                    @if (\App\Support\Context::hasPermission('whatsapp.manage') || \App\Support\Context::hasPermission('settings.edit'))
                        <button type="button" @click="sendTest()"
                            class="w-full min-h-[42px] px-4 rounded-[11px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
                            :disabled="testLoading">
                            <i data-lucide="loader-2" x-show="testLoading" class="w-4 h-4 animate-spin"></i>
                            <i data-lucide="send" x-show="!testLoading" class="w-4 h-4"></i>
                            <span x-text="testLoading ? '{{ __('whatsapp.test_sending') }}' : '{{ __('whatsapp.test_send_btn') }}'"></span>
                        </button>
                    @endif

                    <div x-show="testResult" x-cloak x-text="testResult"
                        class="p-3 rounded-[10px] text-[12px] text-center font-semibold transition-all"
                        :class="testOk ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]'">
                    </div>
                </div>
            </div>

            <!-- 2.4 LIVE SMARTPHONE SIMULATION PREVIEW -->
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 space-y-3.5">
                <div class="flex items-center justify-between px-1">
                    <span class="text-[12px] font-semibold tracking-wider uppercase text-black/40 dark:text-white/40">{{ __('settings.live_preview') }}</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-[#34C759]">
                        <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                        <span>{{ __('settings.auto_sync') }}</span>
                    </span>
                </div>

                <!-- Preview Mode Segmented Switcher -->
                <div class="p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] flex gap-1 text-[12px] font-medium">
                    <button type="button" @click="previewMode = 'waba'"
                        :class="previewMode === 'waba' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="flex-1 py-1.5 px-2 rounded-[9px] transition flex items-center justify-center gap-1.5 min-h-[34px] cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                        <span class="truncate">{{ __('settings.preview_mode_waba') }}</span>
                    </button>
                    <button type="button" @click="previewMode = 'manual'"
                        :class="previewMode === 'manual' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="flex-1 py-1.5 px-2 rounded-[9px] transition flex items-center justify-center gap-1.5 min-h-[34px] cursor-pointer">
                        <i data-lucide="share-2" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span class="truncate">{{ __('settings.preview_mode_manual') }}</span>
                    </button>
                </div>

                <!-- Apple iOS Hardware Phone Frame -->
                <div class="w-full max-w-[320px] mx-auto rounded-[36px] p-2.5 bg-neutral-900 shadow-xl border border-neutral-800">
                    <div class="rounded-[26px] overflow-hidden bg-[#EFEAE2] dark:bg-[#0B141A] flex flex-col min-h-[460px] border border-black/10 dark:border-white/10 select-none relative">

                        <!-- Dynamic Island Header -->
                        <div class="bg-[#075E54] dark:bg-[#1F2C34] pt-2 pb-1 flex justify-center">
                            <div class="w-20 h-3.5 rounded-full bg-black flex items-center justify-end px-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-neutral-700"></div>
                            </div>
                        </div>

                        <!-- WhatsApp Top Bar -->
                        <div class="bg-[#075E54] dark:bg-[#1F2C34] text-white px-3.5 py-2 flex items-center gap-2.5 shadow-sm">
                            <div class="w-7 h-7 rounded-full bg-white/20 text-white font-bold text-[12px] flex items-center justify-center">
                                {{ strtoupper(substr($business->name ?? 'T', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-[12.5px] font-semibold truncate leading-tight text-white flex items-center gap-1">
                                    <span>{{ $business->name ?? 'Toko Saya' }}</span>
                                    <template x-if="previewMode === 'waba'">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] inline-block" title="Official Business Account"></i>
                                    </template>
                                </h4>
                                <p class="text-[10px] text-white/70 leading-tight">
                                    <span x-show="previewMode === 'waba'">{{ __('settings.waba_official_tag') }}</span>
                                    <span x-show="previewMode === 'manual'">Online</span>
                                </p>
                            </div>
                            <div class="flex items-center gap-2.5 text-white/80">
                                <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                <i data-lucide="more-vertical" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>

                        <!-- WhatsApp Chat Body Area -->
                        <div class="flex-1 p-3 flex flex-col justify-end space-y-2 overflow-y-auto">

                            <!-- PREVIEW MODE 1: WABA BOT (META OFFICIAL) -->
                            <div x-show="previewMode === 'waba'"
                                class="bg-[#D9FDD3] dark:bg-[#005C4B] text-[#111B21] dark:text-[#E9EDEF] rounded-[12px] rounded-tr-none p-2.5 shadow-sm max-w-[96%] ml-auto text-[11.5px] leading-relaxed space-y-2">
                                <div class="flex items-center justify-between border-b border-black/10 dark:border-white/15 pb-1 text-[10px] font-mono opacity-70">
                                    <span>TEMPLATE: cooca_pos_receipt</span>
                                    <span class="text-[#34C759] font-bold">APPROVED</span>
                                </div>

                                <!-- Official Meta Template Text Header Component -->
                                <div class="pt-0.5 pb-1 border-b border-black/10 dark:border-white/10 flex items-center justify-between">
                                    <span class="font-bold text-[13px] text-black dark:text-white tracking-tight uppercase">
                                        {{ __('settings.waba_header_text') }}
                                    </span>
                                    <span class="text-[9px] font-medium text-black/40 dark:text-white/40 font-mono">
                                        HEADER: TEXT
                                    </span>
                                </div>
                                <div class="space-y-1 font-sans">
                                    <p class="font-bold">Halo Agung Mustaqim,</p>
                                    <p class="text-[11px] opacity-90 leading-normal">
                                        Terima kasih telah berbelanja di <strong>{{ $business->name }}</strong>. Transaksi <code>POS-20260910-0042</code> telah berhasil.
                                    </p>
                                </div>
                                <div class="p-2 rounded bg-black/5 dark:bg-black/20 text-[11px] space-y-0.5 font-mono">
                                    <div class="flex justify-between">
                                        <span>Total Belanja:</span>
                                        <span class="font-bold">Rp 125.000</span>
                                    </div>
                                    <div class="flex justify-between text-[10px] opacity-75">
                                        <span>Status:</span>
                                        <span>LUNAS (QRIS)</span>
                                    </div>
                                </div>
                                <div class="text-[11px] italic border-t border-black/10 dark:border-white/10 pt-1"
                                    x-text="receiptFooterNote || @json($business->pos_receipt_footer_note ?? __('settings.default_footer_note'))">
                                </div>
                                <div class="pt-1">
                                    <div class="w-full py-1.5 px-2 bg-white dark:bg-white/10 text-[#007AFF] text-center font-semibold rounded text-[11px] shadow-xs flex items-center justify-center gap-1.5">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                        <span>Buka Nota Digital</span>
                                    </div>
                                </div>
                                <div class="text-[9.5px] text-right opacity-60 font-mono flex items-center justify-end gap-1">
                                    <span>{{ now()->format('H:i') }}</span>
                                    <i data-lucide="check-check" class="w-3.5 h-3.5 text-[#53bdeb] inline-block"></i>
                                </div>
                            </div>

                            <!-- PREVIEW MODE 2: MANUAL WA WEB FORMAT -->
                            <div x-show="previewMode === 'manual'"
                                class="bg-white dark:bg-[#1F2C34] text-[#111B21] dark:text-[#E9EDEF] rounded-[12px] rounded-tl-none p-2.5 shadow-sm max-w-[96%] mr-auto text-[11.5px] leading-relaxed font-sans">
                                <div x-html="formatWaMarkdown(waRenderedPreview)" class="space-y-1"></div>
                                <div class="text-[9.5px] text-right opacity-60 font-mono mt-1">
                                    {{ now()->format('H:i') }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Destination Web Page Card: Where receipt image & footer note actually live -->
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-4 sm:p-5 space-y-3.5 shadow-sm">
                <div class="flex items-start justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                            <h4 class="text-[13px] font-semibold text-black dark:text-white">
                                {{ __('settings.waba_receipt_paper_title') }}
                            </h4>
                        </div>
                        <p class="text-[11.5px] text-black/55 dark:text-white/55 leading-relaxed">
                            {{ __('settings.waba_receipt_paper_desc') }}
                        </p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF] shrink-0">
                        {{ __('settings.waba_link_destination_badge') }}
                    </span>
                </div>

                <!-- Browser URL Bar Mockup -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 text-[11px] font-mono text-black/60 dark:text-white/60">
                    <i data-lucide="lock" class="w-3 h-3 text-[#34C759] shrink-0"></i>
                    <span class="truncate">https://cooca.id/r/POS-20260910-0042</span>
                </div>

                <!-- Rendered Thermal Receipt Paper Simulation -->
                <div class="rounded-[12px] bg-[#FAF8F5] dark:bg-[#141416] border border-black/10 dark:border-white/10 p-4 font-mono text-[11.5px] text-black/85 dark:text-white/85 space-y-2.5 shadow-inner">
                    <!-- Store Header -->
                    <div class="text-center space-y-0.5">
                        <div class="text-[13px] font-bold tracking-tight text-black dark:text-white uppercase font-sans">
                            {{ $business->name ?? __('settings.receipt_mock_fallback_store') }}
                        </div>
                        <div class="text-[10px] text-black/50 dark:text-white/50">
                            {{ $business->address ?? __('settings.receipt_mock_fallback_address') }}
                        </div>
                        <div class="text-[9.5px] text-black/40 dark:text-white/40 pt-1">
                            POS-20260910-0042 • {{ now()->format('d/m/Y H:i') }} • {{ __('settings.receipt_mock_cashier_1') }}
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-dashed border-black/20 dark:border-white/20"></div>

                    <!-- Item Table Breakdown -->
                    <div class="space-y-1.5 text-[11px]">
                        <div class="flex justify-between items-start">
                            <div>{{ __('settings.receipt_mock_item_1') }}</div>
                            <div class="tabular-nums">Rp 25.000</div>
                        </div>
                        <div class="flex justify-between items-start">
                            <div>{{ __('settings.receipt_mock_item_2') }}</div>
                            <div class="tabular-nums">Rp 18.000</div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="border-t border-dashed border-black/20 dark:border-white/20 pt-1 space-y-1 text-[11px]">
                        <div class="flex justify-between font-bold">
                            <span>TOTAL</span>
                            <span class="tabular-nums">Rp 43.000</span>
                        </div>
                    </div>

                    <!-- Footer Note -->
                    <div class="border-t border-dashed border-black/20 dark:border-white/20 pt-2 text-center text-[10px] text-black/60 dark:text-white/60">
                        <p x-text="receiptFooterNote || '{{ __('settings.default_footer_note') }}'"></p>
                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- 3. KATALOG TEMPLATE RESMI META (ANTI-BLOKIR 24 JAM) --}}
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-7 space-y-5 transition-colors"
        x-data="{ tplFilter: 'all' }">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
            <div class="flex items-start gap-3.5">
                <div class="w-11 h-11 rounded-[14px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center shrink-0 shadow-xs">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">
                            {{ __('whatsapp.meta_catalog_title') }}
                        </h3>
                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                            {{ __('whatsapp.meta_compliant_badge') }}
                        </span>
                    </div>
                    <p class="text-[12px] text-black/55 dark:text-white/55 mt-0.5 leading-relaxed">
                        {{ __('whatsapp.meta_catalog_subtitle') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('whatsapp.broadcast.index', ['open_composer' => 1]) }}"
                    class="min-h-[38px] px-3.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-bold inline-flex items-center gap-1.5 shadow-sm transition active:scale-[0.98]">
                    <i data-lucide="megaphone" class="w-3.5 h-3.5"></i>
                    <span>{{ __('whatsapp.send_blast_with_template') }}</span>
                </a>
            </div>
        </div>

        {{-- Filter Pills Segmented Control --}}
        <div class="flex items-center gap-2 p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[13px] w-fit">
            <button type="button" @click="tplFilter = 'all'"
                class="px-3 py-1.5 rounded-[10px] text-[12px] font-semibold transition-all cursor-pointer"
                :class="tplFilter === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                <span>{{ __('settings.semua_10') }}</span>
            </button>
            <button type="button" @click="tplFilter = 'UTILITY'"
                class="px-3 py-1.5 rounded-[10px] text-[12px] font-semibold transition-all cursor-pointer flex items-center gap-1.5"
                :class="tplFilter === 'UTILITY' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                <span>Operasional / Utility (7)</span>
            </button>
            <button type="button" @click="tplFilter = 'MARKETING'"
                class="px-3 py-1.5 rounded-[10px] text-[12px] font-semibold transition-all cursor-pointer flex items-center gap-1.5"
                :class="tplFilter === 'MARKETING' ? 'bg-white dark:bg-[#2C2C2E] text-[#34C759] shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'">
                <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                <span>Pemasaran / Marketing (3)</span>
            </button>
        </div>

        <!-- Bento Grid: 10 Preset Templates -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
            @php
                $storeTemplates = [
                    [
                        'name' => 'cooca_pos_receipt',
                        'title' => __('whatsapp.tpl_pos_receipt_title'),
                        'category' => 'UTILITY',
                        'badge' => __('whatsapp.tpl_pos_receipt_badge'),
                        'desc' => __('whatsapp.tpl_pos_receipt_desc'),
                        'icon' => 'receipt',
                    ],
                    [
                        'name' => 'cooca_reservation_reminder',
                        'title' => __('whatsapp.tpl_reservation_reminder_title'),
                        'category' => 'UTILITY',
                        'badge' => __('whatsapp.tpl_reservation_reminder_badge'),
                        'desc' => __('whatsapp.tpl_reservation_reminder_desc'),
                        'icon' => 'calendar-clock',
                    ],
                    [
                        'name' => 'cooca_marketplace_receipt',
                        'title' => __('whatsapp.tpl_marketplace_receipt_title'),
                        'category' => 'UTILITY',
                        'badge' => __('whatsapp.tpl_marketplace_receipt_badge'),
                        'desc' => __('whatsapp.tpl_marketplace_receipt_desc'),
                        'icon' => 'shopping-bag',
                    ],
                    [
                        'name' => 'cooca_shipping_tracking',
                        'title' => __('whatsapp.tpl_shipping_tracking_title'),
                        'category' => 'UTILITY',
                        'badge' => __('whatsapp.tpl_shipping_tracking_badge'),
                        'desc' => __('whatsapp.tpl_shipping_tracking_desc'),
                        'icon' => 'truck',
                    ],
                    [
                        'name' => 'cooca_cart_reminder',
                        'title' => __('whatsapp.tpl_cart_reminder_title'),
                        'category' => 'MARKETING',
                        'badge' => __('whatsapp.tpl_cart_reminder_badge'),
                        'desc' => __('whatsapp.tpl_cart_reminder_desc'),
                        'icon' => 'shopping-cart',
                    ],
                    [
                        'name' => 'cooca_promo_broadcast',
                        'title' => __('whatsapp.tpl_promo_broadcast_title'),
                        'category' => 'MARKETING',
                        'badge' => __('whatsapp.tpl_promo_broadcast_badge'),
                        'desc' => __('whatsapp.tpl_promo_broadcast_desc'),
                        'icon' => 'megaphone',
                    ],
                    [
                        'name' => 'cooca_order_status_update',
                        'title' => __('whatsapp.tpl_order_status_update_title'),
                        'category' => 'UTILITY',
                        'badge' => __('whatsapp.tpl_order_status_update_badge'),
                        'desc' => __('whatsapp.tpl_order_status_update_desc'),
                        'icon' => 'package-check',
                    ],
                    [
                        'name' => 'cooca_sales_invoice',
                        'title' => __('whatsapp.tpl_sales_invoice_title'),
                        'category' => 'UTILITY',
                        'badge' => __('whatsapp.tpl_sales_invoice_badge'),
                        'desc' => __('whatsapp.tpl_sales_invoice_desc'),
                        'icon' => 'file-text',
                    ],
                    [
                        'name' => 'cooca_customer_welcome',
                        'title' => __('whatsapp.tpl_customer_welcome_title'),
                        'category' => 'MARKETING',
                        'badge' => __('whatsapp.tpl_customer_welcome_badge'),
                        'desc' => __('whatsapp.tpl_customer_welcome_desc'),
                        'icon' => 'sparkles',
                    ],
                    [
                        'name' => 'cooca_payment_reminder',
                        'title' => __('whatsapp.tpl_payment_reminder_title'),
                        'category' => 'UTILITY',
                        'badge' => __('whatsapp.tpl_payment_reminder_badge'),
                        'desc' => __('whatsapp.tpl_payment_reminder_desc'),
                        'icon' => 'bell',
                    ],
                ];
            @endphp

            @foreach ($storeTemplates as $tpl)
                <div x-show="tplFilter === 'all' || tplFilter === '{{ $tpl['category'] }}'"
                    class="rounded-[16px] bg-black/[0.015] dark:bg-white/[0.02] border border-black/5 dark:border-white/10 p-4 flex flex-col justify-between space-y-3 hover:border-black/15 dark:hover:border-white/15 transition-all">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold uppercase tracking-wider
                                {{ $tpl['category'] === 'UTILITY' ? 'bg-[#007AFF]/12 text-[#007AFF]' : 'bg-[#34C759]/12 text-[#34C759]' }}">
                                {{ $tpl['category'] }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#34C759]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                <span>Resmi Meta</span>
                            </span>
                        </div>

                        <div class="pt-0.5">
                            <div class="flex items-center gap-2">
                                <i data-lucide="{{ $tpl['icon'] }}" class="w-4 h-4 {{ $tpl['category'] === 'UTILITY' ? 'text-[#007AFF]' : 'text-[#34C759]' }} shrink-0"></i>
                                <h4 class="text-[13.5px] font-bold text-black dark:text-white leading-tight">{{ $tpl['title'] }}</h4>
                            </div>
                            <code class="text-[11px] font-mono text-black/50 dark:text-white/50 block mt-1">{{ $tpl['name'] }}</code>
                            <p class="text-[11.5px] text-black/60 dark:text-white/60 mt-1 line-clamp-2 leading-relaxed">
                                {{ $tpl['desc'] }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[11px]">
                        <span class="text-black/45 dark:text-white/45 font-medium truncate max-w-[150px]">{{ $tpl['badge'] }}</span>
                        @if($tpl['category'] === 'MARKETING')
                            <a href="{{ route('whatsapp.broadcast.index', ['open_composer' => 1]) }}"
                                class="text-[11px] font-bold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                <span>Blast</span> <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        @else
                            <span class="text-[#34C759] font-semibold inline-flex items-center gap-1">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                <span>Aman 24 Jam</span>
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
