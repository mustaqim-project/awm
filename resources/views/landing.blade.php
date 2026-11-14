@extends('layouts.public_marketing')

@php
    $landingMetaTitle = \App\Models\SystemSetting::get(
        'seo_meta_title',
        config('seo.pages./.title', 'COOCA: Aplikasi Kasir, Stok & Pembukuan UMKM Gratis')
    );
    $landingMetaDescription = \App\Models\SystemSetting::get(
        'seo_meta_description',
        config('seo.pages./.description', 'Platform terintegrasi kasir POS, akuntansi riil, stok resep bahan baku, WhatsApp otomatis, dan toko online untuk UMKM Indonesia.')
    );
    $landingMetaKeywords = \App\Models\SystemSetting::get(
        'seo_meta_keywords',
        'Cooca, software kasir gratis, erp umkm, pos kasir toko, aplikasi pembukuan gratis, kalkulator hpp, kalkulator bep, template pembukuan excel, cooca.id'
    );
    $landingOgTitle = \App\Models\SystemSetting::get('seo_og_title') ?: config('seo.pages./.og_title', $landingMetaTitle);
    $landingOgDescription = \App\Models\SystemSetting::get('seo_og_description') ?: config('seo.pages./.og_description', $landingMetaDescription);
    $landingCanonical = \App\Models\SystemSetting::get('seo_canonical_url') ?: url('/');
    $landingAuthor = \App\Models\SystemSetting::get('seo_author', 'COOCA Indonesia');
    $landingRobots = \App\Models\SystemSetting::get('seo_robots', 'index, follow');
    $landingTwitterCard = \App\Models\SystemSetting::get('seo_twitter_card', 'summary_large_image');
    $landingTwitterSite = \App\Models\SystemSetting::get('seo_twitter_site', '@cooca_id');
    $landingOgImageSetting = \App\Models\SystemSetting::get('seo_og_image');
    $landingOgImageUrl = !empty($landingOgImageSetting)
        ? \App\Domain\Storage\AdminStorage::publicUrl($landingOgImageSetting)
        : null;
    $landingAppName = \App\Models\SystemSetting::get('app_name', 'COOCA');
    $showSocialProof = (bool) (config('marketing.social_proof.show', false)
        || (\App\Models\SystemSetting::get('marketing_show_social_proof') === '1'));
    $socialProofUsers = \App\Models\SystemSetting::get('marketing_social_proof_users')
        ?: config('marketing.social_proof.user_count', '10.000+');
    $socialProofRating = \App\Models\SystemSetting::get('marketing_social_proof_rating')
        ?: config('marketing.social_proof.rating_value', '4.9');
    $socialProofUrl = \App\Models\SystemSetting::get('marketing_social_proof_url')
        ?: config('marketing.social_proof.rating_source_url');
@endphp

@section('title', $landingMetaTitle)
@section('description', $landingMetaDescription)
@if (!empty($landingMetaKeywords))
    @section('keywords', $landingMetaKeywords)
@endif
@section('og_title', $landingOgTitle)
@section('og_description', $landingOgDescription)
@section('canonical', $landingCanonical)
@section('robots', $landingRobots)
@section('twitter_card', $landingTwitterCard)
@section('twitter_site', $landingTwitterSite)
@if (!empty($landingAuthor))
    @section('author', $landingAuthor)
@endif
@if (!empty($landingOgImageUrl))
    @section('og_image', $landingOgImageUrl)
@endif

