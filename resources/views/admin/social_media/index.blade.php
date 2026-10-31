@extends('layouts.admin')
@section('title', 'Media Sosial Platform Admin Center - COOCA')

@section('content')
    <div class="space-y-6 max-w-[1250px] w-full min-w-0 mx-auto pb-28 lg:pb-10" x-data="adminSocialCenter()"
        x-init="init()">

        {{-- 1. BENTO HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 min-w-0">
            <div class="flex items-center gap-3.5 min-w-0 flex-1">
                <div
                    class="w-12 h-12 rounded-[18px] bg-gradient-to-br from-[#1877F2] via-[#E1306C] to-[#000000] flex items-center justify-center shadow-md shadow-[#1877F2]/20 shrink-0 text-white">
                    <i data-lucide="share-2" class="w-6 h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h1 class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tracking-tight truncate">Media Sosial Platform Admin Center</h1>
                    <p class="text-[12.5px] sm:text-[13px] text-black/55 dark:text-white/55 mt-0.5 truncate">Pusat publikasi konten resmi Cooca, kotak masuk interaksi, integrasi provider, dan pengawasan merchant</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <span
                    class="px-3.5 py-1.5 rounded-full text-[12px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
                    <span>Graph API {{ $platform['graph_version'] }}</span>
                </span>
                <button type="button" @click="activeTab = 'posts'; openCreatePostModal = true"
                    class="min-h-[38px] px-4 rounded-[11px] text-[12.5px] font-bold text-white bg-gradient-to-r from-[#1877F2] to-[#E1306C] hover:opacity-95 active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Buat Postingan Cooca</span>
                </button>
                <a href="{{ route('admin.settings.index', ['tab' => 'social']) }}"
                    class="min-h-[38px] px-3.5 rounded-[11px] text-[12.5px] font-semibold text-black dark:text-white bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.98] transition-all flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="sliders" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>{{ __('social_media.unified_settings') }}</span>
                    <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 opacity-70"></i>
                </a>
            </div>
        </div>

        {{-- 2. STATS KPI OVERVIEW --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 sm:gap-4">
            <div
                class="p-4 sm:p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
                <span class="text-[11.5px] font-semibold uppercase text-black/50 dark:text-white/50">Postingan
                    Platform</span>
                <p class="text-[22px] sm:text-[26px] font-bold text-[#007AFF] tabular-nums tracking-tight">
                    {{ number_format($platformPosts->total()) }}</p>
            </div>
            <div
                class="p-4 sm:p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
                <span class="text-[11.5px] font-semibold uppercase text-black/50 dark:text-white/50">Pengikut
                    Instagram</span>
                <p class="text-[22px] sm:text-[26px] font-bold text-[#E1306C] tabular-nums tracking-tight">
                    {{ number_format($analytics['instagram']['profile']['followers_count'] ?? 612) }}</p>
            </div>
            <div
                class="p-4 sm:p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
                <span class="text-[11.5px] font-semibold uppercase text-black/50 dark:text-white/50">Sisa Kuota
                    Posting</span>
                <p class="text-[22px] sm:text-[26px] font-bold text-[#34C759] tabular-nums tracking-tight">
                    {{ $analytics['instagram']['quota_remaining'] ?? 24 }}<span
                        class="text-[14px] text-black/40 dark:text-white/40 font-normal">/25</span></p>
            </div>
            <div
                class="p-4 sm:p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
                <span class="text-[11.5px] font-semibold uppercase text-black/50 dark:text-white/50">Halaman Facebook</span>
                <p class="text-[22px] sm:text-[26px] font-bold text-[#1877F2] tabular-nums tracking-tight">
                    {{ number_format($summary['facebook_pages_count']) }}</p>
            </div>
            <div
                class="p-4 sm:p-5 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm space-y-1">
                <span class="text-[11.5px] font-semibold uppercase text-black/50 dark:text-white/50">Komentar Masuk</span>
                <p class="text-[22px] sm:text-[26px] font-bold text-[#AF52DE] tabular-nums tracking-tight">
                    {{ number_format($platformComments->total()) }}</p>
            </div>
        </div>

        {{-- 3. SEGMENTED NAVIGATION TABS --}}
        <div
            class="p-1.5 bg-black/[0.04] dark:bg-white/[0.06] rounded-[16px] flex items-center gap-1.5 overflow-x-auto shadow-inner">
            <button type="button" @click="activeTab = 'posts'"
                :class="activeTab === 'posts' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="min-h-[40px] px-4 rounded-[11px] text-[13px] transition-all flex items-center gap-2 shrink-0">
                <i data-lucide="layout-grid" class="w-4 h-4 text-[#007AFF]"></i>
                <span>{{ __('social_media.manage_platform_content') }}</span>
                @if ($platformPosts->total() > 0)
                    <span
                        class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#007AFF]/15 text-[#007AFF]">{{ $platformPosts->total() }}</span>
                @endif
            </button>
            <button type="button" @click="activeTab = 'analytics'"
                :class="activeTab === 'analytics' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="min-h-[40px] px-4 rounded-[11px] text-[13px] transition-all flex items-center gap-2 shrink-0">
                <i data-lucide="bar-chart-2" class="w-4 h-4 text-[#34C759]"></i>
                <span>Analitik &amp; Performa</span>
                <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Organik</span>
            </button>
            <button type="button" @click="activeTab = 'inbox'"
                :class="activeTab === 'inbox' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="min-h-[40px] px-4 rounded-[11px] text-[13px] transition-all flex items-center gap-2 shrink-0">
                <i data-lucide="message-square" class="w-4 h-4 text-[#AF52DE]"></i>
                <span>{{ __('social_media.interaction_inbox') }}</span>
                @if ($platformComments->total() > 0)
                    <span
                        class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#AF52DE]/15 text-[#AF52DE]">{{ $platformComments->total() }}</span>
                @endif
            </button>
            <button type="button" @click="activeTab = 'settings'"
                :class="activeTab === 'settings' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="min-h-[40px] px-4 rounded-[11px] text-[13px] transition-all flex items-center gap-2 shrink-0">
                <i data-lucide="sliders" class="w-4 h-4 text-[#007AFF]"></i>
                <span>{{ __('social_media.provider_integration_status') }}</span>
            </button>
            <button type="button" @click="activeTab = 'merchants'"
                :class="activeTab === 'merchants' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="min-h-[40px] px-4 rounded-[11px] text-[13px] transition-all flex items-center gap-2 shrink-0">
                <i data-lucide="users" class="w-4 h-4 text-[#34C759]"></i>
                <span>Pengawasan Merchant</span>
            </button>
            <button type="button" @click="activeTab = 'app_review'"
                :class="activeTab === 'app_review' ?
                    'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                class="min-h-[40px] px-4 rounded-[11px] text-[13px] transition-all flex items-center gap-2 shrink-0">
                <i data-lucide="shield-alert" class="w-4 h-4 text-[#FF9500]"></i>
                <span>{{ __('social_media.meta_app_review_guide') }}</span>
            </button>
        </div>

        {{-- 4. TAB 1: KELOLA KONTEN PLATFORM (COOCA OFFICIAL POSTS) --}}
        <div x-show="activeTab === 'posts'" class="space-y-6">
            {{-- BENTO PROFILE SNAPSHOT & QUICK POST ACTION --}}
            <div
                class="rounded-[22px] sm:rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-6">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-5 pb-5 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-center gap-4 min-w-0">
                        <div
                            class="w-14 h-14 rounded-[20px] bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF] p-0.5 shadow-md shrink-0 flex items-center justify-center">
                            @if (!empty($platform['instagram_profile_picture_url']))
                                <img src="{{ $platform['instagram_profile_picture_url'] }}" alt="Instagram Profile"
                                    class="w-full h-full object-cover rounded-[18px]">
                            @else
                                <div
                                    class="w-full h-full rounded-[18px] bg-white dark:bg-[#1C1C1E] flex items-center justify-center text-[#DD2A7B]">
                                    <i data-lucide="instagram" class="w-7 h-7"></i>
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3
                                    class="text-[17px] sm:text-[18px] font-bold text-black dark:text-white tracking-tight truncate">
                                    Cooca Official Media Hub
                                </h3>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                    {{ $platform['instagram_account_type'] ?: 'MEDIA_CREATOR' }}
                                </span>
                            </div>
                            <p class="text-[13px] font-mono text-[#007AFF] mt-0.5 truncate">
                                @<span>{{ $platform['instagram_username'] ?: 'cooca.indonesia' }}</span>
                                <span class="text-black/40 dark:text-white/40 font-sans ml-2 text-[12px]">•
                                    {{ $platform['instagram_media_count'] ?: '0' }} Konten Aktif</span>
                            </p>
                        </div>
                    </div>

                    <button type="button" @click="openCreatePostModal = true"
                        class="min-h-[44px] px-5 rounded-[13px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-sm shrink-0">
                        <i data-lucide="feather" class="w-4 h-4"></i>
                        <span>Buat Postingan Baru</span>
                    </button>
                </div>

                @php
                    $igPlatformAcc = $platformAccounts->first(fn($a) => $a->platform === 'instagram');
                    $fbPlatformAcc = $platformAccounts->first(fn($a) => $a->platform === 'facebook');
                    $ttPlatformAcc = $platformAccounts->first(fn($a) => $a->platform === 'tiktok');
                    $liPlatformAcc = $platformAccounts->first(fn($a) => $a->platform === 'linkedin');
                @endphp

                {{-- Status Channel & 1-Click Connect Grid --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-[13px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                            <i data-lucide="radio" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>{{ __('social_media.official_channel_connection_status') }}</span>
                        </h4>
                        <span class="text-[11.5px] text-black/50 dark:text-white/50">Admin dapat menghubungkan akun resmi secara langsung</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                        {{-- 1. Instagram Platform Card --}}
                        <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] flex flex-col justify-between gap-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-[11px] bg-[#E1306C]/10 text-[#E1306C] flex items-center justify-center shrink-0">
                                        <i data-lucide="instagram" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">Instagram</span>
                                        <div class="text-[13px] font-bold text-black dark:text-white truncate">
                                            @<span>{{ $platform['instagram_username'] ?: 'cooca.indonesia' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] shrink-0">
                                    {{ !empty($platform['instagram_access_token']) ? 'Aktif' : 'Token Off' }}
                                </span>
                            </div>
                            <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-[11px]">
                                <span class="text-black/50 dark:text-white/50">{{ $platform['instagram_account_type'] ?: 'Media Creator' }}</span>
                                <a href="{{ route('admin.settings.index', ['tab' => 'social']) }}" class="text-[#007AFF] font-bold hover:underline inline-flex items-center gap-0.5">
                                    <span>{{ __('common.settings') }}</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </a>
                            </div>
                        </div>

                        {{-- 2. Facebook Page Card --}}
                        <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] flex flex-col justify-between gap-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-[11px] bg-[#1877F2]/10 text-[#1877F2] flex items-center justify-center shrink-0">
                                        <i data-lucide="facebook" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">Facebook Page</span>
                                        <div class="text-[13px] font-bold text-black dark:text-white truncate">
                                            {{ !empty($platform['app_id']) ? 'Meta Platform Active' : 'Belum Konfigurasi' }}
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ !empty($platform['app_id']) ? 'bg-[#1877F2]/15 text-[#1877F2]' : 'bg-black/5 text-black/40' }} shrink-0">
                                    {{ !empty($platform['app_id']) ? 'Siap' : 'Off' }}
                                </span>
                            </div>
                            <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-[11px]">
                                <span class="text-black/50 dark:text-white/50">Graph API {{ $platform['graph_version'] }}</span>
                                <a href="{{ route('admin.settings.index', ['tab' => 'social']) }}" class="text-[#007AFF] font-bold hover:underline inline-flex items-center gap-0.5">
                                    <span>{{ __('common.settings') }}</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </a>
                            </div>
                        </div>

                        {{-- 3. TikTok Open API Card --}}
                        <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] flex flex-col justify-between gap-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-[11px] bg-black/10 dark:bg-white/10 text-black dark:text-white flex items-center justify-center shrink-0">
                                        <i data-lucide="video" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">TikTok Official</span>
                                        <div class="text-[13px] font-bold text-black dark:text-white truncate">
                                            @if ($ttPlatformAcc && $ttPlatformAcc->isConnected())
                                                {{ $ttPlatformAcc->account_name }}
                                            @else
                                                {{ !empty($platform['tiktok_client_key']) ? 'OAuth PKCE Siap' : 'Belum Konfigurasi' }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if ($ttPlatformAcc && $ttPlatformAcc->isConnected())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] shrink-0">
                                        Terhubung
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-black/5 dark:bg-white/10 text-black/40 dark:text-white/40 shrink-0">
                                        {{ !empty($platform['tiktok_client_key']) ? 'Tersedia' : 'Off' }}
                                    </span>
                                @endif
                            </div>

                            <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-[11px]">
                                @if ($ttPlatformAcc && $ttPlatformAcc->isConnected())
                                    <span class="text-black/50 dark:text-white/50 truncate max-w-[100px]">{{ $ttPlatformAcc->username ? '@'.$ttPlatformAcc->username : 'Akun Resmi' }}</span>
                                    @if(Auth::guard('admin')->user()?->hasPermission('admin.social_media.connect'))

                                    <form method="POST" action="{{ route('admin.social-media.accounts.disconnect', $ttPlatformAcc) }}" onsubmit="return confirm('Putus koneksi akun TikTok platform ini?');">
                                        @csrf
                                        <button type="submit" class="text-[#FF3B30] font-bold hover:underline">Putus</button>
                                    </form>

                                    @endif
                                @elseif (!empty($platform['tiktok_client_key']))
                                    <a href="{{ route('admin.social-media.tiktok.connect') }}" class="w-full min-h-[30px] rounded-[8px] bg-black dark:bg-white text-white dark:text-black font-bold text-[11.5px] inline-flex items-center justify-center gap-1.5 hover:opacity-90 active:scale-[0.98] transition-all">
                                        <i data-lucide="link" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('social_media.connect_tiktok') }}</span>
                                    </a>
                                @else
                                    <a href="{{ route('admin.settings.index', ['tab' => 'social']) }}" class="text-[#007AFF] font-bold hover:underline inline-flex items-center gap-0.5">
                                        <span>Konfigurasi TikTok</span>
                                        <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- 4. LinkedIn Official Card --}}
                        <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] flex flex-col justify-between gap-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-[11px] bg-[#0A66C2]/10 text-[#0A66C2] flex items-center justify-center shrink-0">
                                        <i data-lucide="linkedin" class="w-4 h-4"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 block">LinkedIn Official</span>
                                        <div class="text-[13px] font-bold text-black dark:text-white truncate">
                                            @if ($liPlatformAcc && $liPlatformAcc->isConnected())
                                                {{ $liPlatformAcc->account_name }}
                                            @else
                                                {{ !empty($platform['linkedin_client_id']) ? 'OAuth OpenID Siap' : 'Belum Konfigurasi' }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if ($liPlatformAcc && $liPlatformAcc->isConnected())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] shrink-0">
                                        Terhubung
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-black/5 dark:bg-white/10 text-black/40 dark:text-white/40 shrink-0">
                                        {{ !empty($platform['linkedin_client_id']) ? 'Tersedia' : 'Off' }}
                                    </span>
                                @endif
                            </div>

                            <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between text-[11px]">
                                @if ($liPlatformAcc && $liPlatformAcc->isConnected())
                                    <span class="text-black/50 dark:text-white/50 truncate max-w-[100px]">{{ $liPlatformAcc->username ? '@'.$liPlatformAcc->username : 'Member Cooca' }}</span>
                                    @if(Auth::guard('admin')->user()?->hasPermission('admin.social_media.connect'))

                                    <form method="POST" action="{{ route('admin.social-media.accounts.disconnect', $liPlatformAcc) }}" onsubmit="return confirm('Putus koneksi akun LinkedIn platform ini?');">
                                        @csrf
                                        <button type="submit" class="text-[#FF3B30] font-bold hover:underline">Putus</button>
                                    </form>

                                    @endif
                                @elseif (!empty($platform['linkedin_client_id']))
                                    <a href="{{ route('admin.social-media.linkedin.connect') }}" class="w-full min-h-[30px] rounded-[8px] bg-[#0A66C2] text-white font-bold text-[11.5px] inline-flex items-center justify-center gap-1.5 hover:bg-[#084e96] active:scale-[0.98] transition-all shadow-xs">
                                        <i data-lucide="link" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('social_media.connect_linkedin') }}</span>
                                    </a>
                                @else
                                    <a href="{{ route('admin.settings.index', ['tab' => 'social']) }}" class="text-[#007AFF] font-bold hover:underline inline-flex items-center gap-0.5">
                                        <span>Konfigurasi LinkedIn</span>
                                        <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DAFTAR POSTINGAN RESMI PLATFORM --}}
            <div
                class="rounded-[22px] sm:rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-5">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div>
                        <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('social_media.official_platform_posts_history') }}</h3>
                        <p class="text-[12px] sm:text-[12.5px] text-black/50 dark:text-white/50 mt-0.5">Daftar konten yang
                            dipublikasikan atau dijadwalkan oleh Administrator untuk akun Cooca</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">Total
                            {{ $platformPosts->total() }} Postingan</span>
                        <button type="button" @click="openCreatePostModal = true"
                            class="min-h-[34px] px-3.5 rounded-[10px] text-[12px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Buat Postingan</span>
                        </button>
                    </div>
                </div>

                {{-- FILTER TOOLBAR --}}
                <form method="GET" action="{{ route('admin.social-media.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.05]">
                    <input type="hidden" name="tab" value="posts">
                    
                    {{-- Search Input --}}
                    <div class="sm:col-span-5 relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                            placeholder="{{ __('social_media.search_caption_or_id_placeholder') }}"
                            class="w-full h-[36px] pl-9 pr-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 transition-all">
                    </div>

                    {{-- Status Filter --}}
                    <div class="sm:col-span-3">
                        <select name="status" onchange="this.form.submit()"
                            class="w-full h-[36px] px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 transition-all">
                            <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>Tayang (Published)</option>
                            <option value="scheduled" {{ ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' }}>Terjadwal (Scheduled)</option>
                            <option value="publishing" {{ ($filters['status'] ?? '') === 'publishing' ? 'selected' : '' }}>Memproses (Publishing)</option>
                            <option value="failed" {{ ($filters['status'] ?? '') === 'failed' ? 'selected' : '' }}>Gagal (Failed)</option>
                            <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Draf (Draft)</option>
                        </select>
                    </div>

                    {{-- Channel Filter --}}
                    <div class="sm:col-span-3">
                        <select name="channel" onchange="this.form.submit()"
                            class="w-full h-[36px] px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30 transition-all">
                            <option value="all" {{ ($filters['channel'] ?? 'all') === 'all' ? 'selected' : '' }}>Semua Saluran</option>
                            <option value="tiktok" {{ ($filters['channel'] ?? '') === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                            <option value="instagram" {{ ($filters['channel'] ?? '') === 'instagram' ? 'selected' : '' }}>Instagram</option>
                            <option value="facebook" {{ ($filters['channel'] ?? '') === 'facebook' ? 'selected' : '' }}>Facebook</option>
                            <option value="linkedin" {{ ($filters['channel'] ?? '') === 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                            <option value="threads" {{ ($filters['channel'] ?? '') === 'threads' ? 'selected' : '' }}>Threads</option>
                        </select>
                    </div>

                    {{-- Action Filter Buttons (Icon Only) --}}
                    <div class="sm:col-span-1 flex items-center gap-1.5 justify-end">
                        <button type="submit" title="{{ __('social_media.apply_filter_tooltip') }}"
                            class="w-[36px] h-[36px] rounded-[10px] bg-black dark:bg-white text-white dark:text-black flex items-center justify-center hover:opacity-90 active:scale-95 transition-all shadow-sm shrink-0">
                            <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        </button>
                        @if(!empty($filters['search']) || (!empty($filters['status']) && $filters['status'] !== 'all') || (!empty($filters['channel']) && $filters['channel'] !== 'all'))
                            <a href="{{ route('admin.social-media.index', ['tab' => 'posts']) }}" title="{{ __('social_media.reset_filter_tooltip') }}"
                                class="w-[36px] h-[36px] rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center hover:bg-black/10 dark:hover:bg-white/15 active:scale-95 transition-all shrink-0">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>
                </form>

                @if ($platformPosts->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr
                                    class="border-b border-black/[0.06] dark:border-white/[0.08] text-black/45 dark:text-white/45 text-[11px] uppercase tracking-wider bg-black/[0.01] dark:bg-white/[0.02]">
                                    <th class="px-4 py-3 font-semibold">Media &amp; Konten</th>
                                    <th class="px-4 py-3 font-semibold">Saluran</th>
                                    <th class="px-4 py-3 font-semibold text-center">Status</th>
                                    <th class="px-4 py-3 font-semibold">Dibuat Oleh</th>
                                    <th class="px-4 py-3 font-semibold">Waktu Publikasi</th>
                                    <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                @foreach ($platformPosts as $p)
                                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.025] transition-colors">
                                        <td class="px-4 py-3.5 max-w-sm">
                                            <div class="flex items-start gap-3">
                                                @if (!empty($p->media_urls) && is_array($p->media_urls) && count($p->media_urls) > 0)
                                                    <div
                                                        class="w-12 h-12 rounded-[10px] overflow-hidden bg-black/5 shrink-0 border border-black/10 dark:border-white/10 relative">
                                                        <img src="{{ $p->media_urls[0] }}" alt="Media thumbnail"
                                                            class="w-full h-full object-cover">
                                                        @if(count($p->media_urls) > 1)
                                                            <span class="absolute bottom-0.5 right-0.5 px-1 py-0.2 bg-black/70 text-white text-[9px] rounded font-mono font-bold">{{ count($p->media_urls) }}</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div
                                                        class="w-12 h-12 rounded-[10px] bg-black/5 dark:bg-white/5 flex items-center justify-center shrink-0 text-black/40 dark:text-white/40 border border-black/10 dark:border-white/10">
                                                        <i data-lucide="file-text" class="w-5 h-5"></i>
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <p
                                                        class="font-medium text-black dark:text-white line-clamp-2 text-[12.5px]">
                                                        {{ $p->content }}</p>
                                                    @if ($p->platform_post_id)
                                                        <span
                                                            class="text-[10.5px] font-mono text-black/40 dark:text-white/40 block mt-0.5">ID:
                                                            {{ Str::limit($p->platform_post_id, 18) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            @php
                                                $targetChannels = $p->targets->pluck('channel')->filter()->unique();
                                            @endphp
                                            @if ($targetChannels->isNotEmpty())
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    @foreach ($targetChannels as $ch)
                                                        @if ($ch === 'instagram')
                                                            <span
                                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#E1306C]/10 text-[#E1306C]">
                                                                <i data-lucide="instagram" class="w-3.5 h-3.5"></i>
                                                                <span>Instagram</span>
                                                            </span>
                                                        @elseif($ch === 'facebook')
                                                            <span
                                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#1877F2]/10 text-[#1877F2]">
                                                                <i data-lucide="facebook" class="w-3.5 h-3.5"></i>
                                                                <span>Facebook</span>
                                                            </span>
                                                        @elseif($ch === 'threads')
                                                            <span
                                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/10 dark:bg-white/15 text-black dark:text-white border border-black/10 dark:border-white/10">
                                                                <i data-lucide="at-sign" class="w-3.5 h-3.5"></i>
                                                                <span>Threads</span>
                                                            </span>
                                                        @elseif($ch === 'tiktok')
                                                            <span
                                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/10 dark:bg-white/10 text-black dark:text-white">
                                                                <i data-lucide="video" class="w-3.5 h-3.5"></i>
                                                                <span>TikTok</span>
                                                            </span>
                                                        @elseif($ch === 'linkedin')
                                                            <span
                                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#0A66C2]/10 text-[#0A66C2]">
                                                                <i data-lucide="linkedin" class="w-3.5 h-3.5"></i>
                                                                <span>LinkedIn</span>
                                                            </span>
                                                        @else
                                                            <span
                                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/5 text-black dark:text-white">
                                                                <span>{{ ucfirst($ch) }}</span>
                                                            </span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/5 text-black dark:text-white">
                                                    <span>{{ ucfirst($p->platform ?: 'General') }}</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            @if ($p->status === 'published')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                    Tayang
                                                </span>
                                            @elseif($p->status === 'scheduled')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/12 text-[#007AFF]">
                                                    Terjadwal
                                                </span>
                                            @elseif($p->status === 'publishing')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#FF9500]">
                                                    Memproses
                                                </span>
                                            @elseif($p->status === 'draft')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/10 dark:bg-white/10 text-black/70 dark:text-white/70">
                                                    Draf
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#FF3B30]"
                                                    title="{{ $p->error_message }}">
                                                    Gagal
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-black/60 dark:text-white/60 text-[12px]">
                                            {{ $p->admin?->name ?? 'Super Administrator' }}
                                        </td>
                                        <td class="px-4 py-3.5 text-black/60 dark:text-white/60 text-[12px] tabular-nums">
                                            @if ($p->published_at)
                                                {{ $p->published_at->format('d M Y, H:i') }} WIB
                                            @elseif($p->scheduled_at)
                                                {{ $p->scheduled_at->format('d M Y, H:i') }} WIB (Jadwal)
                                            @else
                                                {{ $p->created_at->format('d M Y, H:i') }} WIB
                                            @endif
                                        </td>
                                        {{-- KOLOM AKSI (HANYA MENGGUNAKAN ICON SAJA) --}}
                                        <td class="px-4 py-3.5 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                {{-- 1. Tombol Detail / Preview (Icon Eye) --}}
                                                <button type="button"
                                                    @click="openPreviewModal({{ json_encode([
                                                        'id' => $p->id,
                                                        'content' => $p->content,
                                                        'media_urls' => $p->media_urls,
                                                        'media_type' => $p->media_type,
                                                        'status' => $p->status,
                                                        'error_message' => $p->error_message,
                                                        'created_at' => \App\Support\TimezoneHelper::formatLocal($p->created_at),
                                                        'published_at' => \App\Support\TimezoneHelper::formatLocal($p->published_at),
                                                        'scheduled_at' => \App\Support\TimezoneHelper::formatLocal($p->scheduled_at),
                                                        'targets' => $p->targets->map(fn($t) => [
                                                            'channel' => $t->channel,
                                                            'status' => $t->status,
                                                            'platform_post_id' => $t->platform_post_id,
                                                            'error_message' => $t->error_message,
                                                        ])
                                                    ]) }})"
                                                    title="{{ __('social_media.view_post_detail_tooltip') }}"
                                                    class="w-8 h-8 rounded-[8px] flex items-center justify-center text-black/65 dark:text-white/65 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 active:scale-95 transition-all">
                                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                                </button>

                                                {{-- 2. Tombol Edit (Icon Pencil) --}}
                                                <button type="button"
                                                    @click="openEditModal({{ json_encode([
                                                        'id' => $p->id,
                                                        'content' => $p->content,
                                                        'scheduled_at' => $p->scheduled_at?->format('Y-m-d\TH:i') ?? '',
                                                    ]) }}, '{{ route('admin.social-media.posts.update', $p) }}')"
                                                    title="{{ __('social_media.edit_post_tooltip') }}"
                                                    class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] hover:bg-[#007AFF]/10 active:scale-95 transition-all">
                                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                                </button>

                                                {{-- 3. Tombol Publish Sekarang (Icon Send) untuk Terjadwal / Draf --}}
                                                @if ($p->status === 'scheduled' || $p->status === 'draft')
                                                    <form method="POST"
                                                        action="{{ route('admin.social-media.posts.publish', $p) }}"
                                                        onsubmit="return confirm('Terbitkan postingan ini ke media sosial sekarang?');"
                                                        class="inline">
                                                        @csrf
                                                        <button type="submit" title="Terbitkan Sekarang"
                                                            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#34C759] hover:bg-[#34C759]/10 active:scale-95 transition-all">
                                                            <i data-lucide="send" class="w-4 h-4"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- 4. Tombol Retry / Coba Kirim Ulang (Icon Rotate-CW) untuk yang Gagal --}}
                                                @if ($p->status === 'failed' || $p->status === 'partially_failed')
                                                    <form method="POST"
                                                        action="{{ route('admin.social-media.posts.retry', $p) }}"
                                                        class="inline">
                                                        @csrf
                                                        <button type="submit" title="{{ __('social_media.retry_send_tooltip') }}"
                                                            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#FF9500] hover:bg-[#FF9500]/10 active:scale-95 transition-all">
                                                            <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- 5. Tombol Hapus (Icon Trash) --}}
                                                <form method="POST"
                                                    action="{{ route('admin.social-media.posts.destroy', $p) }}"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus postingan platform ini? Berkas media terkait di storage juga akan dibersihkan.');"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="{{ __('social_media.delete_post_tooltip') }}"
                                                        class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#FF3B30] hover:bg-[#FF3B30]/10 active:scale-95 transition-all">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-2">
                        {{ $platformPosts->appends(array_merge(['tab' => 'posts'], $filters))->links() }}
                    </div>
                @else
                    <div
                        class="p-12 text-center space-y-3 rounded-[18px] bg-black/[0.015] dark:bg-white/[0.02] border border-dashed border-black/10 dark:border-white/10">
                        <div
                            class="w-12 h-12 rounded-[16px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
                            <i data-lucide="feather" class="w-6 h-6"></i>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-[15px] text-black dark:text-white">{{ __('social_media.no_official_platform_posts') }}
                            </h4>
                            <p class="text-[12.5px] text-black/50 dark:text-white/50 max-w-md mx-auto">Buat konten promosi
                                atau pengumuman pertama Anda untuk dipublikasikan langsung ke akun Instagram, Facebook Page,
                                atau TikTok resmi Cooca.</p>
                        </div>
                        <button type="button" @click="openCreatePostModal = true"
                            class="min-h-[40px] px-4 rounded-[11px] text-[12.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Buat Postingan Sekarang</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- TAB: ANALITIK & PERFORMA ORGANIK (EKSKLUSI META ADS) --}}
        <div x-show="activeTab === 'analytics'" class="space-y-6" style="display: none;">
            {{-- Header & Quick Refresh Action --}}
            <div
                class="rounded-[22px] sm:rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-6">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div
                            class="w-12 h-12 rounded-[16px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center shrink-0">
                            <i data-lucide="trending-up" class="w-6 h-6"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[17px] sm:text-[18px] font-bold text-black dark:text-white tracking-tight">
                                Analitik &amp; Pertumbuhan Organik Platform</h3>
                            <p class="text-[12.5px] text-black/50 dark:text-white/50 mt-0.5">
                                Pantau metrik profil resmi, tingkat engagement, dan kuota publikasi Meta Graph API secara
                                live.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="text-[11.5px] text-black/45 dark:text-white/45 tabular-nums">
                            Sinkronisasi: {{ \Carbon\Carbon::parse($analytics['refreshed_at'] ?? now())->format('H:i:s') }}
                            WIB
                        </span>
                        <a href="{{ route('admin.social-media.index', ['tab' => 'analytics', 'refresh_analytics' => 1]) }}"
                            class="min-h-[38px] px-3.5 rounded-[11px] text-[12px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            <span>Segarkan Data Live</span>
                        </a>
                    </div>
                </div>

                {{-- Kebijakan Privasi & Pengecualian Meta Ads Notice --}}
                <div
                    class="p-4 rounded-[16px] bg-[#007AFF]/[0.06] dark:bg-[#007AFF]/[0.1] border border-[#007AFF]/20 flex items-start gap-3.5">
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0 mt-0.5">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <div class="min-w-0 flex-1 space-y-0.5">
                        <h4 class="text-[13px] font-bold text-black dark:text-white">Fokus Penuh pada Pertumbuhan Organik
                            (Eksklusi Meta Ads)</h4>
                        <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                            Sistem ini dirancang khusus untuk mempublikasikan dan memantau performa konten murni secara
                            organik (Postingan Feed, Reels Video, dan Story). Pengelolaan kampanye Meta Ads berbayar dan
                            anggaran iklan secara terarah dikecualikan demi efisiensi operasional dan kepatuhan privasi
                            data.
                        </p>
                    </div>
                </div>

                {{-- Primary KPI Bento Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Card 1: Instagram Profile --}}
                    <div
                        class="p-4 sm:p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <div class="flex items-center justify-between">
                            <span
                                class="text-[11.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Instagram
                                Bisnis</span>
                            <div
                                class="w-7 h-7 rounded-[8px] bg-[#E1306C]/10 text-[#E1306C] flex items-center justify-center">
                                <i data-lucide="instagram" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <div>
                            <div
                                class="text-[26px] sm:text-[28px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                                {{ number_format($analytics['instagram']['profile']['followers_count'] ?? 0) }}
                            </div>
                            <span class="text-[12px] text-black/55 dark:text-white/55">Total Pengikut
                                (@{{ $analytics['instagram']['profile']['username'] ?? 'cooca.indonesia' }})</span>
                        </div>
                        <div
                            class="pt-2 border-t border-black/[0.05] dark:border-white/[0.06] flex items-center justify-between text-[11.5px] text-black/50 dark:text-white/50">
                            <span>Mengikuti: <strong
                                    class="text-black dark:text-white tabular-nums">{{ $analytics['instagram']['profile']['follows_count'] ?? 0 }}</strong></span>
                            <span>Konten: <strong
                                    class="text-black dark:text-white tabular-nums">{{ $analytics['instagram']['profile']['media_count'] ?? 0 }}</strong></span>
                        </div>
                    </div>

                    {{-- Card 2: Publishing Limit Quota --}}
                    <div
                        class="p-4 sm:p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <div class="flex items-center justify-between">
                            <span
                                class="text-[11.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Kuota
                                Publikasi API</span>
                            <div
                                class="w-7 h-7 rounded-[8px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                <i data-lucide="gauge" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <div>
                            <div class="text-[26px] sm:text-[28px] font-bold text-[#34C759] tabular-nums tracking-tight">
                                {{ $analytics['instagram']['quota_remaining'] ?? 25 }} <span
                                    class="text-[14px] text-black/40 dark:text-white/40 font-normal">/ 25</span>
                            </div>
                            <span class="text-[12px] text-black/55 dark:text-white/55">Sisa kuota posting dalam 24
                                jam</span>
                        </div>
                        <div
                            class="pt-2 border-t border-black/[0.05] dark:border-white/[0.06] flex items-center justify-between text-[11.5px] text-black/50 dark:text-white/50">
                            <span>Terpakai: <strong
                                    class="text-black dark:text-white tabular-nums">{{ $analytics['instagram']['quota_usage'] ?? 0 }}</strong>
                                pos</span>
                            <span class="text-[#34C759] font-medium">Batas Aman</span>
                        </div>
                    </div>

                    {{-- Card 3: Engagement Rate --}}
                    <div
                        class="p-4 sm:p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <div class="flex items-center justify-between">
                            <span
                                class="text-[11.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Engagement
                                Rate</span>
                            <div
                                class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                <i data-lucide="activity" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <div>
                            <div class="text-[26px] sm:text-[28px] font-bold text-[#007AFF] tabular-nums tracking-tight">
                                {{ $analytics['instagram']['engagement_rate'] ?? 0 }}%
                            </div>
                            <span class="text-[12px] text-black/55 dark:text-white/55">Rata-rata interaksi per
                                postingan</span>
                        </div>
                        <div
                            class="pt-2 border-t border-black/[0.05] dark:border-white/[0.06] flex items-center justify-between text-[11.5px] text-black/50 dark:text-white/50">
                            <span><strong
                                    class="text-black dark:text-white tabular-nums">{{ number_format($analytics['instagram']['total_likes'] ?? 0) }}</strong>
                                Likes</span>
                            <span><strong
                                    class="text-black dark:text-white tabular-nums">{{ number_format($analytics['instagram']['total_comments'] ?? 0) }}</strong>
                                Komentar</span>
                        </div>
                    </div>

                    {{-- Card 4: Facebook Page --}}
                    <div
                        class="p-4 sm:p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                        <div class="flex items-center justify-between">
                            <span
                                class="text-[11.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Halaman
                                Facebook</span>
                            <div
                                class="w-7 h-7 rounded-[8px] bg-[#1877F2]/10 text-[#1877F2] flex items-center justify-center">
                                <i data-lucide="facebook" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <div>
                            <div
                                class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white truncate tracking-tight">
                                {{ $analytics['facebook']['page']['name'] ?? 'Cooca Indonesia' }}
                            </div>
                            <span
                                class="text-[12px] text-black/55 dark:text-white/55">{{ $analytics['facebook']['page']['category'] ?? 'Software Bisnis' }}</span>
                        </div>
                        <div
                            class="pt-2 border-t border-black/[0.05] dark:border-white/[0.06] flex items-center justify-between text-[11.5px] text-black/50 dark:text-white/50">
                            <span>Membicarakan: <strong
                                    class="text-black dark:text-white tabular-nums">{{ $analytics['facebook']['page']['talking_about_count'] ?? 0 }}</strong></span>
                            <span>Fans: <strong
                                    class="text-black dark:text-white tabular-nums">{{ $analytics['facebook']['page']['fan_count'] ?? 0 }}</strong></span>
                        </div>
                    </div>
                </div>

                {{-- Content Formats Distribution Bento --}}
                <div
                    class="p-5 rounded-[18px] bg-black/[0.015] dark:bg-white/[0.02] border border-black/[0.05] dark:border-white/[0.06] space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-[13.5px] font-bold text-black dark:text-white">Sebaran Format Konten Terpublikasi
                        </h4>
                        <span class="text-[11.5px] text-black/45 dark:text-white/45">Berdasarkan 15 konten terakhir di
                            Instagram</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div
                            class="p-3.5 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/[0.05] dark:border-white/[0.06] flex items-center gap-3">
                            <div
                                class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="image" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span
                                    class="text-[11px] font-bold uppercase text-black/50 dark:text-white/50 block">Postingan
                                    Feed &amp; Carousel</span>
                                <span
                                    class="text-[16px] font-bold text-black dark:text-white tabular-nums">{{ $analytics['instagram']['feed_count'] ?? 0 }}
                                    Konten</span>
                            </div>
                        </div>
                        <div
                            class="p-3.5 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/[0.05] dark:border-white/[0.06] flex items-center gap-3">
                            <div
                                class="w-9 h-9 rounded-[10px] bg-[#E1306C]/10 text-[#E1306C] flex items-center justify-center shrink-0">
                                <i data-lucide="film" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold uppercase text-black/50 dark:text-white/50 block">Reels
                                    Video</span>
                                <span
                                    class="text-[16px] font-bold text-black dark:text-white tabular-nums">{{ $analytics['instagram']['reels_count'] ?? 0 }}
                                    Video</span>
                            </div>
                        </div>
                        <div
                            class="p-3.5 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/[0.05] dark:border-white/[0.06] flex items-center gap-3">
                            <div
                                class="w-9 h-9 rounded-[10px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center shrink-0">
                                <i data-lucide="history" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span
                                    class="text-[11px] font-bold uppercase text-black/50 dark:text-white/50 block">Instagram
                                    Stories</span>
                                <span class="text-[16px] font-bold text-black dark:text-white tabular-nums">Aktif 24
                                    Jam</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Galeri Konten & Performa Live Instagram --}}
            <div
                class="rounded-[22px] sm:rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-5">
                <div
                    class="flex items-center justify-between gap-4 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div>
                        <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">Performa
                            15 Media Terbaru Instagram</h3>
                        <p class="text-[12px] sm:text-[12.5px] text-black/50 dark:text-white/50 mt-0.5">Daftar media yang
                            dipublikasikan pada @cooca.indonesia beserta jumlah likes dan komentar terkini</p>
                    </div>
                    <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">
                        {{ count($analytics['instagram']['recent_media'] ?? []) }} Media
                    </span>
                </div>

                @if (!empty($analytics['instagram']['recent_media']) && count($analytics['instagram']['recent_media']) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($analytics['instagram']['recent_media'] as $item)
                            <div
                                class="p-4 rounded-[18px] bg-black/[0.015] dark:bg-white/[0.02] border border-black/[0.06] dark:border-white/[0.08] space-y-3 flex flex-col justify-between hover:border-black/20 dark:hover:border-white/20 transition-all">
                                <div class="space-y-3">
                                    <div
                                        class="relative w-full aspect-video rounded-[12px] overflow-hidden bg-black/5 dark:bg-white/5">
                                        @if (!empty($item['thumbnail_url']) || !empty($item['media_url']))
                                            <img src="{{ $item['thumbnail_url'] ?: $item['media_url'] }}"
                                                alt="Post Media" class="w-full h-full object-cover">
                                        @else
                                            <div
                                                class="w-full h-full flex items-center justify-center text-black/30 dark:text-white/30">
                                                <i data-lucide="image" class="w-8 h-8"></i>
                                            </div>
                                        @endif
                                        <div class="absolute top-2 left-2">
                                            @if ($item['media_product_type'] === 'REELS' || $item['media_type'] === 'VIDEO')
                                                <span
                                                    class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-black/70 text-white backdrop-blur-xs flex items-center gap-1">
                                                    <i data-lucide="film" class="w-3 h-3 text-[#E1306C]"></i>
                                                    <span>REELS</span>
                                                </span>
                                            @elseif($item['media_type'] === 'CAROUSEL_ALBUM')
                                                <span
                                                    class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-black/70 text-white backdrop-blur-xs flex items-center gap-1">
                                                    <i data-lucide="layers" class="w-3 h-3 text-[#007AFF]"></i>
                                                    <span>CAROUSEL</span>
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-black/70 text-white backdrop-blur-xs flex items-center gap-1">
                                                    <i data-lucide="image" class="w-3 h-3 text-[#34C759]"></i>
                                                    <span>FEED</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <p class="text-[12.5px] text-black/80 dark:text-white/80 line-clamp-3 leading-relaxed">
                                        {{ $item['caption'] ?: '(Tidak ada keterangan teks)' }}
                                    </p>
                                </div>

                                <div
                                    class="pt-3 border-t border-black/[0.05] dark:border-white/[0.06] flex items-center justify-between">
                                    <div class="flex items-center gap-3 text-[12px] text-black/60 dark:text-white/60">
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="heart" class="w-3.5 h-3.5 text-[#E1306C]"></i>
                                            <strong
                                                class="tabular-nums text-black dark:text-white">{{ number_format($item['like_count']) }}</strong>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                            <strong
                                                class="tabular-nums text-black dark:text-white">{{ number_format($item['comments_count']) }}</strong>
                                        </span>
                                    </div>

                                    @if (!empty($item['permalink']))
                                        <a href="{{ $item['permalink'] }}" target="_blank" rel="noopener noreferrer"
                                            class="p-1.5 rounded-[8px] text-black/40 dark:text-white/40 hover:text-[#007AFF] dark:hover:text-[#007AFF] hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
                                            title="{{ __('social_media.open_in_instagram_tooltip') }}">
                                            <i data-lucide="external-link" class="w-4 h-4"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div
                        class="p-10 text-center space-y-2 rounded-[18px] bg-black/[0.015] dark:bg-white/[0.02] border border-dashed border-black/10 dark:border-white/10">
                        <i data-lucide="image" class="w-8 h-8 text-black/30 dark:text-white/30 mx-auto"></i>
                        <p class="text-[13px] text-black/50 dark:text-white/50">{{ __('social_media.no_instagram_media_loaded') }}
                            atau token memerlukan izin akses.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- 5. TAB 2: KOTAK MASUK INTERAKSI (COMMENTS & ENGAGEMENT) --}}
        <div x-show="activeTab === 'inbox'" class="space-y-6">
            {{-- Banner to Standalone Inbox Menu --}}
            <div class="p-4 sm:p-5 rounded-[20px] bg-gradient-to-r from-[#E7F3FF] to-[#F0FDF4] dark:from-[#1877F2]/15 dark:to-[#34C759]/10 border border-[#1877F2]/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-[14px] bg-[#1877F2] text-white flex items-center justify-center shadow-sm shadow-[#1877F2]/25 shrink-0">
                        <i data-lucide="inbox" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-[14px] font-bold text-[#111827] dark:text-white">Menu Kotak Masuk (Inbox) Kini Berdiri Sendiri</h4>
                        <p class="text-[12.5px] text-[#4B5563] dark:text-[#9CA3AF] mt-0.5">Kelola pesan lengkap omnichannel (Messenger, Instagram, WhatsApp & Komentar) di antarmuka Inbox baru.</p>
                    </div>
                </div>
                <a href="{{ route('admin.inbox.index') }}"
                    class="min-h-[38px] px-4 rounded-[11px] text-[12.5px] font-bold text-white bg-[#1877F2] hover:bg-[#166FE5] transition-all flex items-center justify-center gap-1.5 shadow-sm shrink-0">
                    <span>{{ __('social_media.open_new_inbox') }}</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div
                class="rounded-[22px] sm:rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-5">
                <div
                    class="flex items-center justify-between gap-4 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div>
                        <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('social_media.official_account_comments_interactions') }}</h3>
                        <p class="text-[12px] sm:text-[12.5px] text-black/50 dark:text-white/50 mt-0.5">Kelola dan tanggapi
                            komentar pengunjung pada postingan resmi media sosial Cooca</p>
                    </div>
                    <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">Total
                        {{ $platformComments->total() }} Komentar</span>
                </div>

                @if ($platformComments->count() > 0)
                    <div class="divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                        @foreach ($platformComments as $c)
                            <div class="py-4 space-y-2.5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="w-9 h-9 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center font-bold text-[13px] text-black/70 dark:text-white/70">
                                            {{ strtoupper(substr($c->from_name ?: 'User', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="font-bold text-[13px] text-black dark:text-white">{{ $c->from_name ?: 'Pengguna Media Sosial' }}</span>
                                                <span
                                                    class="text-[11px] font-mono text-black/40 dark:text-white/40">({{ $c->platform }})</span>
                                                @if ($c->status === 'replied')
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Dibalas</span>
                                                @else
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500]">Belum
                                                        Dibalas</span>
                                                @endif
                                            </div>
                                            <span class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">
                                                {{ $c->created_time ? $c->created_time->format('d M Y, H:i') : $c->created_at->format('d M Y, H:i') }}
                                                WIB
                                            </span>
                                        </div>
                                    </div>

                                    <button type="button"
                                        @click="openReplyModal($el.dataset.id, $el.dataset.from, $el.dataset.message)"
                                        data-id="{{ $c->id }}"
                                        data-from="{{ $c->from_name ?: 'User' }}"
                                        data-message="{{ $c->message }}"
                                        class="min-h-[32px] px-3 rounded-[9px] text-[12px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-[0.98] transition-all inline-flex items-center gap-1.5">
                                        <i data-lucide="reply" class="w-3.5 h-3.5"></i>
                                        <span>Balas</span>
                                    </button>
                                </div>

                                <div class="pl-11">
                                    <p
                                        class="text-[13px] text-black/85 dark:text-white/85 bg-black/[0.02] dark:bg-white/[0.03] p-3 rounded-[12px] border border-black/[0.04] dark:border-white/[0.05]">
                                        {{ $c->message }}
                                    </p>
                                    @if ($c->post)
                                        <p class="text-[11.5px] text-black/45 dark:text-white/45 mt-1 truncate">
                                            Pada postingan: <span
                                                class="italic">"{{ Str::limit($c->post->content, 60) }}"</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-2">
                        {{ $platformComments->appends(['tab' => 'inbox'])->links() }}
                    </div>
                @else
                    <div
                        class="p-10 text-center space-y-2.5 rounded-[18px] bg-black/[0.015] dark:bg-white/[0.02] border border-dashed border-black/10 dark:border-white/10">
                        <i data-lucide="message-square" class="w-8 h-8 text-black/30 dark:text-white/30 mx-auto"></i>
                        <p class="text-[13px] text-black/50 dark:text-white/50">{{ __('social_media.no_comments_yet') }}
                            postingan resmi platform.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- 6. TAB 3: INTEGRATION HUB & CURRENT PROVIDER STATUS --}}
        <div x-show="activeTab === 'settings'" class="space-y-6">
            <div
                class="rounded-[22px] sm:rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-6 w-full min-w-0">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <div
                            class="w-11 h-11 sm:w-12 sm:h-12 rounded-[16px] bg-gradient-to-br from-[#1877F2] via-[#E1306C] to-[#000000] text-white flex items-center justify-center shadow-md shadow-[#1877F2]/20 shrink-0">
                            <i data-lucide="share-2" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">
                                    Pusat Konfigurasi Media Sosial Platform Terpadu
                                </h3>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                                    Terintegrasi
                                </span>
                            </div>
                            <p class="text-[12px] sm:text-[12.5px] text-black/55 dark:text-white/55 mt-0.5 max-w-2xl">
                                Seluruh konfigurasi kredensial Meta App (Facebook &amp; Threads), Instagram Platform
                                (Cooca-IG), dan TikTok Open API kini dikelola secara terpusat di <strong>Pengaturan Platform
                                    &amp; Sistem</strong>.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('admin.settings.index', ['tab' => 'social']) }}"
                        class="min-h-[44px] px-5 rounded-[12px] text-[13px] font-bold bg-[#007AFF] hover:bg-[#0071E3] text-white active:scale-[0.98] transition-all inline-flex items-center justify-center gap-2 shadow-sm shrink-0">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                        <span>{{ __('social_media.open_social_media_settings') }}</span>
                        <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 opacity-70"></i>
                    </a>
                </div>

                {{-- Status Badges --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div
                        class="p-3.5 rounded-[14px] bg-[#1877F2]/8 border border-[#1877F2]/20 flex items-center justify-between">
                        <span class="text-[12px] font-semibold text-[#1877F2]">Meta App ID</span>
                        <span
                            class="text-[12px] font-mono font-bold text-[#1877F2]">{{ $platform['app_id'] ?: 'Belum Diisi' }}</span>
                    </div>
                    <div
                        class="p-3.5 rounded-[14px] bg-[#E1306C]/8 border border-[#E1306C]/20 flex items-center justify-between">
                        <span class="text-[12px] font-semibold text-[#E1306C]">Instagram Resmi</span>
                        <span class="text-[12px] font-mono font-bold text-[#E1306C]">@{{ $platform['instagram_username'] ? : 'cooca.indonesia' }}</span>
                    </div>
                    <div
                        class="p-3.5 rounded-[14px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 flex items-center justify-between">
                        <span class="text-[12px] font-semibold text-black dark:text-white">TikTok Client Key</span>
                        <span
                            class="text-[12px] font-mono font-bold text-black dark:text-white truncate max-w-[120px]">{{ $platform['tiktok_client_key'] ?: 'Belum Diisi' }}</span>
                    </div>
                    <div
                        class="p-3.5 rounded-[14px] bg-[#0A66C2]/8 border border-[#0A66C2]/20 flex items-center justify-between">
                        <span class="text-[12px] font-semibold text-[#0A66C2]">LinkedIn Client ID</span>
                        <span
                            class="text-[12px] font-mono font-bold text-[#0A66C2] truncate max-w-[120px]">{{ $platform['linkedin_client_id'] ?: 'Belum Diisi' }}</span>
                    </div>
                </div>

                {{-- Current Settings Snapshot Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span
                            class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Meta
                            App Secret</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ !empty($platform['app_secret']) ? '••••••••••••••••' : 'Belum Dikonfigurasi' }}
                        </div>
                    </div>

                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span
                            class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Instagram
                            App Name</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ $platform['instagram_app_name'] ?: 'Cooca-IG' }}
                        </div>
                    </div>

                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span
                            class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Status
                            Token Instagram</span>
                        <div class="text-[13px] font-bold text-[#248A3D] dark:text-[#30D158] flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span>{{ !empty($platform['instagram_access_token']) ? __('social_media.saved_encrypted') : __('social_media.no_token_yet') }}</span>
                        </div>
                    </div>

                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span
                            class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">Webhook
                            Verify Token</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ $platform['webhook_verify_token'] ?: 'cooca_meta_social_webhook_token' }}
                        </div>
                    </div>

                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span
                            class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">TikTok
                            Secret</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ !empty($platform['tiktok_client_secret']) ? '••••••••••••••••' : 'Belum Dikonfigurasi' }}
                        </div>
                    </div>

                    <div
                        class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                        <span
                            class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">LinkedIn
                            Secret</span>
                        <div class="text-[13px] font-mono font-bold text-black dark:text-white truncate">
                            {{ !empty($platform['linkedin_client_secret']) ? '••••••••••••••••' : 'Belum Dikonfigurasi' }}
                        </div>
                    </div>
                </div>

                {{-- Endpoints & Callback URL --}}
                <div class="space-y-3 pt-2">
                    <h4 class="text-[13px] font-bold text-black dark:text-white tracking-tight">{{ __('social_media.official_callback_webhook_endpoint') }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div
                            class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-[#0A66C2]">LinkedIn
                                    Admin Callback</span>
                                <button type="button" @click="copyText('{{ $platform['linkedin_admin_redirect_uri'] }}', 'li_admin_uri')"
                                    class="text-[11px] font-bold text-[#007AFF] hover:underline">
                                    <span x-text="copied === 'li_admin_uri' ? 'Tersalin' : 'Salin'"></span>
                                </button>
                            </div>
                            <div class="font-mono text-[11px] text-black/80 dark:text-white/80 truncate">
                                {{ $platform['linkedin_admin_redirect_uri'] }}</div>
                        </div>

                        <div
                            class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-[#0A66C2]">LinkedIn
                                    Merchant Callback</span>
                                <button type="button" @click="copyText('{{ $platform['linkedin_redirect_uri'] }}', 'li_uri')"
                                    class="text-[11px] font-bold text-[#007AFF] hover:underline">
                                    <span x-text="copied === 'li_uri' ? 'Tersalin' : 'Salin'"></span>
                                </button>
                            </div>
                            <div class="font-mono text-[11px] text-black/80 dark:text-white/80 truncate">
                                {{ $platform['linkedin_redirect_uri'] }}</div>
                        </div>

                        <div
                            class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">TikTok
                                    Admin Callback</span>
                                <button type="button"
                                    @click="copyText('{{ $platform['tiktok_admin_redirect_uri'] }}', 'tt_admin_uri')"
                                    class="text-[11px] font-bold text-[#007AFF] hover:underline">
                                    <span x-text="copied === 'tt_admin_uri' ? 'Tersalin' : 'Salin'"></span>
                                </button>
                            </div>
                            <div class="font-mono text-[11px] text-black/80 dark:text-white/80 truncate">
                                {{ $platform['tiktok_admin_redirect_uri'] }}</div>
                        </div>

                        <div
                            class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">TikTok
                                    Merchant Callback</span>
                                <button type="button"
                                    @click="copyText('{{ $platform['tiktok_redirect_uri'] }}', 'tt_uri')"
                                    class="text-[11px] font-bold text-[#007AFF] hover:underline">
                                    <span x-text="copied === 'tt_uri' ? 'Tersalin' : 'Salin'"></span>
                                </button>
                            </div>
                            <div class="font-mono text-[11px] text-black/80 dark:text-white/80 truncate">
                                {{ $platform['tiktok_redirect_uri'] }}</div>
                        </div>

                        <div
                            class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-[#1877F2]">Meta
                                    Webhook URL</span>
                                <button type="button" @click="copyText('{{ $platform['webhook_url'] }}', 'meta_wh')"
                                    class="text-[11px] font-bold text-[#007AFF] hover:underline">
                                    <span x-text="copied === 'meta_wh' ? 'Tersalin' : 'Salin'"></span>
                                </button>
                            </div>
                            <div class="font-mono text-[11px] text-black/80 dark:text-white/80 truncate">
                                {{ $platform['webhook_url'] }}</div>
                        </div>

                        <div
                            class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-[#E1306C]">Instagram
                                    Redirect URI</span>
                                <button type="button" @click="copyText('{{ $platform['ig_redirect_uri'] }}', 'ig_uri')"
                                    class="text-[11px] font-bold text-[#007AFF] hover:underline">
                                    <span x-text="copied === 'ig_uri' ? 'Tersalin' : 'Salin'"></span>
                                </button>
                            </div>
                            <div class="font-mono text-[11px] text-black/80 dark:text-white/80 truncate">
                                {{ $platform['ig_redirect_uri'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 7. TAB 4: MERCHANTS OVERSIGHT --}}
        <div x-show="activeTab === 'merchants'" class="space-y-6">
            <div
                class="rounded-[22px] sm:rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-5">
                <div
                    class="flex items-center justify-between gap-4 pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div>
                        <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('social_media.merchant_social_media_connection_status') }}</h3>
                        <p class="text-[12px] sm:text-[12.5px] text-black/50 dark:text-white/50 mt-0.5">Daftar
                            toko/merchant yang mengaktifkan integrasi akun Facebook, Instagram, Threads, atau TikTok</p>
                    </div>
                    <span class="text-[12px] text-black/45 dark:text-white/45 tabular-nums">Total
                        {{ $merchants->total() }} Merchant</span>
                </div>

                @if ($merchants->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr
                                    class="border-b border-black/[0.06] dark:border-white/[0.08] text-black/45 dark:text-white/45 text-[11px] uppercase tracking-wider bg-black/[0.01] dark:bg-white/[0.02]">
                                    <th class="px-4 py-3 font-semibold">Bisnis / Merchant</th>
                                    <th class="px-4 py-3 font-semibold">Kanal Terhubung</th>
                                    <th class="px-4 py-3 font-semibold text-center">Status</th>
                                    <th class="px-4 py-3 font-semibold text-right">Terakhir Diperbarui</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                                @foreach ($merchants as $biz)
                                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.025] transition-colors">
                                        <td class="px-4 py-3.5">
                                            <div class="font-bold text-black dark:text-white">{{ $biz->name }}</div>
                                            <div class="text-[11.5px] text-black/50 dark:text-white/50">ID:
                                                {{ Str::limit($biz->id, 8) }}</div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                @foreach ($biz->socialMediaAccounts as $acc)
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[8px] text-[11.5px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/75 dark:text-white/75">
                                                        @if ($acc->platform === 'facebook')
                                                            <i data-lucide="facebook" class="w-3 h-3 text-[#1877F2]"></i>
                                                        @elseif ($acc->platform === 'instagram')
                                                            <i data-lucide="instagram" class="w-3 h-3 text-[#E1306C]"></i>
                                                        @elseif ($acc->platform === 'threads')
                                                            <i data-lucide="at-sign"
                                                                class="w-3 h-3 text-black dark:text-white"></i>
                                                        @elseif ($acc->platform === 'tiktok')
                                                            <i data-lucide="video"
                                                                class="w-3 h-3 text-black dark:text-white"></i>
                                                        @else
                                                            <i data-lucide="globe" class="w-3 h-3 text-black/50"></i>
                                                        @endif
                                                        <span>{{ $acc->account_name }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                                Aktif
                                            </span>
                                        </td>
                                        <td
                                            class="px-4 py-3.5 text-right text-black/50 dark:text-white/50 text-[12px] tabular-nums">
                                            {{ optional($biz->socialMediaAccounts->first()?->updated_at)->diffForHumans() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-2">
                        {{ $merchants->appends(['tab' => 'merchants'])->links() }}
                    </div>
                @else
                    <div
                        class="p-10 text-center space-y-2.5 rounded-[18px] bg-black/[0.015] dark:bg-white/[0.02] border border-dashed border-black/10 dark:border-white/10">
                        <i data-lucide="users" class="w-8 h-8 text-black/30 dark:text-white/30 mx-auto"></i>
                        <p class="text-[13px] text-black/50 dark:text-white/50">{{ __('social_media.no_merchants_activated') }}
                            integrasi media sosial.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- 8. TAB 5: APP REVIEW & COMPLIANCE GUIDE --}}
        <div x-show="activeTab === 'app_review'" class="space-y-6">
            <div
                class="rounded-[22px] sm:rounded-[24px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-md border border-black/[0.08] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-6">
                <div class="pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <h3 class="text-[16px] sm:text-[17px] font-bold text-black dark:text-white tracking-tight">Panduan Meta
                        App Review &amp; Kepatuhan Kebijakan</h3>
                    <p class="text-[12px] sm:text-[12.5px] text-black/50 dark:text-white/50 mt-0.5">Panduan persyaratan
                        untuk mengajukan izin permissions produksi ke Meta App Review</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-[12.5px]">
                    <div
                        class="p-4.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2.5">
                        <span class="font-bold text-black dark:text-white block text-[13.5px]">1. Permissions yang
                            Diwajibkan</span>
                        <ul class="space-y-1.5 text-black/70 dark:text-white/70 list-disc list-inside">
                            <li><code class="text-[#007AFF] font-mono">pages_manage_posts</code>: Membuat postingan feed di
                                Facebook Page.</li>
                            <li><code class="text-[#007AFF] font-mono">pages_read_engagement</code>: Membaca reaksi,
                                komentar, dan metrik FB.</li>
                            <li><code class="text-[#007AFF] font-mono">instagram_basic</code> &amp; <code
                                    class="text-[#007AFF] font-mono">instagram_content_publish</code>: Memposting foto ke
                                akun Instagram Bisnis.</li>
                            <li><code class="text-[#007AFF] font-mono">pages_messaging</code> &amp; <code
                                    class="text-[#007AFF] font-mono">instagram_manage_messages</code>: Balas komentar dan
                                pesan.</li>
                            <li><code class="text-[#007AFF] font-mono">threads_content_publish</code> &amp; <code
                                    class="text-[#007AFF] font-mono">threads_manage_replies</code>: Publikasi konten
                                Threads.</li>
                        </ul>
                    </div>

                    <div
                        class="p-4.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2.5">
                        <span class="font-bold text-black dark:text-white block text-[13.5px]">2. Skenario Rekaman Video
                            Demo</span>
                        <ol class="space-y-1.5 text-black/70 dark:text-white/70 list-decimal list-inside">
                            <li>Rekam login merchant ke dashboard COOCA.</li>
                            <li>Klik tombol <strong>Hubungkan Media Sosial</strong> hingga muncul popup Meta Login.</li>
                            <li>Pilih Facebook Page &amp; Akun Instagram yang dikelola.</li>
                            <li>Tunjukkan cara membuat dan memposting gambar ke Instagram / Facebook dari halaman COOCA.
                            </li>
                            <li>Tunjukkan bukti postingan berhasil tayang di profil Facebook &amp; Instagram.</li>
                            <li>Tunjukkan cara membalas komentar masuk di Kotak Masuk COOCA.</li>
                        </ol>
                    </div>
                </div>

                <div
                    class="p-4 rounded-[16px] bg-[#007AFF]/8 border border-[#007AFF]/20 text-[12px] text-black/70 dark:text-white/70 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                        <span>Dokumentasi Resmi: Meta App Review &amp; Facebook Login for Business</span>
                    </div>
                    <a href="https://developers.facebook.com/docs/app-review" target="_blank" rel="noopener noreferrer"
                        class="px-3 py-1.5 rounded-[9px] bg-[#007AFF] text-white font-bold text-[11.5px] shrink-0 hover:bg-[#0071E3] transition-colors">
                        {{ __('social_media.open_meta_docs') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- 9. MODAL BUAT POSTINGAN RESMI PLATFORM (APPLE HIG MODAL SHEET) --}}
        <template x-teleport="body">
            <div x-show="openCreatePostModal"
                class="fixed inset-0 z-[200] overflow-y-auto"
                x-cloak style="display: none;"
                role="dialog" aria-modal="true"
                @keydown.escape.window="openCreatePostModal = false">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                    @click="openCreatePostModal = false"
                    aria-hidden="true"></div>

                <!-- Centering Wrapper -->
                <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-5 md:p-6 pointer-events-none">
                    <div class="relative w-full max-w-5xl xl:max-w-6xl 2xl:max-w-[1440px] rounded-[26px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-5 sm:p-7 space-y-6 my-4 max-h-[92vh] overflow-y-auto pointer-events-auto"
                        @click.stop>

                        {{-- Modal Header --}}
                        <div class="flex items-center justify-between pb-4 border-b border-black/[0.06] dark:border-white/[0.08]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[14px] bg-gradient-to-br from-[#1877F2]/15 via-[#E1306C]/15 to-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0 shadow-xs">
                                    <i data-lucide="feather" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-[17px] sm:text-[18px] font-bold text-black dark:text-white tracking-tight">{{ __('social_media.create_official_cooca_post') }}</h3>
                                    <p class="text-[12px] sm:text-[12.5px] text-black/55 dark:text-white/55">Publikasikan konten resmi platform ke akun media sosial Cooca dengan pratinjau live omnichannel</p>
                                </div>
                            </div>
                            <button type="button" @click="openCreatePostModal = false"
                                class="w-9 h-9 rounded-full flex items-center justify-center text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors cursor-pointer"
                                aria-label="{{ __('social_media.close_modal') }}">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        @if(Auth::guard('admin')->user()?->hasPermission('admin.social_media.post'))


                        <form method="POST" action="{{ route('admin.social-media.posts.store') }}"
                            enctype="multipart/form-data" class="space-y-6" @submit="isSubmitting = true">
                            @csrf
                            <input type="hidden" name="media_type" :value="postFormat">

                            {{-- Form Content with Bento 2-Column Architecture --}}
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

                                {{-- LEFT COLUMN: FORM CONTROLS & SETTINGS (Col-span 7) --}}
                                <div class="lg:col-span-7 space-y-5">

                                    {{-- 1. Pilihan Platform (Multi-Select Bento Tiles) --}}
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label class="text-[11.5px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                                                Pilih Saluran Publikasi Resmi Platform <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <button type="button" @click="toggleAllPlatforms()"
                                                class="text-[11.5px] font-bold text-[#007AFF] hover:underline cursor-pointer">
                                                <span x-text="selectedPlatforms.length === allPlatforms.length ? 'Pilih Instagram Saja' : 'Pilih Semua Saluran'"></span>
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                            {{-- Instagram --}}
                                            <label class="p-2.5 rounded-[13px] border cursor-pointer transition-all flex flex-col gap-1.5"
                                                :class="selectedPlatforms.includes('instagram') ?
                                                    'bg-[#E1306C]/10 border-[#E1306C] text-[#E1306C] font-bold shadow-xs' :
                                                    'bg-black/[0.02] dark:bg-white/[0.02] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/[0.04]'">
                                                <input type="checkbox" name="platforms[]" value="instagram" x-model="selectedPlatforms" class="sr-only">
                                                <div class="flex items-center justify-between">
                                                    <div class="w-6 h-6 rounded-[7px] bg-[#E1306C]/15 text-[#E1306C] flex items-center justify-center shrink-0">
                                                        <i data-lucide="instagram" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                    <span class="w-2 h-2 rounded-full" :class="selectedPlatforms.includes('instagram') ? 'bg-[#E1306C]' : 'bg-black/10 dark:bg-white/20'"></span>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-[12px] leading-tight font-bold">Instagram</div>
                                                    <div class="text-[10px] opacity-70 truncate font-mono">@cooca.indonesia</div>
                                                </div>
                                            </label>

                                            {{-- Facebook --}}
                                            <label class="p-2.5 rounded-[13px] border cursor-pointer transition-all flex flex-col gap-1.5"
                                                :class="selectedPlatforms.includes('facebook') ?
                                                    'bg-[#1877F2]/10 border-[#1877F2] text-[#1877F2] font-bold shadow-xs' :
                                                    'bg-black/[0.02] dark:bg-white/[0.02] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/[0.04]'">
                                                <input type="checkbox" name="platforms[]" value="facebook" x-model="selectedPlatforms" class="sr-only">
                                                <div class="flex items-center justify-between">
                                                    <div class="w-6 h-6 rounded-[7px] bg-[#1877F2]/15 text-[#1877F2] flex items-center justify-center shrink-0">
                                                        <i data-lucide="facebook" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                    <span class="w-2 h-2 rounded-full" :class="selectedPlatforms.includes('facebook') ? 'bg-[#1877F2]' : 'bg-black/10 dark:bg-white/20'"></span>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-[12px] leading-tight font-bold">Facebook</div>
                                                    <div class="text-[10px] opacity-70 truncate">Halaman Resmi</div>
                                                </div>
                                            </label>

                                            {{-- Threads --}}
                                            <label class="p-2.5 rounded-[13px] border cursor-pointer transition-all flex flex-col gap-1.5"
                                                :class="selectedPlatforms.includes('threads') ?
                                                    'bg-black/10 dark:bg-white/15 border-black dark:border-white text-black dark:text-white font-bold shadow-xs' :
                                                    'bg-black/[0.02] dark:bg-white/[0.02] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/[0.04]'">
                                                <input type="checkbox" name="platforms[]" value="threads" x-model="selectedPlatforms" class="sr-only">
                                                <div class="flex items-center justify-between">
                                                    <div class="w-6 h-6 rounded-[7px] bg-black/10 dark:bg-white/10 text-black dark:text-white flex items-center justify-center shrink-0">
                                                        <i data-lucide="at-sign" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                    <span class="w-2 h-2 rounded-full" :class="selectedPlatforms.includes('threads') ? 'bg-black dark:bg-white' : 'bg-black/10 dark:bg-white/20'"></span>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-[12px] leading-tight font-bold">Threads</div>
                                                    <div class="text-[10px] opacity-70 truncate">Meta Threads</div>
                                                </div>
                                            </label>

                                            {{-- TikTok --}}
                                            <label class="p-2.5 rounded-[13px] border cursor-pointer transition-all flex flex-col gap-1.5"
                                                :class="selectedPlatforms.includes('tiktok') ?
                                                    'bg-black/10 dark:bg-white/15 border-black dark:border-white text-black dark:text-white font-bold shadow-xs' :
                                                    'bg-black/[0.02] dark:bg-white/[0.02] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/[0.04]'">
                                                <input type="checkbox" name="platforms[]" value="tiktok" x-model="selectedPlatforms" class="sr-only">
                                                <div class="flex items-center justify-between">
                                                    <div class="w-6 h-6 rounded-[7px] bg-black/10 dark:bg-white/10 text-black dark:text-white flex items-center justify-center shrink-0">
                                                        <i data-lucide="video" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                    <span class="w-2 h-2 rounded-full" :class="selectedPlatforms.includes('tiktok') ? 'bg-black dark:bg-white' : 'bg-black/10 dark:bg-white/20'"></span>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-[12px] leading-tight font-bold">TikTok</div>
                                                    <div class="text-[10px] opacity-70 truncate">Video Pendek</div>
                                                </div>
                                            </label>

                                            {{-- LinkedIn --}}
                                            <label class="p-2.5 rounded-[13px] border cursor-pointer transition-all flex flex-col gap-1.5"
                                                :class="selectedPlatforms.includes('linkedin') ?
                                                    'bg-[#0A66C2]/10 border-[#0A66C2] text-[#0A66C2] font-bold shadow-xs' :
                                                    'bg-black/[0.02] dark:bg-white/[0.02] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/[0.04]'">
                                                <input type="checkbox" name="platforms[]" value="linkedin" x-model="selectedPlatforms" class="sr-only">
                                                <div class="flex items-center justify-between">
                                                    <div class="w-6 h-6 rounded-[7px] bg-[#0A66C2]/15 text-[#0A66C2] flex items-center justify-center shrink-0">
                                                        <i data-lucide="linkedin" class="w-3.5 h-3.5"></i>
                                                    </div>
                                                    <span class="w-2 h-2 rounded-full" :class="selectedPlatforms.includes('linkedin') ? 'bg-[#0A66C2]' : 'bg-black/10 dark:bg-white/20'"></span>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-[12px] leading-tight font-bold">LinkedIn</div>
                                                    <div class="text-[10px] opacity-70 truncate">Company Page</div>
                                                </div>
                                            </label>
                                        </div>
                                        <div x-show="selectedPlatforms.length === 0" class="p-2.5 rounded-[10px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[11.5px] text-[#FF3B30] font-semibold flex items-center gap-1.5">
                                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                            <span>Pilih minimal 1 saluran untuk mempublikasikan postingan platform.</span>
                                        </div>
                                    </div>

                                    {{-- 2. Pilihan Format Konten (Bento Segmented Selector) --}}
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label class="text-[11.5px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                                                Format Konten <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <span class="text-[11.5px] text-[#007AFF] font-semibold" x-text="postFormatLabel()"></span>
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-6 gap-2">
                                            {{-- Foto Tunggal --}}
                                            <button type="button" @click="setMediaFormat('photo')"
                                                :class="postFormat === 'photo' || postFormat === 'image' ? 'bg-[#007AFF] text-white shadow-sm ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                                class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1 cursor-pointer">
                                                <div class="flex items-center justify-between">
                                                    <i data-lucide="image" class="w-4 h-4"></i>
                                                    <span class="w-1.5 h-1.5 rounded-full" :class="postFormat === 'photo' || postFormat === 'image' ? 'bg-white' : 'bg-transparent'"></span>
                                                </div>
                                                <div class="text-[12px] font-bold leading-tight">Foto Tunggal</div>
                                                <div class="text-[10px] opacity-75 truncate">Feed 4:5 / 1:1</div>
                                            </button>

                                            {{-- Carousel (Multi-Upload) --}}
                                            <button type="button" @click="setMediaFormat('carousel')"
                                                :class="postFormat === 'carousel' ? 'bg-[#FF9500] text-white shadow-sm ring-2 ring-[#FF9500]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                                class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1 cursor-pointer">
                                                <div class="flex items-center justify-between">
                                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                                    <span class="w-1.5 h-1.5 rounded-full" :class="postFormat === 'carousel' ? 'bg-white' : 'bg-transparent'"></span>
                                                </div>
                                                <div class="text-[12px] font-bold leading-tight">Carousel</div>
                                                <div class="text-[10px] opacity-75 truncate">2-10 Berkas</div>
                                            </button>

                                            {{-- Video Feed --}}
                                            <button type="button" @click="setMediaFormat('video')"
                                                :class="postFormat === 'video' ? 'bg-[#007AFF] text-white shadow-sm ring-2 ring-[#007AFF]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                                class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1 cursor-pointer">
                                                <div class="flex items-center justify-between">
                                                    <i data-lucide="video" class="w-4 h-4"></i>
                                                    <span class="w-1.5 h-1.5 rounded-full" :class="postFormat === 'video' ? 'bg-white' : 'bg-transparent'"></span>
                                                </div>
                                                <div class="text-[12px] font-bold leading-tight">Video Feed</div>
                                                <div class="text-[10px] opacity-75 truncate">16:9 / Landscape</div>
                                            </button>

                                            {{-- Reels --}}
                                            <button type="button" @click="setMediaFormat('reels')"
                                                :class="postFormat === 'reels' ? 'bg-[#AF52DE] text-white shadow-sm ring-2 ring-[#AF52DE]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                                class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1 cursor-pointer">
                                                <div class="flex items-center justify-between">
                                                    <i data-lucide="film" class="w-4 h-4"></i>
                                                    <span class="w-1.5 h-1.5 rounded-full" :class="postFormat === 'reels' ? 'bg-white' : 'bg-transparent'"></span>
                                                </div>
                                                <div class="text-[12px] font-bold leading-tight">Reels Video</div>
                                                <div class="text-[10px] opacity-75 truncate">Vertikal 9:16</div>
                                            </button>

                                            {{-- Story --}}
                                            <button type="button" @click="setMediaFormat('story')"
                                                :class="postFormat === 'story' ? 'bg-[#E1306C] text-white shadow-sm ring-2 ring-[#E1306C]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                                class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1 cursor-pointer">
                                                <div class="flex items-center justify-between">
                                                    <i data-lucide="history" class="w-4 h-4"></i>
                                                    <span class="w-1.5 h-1.5 rounded-full" :class="postFormat === 'story' ? 'bg-white' : 'bg-transparent'"></span>
                                                </div>
                                                <div class="text-[12px] font-bold leading-tight">Story</div>
                                                <div class="text-[10px] opacity-75 truncate">{{ __('social_media.story_24h') }}</div>
                                            </button>

                                            {{-- Teks Saja --}}
                                            <button type="button" @click="setMediaFormat('text')"
                                                :class="postFormat === 'text' ? 'bg-[#34C759] text-white shadow-sm ring-2 ring-[#34C759]/30' : 'bg-black/[0.03] dark:bg-white/[0.05] text-black/70 dark:text-white/70 hover:bg-black/[0.06]'"
                                                class="p-2.5 rounded-[14px] border border-black/5 dark:border-white/10 text-left transition-all flex flex-col gap-1 cursor-pointer">
                                                <div class="flex items-center justify-between">
                                                    <i data-lucide="align-left" class="w-4 h-4"></i>
                                                    <span class="w-1.5 h-1.5 rounded-full" :class="postFormat === 'text' ? 'bg-white' : 'bg-transparent'"></span>
                                                </div>
                                                <div class="text-[12px] font-bold leading-tight">Teks Saja</div>
                                                <div class="text-[10px] opacity-75 truncate">Threads / FB / LI</div>
                                            </button>
                                        </div>

                                        {{-- Guidance helper for selected format --}}
                                        <div class="p-2.5 rounded-[11px] bg-black/[0.03] dark:bg-white/[0.04] text-[11.5px] text-black/65 dark:text-white/65 flex items-center gap-2 border border-black/5 dark:border-white/5">
                                            <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                                            <span x-show="postFormat === 'photo' || postFormat === 'image'">Format foto feed standar. Optimal untuk infografis produk, update fitur, promo, atau pengumuman resmi.</span>
                                            <span x-show="postFormat === 'carousel'" style="display: none;">Carousel multi-gambar/video (2 hingga 10 berkas). Audiens dapat menggeser slide ke samping (swipeable).</span>
                                            <span x-show="postFormat === 'video'" style="display: none;">Video Feed reguler (H.264/MP4). Optimal untuk video demonstrasi software, tutorial, dan webinar.</span>
                                            <span x-show="postFormat === 'reels'" style="display: none;">Reels video vertikal 9:16 (1080x1920). Diterbitkan otomatis di Reels &amp; disinkronkan ke feed utama.</span>
                                            <span x-show="postFormat === 'story'" style="display: none;">Instagram Story berdurasi 24 jam dengan visual penuh. Pada API resmi Meta, Story memprioritaskan visual tanpa teks caption panjang.</span>
                                            <span x-show="postFormat === 'text'" style="display: none;">Format teks narasi murni tanpa media berkas. Didukung oleh Threads, Facebook, dan LinkedIn.</span>
                                        </div>
                                    </div>

                                    {{-- 3. Media Upload Area (Single or Carousel) --}}
                                    <div x-show="postFormat !== 'text'" class="space-y-3 pt-1">
                                        <div class="flex items-center justify-between">
                                            <label class="text-[11.5px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                                                Berkas Media Gambar / Video <span class="text-[#FF3B30]">*</span>
                                            </label>
                                            <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.04] dark:bg-white/[0.06] text-[11.5px] font-medium">
                                                <button type="button" @click="mediaSourceTab = 'upload'"
                                                    :class="mediaSourceTab === 'upload' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60'"
                                                    class="px-2.5 py-1 rounded-[7px] transition-colors cursor-pointer">
                                                    {{ __('social_media.upload_file') }}
                                                </button>
                                                <button type="button" @click="mediaSourceTab = 'url'"
                                                    :class="mediaSourceTab === 'url' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60'"
                                                    class="px-2.5 py-1 rounded-[7px] transition-colors cursor-pointer">
                                                    {{ __('social_media.direct_url') }}
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Mode A: Carousel Multi-Upload (2 - 10 Media) --}}
                                        <div x-show="postFormat === 'carousel' && mediaSourceTab === 'upload'" class="space-y-3">
                                            <input type="file" name="media_files[]" id="admin_carousel_files_input" x-ref="carouselInput" multiple
                                                @change="handleCarouselFilesSelect($event)" accept="image/jpeg,image/png,image/webp,video/mp4" class="hidden">

                                            <div class="flex items-center justify-between text-[12px]">
                                                <span class="font-bold text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                                    <span>Urutan Slide Carousel:</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500]" x-text="carouselItems.length + ' / 10 media'"></span>
                                                </span>
                                                <button type="button" @click="$refs.carouselInput.click()"
                                                    class="px-3 py-1 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 font-bold text-[11.5px] transition-colors flex items-center gap-1 cursor-pointer">
                                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                                    <span>Tambah Slide Media</span>
                                                </button>
                                            </div>

                                            {{-- Carousel Empty State Dropzone --}}
                                            <div x-show="carouselItems.length === 0"
                                                @click="$refs.carouselInput.click()"
                                                @dragover.prevent="isDragging = true"
                                                @dragleave.prevent="isDragging = false"
                                                @drop.prevent="handleCarouselDrop($event)"
                                                class="rounded-[18px] border-2 border-dashed p-6 text-center cursor-pointer transition-all group"
                                                :class="isDragging ? 'border-[#FF9500] bg-[#FF9500]/10 scale-[1.01]' : 'border-black/15 dark:border-white/15 hover:border-[#FF9500] hover:bg-[#FF9500]/5'">
                                                <div class="w-12 h-12 rounded-[14px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center mx-auto mb-2.5 transition-transform group-hover:scale-110">
                                                    <i data-lucide="layers" class="w-6 h-6"></i>
                                                </div>
                                                <div class="text-[13px] font-bold text-black dark:text-white group-hover:text-[#FF9500] transition-colors">
                                                    Tarik &amp; Lepas 2 hingga 10 Foto / Video di Sini
                                                </div>
                                                <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-1">
                                                    Mendukung JPG, PNG, WebP, MP4 hingga 20MB per berkas. Klik untuk memilih dari komputer.
                                                </p>
                                            </div>

                                            {{-- Carousel Items Tray with Reordering & Sequence Badges --}}
                                            <div x-show="carouselItems.length > 0" class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                                <template x-for="(item, idx) in carouselItems" :key="idx">
                                                    <div class="relative p-2 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 flex flex-col gap-2 group hover:border-[#FF9500]/50 transition-all">
                                                        {{-- Sequence Badge --}}
                                                        <div class="absolute top-3 left-3 w-5 h-5 rounded-full bg-black/80 backdrop-blur-md text-white text-[10.5px] font-bold flex items-center justify-center z-10 shadow-xs" x-text="idx + 1"></div>

                                                        {{-- Thumbnail Preview --}}
                                                        <div class="w-full aspect-square rounded-[10px] overflow-hidden bg-black/5 dark:bg-black/30 flex items-center justify-center relative">
                                                            <template x-if="item.mime.startsWith('image/')">
                                                                <img :src="item.previewUrl" class="w-full h-full object-cover">
                                                            </template>
                                                            <template x-if="item.mime.startsWith('video/')">
                                                                <video :src="item.previewUrl" class="w-full h-full object-cover"></video>
                                                            </template>
                                                        </div>

                                                        {{-- Slide Title --}}
                                                        <div class="text-[11px] font-medium text-black/70 dark:text-white/70 truncate px-0.5" x-text="item.name"></div>

                                                        {{-- Reorder & Delete Controls --}}
                                                        <div class="flex items-center justify-between text-[11px] pt-0.5 border-t border-black/5 dark:border-white/5">
                                                            <div class="flex items-center gap-1">
                                                                <button type="button" @click="moveCarouselItem(idx, -1)" :disabled="idx === 0"
                                                                    class="w-6 h-6 rounded-[6px] bg-black/5 dark:bg-white/10 hover:bg-black/10 flex items-center justify-center disabled:opacity-25 transition-opacity cursor-pointer">
                                                                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                                                </button>
                                                                <button type="button" @click="moveCarouselItem(idx, 1)" :disabled="idx === carouselItems.length - 1"
                                                                    class="w-6 h-6 rounded-[6px] bg-black/5 dark:bg-white/10 hover:bg-black/10 flex items-center justify-center disabled:opacity-25 transition-opacity cursor-pointer">
                                                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                                                </button>
                                                            </div>
                                                            <button type="button" @click="removeCarouselItem(idx)"
                                                                class="w-6 h-6 rounded-[6px] text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center justify-center transition-colors cursor-pointer">
                                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        {{-- Mode B: Single File Upload (Photo, Video, Reels, Story) --}}
                                        <div x-show="postFormat !== 'carousel' && mediaSourceTab === 'upload'" class="space-y-2.5">
                                            <input type="file" name="media_file" id="admin_media_file_input" x-ref="fileInput"
                                                @change="handleFileSelect($event)"
                                                :accept="postFormat === 'photo' || postFormat === 'image' ? 'image/jpeg,image/png,image/webp,image/gif' : (postFormat === 'video' || postFormat === 'reels' ? 'video/mp4,video/quicktime' : 'image/jpeg,image/png,image/webp,video/mp4,video/quicktime')"
                                                class="hidden">

                                            {{-- Drop Zone when empty --}}
                                            <div x-show="!filePreviewUrl"
                                                @click="$refs.fileInput.click()"
                                                @dragover.prevent="isDragging = true"
                                                @dragleave.prevent="isDragging = false"
                                                @drop.prevent="handleFileDrop($event)"
                                                class="rounded-[18px] border-2 border-dashed p-6 text-center cursor-pointer transition-all group"
                                                :class="isDragging ? 'border-[#007AFF] bg-[#007AFF]/10 scale-[1.01]' : 'border-black/15 dark:border-white/15 hover:border-[#007AFF] hover:bg-[#007AFF]/5'">
                                                <div class="w-12 h-12 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] text-black/50 dark:text-white/50 group-hover:text-[#007AFF] group-hover:scale-110 transition-all flex items-center justify-center mx-auto mb-2.5">
                                                    <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                                                </div>
                                                <div class="text-[13px] font-bold text-black dark:text-white group-hover:text-[#007AFF] transition-colors">
                                                    Tarik &amp; Lepas Berkas di Sini, atau Klik untuk Memilih
                                                </div>
                                                <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-1"
                                                    x-text="postFormat === 'reels' ? 'Wajib video vertikal MP4/MOV (H.264) rasio 9:16 hingga 100MB' : ((postFormat === 'video') ? 'Video horizontal MP4/MOV hingga 100MB (Rasio 16:9 disarankan)' : 'Foto JPG, PNG, WebP hingga 15MB • Rekomendasi rasio 4:5 (1080x1350) atau 1:1')">
                                                </p>
                                            </div>

                                            {{-- File Preview Card when selected --}}
                                            <div x-show="filePreviewUrl" style="display: none;"
                                                class="rounded-[16px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 p-3.5 flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <template x-if="fileMime && fileMime.startsWith('image/')">
                                                        <img :src="filePreviewUrl" alt="Pratinjau Foto" class="w-16 h-16 rounded-[10px] object-cover border border-black/10 dark:border-white/10 shrink-0">
                                                    </template>
                                                    <template x-if="fileMime && fileMime.startsWith('video/')">
                                                        <video :src="filePreviewUrl" controls class="w-24 h-16 rounded-[10px] object-cover bg-black shrink-0"></video>
                                                    </template>
                                                    <div class="min-w-0">
                                                        <div class="text-[13px] font-bold text-black dark:text-white truncate" x-text="fileName"></div>
                                                        <div class="text-[11.5px] text-black/50 dark:text-white/50 flex items-center gap-2 mt-0.5">
                                                            <span x-text="fileSize"></span>
                                                            <span>•</span>
                                                            <span class="uppercase font-semibold text-[#007AFF]" x-text="postFormat"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <button type="button" @click="$refs.fileInput.click()"
                                                        class="h-8 px-2.5 rounded-[8px] text-[11.5px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors cursor-pointer">
                                                        Ganti Berkas
                                                    </button>
                                                    <button type="button" @click="clearFile()"
                                                        class="h-8 w-8 rounded-[8px] text-[#FF3B30] hover:bg-[#FF3B30]/10 transition-colors flex items-center justify-center cursor-pointer">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Mode C: Direct URL --}}
                                        <div x-show="mediaSourceTab === 'url'" style="display: none;" class="space-y-1.5">
                                            <div class="relative">
                                                <i data-lucide="link" class="w-4 h-4 absolute left-3 top-3 text-black/40 dark:text-white/40"></i>
                                                <input type="url" name="media_url" x-model="mediaUrl"
                                                    placeholder="https://cdn.cooca.id/promo/cooca-erp-v2.jpg"
                                                    class="w-full h-10 pl-9 pr-3 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                            </div>
                                            <p class="text-[11px] text-black/50 dark:text-white/50">
                                                Pastikan URL dapat diakses publik dengan protokol HTTPS untuk diunduh langsung oleh server Instagram, Facebook, dan TikTok Graph API.
                                            </p>
                                        </div>
                                    </div>

                                    {{-- 4. Isi Caption & Tagar --}}
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label class="text-[11.5px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                                                Caption / Isi Postingan
                                                <span x-show="postFormat === 'story'" class="text-[10.5px] font-normal lowercase opacity-70">(opsional untuk story)</span>
                                            </label>
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold inline-flex items-center gap-1 border transition-colors"
                                                    :class="hashtagCount <= 5 ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/30' : 'bg-[#FF3B30]/15 text-[#FF3B30] border-[#FF3B30]/30'">
                                                    <i data-lucide="hash" class="w-3 h-3"></i>
                                                    <span x-text="hashtagCount + ' / 5 tagar'"></span>
                                                </span>
                                                <span class="text-[11.5px] font-mono text-black/45 dark:text-white/45">
                                                    <span x-text="postCaption.length"></span> / 2.200
                                                </span>
                                            </div>
                                        </div>
                                        <textarea name="content" x-model="postCaption" rows="4" :required="postFormat !== 'story'"
                                            maxlength="2200"
                                            :placeholder="postFormat === 'story' ?
                                                'Caption opsional (Instagram Story memprioritaskan visual media)...' :
                                                'Tuliskan caption postingan resmi Cooca... Jelaskan fitur baru, solusi sistem kasir, atau promo berlangganan UMKM.'"
                                            class="w-full bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/15 rounded-[14px] p-3.5 text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 resize-none transition-all"></textarea>

                                        {{-- Alert Exceeding 5 Hashtags --}}
                                        <div x-show="hashtagCount > 5" style="display: none;"
                                            class="p-2.5 rounded-[11px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[11.5px] text-[#FF3B30] flex items-center gap-2">
                                            <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                                            <span>Maksimal 5 tagar direkomendasikan algoritma Meta untuk jangkauan organik terbaik. Kurangi <span x-text="hashtagCount - 5" class="font-bold"></span> tagar.</span>
                                        </div>

                                        {{-- Chip Tagar Cepat Cooca --}}
                                        <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                            <span class="text-[11px] text-black/45 dark:text-white/45">Tagar resmi:</span>
                                            @foreach (['#CoocaERP', '#UMKMIndonesia', '#KasirDigital', '#BisnisSukses', '#SaaSMaju'] as $tag)
                                                <button type="button" @click="insertHashtag('{{ $tag }}')"
                                                    class="px-2 py-0.5 rounded-[7px] text-[11px] font-mono bg-black/[0.04] dark:bg-white/[0.06] text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors cursor-pointer">
                                                    {{ $tag }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- 5. Opsi Waktu Publikasi (Multi-Mode & Per-Channel Support) --}}
                                    <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="text-[12.5px] font-bold text-black dark:text-white">Waktu &amp; Penjadwalan Publikasi</span>
                                                <p class="text-[11px] text-black/50 dark:text-white/50">Pilih penayangan langsung serentak atau atur jadwal per saluran</p>
                                            </div>
                                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">Fleksibel</span>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[12px]">
                                            <label class="p-2.5 rounded-[10px] border cursor-pointer transition-all flex items-center gap-2"
                                                :class="postPublishMode === 'now' ?
                                                    'bg-[#007AFF]/10 border-[#007AFF] text-black dark:text-white font-bold shadow-xs' :
                                                    'bg-white dark:bg-[#2C2C2E] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                                <input type="radio" name="timing_mode" value="now" x-model="postPublishMode" class="sr-only">
                                                <i data-lucide="zap" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                                <span>Langsung Sekarang</span>
                                            </label>

                                            <label class="p-2.5 rounded-[10px] border cursor-pointer transition-all flex items-center gap-2"
                                                :class="postPublishMode === 'schedule_all' ?
                                                    'bg-[#5856D6]/10 border-[#5856D6] text-black dark:text-white font-bold shadow-xs' :
                                                    'bg-white dark:bg-[#2C2C2E] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                                <input type="radio" name="timing_mode" value="schedule_all" x-model="postPublishMode" class="sr-only">
                                                <i data-lucide="clock" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                                                <span>Jadwal Serentak</span>
                                            </label>

                                            <label class="p-2.5 rounded-[10px] border cursor-pointer transition-all flex items-center gap-2"
                                                :class="postPublishMode === 'per_channel' ?
                                                    'bg-[#FF9500]/10 border-[#FF9500] text-black dark:text-white font-bold shadow-xs' :
                                                    'bg-white dark:bg-[#2C2C2E] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                                                <input type="radio" name="timing_mode" value="per_channel" x-model="postPublishMode" class="sr-only">
                                                <i data-lucide="sliders" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                                                <span>Beda per Saluran</span>
                                            </label>
                                        </div>

                                        {{-- Mode B: Jadwal Serentak --}}
                                        <div x-show="postPublishMode === 'schedule_all'" style="display: none;"
                                            class="pt-2 border-t border-black/5 dark:border-white/5 space-y-1.5">
                                            <label class="block text-[11.5px] font-semibold text-black/70 dark:text-white/70">Pilih Tanggal &amp; Jam Penayangan Serentak</label>
                                            <input type="datetime-local" name="scheduled_at"
                                                class="w-full min-h-[38px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[10px] px-3 text-[12.5px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#5856D6]">
                                            <p class="text-[11px] text-black/50 dark:text-white/50">
                                                Seluruh saluran yang dipilih akan otomatis dipublikasikan bersamaan saat waktu tiba.
                                            </p>
                                        </div>

                                        {{-- Mode C: Beda Waktu per Saluran --}}
                                        <div x-show="postPublishMode === 'per_channel'" style="display: none;"
                                            class="pt-2 border-t border-black/5 dark:border-white/5 space-y-2.5">
                                            <p class="text-[11.5px] text-black/60 dark:text-white/60">
                                                Atur waktu spesifik per media sosial (misal: Instagram langsung sekarang, Facebook jam 2 siang, Threads nanti malam):
                                            </p>
                                            <div class="space-y-2">
                                                <template x-for="ch in selectedPlatforms" :key="ch">
                                                    <div class="p-3 rounded-[12px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 space-y-2">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-6 h-6 rounded-[6px] flex items-center justify-center"
                                                                    :class="ch === 'instagram' ? 'bg-[#E1306C]/15 text-[#E1306C]' : (
                                                                        ch === 'facebook' ? 'bg-[#1877F2]/15 text-[#1877F2]' : (
                                                                        ch === 'linkedin' ? 'bg-[#0A66C2]/15 text-[#0A66C2]' :
                                                                        'bg-black/10 dark:bg-white/15 text-black dark:text-white'))">
                                                                    <template x-if="ch === 'instagram'"><i data-lucide="instagram" class="w-3.5 h-3.5"></i></template>
                                                                    <template x-if="ch === 'facebook'"><i data-lucide="facebook" class="w-3.5 h-3.5"></i></template>
                                                                    <template x-if="ch === 'threads'"><i data-lucide="at-sign" class="w-3.5 h-3.5"></i></template>
                                                                    <template x-if="ch === 'tiktok'"><i data-lucide="video" class="w-3.5 h-3.5"></i></template>
                                                                    <template x-if="ch === 'linkedin'"><i data-lucide="linkedin" class="w-3.5 h-3.5"></i></template>
                                                                </div>
                                                                <span class="text-[12.5px] font-bold capitalize text-black dark:text-white" x-text="ch"></span>
                                                            </div>
                                                            <div class="inline-flex p-0.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] text-[11px]">
                                                                <button type="button" @click="channelTiming[ch] = 'now'"
                                                                    :class="channelTiming[ch] === 'now' ? 'bg-white dark:bg-[#1C1C1E] text-[#007AFF] font-bold shadow-xs' : 'text-black/60 dark:text-white/60'"
                                                                    class="px-2.5 py-0.5 rounded-[6px] transition-colors cursor-pointer">
                                                                    {{ __('social_media.direct_now') }}
                                                                </button>
                                                                <button type="button" @click="channelTiming[ch] = 'schedule'"
                                                                    :class="channelTiming[ch] === 'schedule' ? 'bg-white dark:bg-[#1C1C1E] text-[#FF9500] font-bold shadow-xs' : 'text-black/60 dark:text-white/60'"
                                                                    class="px-2.5 py-0.5 rounded-[6px] transition-colors cursor-pointer">
                                                                    {{ __('common.schedule') }}
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <input type="hidden" :name="'platform_timing[' + ch + ']'" :value="channelTiming[ch]">

                                                        <div x-show="channelTiming[ch] === 'schedule'" class="pt-1.5 border-t border-black/5 dark:border-white/5">
                                                            <input type="datetime-local" :name="'platform_scheduled_at[' + ch + ']'"
                                                                x-model="channelScheduledAt[ch]"
                                                                class="w-full h-8 px-2.5 rounded-[8px] text-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#FF9500]">
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- RIGHT COLUMN: LIVE OMNICHANNEL PREVIEW & INSPECTOR (Col-span 5) --}}
                                <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-4">

                                    {{-- Video Aspect Ratio Inspector Alert --}}
                                    <div x-show="(postFormat === 'reels' || postFormat === 'video') && isLandscapeVideo" style="display: none;"
                                        class="p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] space-y-1.5 shadow-xs">
                                        <div class="flex items-center gap-2 font-bold text-[12.5px]">
                                            <i data-lucide="smartphone" class="w-4 h-4 shrink-0"></i>
                                            <span>Video Horizontal Terdeteksi (<span x-text="videoRatio ? videoRatio + ':1' : ''"></span>)</span>
                                        </div>
                                        <p class="text-[11.5px] leading-relaxed opacity-90">
                                            Video yang diunggah berukuran <span x-text="videoWidth + 'x' + videoHeight" class="font-mono font-bold"></span>. Untuk Instagram Reels &amp; TikTok, disarankan format vertikal 9:16 (1080x1920) agar memenuhi layar smartphone secara sempurna.
                                        </p>
                                    </div>

                                    <div x-show="(postFormat === 'reels' || postFormat === 'video') && !isLandscapeVideo && videoWidth && videoHeight" style="display: none;"
                                        class="p-2.5 rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] flex items-center gap-2 text-[11.5px] font-semibold">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                                        <span>Rasio vertikal optimal terdeteksi (<span x-text="videoWidth + 'x' + videoHeight" class="font-mono"></span>).</span>
                                    </div>

                                    {{-- LIVE SMARTPHONE PREVIEW CARD (Apple HIG Bento) --}}
                                    <div class="rounded-[22px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 p-3.5 space-y-3">
                                        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/5">
                                            <div class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55">
                                                <i data-lucide="smartphone" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                                <span>Pratinjau Saluran Live</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF]" x-text="postFormat.toUpperCase()"></span>
                                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70" x-text="previewAspectRatio"></span>
                                            </div>
                                        </div>

                                        {{-- Aspect Ratio & Social Media Size Toolbar --}}
                                        <div class="space-y-1.5" x-show="postFormat !== 'text'">
                                            <div class="flex items-center justify-between text-[11px]">
                                                <span class="font-bold text-black/60 dark:text-white/60">Simulasi Rasio:</span>
                                                <span class="text-[10px] font-medium text-[#007AFF] truncate max-w-[210px]" x-text="getAspectRatioDescription()"></span>
                                            </div>
                                            <div class="grid grid-cols-4 gap-1 p-1 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] text-[11px] font-bold">
                                                {{-- 4:5 Feed IG --}}
                                                <button type="button" @click="previewAspectRatio = '4:5'"
                                                    :class="previewAspectRatio === '4:5' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs ring-1 ring-[#007AFF]/30' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                                    class="py-1.5 rounded-[9px] transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                                                    <span class="text-[12px]">4:5</span>
                                                    <span class="text-[9px] opacity-75 font-normal">Feed IG</span>
                                                </button>
                                                {{-- 1:1 Square --}}
                                                <button type="button" @click="previewAspectRatio = '1:1'"
                                                    :class="previewAspectRatio === '1:1' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs ring-1 ring-[#007AFF]/30' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                                    class="py-1.5 rounded-[9px] transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                                                    <span class="text-[12px]">1:1</span>
                                                    <span class="text-[9px] opacity-75 font-normal">Square</span>
                                                </button>
                                                {{-- 9:16 Reels / Story --}}
                                                <button type="button" @click="previewAspectRatio = '9:16'"
                                                    :class="previewAspectRatio === '9:16' ? 'bg-white dark:bg-[#2C2C2E] text-[#AF52DE] shadow-xs ring-1 ring-[#AF52DE]/30' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                                    class="py-1.5 rounded-[9px] transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                                                    <span class="text-[12px]">9:16</span>
                                                    <span class="text-[9px] opacity-75 font-normal">Reels</span>
                                                </button>
                                                {{-- 16:9 Landscape --}}
                                                <button type="button" @click="previewAspectRatio = '16:9'"
                                                    :class="previewAspectRatio === '16:9' ? 'bg-white dark:bg-[#2C2C2E] text-[#FF9500] shadow-xs ring-1 ring-[#FF9500]/30' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                                                    class="py-1.5 rounded-[9px] transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                                                    <span class="text-[12px]">16:9</span>
                                                    <span class="text-[9px] opacity-75 font-normal">Landscape</span>
                                                </button>
                                            </div>

                                            {{-- Fit / Cover mode pill switcher --}}
                                            <div class="flex items-center justify-between text-[10.5px] px-1 pt-0.5 text-black/50 dark:text-white/50">
                                                <span>Mode Tampilan:</span>
                                                <div class="inline-flex items-center gap-1.5">
                                                    <button type="button" @click="mediaFitMode = 'cover'"
                                                        :class="mediaFitMode === 'cover' ? 'text-[#007AFF] font-bold underline' : 'hover:text-black dark:hover:text-white'"
                                                        class="cursor-pointer">
                                                        Cover (Penuh)
                                                    </button>
                                                    <span>•</span>
                                                    <button type="button" @click="mediaFitMode = 'contain'"
                                                        :class="mediaFitMode === 'contain' ? 'text-[#007AFF] font-bold underline' : 'hover:text-black dark:hover:text-white'"
                                                        class="cursor-pointer">
                                                        Fit (Utuh)
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Phone Card Container --}}
                                        <div class="rounded-[20px] bg-white dark:bg-[#151516] border border-black/10 dark:border-white/10 shadow-lg overflow-hidden text-black dark:text-white transition-all duration-300"
                                            :class="getPreviewCardWidthClass()">

                                            {{-- Reels Top Bar (when 9:16) --}}
                                            <div x-show="previewAspectRatio === '9:16'" class="px-3.5 py-2.5 bg-black text-white flex items-center justify-between text-[11px] font-bold">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[13px] tracking-tight">Reels</span>
                                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-70"></i>
                                                </div>
                                                <div class="flex items-center gap-2 opacity-80">
                                                    <i data-lucide="camera" class="w-4 h-4"></i>
                                                </div>
                                            </div>

                                            {{-- Post Card Header (Feed mode: 4:5, 1:1, 16:9) --}}
                                            <div x-show="previewAspectRatio !== '9:16'" class="p-3 flex items-center justify-between">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#1877F2] via-[#E1306C] to-[#000000] text-white font-bold text-[11px] flex items-center justify-center shrink-0 shadow-xs">
                                                        C
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="text-[12.5px] font-bold truncate flex items-center gap-1">
                                                            <span>cooca.indonesia</span>
                                                            <i data-lucide="badge-check" class="w-3.5 h-3.5 text-[#007AFF] fill-[#007AFF]"></i>
                                                        </div>
                                                        <div class="text-[10px] text-black/50 dark:text-white/50 flex items-center gap-1 truncate">
                                                            <span x-text="postPublishMode === 'now' ? 'Baru saja' : 'Terjadwal'"></span>
                                                            <span>•</span>
                                                            <span x-text="selectedPlatforms.length ? selectedPlatforms.join(', ') : 'Saluran platform'"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <i data-lucide="more-horizontal" class="w-4 h-4 text-black/40 dark:text-white/40"></i>
                                            </div>

                                            {{-- Post Media Display Area --}}
                                            <div class="bg-black/5 dark:bg-black/30 relative flex items-center justify-center overflow-hidden transition-all duration-200"
                                                :class="getPreviewAspectClass()">

                                                {{-- Single Photo Preview --}}
                                                <template x-if="postFormat === 'photo' || postFormat === 'image' || postFormat === 'story'">
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <template x-if="filePreviewUrl">
                                                            <img :src="filePreviewUrl" class="w-full h-full" :class="mediaFitMode === 'contain' ? 'object-contain bg-black' : 'object-cover'">
                                                        </template>
                                                        <template x-if="!filePreviewUrl && mediaSourceTab === 'url' && mediaUrl">
                                                            <img :src="mediaUrl" class="w-full h-full" :class="mediaFitMode === 'contain' ? 'object-contain bg-black' : 'object-cover'">
                                                        </template>
                                                        <template x-if="!filePreviewUrl && (!mediaUrl || mediaSourceTab !== 'url')">
                                                            <div class="text-center p-6 space-y-2">
                                                                <div class="w-12 h-12 rounded-[14px] bg-black/5 dark:bg-white/10 flex items-center justify-center mx-auto text-black/40 dark:text-white/40">
                                                                    <i data-lucide="image" class="w-6 h-6"></i>
                                                                </div>
                                                                <div class="text-[11.5px] font-medium text-black/50 dark:text-white/50">Pratinjau Foto Platform</div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                                {{-- Carousel Preview --}}
                                                <template x-if="postFormat === 'carousel'">
                                                    <div class="w-full h-full relative flex items-center justify-center">
                                                        <template x-if="carouselItems.length > 0">
                                                            <div class="w-full h-full relative">
                                                                <template x-if="carouselItems[activePreviewSlide] && carouselItems[activePreviewSlide].mime.startsWith('image/')">
                                                                    <img :src="carouselItems[activePreviewSlide].previewUrl" class="w-full h-full" :class="mediaFitMode === 'contain' ? 'object-contain bg-black' : 'object-cover'">
                                                                </template>
                                                                <template x-if="carouselItems[activePreviewSlide] && carouselItems[activePreviewSlide].mime.startsWith('video/')">
                                                                    <video :src="carouselItems[activePreviewSlide].previewUrl" class="w-full h-full" :class="mediaFitMode === 'contain' ? 'object-contain bg-black' : 'object-cover'" controls></video>
                                                                </template>
                                                                {{-- Slide Counter Pill --}}
                                                                <div class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full bg-black/70 backdrop-blur-md text-white text-[10px] font-bold">
                                                                    <span x-text="(activePreviewSlide + 1) + ' / ' + carouselItems.length"></span>
                                                                </div>
                                                                {{-- Slide Navigation Arrows --}}
                                                                <div x-show="carouselItems.length > 1" class="absolute inset-x-2 top-1/2 -translate-y-1/2 flex items-center justify-between pointer-events-none">
                                                                    <button type="button" @click="activePreviewSlide = Math.max(0, activePreviewSlide - 1)"
                                                                        :disabled="activePreviewSlide === 0"
                                                                        class="w-7 h-7 rounded-full bg-black/60 text-white flex items-center justify-center pointer-events-auto disabled:opacity-20 transition-opacity cursor-pointer">
                                                                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                                                                    </button>
                                                                    <button type="button" @click="activePreviewSlide = Math.min(carouselItems.length - 1, activePreviewSlide + 1)"
                                                                        :disabled="activePreviewSlide === carouselItems.length - 1"
                                                                        class="w-7 h-7 rounded-full bg-black/60 text-white flex items-center justify-center pointer-events-auto disabled:opacity-20 transition-opacity cursor-pointer">
                                                                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <template x-if="carouselItems.length === 0">
                                                            <div class="text-center p-6 space-y-2">
                                                                <div class="w-12 h-12 rounded-[14px] bg-[#FF9500]/10 flex items-center justify-center mx-auto text-[#FF9500]">
                                                                    <i data-lucide="layers" class="w-6 h-6"></i>
                                                                </div>
                                                                <div class="text-[11.5px] font-medium text-black/50 dark:text-white/50">{{ __('social_media.no_media_slide_selected') }}</div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                                {{-- Video / Reels Preview --}}
                                                <template x-if="postFormat === 'video' || postFormat === 'reels'">
                                                    <div class="w-full h-full flex items-center justify-center bg-black">
                                                        <template x-if="filePreviewUrl">
                                                            <video :src="filePreviewUrl" controls class="w-full h-full" :class="mediaFitMode === 'contain' ? 'object-contain' : 'object-cover'"></video>
                                                        </template>
                                                        <template x-if="!filePreviewUrl && mediaSourceTab === 'url' && mediaUrl">
                                                            <video :src="mediaUrl" controls class="w-full h-full" :class="mediaFitMode === 'contain' ? 'object-contain' : 'object-cover'"></video>
                                                        </template>
                                                        <template x-if="!filePreviewUrl && (!mediaUrl || mediaSourceTab !== 'url')">
                                                            <div class="text-center p-6 space-y-2">
                                                                <div class="w-12 h-12 rounded-[14px] bg-white/10 flex items-center justify-center mx-auto text-white/50">
                                                                    <i data-lucide="video" class="w-6 h-6"></i>
                                                                </div>
                                                                <div class="text-[11.5px] font-medium text-white/60">Pratinjau Video Platform</div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                                {{-- Text Format Preview --}}
                                                <template x-if="postFormat === 'text'">
                                                    <div class="w-full h-full p-4 flex flex-col justify-center bg-gradient-to-br from-black/[0.03] to-black/[0.06] dark:from-white/[0.03] dark:to-white/[0.06]">
                                                        <p class="text-[13px] leading-relaxed text-black/80 dark:text-white/80 italic font-serif"
                                                            x-text="postCaption ? postCaption : 'Pratinjau konten teks postingan Threads / Facebook / LinkedIn...'"></p>
                                                    </div>
                                                </template>
                                            </div>

                                            {{-- Engagement Action Bar --}}
                                            <div class="p-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <i data-lucide="heart" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                                                    <i data-lucide="message-circle" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                                                    <i data-lucide="send" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                                                </div>
                                                <i data-lucide="bookmark" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                                            </div>

                                            {{-- Live Caption Display --}}
                                            <div class="px-3 pb-3 text-[12px] leading-relaxed space-y-1">
                                                <div class="text-black/85 dark:text-white/85">
                                                    <span class="font-bold mr-1.5">cooca.indonesia</span>
                                                    <span x-text="postCaption ? (postCaption.length > 140 ? postCaption.slice(0, 140) + '...' : postCaption) : 'Caption postingan resmi platform Cooca akan ditampilkan di sini.'"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Brand Integrity Reminder Card --}}
                                    <div class="p-3.5 rounded-[16px] bg-[#007AFF]/10 border border-[#007AFF]/20 text-[#007AFF] space-y-1 shadow-xs">
                                        <div class="flex items-center gap-2 font-bold text-[12px]">
                                            <i data-lucide="shield-check" class="w-4 h-4 shrink-0"></i>
                                            <span>Standar Konten Platform Resmi</span>
                                        </div>
                                        <p class="text-[11px] leading-relaxed opacity-90">
                                            Konten yang dipublikasikan dari Admin Center merepresentasikan brand resmi Cooca Indonesia. Pastikan visual tajam dan narasi selaras dengan identitas brand.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Footer Actions --}}
                            <div class="pt-4 flex items-center justify-end gap-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <button type="button" @click="openCreatePostModal = false"
                                    class="min-h-[42px] px-4 rounded-[11px] text-[13px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition-colors cursor-pointer">
                                    {{ __('common.cancel') }}
                                </button>
                                <button type="submit" :disabled="selectedPlatforms.length === 0 || isSubmitting"
                                    class="min-h-[42px] px-5 rounded-[11px] text-[13px] font-bold text-white bg-gradient-to-r from-[#1877F2] to-[#E1306C] hover:opacity-95 active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none transition-all inline-flex items-center gap-2 shadow-sm cursor-pointer">
                                    <i data-lucide="send" class="w-4 h-4"></i>
                                    <span x-text="isSubmitting ? 'Memproses...' : (postPublishMode !== 'now' ? 'Simpan Postingan & Jadwal' : 'Publikasikan Sekarang')"></span>
                                </button>
                            </div>
                        </form>
