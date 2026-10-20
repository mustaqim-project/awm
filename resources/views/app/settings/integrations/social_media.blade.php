{{--
    =============================================================================
    COOCA SETTINGS HUB - INTEGRASI MEDIA SOSIAL (OMNICHANNEL)
    Cluster 4: Unified Settings Hub (Bento Apple HIG Standard)
    Meta (Facebook & Instagram), TikTok, LinkedIn OAuth 2.0 Management
    =============================================================================
--}}

@php
    $socialList = $socialAccounts ?? ($accounts ?? collect());
    $metaAccounts = $socialList->whereIn('platform', ['facebook', 'instagram', 'threads'])->where('status', 'active');
    $tiktokAccounts = $socialList->where('platform', 'tiktok')->where('status', 'active');
    $linkedinAccounts = $socialList->where('platform', 'linkedin')->where('status', 'active');
    $activeCount = $socialList->where('status', 'active')->count();
@endphp

<div class="space-y-6">

    {{-- 1. HEADER COCKPIT INFORMASI & STATUS KONEKSI --}}
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 transition-colors">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-[16px] bg-gradient-to-br from-[#1877F2] via-[#E1306C] to-[#0A66C2] text-white flex items-center justify-center shrink-0 shadow-md shadow-[#1877F2]/20">
                    <i data-lucide="share-2" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight">
                            {{ __('social_media.connected_accounts') }}
                        </h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                            Omnichannel Hub
                        </span>
                    </div>
                    <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">
                        {{ __('social_media.connected_accounts_sub') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <span class="px-3 py-1.5 rounded-full text-[11.5px] font-bold inline-flex items-center gap-1.5 {{ $activeCount > 0 ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25' : 'bg-black/[0.05] dark:bg-white/[0.08] text-black/55 dark:text-white/55 border border-black/10 dark:border-white/10' }}">
                    <span class="w-2 h-2 rounded-full {{ $activeCount > 0 ? 'bg-[#34C759]' : 'bg-[#FF9500]' }}"></span>
                    <span>{{ $activeCount > 0 ? __('social_media.connected_count', ['count' => $activeCount]) : __('social_media.not_connected') }}</span>
                </span>
                <a href="{{ route('social-media.posts.index') }}"
                    class="min-h-[38px] px-3.5 rounded-[10px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>{{ __('social_media.create_post_btn') }}</span>
                </a>
            </div>
        </div>
    </div>

    {{-- FEEDBACK ALERTS --}}
    <div x-show="socialErrorMessage" x-cloak x-transition class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[12.5px] text-[#C41E17] dark:text-[#FF453A] flex items-center gap-2.5">
        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
        <span x-text="socialErrorMessage"></span>
    </div>

    <div x-show="socialSuccessMessage" x-cloak x-transition class="p-3.5 rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/30 text-[12.5px] text-[#248A3D] dark:text-[#30D158] flex items-center gap-2.5">
        <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
        <span x-text="socialSuccessMessage"></span>
    </div>

    {{-- 2. GRID INTEGRASI PLATFORM & AKUN TERHUBUNG --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- KOLOM KIRI (7 COLS): KARTU PLATFORM OAUTH --}}
        <div class="lg:col-span-7 space-y-5">

            {{-- 2.1 META (Facebook Pages & Instagram Business) --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 space-y-4 transition-all">
                <div class="flex items-center justify-between gap-3 pb-3.5 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[13px] bg-gradient-to-tr from-[#1877F2] to-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-sm">
                            <x-social-icon platform="meta" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white">1. Meta (Facebook &amp; Instagram)</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Facebook Page &amp; Instagram Business Account</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 {{ $metaAccounts->isNotEmpty() ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/5 text-black/45 dark:bg-white/5 dark:text-white/45' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $metaAccounts->isNotEmpty() ? 'bg-[#34C759]' : 'bg-black/30 dark:bg-white/30' }}"></span>
                        <span>{{ $metaAccounts->isNotEmpty() ? __('social_media.status_connected') : __('social_media.not_connected') }}</span>
                    </span>
                </div>

                {{-- Akun Terkoneksi Meta --}}
                <div class="space-y-2.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">{{ __('social_media.active_assets') }}</span>
                    @if($metaAccounts->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($metaAccounts as $acc)
                                <div x-show="!disconnectedSocialIds.includes('{{ $acc->id }}')" x-transition.duration.200ms
                                    class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-[10px] flex items-center justify-center shrink-0 {{ $acc->platform === 'facebook' ? 'bg-[#1877F2]/12 text-[#1877F2]' : 'bg-[#E1306C]/12 text-[#E1306C]' }}">
                                            <x-social-icon :platform="$acc->platform" class="w-4 h-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-black dark:text-white text-[13px] truncate">{{ $acc->account_name }}</div>
                                            <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1.5">
                                                <span class="capitalize font-semibold">{{ $acc->platform }}</span>
                                                @if($acc->username)
                                                    <span>•</span>
                                                    <span class="font-mono text-[#007AFF]">{{ $acc->username }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" @click="disconnectSocial('{{ $acc->id }}')"
                                        class="min-h-[32px] px-3 rounded-[8px] text-[11px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all shrink-0 cursor-pointer">
                                        {{ __('social_media.disconnect') }}
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-[12px] text-black/45 dark:text-white/45 italic py-1">Belum ada akun Facebook atau Instagram yang terhubung.</p>
                    @endif
                </div>

                {{-- Action Button --}}
                <div class="pt-2 flex items-center justify-between gap-3 border-t border-black/5 dark:border-white/10">
                    <p class="text-[11.5px] text-black/50 dark:text-white/50 hidden sm:block">{{ __('social_media.meta_card_desc') }}</p>
                    <button type="button" @click="launchMetaLogin()" :disabled="socialLoading"
                        class="min-h-[38px] px-4 rounded-[11px] bg-gradient-to-r from-[#1877F2] to-[#007AFF] hover:from-[#166FE5] hover:to-[#0071E3] text-white font-semibold text-[12.5px] inline-flex items-center justify-center gap-2 shadow-xs active:scale-[0.98] transition-all disabled:opacity-50 cursor-pointer shrink-0 ml-auto">
                        <i data-lucide="loader-2" x-show="socialLoading" class="w-3.5 h-3.5 animate-spin"></i>
                        <span x-show="!socialLoading" class="inline-flex items-center"><x-social-icon platform="meta" class="w-3.5 h-3.5" /></span>
                        <span x-text="socialLoading ? '{{ __('social_media.meta_connecting_btn') }}' : '{{ $metaAccounts->isNotEmpty() ? __('social_media.meta_card_title_connected') : __('social_media.meta_connect_btn') }}'"></span>
                    </button>
                </div>
            </div>

            {{-- 2.2 TIKTOK PLATFORM CARD --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 space-y-4 transition-all">
                <div class="flex items-center justify-between gap-3 pb-3.5 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[13px] bg-black dark:bg-white text-white dark:text-black flex items-center justify-center shrink-0 shadow-sm">
                            <x-social-icon platform="tiktok" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white">2. TikTok</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">TikTok Official Business &amp; Creator Account</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 {{ $tiktokAccounts->isNotEmpty() ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/5 text-black/45 dark:bg-white/5 dark:text-white/45' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $tiktokAccounts->isNotEmpty() ? 'bg-[#34C759]' : 'bg-black/30 dark:bg-white/30' }}"></span>
                        <span>{{ $tiktokAccounts->isNotEmpty() ? __('social_media.status_connected') : __('social_media.not_connected') }}</span>
                    </span>
                </div>

                {{-- Akun Terkoneksi TikTok --}}
                <div class="space-y-2.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">{{ __('social_media.active_assets') }}</span>
                    @if($tiktokAccounts->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($tiktokAccounts as $acc)
                                <div x-show="!disconnectedSocialIds.includes('{{ $acc->id }}')" x-transition.duration.200ms
                                    class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-[10px] bg-black/10 dark:bg-white/15 text-black dark:text-white flex items-center justify-center shrink-0">
                                            <x-social-icon :platform="$acc->platform" class="w-4 h-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-black dark:text-white text-[13px] truncate">{{ $acc->account_name }}</div>
                                            <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1.5">
                                                <span class="capitalize font-semibold">{{ $acc->platform }}</span>
                                                @if($acc->username)
                                                    <span>•</span>
                                                    <span class="font-mono text-[#007AFF]">{{ $acc->username }}</span>
                                                @endif
                                                @if($acc->token_expires_at)
                                                    <span>•</span>
                                                    <span class="text-[10px] text-[#34C759]">{{ __('social_media.auto_refresh_active') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" @click="disconnectSocial('{{ $acc->id }}')"
                                        class="min-h-[32px] px-3 rounded-[8px] text-[11px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all shrink-0 cursor-pointer">
                                        {{ __('social_media.disconnect') }}
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-[12px] text-black/45 dark:text-white/45 italic py-1">Belum ada akun TikTok yang terhubung.</p>
                    @endif
                </div>

                {{-- Action Button --}}
                <div class="pt-2 flex items-center justify-between gap-3 border-t border-black/5 dark:border-white/10">
                    <p class="text-[11.5px] text-black/50 dark:text-white/50 hidden sm:block">{{ __('social_media.tiktok_card_desc') }}</p>
                    <a href="{{ route('social-media.tiktok.connect') }}"
                        class="min-h-[38px] px-4 rounded-[11px] bg-black dark:bg-white text-white dark:text-black hover:opacity-90 font-semibold text-[12.5px] inline-flex items-center justify-center gap-2 shadow-xs active:scale-[0.98] transition-all shrink-0 ml-auto cursor-pointer">
                        <x-social-icon platform="tiktok" class="w-3.5 h-3.5" />
                        <span>{{ $tiktokAccounts->isNotEmpty() ? __('social_media.tiktok_card_title_connected') : __('social_media.tiktok_connect_btn') }}</span>
                    </a>
                </div>
            </div>

            {{-- 2.3 LINKEDIN PLATFORM CARD --}}
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 sm:p-6 space-y-4 transition-all">
                <div class="flex items-center justify-between gap-3 pb-3.5 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[13px] bg-[#0A66C2] text-white flex items-center justify-center shrink-0 shadow-sm">
                            <x-social-icon platform="linkedin" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-black dark:text-white">3. LinkedIn</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">LinkedIn Official Organization &amp; Member Profile</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 {{ $linkedinAccounts->isNotEmpty() ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/5 text-black/45 dark:bg-white/5 dark:text-white/45' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $linkedinAccounts->isNotEmpty() ? 'bg-[#34C759]' : 'bg-black/30 dark:bg-white/30' }}"></span>
                        <span>{{ $linkedinAccounts->isNotEmpty() ? __('social_media.status_connected') : __('social_media.not_connected') }}</span>
                    </span>
                </div>

                {{-- Akun Terkoneksi LinkedIn --}}
                <div class="space-y-2.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">{{ __('social_media.active_assets') }}</span>
                    @if($linkedinAccounts->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($linkedinAccounts as $acc)
                                <div x-show="!disconnectedSocialIds.includes('{{ $acc->id }}')" x-transition.duration.200ms
                                    class="p-3.5 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-[10px] bg-[#0A66C2]/12 text-[#0A66C2] flex items-center justify-center shrink-0">
                                            <x-social-icon :platform="$acc->platform" class="w-4 h-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-black dark:text-white text-[13px] truncate">{{ $acc->account_name }}</div>
                                            <div class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-1.5">
                                                <span class="capitalize font-semibold">{{ $acc->platform }}</span>
                                                @if($acc->username)
                                                    <span>•</span>
                                                    <span class="font-mono text-[#007AFF]">{{ $acc->username }}</span>
                                                @endif
                                                @if($acc->token_expires_at)
                                                    <span>•</span>
                                                    <span class="text-[10px] text-[#34C759]">{{ __('social_media.auto_refresh_active') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" @click="disconnectSocial('{{ $acc->id }}')"
                                        class="min-h-[32px] px-3 rounded-[8px] text-[11px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all shrink-0 cursor-pointer">
                                        {{ __('social_media.disconnect') }}
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-[12px] text-black/45 dark:text-white/45 italic py-1">Belum ada akun LinkedIn yang terhubung.</p>
                    @endif
                </div>

                {{-- Action Button --}}
                <div class="pt-2 flex items-center justify-between gap-3 border-t border-black/5 dark:border-white/10">
                    <p class="text-[11.5px] text-black/50 dark:text-white/50 hidden sm:block">{{ __('social_media.linkedin_card_desc') }}</p>
                    <a href="{{ route('social-media.linkedin.connect') }}"
                        class="min-h-[38px] px-4 rounded-[11px] bg-[#0A66C2] hover:bg-[#004182] text-white font-semibold text-[12.5px] inline-flex items-center justify-center gap-2 shadow-xs active:scale-[0.98] transition-all shrink-0 ml-auto cursor-pointer">
                        <x-social-icon platform="linkedin" class="w-3.5 h-3.5" />
                        <span>{{ $linkedinAccounts->isNotEmpty() ? __('social_media.linkedin_card_title_connected') : __('social_media.linkedin_connect_btn') }}</span>
                    </a>
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN (5 COLS): SHORTCUTS KE OPERASIONAL & BREADCRUMB PINTAS --}}
        <div class="lg:col-span-5 space-y-5">

            <!-- FAST NAVIGATION TO DAILY SOCIAL OPERATIONS -->
            <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-5 space-y-4">
                <div class="flex items-center gap-2.5 pb-2 border-b border-black/5 dark:border-white/10">
                    <i data-lucide="layers" class="w-4 h-4 text-[#007AFF]"></i>
                    <h4 class="text-[14px] font-bold text-black dark:text-white">Operasional Saluran Pemasaran</h4>
                </div>

                <div class="space-y-2">
                    <a href="{{ route('social-media.posts.index') }}"
                        class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 hover:border-[#007AFF]/30 transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-[9px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="image" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-[13px] font-semibold text-black dark:text-white group-hover:text-[#007AFF] transition-colors">Postingan &amp; Publikasi Konten</p>
                                <p class="text-[11px] text-black/50 dark:text-white/50">Jadwalkan posting multi-channel serentak</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-black/30 dark:text-white/30 group-hover:translate-x-0.5 transition-transform"></i>
                    </a>

                    <a href="{{ route('social-media.calendar') }}"
                        class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 hover:border-[#34C759]/30 transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-[9px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-[13px] font-semibold text-black dark:text-white group-hover:text-[#34C759] transition-colors">Kalender Perencanaan Konten</p>
                                <p class="text-[11px] text-black/50 dark:text-white/50">Tinjau antrean posting per tanggal</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-black/30 dark:text-white/30 group-hover:translate-x-0.5 transition-transform"></i>
                    </a>

                    <a href="{{ route('social-media.inbox.index') }}"
                        class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 hover:border-[#FF9500]/30 transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-[9px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                                <i data-lucide="message-square" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-[13px] font-semibold text-black dark:text-white group-hover:text-[#FF9500] transition-colors">Kotak Masuk Terpadu (Live Chat)</p>
                                <p class="text-[11px] text-black/50 dark:text-white/50">Balas DM &amp; komentar dengan asisten AI</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-black/30 dark:text-white/30 group-hover:translate-x-0.5 transition-transform"></i>
                    </a>

                    <a href="{{ route('social-media.insights.index') }}"
                        class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 hover:border-[#AF52DE]/30 transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-[9px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center shrink-0">
                                <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-[13px] font-semibold text-black dark:text-white group-hover:text-[#AF52DE] transition-colors">Analitik &amp; Performa Pemasaran</p>
                                <p class="text-[11px] text-black/50 dark:text-white/50">Statistik jangkauan &amp; interaksi audiens</p>
                            </div>
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-black/30 dark:text-white/30 group-hover:translate-x-0.5 transition-transform"></i>
                    </a>
                </div>
            </div>

            <!-- SECURITY & TOKEN AUTO-REFRESH CALLOUT -->
            <div class="rounded-[20px] bg-[#007AFF]/[0.05] dark:bg-[#007AFF]/[0.08] border border-[#007AFF]/20 p-5 space-y-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-[8px] bg-[#007AFF] text-white flex items-center justify-center shrink-0">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <h4 class="text-[13.5px] font-bold text-black dark:text-white">Otorisasi &amp; Token Aman</h4>
                </div>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sistem COOCA menggunakan protokol resmi OAuth 2.0 dengan enkripsi token AES-256. Token TikTok dan LinkedIn diperbarui secara otomatis di latar belakang tanpa mengganggu operasional.
                </p>
            </div>

        </div>

    </div>

</div>