@push('seo')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&display=swap">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&display=swap" rel="stylesheet"></noscript>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "{{ $landingAppName }}",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web, Cloud-based",
        "description": "{{ addslashes($landingMetaDescription) }}",
        "url": "{{ $landingCanonical }}",
        "offers": [
            {
                "@@type": "Offer",
                "name": "Gratis",
                "price": "0",
                "priceCurrency": "IDR",
                "availability": "https://schema.org/InStock",
                "description": "Paket Gratis Selamanya untuk {{ $tierLimits['free']['outlet'] ?? 1 }} toko, {{ $tierLimits['free']['product'] ?? 10 }} produk, {{ $tierLimits['free']['pos'] ?? 30 }} transaksi per bulan"
            },
            {
                "@@type": "Offer",
                "name": "Standard",
                "price": "{{ $pricingData['standard']['monthly_price'] ?? 39000 }}",
                "priceCurrency": "IDR",
                "availability": "https://schema.org/InStock",
                "description": "Paket Standard Rp{{ $pricingData['standard']['monthly_price_formatted'] ?? '39.000' }}/bulan untuk usaha bertumbuh"
            },
            {
                "@@type": "Offer",
                "name": "Premium",
                "price": "{{ $pricingData['premium']['monthly_price'] ?? 89000 }}",
                "priceCurrency": "IDR",
                "availability": "https://schema.org/InStock",
                "description": "Paket Premium Rp{{ $pricingData['premium']['monthly_price_formatted'] ?? '89.000' }}/bulan multi-kasir dan inventori cabang"
            },
            {
                "@@type": "Offer",
                "name": "Prestige",
                "price": "{{ $pricingData['prestige']['monthly_price'] ?? 199000 }}",
                "priceCurrency": "IDR",
                "availability": "https://schema.org/InStock",
                "description": "Paket Prestige Rp{{ $pricingData['prestige']['monthly_price_formatted'] ?? '199.000' }}/bulan ERP lengkap tanpa batasan"
            }
        ],
        @if ($showSocialProof && !empty($socialProofUrl) && !empty($socialProofRating))
        "aggregateRating": {
            "@@type": "AggregateRating",
            "ratingValue": "{{ $socialProofRating }}",
            "bestRating": "5",
            "ratingCount": "100"
        },
        @endif
        "publisher": {
            "@@type": "Organization",
            "name": "{{ $landingAppName }}",
            "url": "{{ $landingCanonical }}"
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "name": "COOCA",
        "url": "{{ $landingCanonical }}",
        "potentialAction": {
            "@@type": "SearchAction",
            "target": "{{ $landingCanonical }}/marketplace/cari?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "COOCA",
        "alternateName": "COOCA Aplikasi Kasir & ERP UMKM",
        "url": "{{ $landingCanonical }}",
        "logo": "{{ asset('assets/image/1785229034_logo_dark.png') }}",
        "sameAs": [
            "https://instagram.com/cooca.indonesia",
            "https://tiktok.com/@cooca.id",
            "https://linkedin.com/company/cooca-indonesia",
            "https://www.facebook.com/profile.php?id=61594556787735"
        ],
        "contactPoint": {
            "@@type": "ContactPoint",
            "telephone": "+62 852-8786-4176",
            "contactType": "customer support",
            "availableLanguage": ["id", "en"]
        }
    }
    </script>
@endpush


@php
    $siteLogoLightSetting = \App\Models\SystemSetting::get('site_logo_light');
    $siteLogoDarkSetting = \App\Models\SystemSetting::get('site_logo_dark');
    $siteLogoLightUrl = \App\Domain\Storage\AdminStorage::publicUrl($siteLogoLightSetting);
    $siteLogoDarkUrl = \App\Domain\Storage\AdminStorage::publicUrl($siteLogoDarkSetting);
@endphp

@section('content')
    <div class="relative overflow-hidden w-full font-sans">
        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <!-- â•â•â• 1. HERO SECTION (Executive Dashboard & UMKM OS - Full Viewport) â•â•â•â•â•â• -->
        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <section
            class="relative w-full min-w-full bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">

            <!-- Subtle Ambient Background Glows (Pure CSS, No Heavy Images) -->
            <div
                class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[450px] h-[450px] bg-[#00C4D8]/10 rounded-full blur-[130px] pointer-events-none -z-0">
            </div>

            <!-- Container Konten Hero (Safe from Fixed Bottom Nav on Mobile) -->
            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-3 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-6 sm:pb-24 lg:py-14">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-3.5 sm:gap-6 lg:gap-12 items-center w-full">

                    <!-- KIRI: Eyebrow, Headline, Subtitle, CTAs & Value Proof (Left-aligned on Mobile and Desktop ~ 5 Cols) -->
                    <div
                        class="lg:col-span-6 space-y-5 sm:space-y-6 lg:space-y-7 text-left flex flex-col items-start w-full">
                        <!-- Pure Typographic Overline Kicker with Pulse Dot -->
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#00C2FF] animate-pulse"></span>
                            <p class="text-xs sm:text-sm lg:text-[14px] font-bold uppercase tracking-wider text-[#00C2FF]">
                                {{ __('public_home.hero_kicker') }}
                            </p>
                        </div>

                        <!-- Main Headline with Gradient Glow Accent -->
                        <div class="w-full">
                            <h1
                                class="text-2xl xs:text-3xl sm:text-5xl md:text-6xl lg:text-[3.25rem] xl:text-[4.5rem] font-extrabold text-white tracking-tight leading-[1.25] sm:leading-[1.18] text-balance break-words max-w-[22rem] sm:max-w-2xl lg:max-w-none">
                    {{ app()->getLocale() === 'en' ? __('public_home.hero_title') : (\App\Services\Seo\SeoMetadataService::getH1() ?: __('public_home.hero_title')) }}
                </h1>
                        </div>

                        <!-- Subtitle Copy -->
                        <p
                            class="text-sm sm:text-lg lg:text-xl text-slate-300 leading-relaxed sm:leading-loose max-w-[24rem] sm:max-w-[34rem] lg:max-w-2xl font-normal text-pretty break-words">
                            {{ __('public_home.hero_subtitle') }}
                        </p>

                        <!-- 3 Seamless Micro-Pills (Langsung Paham 3 Detik) -->
                        <div class="flex flex-wrap items-center gap-2 sm:gap-2.5 pt-0.5">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/[0.05] border border-white/10 text-xs text-slate-200 backdrop-blur-xs">
                                <i data-lucide="store" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span class="font-medium">{{ __('public_home.hero_pill_pos') }}</span>
                            </div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/[0.05] border border-white/10 text-xs text-slate-200 backdrop-blur-xs">
                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-[#FF5722]"></i>
                                <span class="font-medium">{{ __('public_home.hero_pill_omni') }}</span>
                            </div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/[0.05] border border-white/10 text-xs text-slate-200 backdrop-blur-xs">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-[#00C2FF]"></i>
                                <span class="font-medium">{{ __('public_home.hero_pill_sync') }}</span>
                            </div>
                        </div>

                        <!-- Action Buttons (Row Left-Aligned on Mobile & Desktop) -->
                        <div class="pt-1 flex flex-row flex-wrap sm:flex-nowrap items-center justify-start gap-2.5 sm:gap-3.5 w-full sm:w-auto">
                            @if (auth('admin')->check())
                                <a href="{{ route('admin.dashboard') }}"
                                    class="flex-1 sm:flex-none h-11 sm:h-12 px-5 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[44px] sm:min-h-[48px] touch-action-manipulation">
                                    <span>{{ __('public_home.btn_dashboard') }}</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                </a>
                            @elseif (auth('web')->check())
                                <a href="{{ route('dashboard') }}"
                                    class="flex-1 sm:flex-none h-11 sm:h-12 px-5 sm:px-7 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[44px] sm:min-h-[48px] touch-action-manipulation">
                                    <span>{{ __('public_home.btn_dashboard') }}</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                </a>
                            @else
                                <a href="{{ route('register') }}"
                                    class="flex-1 sm:flex-none h-11 sm:h-12 px-4.5 sm:px-8 rounded-[12px] sm:rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-xs sm:text-base flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.45)] hover:shadow-[0_6px_25px_rgba(0,122,255,0.6)] active:scale-[0.98] transition-all min-h-[44px] sm:min-h-[48px] shrink-0 border border-white/20 touch-action-manipulation">
                                    <span>{{ __('public_home.hero_cta_primary') }}</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                </a>
                            @endif

                            <!-- Tombol Coba Demo Interaktif Samping Coba Gratis -->
                            <a href="{{ route('public.demo') }}"
                                class="flex-1 sm:flex-none h-11 sm:h-12 px-3.5 sm:px-6 rounded-[12px] sm:rounded-[14px] bg-white/[0.08] hover:bg-white/[0.14] border border-white/20 hover:border-cyan-400/50 text-white font-semibold text-xs sm:text-base flex items-center justify-center gap-1.5 sm:gap-2 shadow-[0_4px_20px_rgba(0,0,0,0.25)] hover:shadow-[0_0_20px_rgba(0,194,255,0.3)] active:scale-[0.98] transition-all min-h-[44px] sm:min-h-[48px] shrink-0 touch-action-manipulation backdrop-blur-md group"
                                title="Lihat Demo Interaktif COOCA">
                                <i data-lucide="play-circle" class="w-4 h-4 sm:w-4.5 sm:h-4.5 text-cyan-400 group-hover:scale-110 transition-transform"></i>
                                <span>{{ __('public_home.hero_cta_secondary') }}</span>
                            </a>
                        </div>

                        <!-- Social Proof or Factual Trust Badge (Prompt 1 Compliant) -->
                        @if ($showSocialProof)
                            <div class="pt-0.5 sm:pt-1 flex items-center gap-2.5 sm:gap-3.5">
                                <div class="flex -space-x-2 overflow-hidden shrink-0">
                                    <div
                                        class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-sky-400 to-blue-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                        <span>BS</span>
                                    </div>
                                    <div
                                        class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-emerald-400 to-teal-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                        <span>RM</span>
                                    </div>
                                    <div
                                        class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-amber-400 to-orange-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                        <span>FD</span>
                                    </div>
                                    <div
                                        class="inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-full ring-2 ring-[#060B1E] bg-gradient-to-tr from-purple-400 to-indigo-600 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                        <span>{{ $socialProofUsers }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-col justify-center">
                                    @if (!empty($socialProofUrl))
                                        <a href="{{ $socialProofUrl }}" target="_blank" rel="noopener noreferrer"
                                            class="flex items-center gap-1 text-amber-400 hover:underline group">
                                            <div class="flex items-center gap-0.5">
                                                @for ($i = 0; $i < 5; $i++)
                                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                                @endfor
                                            </div>
                                            <span class="text-xs sm:text-sm font-extrabold text-white ml-1 tabular-nums">{{ $socialProofRating }} / 5.0</span>
                                            <i data-lucide="external-link" class="w-3 h-3 text-slate-400 group-hover:text-white transition-colors ml-0.5"></i>
                                        </a>
                                    @else
                                        <div class="flex items-center gap-1 text-amber-400">
                                            <div class="flex items-center gap-0.5">
                                                @for ($i = 0; $i < 5; $i++)
                                                    <i data-lucide="star" class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-amber-400"></i>
                                                @endfor
                                            </div>
                                            <span class="text-xs sm:text-sm font-extrabold text-white ml-1 tabular-nums">{{ $socialProofRating }} / 5.0</span>
                                        </div>
                                    @endif
                                    <span class="text-xs text-slate-400 font-medium">{{ __('public_home.hero_social_proof_count', ['users' => $socialProofUsers]) }}</span>
                                </div>
                            </div>

                            <!-- Reassurance Checkpoints (Displayed with Social Proof) -->
                            <div
                                class="pt-0.5 sm:pt-1 flex flex-wrap items-center justify-start gap-x-4 sm:gap-x-5 gap-y-1.5 text-xs text-slate-300">
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                    <span>{{ __('public_home.reassurance_free', ['limit' => $tierLimits['free']['pos'] ?? 30]) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                    <span>{{ __('public_home.reassurance_no_card') }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                                    <span>{{ __('public_home.reassurance_ready') }}</span>
                                </div>
                            </div>
                        @else
                            <!-- Factual Trust Badge (Default Verified State - Clean Single Reassurance) -->
                            <div class="pt-0.5 sm:pt-1 inline-flex flex-wrap items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/[0.06] border border-white/10 text-[11.5px] sm:text-xs text-slate-300 backdrop-blur-sm shadow-xs">
                                <div class="flex items-center gap-1.5 text-emerald-400 font-semibold">
                                    <i data-lucide="shield-check" class="w-4 h-4 shrink-0 text-emerald-400"></i>
                                    <span class="text-white">{{ __('public_home.trust_badge_free') }}</span>
                                </div>
                                <span class="text-slate-500">•</span>
                                <span>{{ __('public_home.trust_badge_no_card') }}</span>
                                <span class="text-slate-500">•</span>
                                <span>{{ __('public_home.trust_badge_data_owner') }}</span>
                                <span class="text-slate-500">•</span>
                                <span class="text-[#00C2FF] font-medium">{{ __('public_home.trust_badge_made_for') }}</span>
                            </div>
                        @endif

                    </div>

                    <!-- KANAN: High-Tech Omnichannel Ecosystem (3D Platform Visual & Live Transaction Mockup Cards) -->
                    <div class="lg:col-span-6 relative flex flex-col items-center justify-center w-full" x-data="{
                        activeStation: null,
                        defaultTip: '{{ __('public_home.omni_mesh_default_tip') }}',
                        currentTip: '{{ __('public_home.omni_mesh_default_tip') }}',
                        setStation(key, tip) {
                            this.activeStation = key;
                            this.currentTip = tip;
                        },
                        resetStation() {
                            this.activeStation = null;
                            this.currentTip = this.defaultTip;
                        }
                    }" @click.outside="resetStation()">

                        {{-- 3D Isometric Ecosystem Stage --}}
                        <div class="relative w-full max-w-[420px] xs:max-w-[480px] sm:max-w-[540px] lg:max-w-[620px] select-none mx-auto">

                            {{-- Ambient Background Glow --}}
                            <div class="absolute inset-4 sm:inset-8 bg-gradient-to-tr from-[#007AFF]/25 via-[#00C2FF]/15 to-[#A855F7]/15 rounded-3xl blur-3xl pointer-events-none transform-gpu -z-10"></div>

                            {{-- Frame Wrapper for the 3D Illustration --}}
                            <div class="relative w-full aspect-[16/10] sm:aspect-[16/9.6] rounded-2xl sm:rounded-3xl overflow-hidden border border-white/10 shadow-[0_12px_45px_rgba(0,0,0,0.7)] bg-[#070D1F] group">
                                <picture>
                                    <source srcset="{{ asset('images/hero/omnichannel-mesh.webp') }}" type="image/webp">
                                    <img src="{{ asset('images/hero/omnichannel-mesh.jpg') }}"
                                         alt="{{ __('public_home.omni_image_alt') }}"
                                         class="w-full h-full object-cover transform-gpu group-hover:scale-[1.02] transition-transform duration-700 ease-out"
                                         width="620" height="372"
                                         fetchpriority="high"
                                         loading="eager">
                                </picture>

                                {{-- Soft Vignette & Cybernet Gradients --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-[#070D1F]/90 via-transparent to-transparent pointer-events-none"></div>
                                <div class="absolute inset-0 ring-1 ring-inset ring-white/10 rounded-2xl sm:rounded-3xl pointer-events-none"></div>

                                {{-- Central Core Floating Pulse Badge (Pusat Hub COOCA) --}}
                                <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-20 cursor-pointer"
                                     @mouseenter="setStation('core', '{{ __('public_home.omni_mesh_default_tip') }}')"
                                     @mouseleave="resetStation()"
                                     @click="resetStation()">
                                    <div class="px-2.5 xs:px-3.5 py-1 xs:py-1.5 rounded-full bg-[#071329]/95 backdrop-blur-md border-2 border-cyan-400 shadow-[0_0_28px_rgba(0,194,255,0.7)] hover:scale-105 transition-transform duration-200 flex items-center gap-1.5 xs:gap-2">
                                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                                        <span class="font-black text-[10px] xs:text-xs sm:text-sm tracking-wider text-white">COOCA HUB</span>
                                        <span class="text-[8.5px] xs:text-[9.5px] font-mono text-cyan-300 font-bold border-l border-white/20 pl-1.5 xs:pl-2">Sync &lt; 1s</span>
                                    </div>
                                </div>

                                {{-- Station 1: Kasir POS Toko Fisik (Top Left ~ over Cashier Counter) --}}
                                <div class="absolute left-2 xs:left-3 sm:left-4 top-2 xs:top-3 sm:top-4 z-20 group/card cursor-pointer"
                                     @mouseenter="setStation('pos', '{{ __('public_home.omni_node_pos_desc') }}')"
                                     @mouseleave="resetStation()"
                                     @click="setStation('pos', '{{ __('public_home.omni_node_pos_desc') }}')">
                                    <div class="px-2 xs:px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl sm:rounded-2xl bg-[#071329]/90 backdrop-blur-md border border-cyan-400/40 hover:border-cyan-300 shadow-[0_6px_20px_rgba(0,194,255,0.25)] hover:shadow-[0_0_24px_rgba(0,194,255,0.6)] transition-all duration-200 flex items-center gap-1.5 xs:gap-2 group-hover/card:scale-105"
                                         :class="activeStation === 'pos' ? 'scale-105 !border-cyan-300 !shadow-[0_0_28px_rgba(0,194,255,0.8)]' : ''">
                                        <div class="w-6 h-6 xs:w-7 xs:h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-[#007AFF]/20 border border-[#007AFF]/40 flex items-center justify-center text-[#00C2FF] shrink-0">
                                            <i data-lucide="store" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                        </div>
                                        <div class="flex flex-col text-left">
                                            <div class="flex items-center gap-1 xs:gap-1.5">
                                                <span class="text-[9.5px] xs:text-[10.5px] sm:text-xs font-bold text-white">{{ __('public_home.omni_card_pos_title') }}</span>
                                                <span class="px-1 py-0.2 rounded text-[7.5px] xs:text-[8px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">QRIS</span>
                                            </div>
                                            <span class="text-[8px] xs:text-[8.5px] sm:text-[9.5px] text-slate-300 font-mono">Nota #1042 • Rp 33.000</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Station 2: Multi-Gudang & Kurir Pengiriman (Top Right ~ over Courier Scooter) --}}
                                <div class="absolute right-2 xs:right-3 sm:right-4 top-2 xs:top-3 sm:top-4 z-20 group/card cursor-pointer"
                                     @mouseenter="setStation('inventory', '{{ __('public_home.omni_node_inventory_desc') }}')"
                                     @mouseleave="resetStation()"
                                     @click="setStation('inventory', '{{ __('public_home.omni_node_inventory_desc') }}')">
                                    <div class="px-2 xs:px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl sm:rounded-2xl bg-[#071329]/90 backdrop-blur-md border border-amber-400/40 hover:border-amber-300 shadow-[0_6px_20px_rgba(245,158,11,0.25)] hover:shadow-[0_0_24px_rgba(245,158,11,0.6)] transition-all duration-200 flex items-center gap-1.5 xs:gap-2 group-hover/card:scale-105"
                                         :class="activeStation === 'inventory' ? 'scale-105 !border-amber-300 !shadow-[0_0_28px_rgba(245,158,11,0.8)]' : ''">
                                        <div class="w-6 h-6 xs:w-7 xs:h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-300 shrink-0">
                                            <i data-lucide="package" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                        </div>
                                        <div class="flex flex-col text-left">
                                            <div class="flex items-center gap-1 xs:gap-1.5">
                                                <span class="text-[9.5px] xs:text-[10.5px] sm:text-xs font-bold text-white">{{ __('public_home.omni_card_logistics_title') }}</span>
                                                <span class="px-1 py-0.2 rounded text-[7.5px] xs:text-[8px] font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">Auto Resi</span>
                                            </div>
                                            <span class="text-[8px] xs:text-[8.5px] sm:text-[9.5px] text-slate-300 font-mono">Gudang &rarr; Ekspedisi</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Station 3: WhatsApp Commerce & Nota Digital (Bottom Left ~ over Phone screen) --}}
                                <div class="absolute left-2 xs:left-3 sm:left-4 bottom-2.5 xs:bottom-3 sm:bottom-4 z-20 group/card cursor-pointer"
                                     @mouseenter="setStation('whatsapp', '{{ __('public_home.omni_node_whatsapp_desc') }}')"
                                     @mouseleave="resetStation()"
                                     @click="setStation('whatsapp', '{{ __('public_home.omni_node_whatsapp_desc') }}')">
                                    <div class="px-2 xs:px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl sm:rounded-2xl bg-[#071329]/90 backdrop-blur-md border border-emerald-500/40 hover:border-emerald-400 shadow-[0_6px_20px_rgba(34,197,94,0.25)] hover:shadow-[0_0_24px_rgba(34,197,94,0.6)] transition-all duration-200 flex items-center gap-1.5 xs:gap-2 group-hover/card:scale-105"
                                         :class="activeStation === 'whatsapp' ? 'scale-105 !border-emerald-400 !shadow-[0_0_28px_rgba(34,197,94,0.8)]' : ''">
                                        <div class="w-6 h-6 xs:w-7 xs:h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 shrink-0">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                        </div>
                                        <div class="flex flex-col text-left">
                                            <div class="flex items-center gap-1 xs:gap-1.5">
                                                <span class="text-[9.5px] xs:text-[10.5px] sm:text-xs font-bold text-white">{{ __('public_home.omni_card_wa_title') }}</span>
                                                <span class="px-1 py-0.2 rounded text-[7.5px] xs:text-[8px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Otomatis</span>
                                            </div>
                                            <span class="text-[8px] xs:text-[8.5px] sm:text-[9.5px] text-slate-300 font-mono">Nota PDF ke WA Pelanggan</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Station 4: Shopee, Tokopedia & Web Store Sync (Bottom Right ~ over Web Store Window) --}}
                                <div class="absolute right-2 xs:right-3 sm:right-4 bottom-2.5 xs:bottom-3 sm:bottom-4 z-20 group/card cursor-pointer"
                                     @mouseenter="setStation('marketplace', '{{ __('public_home.omni_node_marketplace_desc') }}')"
                                     @mouseleave="resetStation()"
                                     @click="setStation('marketplace', '{{ __('public_home.omni_node_marketplace_desc') }}')">
                                    <div class="px-2 xs:px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl sm:rounded-2xl bg-[#071329]/90 backdrop-blur-md border border-[#FF5722]/50 hover:border-[#FF5722] shadow-[0_6px_20px_rgba(255,87,34,0.25)] hover:shadow-[0_0_24px_rgba(255,87,34,0.6)] transition-all duration-200 flex items-center gap-1.5 xs:gap-2 group-hover/card:scale-105"
                                         :class="activeStation === 'marketplace' ? 'scale-105 !border-[#FF5722] !shadow-[0_0_28px_rgba(255,87,34,0.8)]' : ''">
                                        <div class="w-6 h-6 xs:w-7 xs:h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl bg-[#FF5722]/20 border border-[#FF5722]/40 flex items-center justify-center text-[#FF5722] shrink-0">
                                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                        </div>
                                        <div class="flex flex-col text-left">
                                            <div class="flex items-center gap-1 xs:gap-1.5">
                                                <span class="text-[9.5px] xs:text-[10.5px] sm:text-xs font-bold text-white">{{ __('public_home.omni_card_market_title') }}</span>
                                                <span class="px-1 py-0.2 rounded text-[7.5px] xs:text-[8px] font-semibold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">0.8s Sync</span>
                                            </div>
                                            <span class="text-[8px] xs:text-[8.5px] sm:text-[9.5px] text-slate-300 font-mono">Stok Sinkron • Nol Selisih</span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                        {{-- Interactive Live Channel Explanation Pill Console --}}
                        <div class="mt-3 sm:mt-4 p-2.5 sm:p-3 rounded-2xl bg-white/[0.04] border border-white/10 backdrop-blur-md flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs w-full max-w-[540px]">
                            <div class="flex items-center gap-2 min-w-0 w-full sm:w-auto">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                                <span class="text-slate-200 text-[11px] sm:text-xs leading-snug truncate" x-text="currentTip"></span>
                            </div>
                            <a href="{{ route('public.omnichannel.orders') }}"
                                class="text-[11px] font-semibold text-cyan-400 hover:text-white shrink-0 inline-flex items-center gap-1 group self-end sm:self-auto">
                                <span>{{ __('public_home.omni_mesh_explore_btn') }}</span>
                                <i data-lucide="arrow-right" class="w-3 h-3 group-hover:translate-x-0.5 transition-transform"></i>
                            </a>
                        </div>

                    </div>

                </div>

            </div>
        </section>

        <!-- â•â•â• 2. FEATURE GRID ("Semua yang Anda Butuhkan...") â•â•â• -->
        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <section
            class="bg-white dark:bg-[#070A14] py-20 lg:py-24 border-b border-slate-100 dark:border-white/5 transition-colors">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Section Header -->
                <div class="text-center max-w-4xl mx-auto space-y-3">
                    <p
                        class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        {{ __('public_home.ecosystem_kicker') }}
                    </p>
                    <h2
                        class="text-4xl sm:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-extrabold text-slate-900 dark:text-white tracking-tight leading-[1.15]">
                        {{ __('public_home.ecosystem_title') }}
                    </h2>
                    <p class="text-xs sm:text-sm font-semibold text-[#007AFF] uppercase tracking-wider">
                        {{ __('public_home.ecosystem_subtitle') }}
                    </p>
                    <div
                        class="text-slate-600 dark:text-slate-400 text-sm sm:text-base leading-relaxed max-w-2xl mx-auto space-y-2">
                        <p class="font-bold text-slate-900 dark:text-white pt-1">
                            {{ __('public_home.ecosystem_tag') }}
                        </p>
                    </div>
                </div>

                <!-- 8 Bento Squircle Cards (Mobile 2-Col Bento Grid, Desktop 4-Col Grid) -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 min-[360px]:gap-3.5 sm:gap-5 xl:gap-6 mt-8 sm:mt-12">

                    <!-- 1. Sales -->
                    <a href="{{ route('public.erp.pos') }}"
                        class="group block p-3.5 min-[360px]:p-4 sm:p-5 lg:p-6 rounded-[18px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.03)] hover:border-black/15 dark:hover:border-white/20 hover:shadow-[0_12px_28px_rgba(0,122,255,0.08)] active:scale-[0.98] transition-all duration-200 touch-manipulation">
                        <div
                            class="w-9 h-9 min-[360px]:w-10 min-[360px]:h-10 sm:w-12 sm:h-12 rounded-[10px] sm:rounded-[14px] bg-sky-50 dark:bg-sky-950/60 border border-sky-100 dark:border-sky-800/40 text-[#007AFF] flex items-center justify-center mb-2.5 min-[360px]:mb-3 sm:mb-4 group-hover:scale-105 transition-transform">
                            <i data-lucide="shopping-cart" class="w-4.5 h-4.5 min-[360px]:w-5 min-[360px]:h-5 sm:w-6 sm:h-6"></i>
                        </div>
                        <h3
                            class="text-xs min-[360px]:text-sm sm:text-base font-bold text-slate-900 dark:text-white mb-1 sm:mb-1.5 flex items-center justify-between">
                            <span>{{ __('public_home.card_pos_title') }}</span>
                            <i data-lucide="arrow-right"
                                class="w-3.5 h-3.5 sm:w-4 sm:h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#007AFF]"></i>
                        </h3>
                        <p class="text-[11px] min-[360px]:text-xs text-slate-500 dark:text-slate-400 leading-snug sm:leading-relaxed line-clamp-3 sm:line-clamp-none">
                            {{ __('public_home.card_pos_desc') }}
                        </p>
                    </a>

                    <!-- 2. Inventory -->
                    <a href="{{ route('public.erp.inventory') }}"
                        class="group block p-3.5 min-[360px]:p-4 sm:p-5 lg:p-6 rounded-[18px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.03)] hover:border-black/15 dark:hover:border-white/20 hover:shadow-[0_12px_28px_rgba(0,122,255,0.08)] active:scale-[0.98] transition-all duration-200 touch-manipulation">
                        <div
                            class="w-9 h-9 min-[360px]:w-10 min-[360px]:h-10 sm:w-12 sm:h-12 rounded-[10px] sm:rounded-[14px] bg-purple-50 dark:bg-purple-950/60 border border-purple-100 dark:border-purple-800/40 text-purple-500 flex items-center justify-center mb-2.5 min-[360px]:mb-3 sm:mb-4 group-hover:scale-105 transition-transform">
                            <i data-lucide="layers" class="w-4.5 h-4.5 min-[360px]:w-5 min-[360px]:h-5 sm:w-6 sm:h-6"></i>
                        </div>
                        <h3
                            class="text-xs min-[360px]:text-sm sm:text-base font-bold text-slate-900 dark:text-white mb-1 sm:mb-1.5 flex items-center justify-between">
                            <span>{{ __('public_home.card_stock_title') }}</span>
                            <i data-lucide="arrow-right"
                                class="w-3.5 h-3.5 sm:w-4 sm:h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#007AFF]"></i>
                        </h3>
                        <p class="text-[11px] min-[360px]:text-xs text-slate-500 dark:text-slate-400 leading-snug sm:leading-relaxed line-clamp-3 sm:line-clamp-none">
                            {{ __('public_home.card_stock_desc') }}
                        </p>
                    </a>

                    <!-- 3. Finance -->
                    <a href="{{ route('public.erp.finance') }}"
                        class="group block p-3.5 min-[360px]:p-4 sm:p-5 lg:p-6 rounded-[18px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.03)] hover:border-black/15 dark:hover:border-white/20 hover:shadow-[0_12px_28px_rgba(0,122,255,0.08)] active:scale-[0.98] transition-all duration-200 touch-manipulation">
                        <div
                            class="w-9 h-9 min-[360px]:w-10 min-[360px]:h-10 sm:w-12 sm:h-12 rounded-[10px] sm:rounded-[14px] bg-blue-50 dark:bg-blue-950/60 border border-blue-100 dark:border-blue-800/40 text-blue-500 flex items-center justify-center mb-2.5 min-[360px]:mb-3 sm:mb-4 group-hover:scale-105 transition-transform">
                            <i data-lucide="wallet" class="w-4.5 h-4.5 min-[360px]:w-5 min-[360px]:h-5 sm:w-6 sm:h-6"></i>
                        </div>
                        <h3
                            class="text-xs min-[360px]:text-sm sm:text-base font-bold text-slate-900 dark:text-white mb-1 sm:mb-1.5 flex items-center justify-between">
                            <span>{{ __('public_home.card_finance_title') }}</span>
                            <i data-lucide="arrow-right"
                                class="w-3.5 h-3.5 sm:w-4 sm:h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#007AFF]"></i>
                        </h3>
                        <p class="text-[11px] min-[360px]:text-xs text-slate-500 dark:text-slate-400 leading-snug sm:leading-relaxed line-clamp-3 sm:line-clamp-none">
                            {{ __('public_home.card_finance_desc') }}
                        </p>
                    </a>

                    <!-- 4. Customer -->
                    <a href="{{ route('public.omnichannel.customer') }}"
                        class="group block p-3.5 min-[360px]:p-4 sm:p-5 lg:p-6 rounded-[18px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.03)] hover:border-black/15 dark:hover:border-white/20 hover:shadow-[0_12px_28px_rgba(0,122,255,0.08)] active:scale-[0.98] transition-all duration-200 touch-manipulation">
                        <div
                            class="w-9 h-9 min-[360px]:w-10 min-[360px]:h-10 sm:w-12 sm:h-12 rounded-[10px] sm:rounded-[14px] bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-100 dark:border-cyan-800/40 text-cyan-500 flex items-center justify-center mb-2.5 min-[360px]:mb-3 sm:mb-4 group-hover:scale-105 transition-transform">
                            <i data-lucide="users" class="w-4.5 h-4.5 min-[360px]:w-5 min-[360px]:h-5 sm:w-6 sm:h-6"></i>
                        </div>
                        <h3
                            class="text-xs min-[360px]:text-sm sm:text-base font-bold text-slate-900 dark:text-white mb-1 sm:mb-1.5 flex items-center justify-between">
                            <span>{{ __('public_home.card_customer_title') }}</span>
                            <i data-lucide="arrow-right"
                                class="w-3.5 h-3.5 sm:w-4 sm:h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#007AFF]"></i>
                        </h3>
                        <p class="text-[11px] min-[360px]:text-xs text-slate-500 dark:text-slate-400 leading-snug sm:leading-relaxed line-clamp-3 sm:line-clamp-none">
                            {{ __('public_home.card_customer_desc') }}
                        </p>
                    </a>

                    <!-- 5. Social Media -->
                    <a href="{{ route('public.omnichannel.social-media') }}"
                        class="group block p-3.5 min-[360px]:p-4 sm:p-5 lg:p-6 rounded-[18px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.03)] hover:border-black/15 dark:hover:border-white/20 hover:shadow-[0_12px_28px_rgba(0,122,255,0.08)] active:scale-[0.98] transition-all duration-200 touch-manipulation">
                        <div
                            class="w-9 h-9 min-[360px]:w-10 min-[360px]:h-10 sm:w-12 sm:h-12 rounded-[10px] sm:rounded-[14px] bg-pink-50 dark:bg-pink-950/60 border border-pink-100 dark:border-pink-800/40 flex items-center justify-center gap-1.5 mb-2.5 min-[360px]:mb-3 sm:mb-4 group-hover:scale-105 transition-transform">
                            <span
                                class="w-4 h-4 rounded-[4px] bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888] flex items-center justify-center text-white shrink-0 shadow-xs">
                                <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <rect x="2" y="2" width="20" height="20" rx="5"></rect>
                                    <circle cx="12" cy="12" r="3.5"></circle>
                                </svg>
                            </span>
                            <span
                                class="w-4 h-4 rounded-[4px] bg-[#1877F2] flex items-center justify-center text-white shrink-0 shadow-xs">
                                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24">
                                    <path
                                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                            </span>
                        </div>
                        <h3
                            class="text-xs min-[360px]:text-sm sm:text-base font-bold text-slate-900 dark:text-white mb-1 sm:mb-1.5 flex items-center justify-between">
                            <span>{{ __('public_home.card_social_title') }}</span>
                            <i data-lucide="arrow-right"
                                class="w-3.5 h-3.5 sm:w-4 sm:h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#007AFF]"></i>
                        </h3>
                        <p class="text-[11px] min-[360px]:text-xs text-slate-500 dark:text-slate-400 leading-snug sm:leading-relaxed line-clamp-3 sm:line-clamp-none">
                            {{ __('public_home.card_social_desc') }}
                        </p>
                    </a>

                    <!-- 6. Content Automation -->
                    <a href="{{ route('public.content.creation') }}"
                        class="group block p-3.5 min-[360px]:p-4 sm:p-5 lg:p-6 rounded-[18px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.03)] hover:border-black/15 dark:hover:border-white/20 hover:shadow-[0_12px_28px_rgba(0,122,255,0.08)] active:scale-[0.98] transition-all duration-200 touch-manipulation">
                        <div
                            class="w-9 h-9 min-[360px]:w-10 min-[360px]:h-10 sm:w-12 sm:h-12 rounded-[10px] sm:rounded-[14px] bg-fuchsia-50 dark:bg-fuchsia-950/60 border border-fuchsia-100 dark:border-fuchsia-800/40 text-fuchsia-500 flex items-center justify-center mb-2.5 min-[360px]:mb-3 sm:mb-4 group-hover:scale-105 transition-transform">
                            <i data-lucide="megaphone" class="w-4.5 h-4.5 min-[360px]:w-5 min-[360px]:h-5 sm:w-6 sm:h-6"></i>
                        </div>
                        <h3
                            class="text-xs min-[360px]:text-sm sm:text-base font-bold text-slate-900 dark:text-white mb-1 sm:mb-1.5 flex items-center justify-between">
                            <span>{{ __('public_home.card_content_title') }}</span>
                            <i data-lucide="arrow-right"
                                class="w-3.5 h-3.5 sm:w-4 sm:h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#007AFF]"></i>
                        </h3>
                        <p class="text-[11px] min-[360px]:text-xs text-slate-500 dark:text-slate-400 leading-snug sm:leading-relaxed line-clamp-3 sm:line-clamp-none">
                            {{ __('public_home.card_content_desc') }}
                        </p>
                    </a>

                    <!-- 7. Marketplace -->
                    <a href="{{ route('marketplace.index') }}"
                        class="group block p-3.5 min-[360px]:p-4 sm:p-5 lg:p-6 rounded-[18px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.03)] hover:border-black/15 dark:hover:border-white/20 hover:shadow-[0_12px_28px_rgba(0,122,255,0.08)] active:scale-[0.98] transition-all duration-200 touch-manipulation">
                        <div
                            class="w-9 h-9 min-[360px]:w-10 min-[360px]:h-10 sm:w-12 sm:h-12 rounded-[10px] sm:rounded-[14px] bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/40 text-indigo-500 flex items-center justify-center mb-2.5 min-[360px]:mb-3 sm:mb-4 group-hover:scale-105 transition-transform">
                            <i data-lucide="store" class="w-4.5 h-4.5 min-[360px]:w-5 min-[360px]:h-5 sm:w-6 sm:h-6"></i>
                        </div>
                        <h3
                            class="text-xs min-[360px]:text-sm sm:text-base font-bold text-slate-900 dark:text-white mb-1 sm:mb-1.5 flex items-center justify-between">
                            <span>{{ __('public_home.card_marketplace_title') }}</span>
                            <i data-lucide="arrow-right"
                                class="w-3.5 h-3.5 sm:w-4 sm:h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#007AFF]"></i>
                        </h3>
                        <p class="text-[11px] min-[360px]:text-xs text-slate-500 dark:text-slate-400 leading-snug sm:leading-relaxed line-clamp-3 sm:line-clamp-none">
                            {{ __('public_home.card_marketplace_desc') }}
                        </p>
                    </a>

                    <!-- 8. Analytics & AI -->
                    <a href="{{ route('public.erp.analytics') }}"
                        class="group block p-3.5 min-[360px]:p-4 sm:p-5 lg:p-6 rounded-[18px] sm:rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.03)] hover:border-black/15 dark:hover:border-white/20 hover:shadow-[0_12px_28px_rgba(0,122,255,0.08)] active:scale-[0.98] transition-all duration-200 touch-manipulation">
                        <div
                            class="w-9 h-9 min-[360px]:w-10 min-[360px]:h-10 sm:w-12 sm:h-12 rounded-[10px] sm:rounded-[14px] bg-violet-50 dark:bg-violet-950/60 border border-violet-100 dark:border-violet-800/40 text-violet-500 flex items-center justify-center mb-2.5 min-[360px]:mb-3 sm:mb-4 group-hover:scale-105 transition-transform">
                            <i data-lucide="trending-up" class="w-4.5 h-4.5 min-[360px]:w-5 min-[360px]:h-5 sm:w-6 sm:h-6"></i>
                        </div>
                        <h3
                            class="text-xs min-[360px]:text-sm sm:text-base font-bold text-slate-900 dark:text-white mb-1 sm:mb-1.5 flex items-center justify-between">
                            <span>{{ __('public_home.card_analytics_title') }}</span>
                            <i data-lucide="arrow-right"
                                class="w-3.5 h-3.5 sm:w-4 sm:h-4 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all text-[#007AFF]"></i>
                        </h3>
                        <p class="text-[11px] min-[360px]:text-xs text-slate-500 dark:text-slate-400 leading-snug sm:leading-relaxed line-clamp-3 sm:line-clamp-none">
                            {{ __('public_home.card_analytics_desc') }}
                        </p>
                    </a>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            {{ __('public_home.card_analytics_desc') }}
                        </p>
                    </a>

                </div>
            </div>
        </section>

        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <!-- â•â•â• 4. SPLIT SECTION: CONTENT AUTOMATION & MARKETPLACE â•â•â• -->
        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <section
            class="bg-[#F8FAFC] dark:bg-[#070B18] py-16 sm:py-20 lg:py-24 border-b border-slate-100 dark:border-white/5 transition-colors">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 items-stretch">

                    <!-- ════ LEFT CARD: CONTENT AUTOMATION ════ -->
                    <div
                        class="p-6 sm:p-8 rounded-[28px] bg-white dark:bg-[#101726] border border-slate-200/80 dark:border-white/5 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between h-full">
                        <div class="space-y-4 sm:space-y-5">
                            <div>
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-950/40 text-[#007AFF] dark:text-[#38bdf8] text-[11px] font-bold uppercase tracking-wider mb-2.5 border border-blue-100/70 dark:border-blue-900/40">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] dark:bg-[#38bdf8]"></span>
                                    {{ __('public_home.split_content_kicker') }}
                                </span>
                                <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                                    {{ __('public_home.split_content_title') }}
                                </h3>
                                <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm mt-2 leading-relaxed">
                                    {{ __('public_home.split_content_desc') }}
                                </p>
                            </div>

                            <div>
                                <a href="{{ route('public.content.creation') }}"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs sm:text-sm tracking-tight shadow-sm hover:shadow-[0_4px_14px_rgba(0,122,255,0.25)] active:scale-[0.98] transition-all min-h-[40px] touch-action-manipulation group">
                                    <span>{{ __('public_home.split_content_btn') }}</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Visual UI Mockup (Composer + Scheduled Posts) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-6 mt-6 border-t border-slate-100 dark:border-white/5">
                            <!-- Post Preview with Coffee/Pastry -->
                            <div
                                class="rounded-2xl border border-slate-200/70 dark:border-white/10 overflow-hidden bg-slate-50/80 dark:bg-slate-900/60 p-3 flex flex-col justify-between">
                                <div>
                                    <div class="relative rounded-xl overflow-hidden mb-2.5">
                                        <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=400&q=70" alt="Post Preview" loading="lazy" decoding="async" width="300" height="128" class="w-full h-32 object-cover">

                                        <div class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-black/60 backdrop-blur-md text-white text-[10px] font-medium flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                            <span>{{ __('public_home.split_content_status_ready') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <div
                                            class="w-5 h-5 rounded-md bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888] flex items-center justify-center text-white shrink-0 shadow-xs">
                                            <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                                <circle cx="12" cy="12" r="3.5"></circle>
                                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke-width="2.5"></line>
                                            </svg>
                                        </div>
                                        <span
                                            class="text-[12px] font-bold text-slate-800 dark:text-slate-200 truncate">{{ __('public_home.split_content_mock_title') }}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                                        {{ __('public_home.split_content_mock_desc') }}
                                    </p>
                                    <div class="flex items-center justify-between text-[10.5px] text-slate-500 dark:text-slate-400 mt-2.5 pt-2 border-t border-slate-200/50 dark:border-white/5">
                                        <span class="flex items-center gap-1 font-medium text-slate-600 dark:text-slate-300">
                                            <i data-lucide="clock" class="w-3 h-3 text-[#007AFF]"></i>
                                            <span>{{ __('public_home.split_content_time_today') }}</span>
                                        </span>
                                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>{{ __('public_home.split_content_status_queued') }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Scheduled Channels List -->
                            <div
                                class="rounded-2xl border border-slate-200/70 dark:border-white/10 bg-slate-50/80 dark:bg-slate-900/60 p-3 flex flex-col justify-between">
                                <div class="flex items-center justify-between mb-2 pb-1.5 border-b border-slate-200/50 dark:border-white/5">
                                    <div
                                        class="text-[10.5px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        {{ __('public_home.split_content_schedule_label') }}
                                    </div>
                                    <div class="flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>{{ __('public_home.split_content_status_autosync') }}</span>
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <div
                                        class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-100 dark:border-white/5 text-[11px] shadow-xs">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-4 h-4 rounded-[4px] bg-gradient-to-tr from-[#f09433] via-[#dc2743] to-[#bc1888] flex items-center justify-center text-white shrink-0 shadow-xs">
                                                <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2.5">
                                                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                                    <circle cx="12" cy="12" r="3.5"></circle>
                                                </svg>
                                            </span>
                                            <span class="font-semibold text-slate-700 dark:text-slate-200">Instagram</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700/60 font-semibold text-slate-700 dark:text-slate-300 text-[11px] tracking-tight">12:00</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-100 dark:border-white/5 text-[11px] shadow-xs">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-4 h-4 rounded-[4px] bg-[#1877F2] flex items-center justify-center text-white shrink-0 shadow-xs">
                                                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24">
                                                    <path
                                                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                                </svg>
                                            </span>
                                            <span class="font-semibold text-slate-700 dark:text-slate-200">Facebook</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700/60 font-semibold text-slate-700 dark:text-slate-300 text-[11px] tracking-tight">15:00</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-100 dark:border-white/5 text-[11px] shadow-xs">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-4 h-4 rounded-[4px] bg-black dark:bg-white dark:text-black flex items-center justify-center text-white shrink-0 shadow-xs">
                                                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24">
                                                    <path
                                                        d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 0 0-1-.08A6.34 6.34 0 0 0 3 15.66a6.34 6.34 0 0 0 10.82 4.49 6.27 6.27 0 0 0 1.93-4.49V8.6a8.18 8.18 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-.93-.03z" />
                                                </svg>
                                            </span>
                                            <span class="font-semibold text-slate-700 dark:text-slate-200">TikTok</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700/60 font-semibold text-slate-700 dark:text-slate-300 text-[11px] tracking-tight">19:00</span>
                                    </div>
                                    <div
                                        class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-slate-800/80 border border-slate-100 dark:border-white/5 text-[11px] shadow-xs">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="w-4 h-4 rounded-[4px] bg-slate-900 dark:bg-slate-700 flex items-center justify-center text-white shrink-0 shadow-xs">
                                                <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 192 192">
                                                    <path
                                                        d="M141.537 88.9883C140.71 88.5919 139.87 88.2104 139.019 87.8451C137.537 60.5382 122.616 44.905 97.5619 44.745C97.4484 44.7443 97.3355 44.7443 97.222 44.7443C82.2364 44.7443 69.7731 51.1409 62.102 62.7807L75.0592 71.3093C80.8988 62.4521 90.0768 59.8804 97.222 59.8804C108.685 59.8804 117.845 66.868 119.539 80.0827C114.733 79.5298 109.689 79.3149 104.423 79.4357C76.8532 80.0682 60.1009 94.6192 60.6276 114.947C61.1274 134.249 76.5414 147.256 95.8087 147.256C112.527 147.256 122.951 138.891 127.818 126.049C133.513 135.539 141.874 140.897 153.861 140.897C168.423 140.897 178.688 129.845 178.688 110.822C178.688 77.0864 153.255 46.7583 103.784 46.7583C61.4287 46.7583 31.7828 75.3129 31.7828 116.634C31.7828 157.069 60.2783 186.256 103.047 186.256C121.758 186.256 137.957 180.378 149.336 169.311L138.647 157.859C129.742 166.52 117.579 171.12 103.047 171.12C70.6205 171.12 47.0116 148.145 47.0116 116.634C47.0116 83.9877 70.1878 61.8944 103.784 61.8944C143.082 61.8944 163.552 86.8778 163.552 110.822C163.552 121.849 157.947 127.807 149.605 127.807C142.062 127.807 136.702 122.569 134.195 112.518C139.734 104.305 142.115 94.7578 141.537 88.9883ZM103.957 132.183C88.4279 132.183 75.8776 123.633 75.5256 110.027C75.1432 95.2476 87.2796 93.3087 104.225 93.3087C109.07 93.3087 113.673 93.6309 117.917 94.2755C115.656 118.91 106.671 132.183 103.957 132.183Z" />
                                                </svg>
                                            </span>
                                            <span class="font-semibold text-slate-700 dark:text-slate-200">Threads</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700/60 font-semibold text-slate-700 dark:text-slate-300 text-[11px] tracking-tight">21:00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════ RIGHT CARD: MARKETPLACE ════ -->
                    <div
                        class="p-6 sm:p-8 rounded-[28px] bg-white dark:bg-[#101726] border border-slate-200/80 dark:border-white/5 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between h-full">
                        <div class="space-y-4 sm:space-y-5">
                            <div>
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-cyan-50 dark:bg-cyan-950/40 text-[#0284c7] dark:text-[#38bdf8] text-[11px] font-bold uppercase tracking-wider mb-2.5 border border-cyan-100/70 dark:border-cyan-900/40">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#0284c7] dark:bg-[#38bdf8]"></span>
                                    {{ __('public_home.split_marketplace_kicker') }}
                                </span>
                                <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                                    {{ __('public_home.split_marketplace_title') }}
                                </h3>
                                <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm mt-2 leading-relaxed">
                                    {{ __('public_home.split_marketplace_desc') }}
                                </p>
                            </div>

                            <div>
                                <a href="{{ route('marketplace.index') }}"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-xs sm:text-sm tracking-tight shadow-sm hover:shadow-[0_4px_14px_rgba(0,122,255,0.25)] active:scale-[0.98] transition-all min-h-[40px] touch-action-manipulation group">
                                    <span>{{ __('public_home.split_marketplace_btn') }}</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Interactive Marketplace Search & Discovery -->
                        <div class="space-y-3.5 pt-6 mt-6 border-t border-slate-100 dark:border-white/5">
                            <!-- Search & Filter Bar with Autocomplete -->
                            <div x-data="{
                                query: '',
                                results: [],
                                loading: false,
                                open: false,
                                searchProducts() {
                                    if (this.query.trim().length < 2) {
                                        this.results = [];
                                        this.open = false;
                                        return;
                                    }
                                    this.loading = true;
                                    fetch('{{ route('marketplace.search') }}?q=' + encodeURIComponent(this.query), {
                                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                                        })
                                        .then(res => res.json())
                                        .then(data => {
                                            this.results = data.data || [];
                                            this.open = true;
                                            this.loading = false;
                                        })
                                        .catch(() => {
                                            this.loading = false;
                                        });
                                }
                            }" @click.outside="open = false" class="relative">
                                <form action="{{ route('marketplace.search') }}" method="GET" class="relative">
                                    <div
                                        class="flex items-center gap-2 p-1.5 rounded-2xl bg-slate-50/90 dark:bg-slate-900/70 border border-slate-200/80 dark:border-white/10 text-xs focus-within:border-[#007AFF] focus-within:ring-2 focus-within:ring-[#007AFF]/20 transition-all">
                                        <i data-lucide="search" class="w-4 h-4 text-slate-400 ml-2.5 shrink-0"></i>
                                        <input type="text" name="q" x-model="query"
                                            @input.debounce.300ms="searchProducts()"
                                            placeholder="{{ __('public_home.split_marketplace_search_placeholder') }}" autocomplete="off"
                                            class="w-full bg-transparent border-0 text-[13px] sm:text-xs text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-0 py-1.5 px-1">
                                        
                                        <div
                                            class="hidden sm:flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 px-2.5 py-1.5 rounded-xl border border-slate-200/60 dark:border-white/5 shrink-0">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-500 shrink-0"></i>
                                            <span>{{ __('public_home.split_marketplace_all_cities') }}</span>
                                        </div>

                                        <button type="submit"
                                            class="shrink-0 px-4 py-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-slate-100 font-semibold text-xs shadow-xs hover:shadow-sm active:scale-95 transition-all inline-flex items-center min-h-[36px] touch-action-manipulation">
                                            <span>{{ __('public_home.split_marketplace_search_submit') }}</span>
                                        </button>
                                    </div>
                                </form>

                                <!-- Instant Search Dropdown (Only show_in_website products) -->
                                <div x-show="open && results.length > 0" x-cloak
                                    class="absolute left-0 right-0 top-full mt-2 z-30 bg-white dark:bg-[#151D2F] border border-slate-200 dark:border-white/10 rounded-2xl shadow-xl overflow-hidden divide-y divide-slate-100 dark:divide-white/5">
                                    <div
                                        class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/50 dark:bg-white/[0.02]">
                                        {{ __('public_home.split_marketplace_verified_heading') }}
                                    </div>
                                    <div class="max-h-60 overflow-y-auto">
                                        <template x-for="item in results" :key="item.id">
                                            <a :href="item.url"
                                                class="flex items-center gap-2.5 p-2.5 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                                <img :src="item.image_url ||
                                                    'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=100&q=80'"
                                                    :alt="item.name" loading="lazy" decoding="async" width="36" height="36"
                                                    class="w-9 h-9 rounded-lg object-cover bg-slate-100 dark:bg-slate-800 shrink-0">
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-xs font-semibold text-slate-800 dark:text-white truncate group-hover:text-[#007AFF]"
                                                        x-text="item.name"></div>
                                                    <div class="flex items-center gap-2 text-[10px] text-slate-500">
                                                        <span x-text="item.category"></span>
                                                        <span>•</span>
                                                        <span class="font-medium text-[#007AFF]"
                                                            x-text="item.formatted_price"></span>
                                                    </div>
                                                </div>
                                                <i data-lucide="chevron-right"
                                                    class="w-3.5 h-3.5 text-slate-400 group-hover:translate-x-0.5 transition-transform shrink-0"></i>
                                            </a>
                                        </template>
                                    </div>
                                    <a :href="'{{ route('marketplace.search') }}?q=' + encodeURIComponent(query)"
                                        class="block p-2 text-center text-[11px] font-bold text-[#007AFF] hover:bg-[#007AFF]/10 transition-colors">
                                        {{ __('public_home.split_marketplace_view_all_prefix') }} "<span x-text="query"></span>" &rarr;
                                    </a>
                                </div>
                            </div>

                            <!-- Category Filter Chips -->
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs -mx-1 px-1 scrollbar-none">
                                <a href="{{ route('marketplace.search') }}"
                                    class="px-3.5 py-1.5 rounded-full bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-semibold text-[11.5px] shrink-0 shadow-xs hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors min-h-[32px] flex items-center touch-action-manipulation">{{ __('public_home.category_all') }}</a>
                                <a href="{{ route('marketplace.search', ['kategori' => 'fnb']) }}"
                                    class="px-3.5 py-1.5 rounded-full bg-slate-100/90 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-medium text-[11.5px] shrink-0 hover:bg-slate-200 dark:hover:bg-slate-700/80 hover:text-slate-900 dark:hover:text-white transition-colors min-h-[32px] flex items-center touch-action-manipulation">{{ __('public_home.category_fnb') }}</a>
                                <a href="{{ route('marketplace.search', ['kategori' => 'retail']) }}"
                                    class="px-3.5 py-1.5 rounded-full bg-slate-100/90 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-medium text-[11.5px] shrink-0 hover:bg-slate-200 dark:hover:bg-slate-700/80 hover:text-slate-900 dark:hover:text-white transition-colors min-h-[32px] flex items-center touch-action-manipulation">{{ __('public_home.category_retail') }}</a>
                                <a href="{{ route('marketplace.search', ['kategori' => 'service']) }}"
                                    class="px-3.5 py-1.5 rounded-full bg-slate-100/90 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-medium text-[11.5px] shrink-0 hover:bg-slate-200 dark:hover:bg-slate-700/80 hover:text-slate-900 dark:hover:text-white transition-colors min-h-[32px] flex items-center touch-action-manipulation">{{ __('public_home.category_workshop') }}</a>
                                <a href="{{ route('marketplace.search', ['kategori' => 'service']) }}"
                                    class="px-3.5 py-1.5 rounded-full bg-slate-100/90 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-medium text-[11.5px] shrink-0 hover:bg-slate-200 dark:hover:bg-slate-700/80 hover:text-slate-900 dark:hover:text-white transition-colors min-h-[32px] flex items-center touch-action-manipulation">{{ __('public_home.category_laundry') }}</a>
                                <a href="{{ route('marketplace.search') }}"
                                    class="px-3.5 py-1.5 rounded-full bg-slate-100/90 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-medium text-[11.5px] shrink-0 hover:bg-slate-200 dark:hover:bg-slate-700/80 hover:text-slate-900 dark:hover:text-white transition-colors min-h-[32px] flex items-center touch-action-manipulation">{{ __('public_home.category_other') }}</a>
                            </div>

                            <!-- Mini Product Showcase or Honest Merchant Invitation (Mobile Snap Rail + Desktop Grid) -->
                            @if (isset($previewProducts) && $previewProducts->count() >= 3)
                                <div class="flex sm:grid overflow-x-auto sm:overflow-visible snap-x snap-mandatory scrollbar-none gap-2 sm:gap-2.5 pb-1 sm:pb-0 sm:grid-cols-3 -mx-1 px-1 sm:mx-0 sm:px-0">
                                    @foreach ($previewProducts->take(3) as $product)
                                        @php
                                            $productUrl = url(
                                                '/' .
                                                    ($product->business?->slug ?? 'toko') .
                                                    '/produk/' .
                                                    ($product->slug ?: $product->id),
                                            );
                                        @endphp
                                        <a href="{{ $productUrl }}"
                                            class="w-[38vw] min-w-[125px] max-w-[155px] shrink-0 snap-start sm:w-auto sm:shrink sm:snap-align-none p-2 sm:p-2.5 rounded-2xl bg-slate-50/90 dark:bg-slate-900/60 border border-slate-200/70 dark:border-white/10 text-left hover:border-[#007AFF]/50 hover:bg-white dark:hover:bg-slate-800/80 hover:shadow-md transition-all group flex flex-col justify-between active:scale-[0.98] touch-manipulation">
                                            <div>
                                                <div class="relative w-full h-18 sm:h-20 rounded-xl mb-2 overflow-hidden bg-slate-100 dark:bg-slate-800">
                                                    @if ($product->image_url)
                                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                            loading="lazy" decoding="async" width="120" height="80"
                                                            onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=400&q=80';"
                                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                    @else
                                                        <div class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200/70 dark:from-slate-800 dark:to-slate-700/60 flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 group-hover:scale-105 transition-transform duration-300">
                                                            <i data-lucide="package" class="w-5 h-5 mb-0.5 opacity-60"></i>
                                                            <span class="text-[9px] font-medium tracking-tight">{{ __('public_home.umkm_catalog') }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div
                                                    class="text-[11.5px] font-bold text-slate-800 dark:text-white truncate group-hover:text-[#007AFF] transition-colors"
                                                    title="{{ $product->name }}">
                                                    {{ $product->name }}
                                                </div>
                                                <div
                                                    class="text-[10px] text-slate-400 dark:text-slate-500 truncate mt-0.5">
                                                    {{ $product->category?->name ?? 'Produk' }}
                                                </div>
                                            </div>
                                            <div
                                                class="flex items-center justify-between text-[11px] mt-2 pt-1.5 border-t border-slate-200/50 dark:border-white/5 gap-1">
                                                <span class="font-bold text-[#007AFF] dark:text-[#38bdf8] truncate">
                                                    Rp {{ number_format((float) $product->selling_price, 0, ',', '.') }}
                                                </span>
                                                <span
                                                    class="text-[9px] text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200/60 dark:border-white/5 truncate shrink-0 max-w-[55px]"
                                                    title="{{ $product->business?->storeSetting?->city ?? 'Lokal' }}">{{ $product->business?->storeSetting?->city ?? 'Lokal' }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <!-- Honest Empty State (No Dummy/Fake Products) -->
                                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/70 dark:border-white/10 text-center space-y-2.5">
                                    <div class="w-9 h-9 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
                                        <i data-lucide="store" class="w-4 h-4"></i>
                                    </div>
                                    <div class="space-y-0.5">
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                                            {{ __('public_home.split_marketplace_empty_title') }}
                                        </h4>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 max-w-xs mx-auto leading-relaxed">
                                            {{ __('public_home.split_marketplace_empty_desc') }}
                                        </p>
                                    </div>
                                    <a href="{{ route('register') }}"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-[#007AFF] to-[#0062D2] hover:from-[#0071E3] hover:to-[#0055BA] text-white text-[11px] sm:text-xs font-semibold shadow-xs transition">
                                        <span>{{ __('public_home.split_marketplace_empty_btn') }}</span>
                                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            @endif

                        </div>
                    </div>

                </div>
            </div>
        </section>


        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <!-- â•â•â• 5. "COCOK UNTUK BERBAGAI JENIS BISNIS" (Solutions) â•â•â• -->
        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <section class="bg-white dark:bg-[#070A14] py-20 lg:py-24 transition-colors">
            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Section Header with Right-Aligned Button -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
                    <div class="space-y-2">
                        <p
                            class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                            {{ __('public_home.solutions_section_kicker') }}
                        </p>
                        <h2
                            class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-[1.15]">
                            {{ __('public_home.solutions_section_title') }}
                        </h2>
                        <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base">
                            {{ __('public_home.solutions_section_desc') }}
                        </p>
                    </div>
                    <div>
                        <a href="{{ route('public.solutions.fnb') }}"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm shadow-[0_4px_16px_rgba(0,122,255,0.3)] hover:scale-[1.02] active:scale-[0.98] transition-all min-h-[44px] touch-action-manipulation">
                            <span>{{ __('public_home.solutions_section_btn') }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- 6 Industry Photo Cards with Module Badges (Mobile App Horizontal Snap Rail + Desktop 6-Col Grid) -->
                <div class="flex sm:grid overflow-x-auto sm:overflow-visible snap-x snap-mandatory scrollbar-none gap-3.5 -mx-4 px-4 sm:mx-0 sm:px-0 sm:grid-cols-3 lg:grid-cols-6 sm:gap-4 pb-2 sm:pb-0">

                    <!-- 1. F&B -->
                    <a href="{{ route('public.solutions.fnb') }}"
                        class="group relative h-52 sm:h-56 w-[72vw] min-w-[240px] max-w-[280px] shrink-0 snap-center sm:w-auto sm:shrink sm:snap-align-none rounded-2xl overflow-hidden block shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=400&q=70"
                            alt="{{ __('public_home.ind_fnb') }}"
                            loading="lazy" decoding="async" width="400" height="267"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/40 to-transparent">
                        </div>
                        <div class="absolute bottom-3 inset-x-3 text-white space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold">{{ __('public_home.solution_fnb_name') }}</span>
                                <i data-lucide="arrow-right"
                                    class="w-3.5 h-3.5 opacity-80 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
                            </div>
                            <p class="text-xs text-slate-300 leading-tight">
                                {{ __('public_home.solution_fnb_features') }}
                            </p>
                        </div>
                    </a>

                    <!-- 2. Retail -->
                    <a href="{{ route('public.solutions.retail') }}"
                        class="group relative h-52 sm:h-56 w-[72vw] min-w-[240px] max-w-[280px] shrink-0 snap-center sm:w-auto sm:shrink sm:snap-align-none rounded-2xl overflow-hidden block shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=400&q=70"
                            alt="{{ __('public_home.ind_retail') }}"
                            loading="lazy" decoding="async" width="400" height="267"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/40 to-transparent">
                        </div>
                        <div class="absolute bottom-3 inset-x-3 text-white space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold">{{ __('public_home.solution_retail_name') }}</span>
                                <i data-lucide="arrow-right"
                                    class="w-3.5 h-3.5 opacity-80 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
                            </div>
                            <p class="text-xs text-slate-300 leading-tight">
                                {{ __('public_home.solution_retail_features') }}
                            </p>
                        </div>
                    </a>

                    <!-- 3. Workshop -->
                    <a href="{{ route('public.solutions.workshop') }}"
                        class="group relative h-52 sm:h-56 w-[72vw] min-w-[240px] max-w-[280px] shrink-0 snap-center sm:w-auto sm:shrink sm:snap-align-none rounded-2xl overflow-hidden block shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=400&q=70"
                            alt="{{ __('public_home.ind_workshop') }}"
                            loading="lazy" decoding="async" width="400" height="267"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/40 to-transparent">
                        </div>
                        <div class="absolute bottom-3 inset-x-3 text-white space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold">{{ __('public_home.solution_workshop_name') }}</span>
                                <i data-lucide="arrow-right"
                                    class="w-3.5 h-3.5 opacity-80 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
                            </div>
                            <p class="text-xs text-slate-300 leading-tight">
                                {{ __('public_home.solution_workshop_features') }}
                            </p>
                        </div>
                    </a>

                    <!-- 4. Laundry -->
                    <a href="{{ route('public.solutions.laundry') }}"
                        class="group relative h-52 sm:h-56 w-[72vw] min-w-[240px] max-w-[280px] shrink-0 snap-center sm:w-auto sm:shrink sm:snap-align-none rounded-2xl overflow-hidden block shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1517677208171-0bc6725a3e60?auto=format&fit=crop&w=400&q=70"
                            alt="{{ __('public_home.ind_laundry') }}"
                            loading="lazy" decoding="async" width="400" height="267"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/40 to-transparent">
                        </div>
                        <div class="absolute bottom-3 inset-x-3 text-white space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold">{{ __('public_home.solution_laundry_name') }}</span>
                                <i data-lucide="arrow-right"
                                    class="w-3.5 h-3.5 opacity-80 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
                            </div>
                            <p class="text-xs text-slate-300 leading-tight">
                                {{ __('public_home.solution_laundry_features') }}
                            </p>
                        </div>
                    </a>

                    <!-- 5. Manufacturing -->
                    <a href="{{ route('public.solutions.manufacturing') }}"
                        class="group relative h-52 sm:h-56 w-[72vw] min-w-[240px] max-w-[280px] shrink-0 snap-center sm:w-auto sm:shrink sm:snap-align-none rounded-2xl overflow-hidden block shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=400&q=70"
                            alt="{{ __('public_home.ind_manufacturing') }}"
                            loading="lazy" decoding="async" width="400" height="267"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/40 to-transparent">
                        </div>
                        <div class="absolute bottom-3 inset-x-3 text-white space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold">{{ __('public_home.solution_mfg_name') }}</span>
                                <i data-lucide="arrow-right"
                                    class="w-3.5 h-3.5 opacity-80 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
                            </div>
                            <p class="text-xs text-slate-300 leading-tight">
                                {{ __('public_home.solution_mfg_features') }}
                            </p>
                        </div>
                    </a>

                    <!-- 6. Services -->
                    <a href="{{ route('public.solutions.services') }}"
                        class="group relative h-52 sm:h-56 w-[72vw] min-w-[240px] max-w-[280px] shrink-0 snap-center sm:w-auto sm:shrink sm:snap-align-none rounded-2xl overflow-hidden block shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&fit=crop&w=400&q=70"
                            alt="{{ __('public_home.ind_services') }}"
                            loading="lazy" decoding="async" width="400" height="267"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-950/40 to-transparent">
                        </div>
                        <div class="absolute bottom-3 inset-x-3 text-white space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold">{{ __('public_home.solution_services_name') }}</span>
                                <i data-lucide="arrow-right"
                                    class="w-3.5 h-3.5 opacity-80 group-hover:translate-x-1 group-hover:opacity-100 transition-all"></i>
                            </div>
                            <p class="text-xs text-slate-300 leading-tight">
                                {{ __('public_home.solution_services_features') }}
                            </p>
                        </div>
                    </a>

                </div>

                <!-- Subtle Swipe Guide for Mobile (< sm) -->
                <div class="flex sm:hidden items-center justify-center gap-1.5 pt-2 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                    <i data-lucide="chevrons-right" class="w-3.5 h-3.5 text-[#007AFF] animate-pulse"></i>
                    <span>Geser untuk melihat 6 sektor industri</span>
                </div>

                <div class="mt-8 text-center space-y-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ __('public_home.solutions_footer_text') }}
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
                        <a href="/aplikasi-kasir-gratis" class="px-3 py-1.5 rounded-full bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-[#007AFF] dark:hover:text-[#00C2FF] transition-colors">{{ __('public_home.solution_tag_free') }}</a>
                        <a href="/aplikasi-kasir-android" class="px-3 py-1.5 rounded-full bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-[#007AFF] dark:hover:text-[#00C2FF] transition-colors">{{ __('public_home.solution_tag_android') }}</a>
                        <a href="/aplikasi-kasir-offline" class="px-3 py-1.5 rounded-full bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-[#007AFF] dark:hover:text-[#00C2FF] transition-colors">{{ __('public_home.solution_tag_offline') }}</a>
                        <a href="{{ route('solusi.show', 'kasir-apotek') }}" class="px-3 py-1.5 rounded-full bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-[#007AFF] dark:hover:text-[#00C2FF] transition-colors">{{ __('public_home.solution_tag_pharmacy') }}</a>
                        <a href="{{ route('solusi.show', 'aplikasi-catering') }}" class="px-3 py-1.5 rounded-full bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-[#007AFF] dark:hover:text-[#00C2FF] transition-colors">{{ __('public_home.solution_tag_catering') }}</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <!-- â•â•â• 6. BOTTOM CTA BANNER (Dark with Desk Setup & Handwriting) â•â•â• -->
        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <section class="relative bg-[#070B19] text-white py-20 lg:py-24 overflow-hidden border-t border-white/10">
            <!-- Ambient lighting -->
            <div
                class="absolute -top-24 right-1/4 w-[450px] h-[450px] bg-[#00C2FF]/15 rounded-full blur-[140px] pointer-events-none">
            </div>

            <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

                    <!-- Left Column: Typography Hierarchy (Kicker, H2, H3, Body, 4-Step Ribbon, Dual CTAs) -->
                    <div class="lg:col-span-6 space-y-6 text-center lg:text-left">

                        <!-- Pure Typographic Overline Kicker -->
                        <p class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-cyan-400">
                            {{ __('public_home.bottom_cta_kicker') }}
                        </p>

                        <!-- Main Headline (H2) -->
                        <h2
                            class="text-4xl sm:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-extrabold text-white tracking-tight leading-[1.12]">
                            {{ __('public_home.bottom_cta_title') }}
                        </h2>

                        <!-- Subheadline -->
                        <p class="text-base sm:text-lg lg:text-xl font-semibold text-slate-200 tracking-tight">
                            {{ __('public_home.bottom_cta_subtitle') }}
                        </p>

                        <!-- Supporting Copy -->
                        <p class="text-slate-400 text-sm sm:text-base leading-relaxed max-w-xl mx-auto lg:mx-0">
                            {{ __('public_home.bottom_cta_desc') }}
                        </p>

                        <!-- 4 Step Flow Ribbon (Bento Tiles) -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-1 text-left">
                            <div
                                class="p-3 rounded-[14px] bg-white/[0.04] border border-white/10 backdrop-blur-sm hover:bg-white/[0.08] transition-all group">
                                <span class="text-cyan-400 font-bold block text-[11px] tabular-nums mb-1">01</span>
                                <span class="text-xs font-semibold text-white/90 leading-snug block">{{ __('public_home.bottom_cta_step_1') }}</span>
                            </div>
                            <div
                                class="p-3 rounded-[14px] bg-white/[0.04] border border-white/10 backdrop-blur-sm hover:bg-white/[0.08] transition-all group">
                                <span class="text-cyan-400 font-bold block text-[11px] tabular-nums mb-1">02</span>
                                <span class="text-xs font-semibold text-white/90 leading-snug block">{{ __('public_home.bottom_cta_step_2') }}</span>
                            </div>
                            <div
                                class="p-3 rounded-[14px] bg-white/[0.04] border border-white/10 backdrop-blur-sm hover:bg-white/[0.08] transition-all group">
                                <span class="text-cyan-400 font-bold block text-[11px] tabular-nums mb-1">03</span>
                                <span class="text-xs font-semibold text-white/90 leading-snug block">{{ __('public_home.bottom_cta_step_3') }}</span>
                            </div>
                            <div
                                class="p-3 rounded-[14px] bg-white/[0.04] border border-white/10 backdrop-blur-sm hover:bg-white/[0.08] transition-all group">
                                <span class="text-cyan-400 font-bold block text-[11px] tabular-nums mb-1">04</span>
                                <span class="text-xs font-semibold text-white/90 leading-snug block">{{ __('public_home.bottom_cta_step_4') }}</span>
                            </div>
                        </div>

                        <!-- Auth-Aware Dual CTAs with Apple HIG Button Geometry -->
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 pt-2">
                            @if (auth('admin')->check())
                                <a href="{{ route('admin.dashboard') }}"
                                    class="w-full sm:w-auto px-7 py-3.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-bold text-sm flex items-center justify-center gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.4)] hover:scale-[1.02] active:scale-[0.98] transition-all min-h-[48px] touch-action-manipulation">
                                    <span>{{ __('public_home.bottom_cta_btn_dashboard') }}</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            @elseif (auth('web')->check())
                                <a href="{{ route('dashboard') }}"
                                    class="w-full sm:w-auto px-7 py-3.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-bold text-sm flex items-center justify-center gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.4)] hover:scale-[1.02] active:scale-[0.98] transition-all min-h-[48px] touch-action-manipulation">
                                    <span>{{ __('public_home.bottom_cta_btn_dashboard') }}</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            @else
                                <a href="{{ route('register') }}"
                                    class="w-full sm:w-auto px-7 py-3.5 rounded-[14px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-bold text-sm flex items-center justify-center gap-2 shadow-[0_4px_20px_rgba(0,122,255,0.4)] hover:scale-[1.02] active:scale-[0.98] transition-all min-h-[48px] touch-action-manipulation">
                                    <span>{{ __('public_home.bottom_cta_btn_free') }}</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Right Column: Laptop Workspace with Handwritten Annotation -->
                    <div class="lg:col-span-6 relative">
                        <div class="relative max-w-md mx-auto lg:max-w-none">

                            <!-- Handwritten Annotation (One System, Endless Possibilities) -->
                            <div class="absolute -top-10 right-4 z-20 hidden sm:block text-right">
                                <span style="font-family: 'Caveat', cursive;"
                                    class="text-2xl sm:text-3xl text-cyan-300 font-bold block transform -rotate-3 drop-shadow-[0_2px_10px_rgba(0,194,255,0.5)]">
                                    {{ __('public_home.bottom_cta_annotation') }}
                                </span>
                                <!-- Hand-drawn curved arrow pointing to laptop -->
                                <svg class="w-16 h-8 text-cyan-300 ml-auto mt-0.5" viewBox="0 0 100 50" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <path d="M 85 5 Q 50 35 15 45" />
                                    <path d="M 15 45 L 28 40" />
                                    <path d="M 15 45 L 24 32" />
                                </svg>
                            </div>

                            <!-- Desk Mockup Container -->
                            <div
                                class="rounded-[24px] overflow-hidden border border-white/15 shadow-[0_25px_60px_rgba(0,0,0,0.5)] bg-slate-900/80 backdrop-blur-md">
                                <img src="https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=640&q=70"
                                    alt="COOCA Workspace"
                                    loading="lazy" decoding="async" width="640" height="380"
                                    class="w-full h-72 sm:h-80 object-cover opacity-90 hover:opacity-100 transition-opacity duration-300">
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- Verified Customer Testimonials & Video Proof (Auto-hidden if empty) --}}
        @include('public.partials.verified_testimonials')
        @include('public.partials.video_testimonials')

    </div>
@endsection