@endif
                    </div>
                </div>
            </div>
        </template>

        {{-- 10. MODAL BALAS KOMENTAR (APPLE HIG MODAL SHEET) --}}
        <template x-teleport="body">
            <div x-show="replyModalOpen"
                class="fixed inset-0 z-[200] overflow-y-auto"
                x-cloak style="display: none;"
                role="dialog" aria-modal="true"
                @keydown.escape.window="replyModalOpen = false">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                    @click="replyModalOpen = false"
                    aria-hidden="true"></div>

                <!-- Centering Wrapper -->
                <div class="relative z-10 min-h-full flex items-center justify-center p-4 pointer-events-none">
                    <div class="bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[24px] shadow-2xl max-w-lg w-full p-6 space-y-4 pointer-events-auto"
                        @click.stop>

                <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-9 h-9 rounded-[12px] bg-[#AF52DE]/15 text-[#AF52DE] flex items-center justify-center">
                            <i data-lucide="reply" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-[16px] font-bold text-black dark:text-white">Balas Komentar</h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50">Kirim balasan resmi sebagai Cooca
                                Indonesia</p>
                        </div>
                    </div>
                    <button type="button" @click="replyModalOpen = false"
                        class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div
                    class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                    <span class="text-[11px] font-bold text-black/50 dark:text-white/50"
                        x-text="'Dari: ' + activeAuthor"></span>
                    <p class="text-[12.5px] text-black dark:text-white" x-text="activeCommentText"></p>
                </div>

                @if(Auth::guard('admin')->user()?->hasPermission('admin.social_media.reply'))


                <form method="POST" :action="replyActionUrl" class="space-y-4">
                    @csrf
                    <div>
                        <label
                            class="block text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">Isi
                            Balasan Anda</label>
                        <textarea name="message" rows="4" required maxlength="1000"
                            placeholder="{{ __('social_media.reply_official_placeholder') }}"
                            class="w-full bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] p-3 text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all"></textarea>
                    </div>

                    <div
                        class="pt-2 flex items-center justify-end gap-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <button type="button" @click="replyModalOpen = false"
                            class="min-h-[40px] px-4 rounded-[11px] text-[12.5px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                            {{ __('common.cancel') }}
                        </button>
                        <button type="submit"
                            class="min-h-[40px] px-5 rounded-[11px] text-[12.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                            <span>{{ __('social_media.send_reply') }}</span>
                        </button>
                    </div>
                </form>
        @endif
                </div>
            </div>
        </template>

        {{-- MODAL EDIT POSTINGAN PLATFORM --}}
        <template x-teleport="body">
            <div x-show="editModalOpen"
                class="fixed inset-0 z-[200] overflow-y-auto"
                x-cloak style="display: none;"
                role="dialog" aria-modal="true"
                @keydown.escape.window="editModalOpen = false">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                    @click="editModalOpen = false"
                    aria-hidden="true"></div>

                <!-- Centering Wrapper -->
                <div class="relative z-10 min-h-full flex items-center justify-center p-4 pointer-events-none">
                    <div class="bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[24px] shadow-2xl max-w-lg w-full p-6 space-y-4 pointer-events-auto"
                        @click.stop>

                        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-[12px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('social_media.edit_post_content') }}</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">Perbarui teks narasi atau jadwal penayangan</p>
                                </div>
                            </div>
                            <button type="button" @click="editModalOpen = false"
                                class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        @if(Auth::guard('admin')->user()?->hasPermission('admin.social_media.post'))


                        <form method="POST" :action="editPost.update_url" class="space-y-4">
                            @csrf
                            @method('PUT')
                            
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Teks Caption / Konten
                                </label>
                                <textarea name="content" rows="4" required maxlength="5000"
                                    x-model="editPost.content"
                                    placeholder="Tuliskan teks caption postingan..."
                                    class="w-full bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] p-3 text-[13px] text-black dark:text-white placeholder-black/35 dark:placeholder-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all"></textarea>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1.5">
                                    Waktu Penayangan Terjadwal (Opsional)
                                </label>
                                <input type="datetime-local" name="scheduled_at"
                                    x-model="editPost.scheduled_at"
                                    class="w-full h-[40px] px-3 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/15 rounded-[12px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 transition-all">
                                <span class="text-[11px] text-black/45 dark:text-white/45 mt-1 block">Biarkan kosong jika tidak ingin mengubah jadwal penayangan.</span>
                            </div>

                            <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.05] text-[11.5px] text-black/55 dark:text-white/55">
                                <i data-lucide="info" class="w-3.5 h-3.5 inline mr-1 text-[#007AFF]"></i>
                                Catatan: Pengeditan teks di sini memperbarui arsip sistem Cooca dan target postingan yang belum terkirim.
                            </div>

                            <div class="pt-2 flex items-center justify-end gap-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <button type="button" @click="editModalOpen = false"
                                    class="min-h-[40px] px-4 rounded-[11px] text-[12.5px] font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                                    {{ __('common.cancel') }}
                                </button>
                                <button type="submit"
                                    class="min-h-[40px] px-5 rounded-[11px] text-[12.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all inline-flex items-center gap-1.5 shadow-sm">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('common.save_changes') }}</span>
                                </button>
                            </div>
                        </form>
        @endif
                    </div>
                </div>
            </div>
        </template>

        {{-- MODAL PREVIEW DETAIL POSTINGAN PLATFORM --}}
        <template x-teleport="body">
            <div x-show="previewModalOpen && previewPost"
                class="fixed inset-0 z-[200] overflow-y-auto"
                x-cloak style="display: none;"
                role="dialog" aria-modal="true"
                @keydown.escape.window="previewModalOpen = false">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                    @click="previewModalOpen = false"
                    aria-hidden="true"></div>

                <!-- Centering Wrapper -->
                <div class="relative z-10 min-h-full flex items-center justify-center p-4 pointer-events-none">
                    <div class="bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[24px] shadow-2xl max-w-xl w-full p-6 space-y-4 pointer-events-auto"
                        @click.stop>

                        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-[12px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('social_media.platform_post_detail') }}</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50" x-text="'ID: #' + (previewPost?.id || '')"></p>
                                </div>
                            </div>
                            <button type="button" @click="previewModalOpen = false"
                                class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition-colors">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-1">
                            {{-- Status & Meta Badges --}}
                            <div class="flex items-center justify-between flex-wrap gap-2 p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.05]">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-black/40 dark:text-white/40">Status:</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold"
                                        :class="{
                                            'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]': previewPost?.status === 'published',
                                            'bg-[#007AFF]/15 text-[#007AFF]': previewPost?.status === 'scheduled',
                                            'bg-[#FF9500]/15 text-[#FF9500]': previewPost?.status === 'publishing',
                                            'bg-[#FF3B30]/15 text-[#FF3B30]': previewPost?.status === 'failed',
                                            'bg-black/10 dark:bg-white/10 text-black/70 dark:text-white/70': previewPost?.status === 'draft'
                                        }"
                                        x-text="previewPost?.status?.toUpperCase()"></span>
                                </div>
                                <span class="text-[11.5px] text-black/50 dark:text-white/50" x-text="previewPost?.published_at ? ('Tayang: ' + previewPost.published_at) : (previewPost?.scheduled_at ? ('Jadwal: ' + previewPost.scheduled_at) : ('Dibuat: ' + (previewPost?.created_at || '')))"></span>
                            </div>

                            {{-- Media Thumbnail / Carousel --}}
                            <template x-if="previewPost?.media_urls && previewPost.media_urls.length > 0">
                                <div class="space-y-2">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Media Terlampir</label>
                                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                        <template x-for="(url, idx) in previewPost.media_urls" :key="idx">
                                            <a :href="url" target="_blank" class="block aspect-square rounded-[12px] overflow-hidden bg-black/5 border border-black/10 dark:border-white/10 relative group" rel="noopener noreferrer">
                                                <img :src="url" alt="Media preview" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Caption Content --}}
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 mb-1">Isi Caption</label>
                                <div class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.05] dark:border-white/[0.06] text-[13px] text-black dark:text-white whitespace-pre-wrap font-sans leading-relaxed"
                                    x-text="previewPost?.content"></div>
                            </div>

                            {{-- Error Message if any --}}
                            <template x-if="previewPost?.error_message">
                                <div class="p-3.5 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-[12px] space-y-1">
                                    <div class="font-bold flex items-center gap-1.5">
                                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                        <span>Catatan Masalah / Error:</span>
                                    </div>
                                    <p class="font-mono text-[11.5px] leading-relaxed" x-text="previewPost.error_message"></p>
                                </div>
                            </template>

                            {{-- Target Channels Status List --}}
                            <div class="space-y-2">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Status Per Saluran Media Sosial</label>
                                <div class="space-y-2">
                                    <template x-for="target in previewPost?.targets || []" :key="target.channel">
                                        <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.05] flex items-center justify-between gap-3 text-[12.5px]">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold capitalize text-black dark:text-white" x-text="target.channel"></span>
                                                <template x-if="target.platform_post_id">
                                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60" x-text="'ID: ' + target.platform_post_id"></span>
                                                </template>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-semibold"
                                                    :class="{
                                                        'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]': target.status === 'published',
                                                        'bg-[#007AFF]/15 text-[#007AFF]': target.status === 'scheduled',
                                                        'bg-[#FF9500]/15 text-[#FF9500]': target.status === 'publishing',
                                                        'bg-[#FF3B30]/15 text-[#FF3B30]': target.status === 'failed',
                                                        'bg-black/10 dark:bg-white/10 text-black/70 dark:text-white/70': target.status === 'pending'
                                                    }"
                                                    x-text="target.status"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 flex items-center justify-end border-t border-black/[0.06] dark:border-white/[0.08]">
                            <button type="button" @click="previewModalOpen = false"
                                class="min-h-[40px] px-5 rounded-[11px] text-[12.5px] font-semibold text-black dark:text-white bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 transition-colors">
                                {{ __('common.close') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </div>
@endsection

@push('scripts')
    <script>
        function adminSocialCenter() {
            return {
                activeTab: @js($tab),
                openCreatePostModal: false,
                replyModalOpen: false,
                editModalOpen: false,
                editPost: { id: null, content: '', scheduled_at: '', update_url: '' },
                previewModalOpen: false,
                previewPost: null,
                activeCommentId: null,
                activeAuthor: '',
                activeCommentText: '',
                replyActionUrl: '',
                selectedPlatforms: ['instagram'],
                allPlatforms: ['instagram', 'facebook', 'threads', 'tiktok', 'linkedin'],
                channelTiming: {
                    instagram: 'now',
                    facebook: 'now',
                    threads: 'now',
                    tiktok: 'now',
                    linkedin: 'now'
                },
                channelScheduledAt: {
                    instagram: '',
                    facebook: '',
                    threads: '',
                    tiktok: '',
                    linkedin: ''
                },
                postCaption: '',
                postFormat: 'photo', // 'photo' | 'carousel' | 'video' | 'reels' | 'story' | 'text'
                postPublishMode: 'now',
                copied: null,

                // Media upload state
                mediaSourceTab: 'upload', // 'upload' | 'url'
                mediaUrl: '',
                filePreviewUrl: null,
                fileName: '',
                fileSize: '',
                fileMime: '',
                videoWidth: null,
                videoHeight: null,
                videoRatio: null,
                isLandscapeVideo: false,
                carouselItems: [],
                activePreviewSlide: 0,
                previewAspectRatio: '4:5',
                mediaFitMode: 'cover',
                isDragging: false,
                isSubmitting: false,

                get hashtagCount() {
                    const matches = this.postCaption.match(/#[a-z0-9_]+/gi);
                    return matches ? matches.length : 0;
                },

                postFormatLabel() {
                    if (this.postFormat === 'carousel') return 'Carousel (2 - 10 Media)';
                    if (this.postFormat === 'video') return 'Video Feed (16:9 Landscape)';
                    if (this.postFormat === 'reels') return 'Reels Video (9:16 Vertikal)';
                    if (this.postFormat === 'story') return 'Instagram Story (24 Jam)';
                    if (this.postFormat === 'text') return 'Teks Narasi Murni';
                    return 'Foto Feed (4:5 / 1:1)';
                },

                setMediaFormat(fmt) {
                    this.postFormat = fmt;
                    if (fmt === 'text') {
                        this.clearFile();
                        this.carouselItems = [];
                        this.syncCarouselInput();
                        this.mediaUrl = '';
                        this.previewAspectRatio = 'text';
                    } else if (fmt === 'reels' || fmt === 'story') {
                        this.previewAspectRatio = '9:16';
                    } else if (fmt === 'photo' || fmt === 'image' || fmt === 'carousel') {
                        if (this.previewAspectRatio === 'text' || this.previewAspectRatio === '9:16') {
                            this.previewAspectRatio = '4:5';
                        }
                    } else if (fmt === 'video') {
                        if (this.isLandscapeVideo) {
                            this.previewAspectRatio = '16:9';
                        } else if (this.videoRatio && this.videoRatio < 0.75) {
                            this.previewAspectRatio = '9:16';
                        } else {
                            this.previewAspectRatio = '16:9';
                        }
                    }
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    this.processSingleFile(file);
                },

                handleFileDrop(event) {
                    this.isDragging = false;
                    const dt = event.dataTransfer;
                    if (!dt || !dt.files || !dt.files.length) return;
                    const file = dt.files[0];
                    if (this.$refs.fileInput) {
                        try {
                            const transfer = new DataTransfer();
                            transfer.items.add(file);
                            this.$refs.fileInput.files = transfer.files;
                        } catch (e) {}
                    }
                    this.processSingleFile(file);
                },

                processSingleFile(file) {
                    this.fileName = file.name;
                    this.fileMime = file.type;
                    const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
                    this.fileSize = sizeInMB >= 1 ? sizeInMB + ' MB' : Math.round(file.size / 1024) + ' KB';

                    if (this.filePreviewUrl) {
                        URL.revokeObjectURL(this.filePreviewUrl);
                    }
                    this.filePreviewUrl = URL.createObjectURL(file);

                    this.videoWidth = null;
                    this.videoHeight = null;
                    this.videoRatio = null;
                    this.isLandscapeVideo = false;

                    if (file.type.startsWith('video/')) {
                        const vid = document.createElement('video');
                        vid.preload = 'metadata';
                        vid.src = this.filePreviewUrl;
                        vid.onloadedmetadata = () => {
                            this.videoWidth = vid.videoWidth;
                            this.videoHeight = vid.videoHeight;
                            if (vid.videoHeight > 0) {
                                const r = vid.videoWidth / vid.videoHeight;
                                this.videoRatio = r.toFixed(2);
                                this.isLandscapeVideo = vid.videoWidth > vid.videoHeight;
                                if (r >= 1.2) {
                                    this.previewAspectRatio = '16:9';
                                } else if (r <= 0.65) {
                                    this.previewAspectRatio = '9:16';
                                } else if (r <= 0.85) {
                                    this.previewAspectRatio = '4:5';
                                } else {
                                    this.previewAspectRatio = '1:1';
                                }
                            }
                        };
                    } else if (file.type.startsWith('image/')) {
                        const img = new Image();
                        img.src = this.filePreviewUrl;
                        img.onload = () => {
                            if (img.height > 0) {
                                const r = img.width / img.height;
                                if (r >= 1.35) {
                                    this.previewAspectRatio = '16:9';
                                } else if (r <= 0.65) {
                                    this.previewAspectRatio = '9:16';
                                } else if (r <= 0.85) {
                                    this.previewAspectRatio = '4:5';
                                } else if (r >= 0.95 && r <= 1.05) {
                                    this.previewAspectRatio = '1:1';
                                } else {
                                    this.previewAspectRatio = '4:5';
                                }
                            }
                        };
                    }

                    if (file.type.startsWith('video/') && (this.postFormat === 'photo' || this.postFormat === 'image')) {
                        this.postFormat = 'video';
                    } else if (file.type.startsWith('image/') && (this.postFormat === 'video' || this.postFormat === 'reels')) {
                        this.postFormat = 'photo';
                    }

                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                handleCarouselFilesSelect(event) {
                    const files = Array.from(event.target.files);
                    if (!files.length) return;
                    this.addCarouselFiles(files);
                },

                handleCarouselDrop(event) {
                    this.isDragging = false;
                    const dt = event.dataTransfer;
                    if (!dt || !dt.files || !dt.files.length) return;
                    this.addCarouselFiles(Array.from(dt.files));
                },

                addCarouselFiles(files) {
                    for (const file of files) {
                        if (this.carouselItems.length >= 10) break;
                        this.carouselItems.push({
                            file: file,
                            name: file.name,
                            mime: file.type,
                            previewUrl: URL.createObjectURL(file)
                        });
                    }
                    this.syncCarouselInput();
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                syncCarouselInput() {
                    if (!this.$refs.carouselInput) return;
                    try {
                        const dt = new DataTransfer();
                        for (const item of this.carouselItems) {
                            if (item.file) {
                                dt.items.add(item.file);
                            }
                        }
                        this.$refs.carouselInput.files = dt.files;
                    } catch (e) {}
                },

                moveCarouselItem(index, direction) {
                    const targetIndex = index + direction;
                    if (targetIndex < 0 || targetIndex >= this.carouselItems.length) return;
                    const temp = this.carouselItems[index];
                    this.carouselItems[index] = this.carouselItems[targetIndex];
                    this.carouselItems[targetIndex] = temp;
                    this.syncCarouselInput();
                },

                removeCarouselItem(index) {
                    const item = this.carouselItems[index];
                    if (item && item.previewUrl) {
                        URL.revokeObjectURL(item.previewUrl);
                    }
                    this.carouselItems.splice(index, 1);
                    if (this.activePreviewSlide >= this.carouselItems.length) {
                        this.activePreviewSlide = Math.max(0, this.carouselItems.length - 1);
                    }
                    this.syncCarouselInput();
                },

                clearFile() {
                    if (this.filePreviewUrl) {
                        URL.revokeObjectURL(this.filePreviewUrl);
                    }
                    this.filePreviewUrl = null;
                    this.fileName = '';
                    this.fileSize = '';
                    this.fileMime = '';
                    this.videoWidth = null;
                    this.videoHeight = null;
                    this.videoRatio = null;
                    this.isLandscapeVideo = false;
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                getPreviewAspectClass() {
                    if (this.postFormat === 'text') return 'min-h-[220px] p-4';
                    if (this.previewAspectRatio === '9:16') return 'aspect-[9/16]';
                    if (this.previewAspectRatio === '1:1') return 'aspect-square';
                    if (this.previewAspectRatio === '16:9') return 'aspect-[16/9]';
                    return 'aspect-[4/5]';
                },

                getPreviewCardWidthClass() {
                    if (this.previewAspectRatio === '9:16') return 'w-full max-w-[280px] sm:max-w-[300px] mx-auto';
                    if (this.previewAspectRatio === '16:9') return 'w-full max-w-[380px] mx-auto';
                    return 'w-full max-w-[340px] sm:max-w-[360px] mx-auto';
                },

                getAspectRatioDescription() {
                    switch (this.previewAspectRatio) {
                        case '4:5': return 'Feed Portrait (1080x1350) - IG Feed';
                        case '1:1': return 'Square Feed (1080x1080) - Universal';
                        case '9:16': return 'Vertikal (1080x1920) - Reels & Story';
                        case '16:9': return 'Landscape (1920x1080) - Video';
                        default: return '';
                    }
                },

                toggleAllPlatforms() {
                    if (this.selectedPlatforms.length === this.allPlatforms.length) {
                        this.selectedPlatforms = ['instagram'];
                    } else {
                        this.selectedPlatforms = [...this.allPlatforms];
                    }
                },

                copyText(text, key) {
                    navigator.clipboard.writeText(text);
                    this.copied = key;
                    setTimeout(() => this.copied = null, 2000);
                },

                insertHashtag(tag) {
                    this.postCaption += (this.postCaption.length ? ' ' : '') + tag;
                },

                openReplyModal(commentId, author, text) {
                    this.activeCommentId = commentId;
                    this.activeAuthor = author;
                    this.activeCommentText = text;
                    this.replyActionUrl = "{{ url('admin/social-media/comments') }}/" + commentId + "/reply";
                    this.replyModalOpen = true;
                },

                openEditModal(post, updateUrl) {
                    this.editPost = {
                        id: post.id,
                        content: post.content || '',
                        scheduled_at: post.scheduled_at || '',
                        update_url: updateUrl
                    };
                    this.editModalOpen = true;
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                openPreviewModal(post) {
                    this.previewPost = post;
                    this.previewModalOpen = true;
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const tabParam = urlParams.get('tab');
                    if (tabParam) {
                        this.activeTab = tabParam;
                    }
                    this.$watch('openCreatePostModal', (val) => {
                        if (val) {
                            this.$nextTick(() => {
                                if (window.lucide) window.lucide.createIcons();
                            });
                        }
                    });
                    this.$watch('editModalOpen', (val) => {
                        if (val) {
                            this.$nextTick(() => {
                                if (window.lucide) window.lucide.createIcons();
                            });
                        }
                    });
                    this.$watch('previewModalOpen', (val) => {
                        if (val) {
                            this.$nextTick(() => {
                                if (window.lucide) window.lucide.createIcons();
                            });
                        }
                    });
                }
            };
        }
    </script>
@endpush
