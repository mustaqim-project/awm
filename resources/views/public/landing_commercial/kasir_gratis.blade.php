@extends('layouts.public_marketing')

@php
    $pageSeo = \App\Services\Seo\SeoMetadataService::getPageSeo();
@endphp

@section('title', $pageSeo['title'] ?? __('public_commercial.free_title') . ' | COOCA')
@section('description', $pageSeo['description'] ?? __('public_commercial.free_subtitle'))
@section('og_title', $pageSeo['og_title'] ?? __('public_commercial.free_title') . ' | COOCA')
@section('og_description', $pageSeo['og_description'] ?? __('public_commercial.free_subtitle'))
@section('canonical', url('/aplikasi-kasir-gratis'))
@section('og_type', 'product')

@push('seo')
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "COOCA Aplikasi Kasir Gratis",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web, Android, iOS, Windows, macOS",
      "description": "Aplikasi kasir gratis selamanya untuk UMKM Indonesia tanpa kartu kredit dan tanpa biaya berlangganan tersembunyi.",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "IDR"
      }
    }
    </script>
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "{{ __('public_commercial.free_faq_q1') }}",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "{{ __('public_commercial.free_faq_a1') }}"
          }
        },
        {
          "@type": "Question",
          "name": "{{ __('public_commercial.free_faq_q2') }}",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "{{ __('public_commercial.free_faq_a2', ['outlet' => $tierLimits['free']['outlet'] ?? 1, 'product' => $tierLimits['free']['product'] ?? 10, 'pos' => $tierLimits['free']['pos'] ?? 30]) }}"
          }
        },
        {
          "@type": "Question",
          "name": "{{ __('public_commercial.free_faq_q3') }}",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "{{ __('public_commercial.free_faq_a3') }}"
          }
        },
        {
          "@type": "Question",
          "name": "{{ __('public_commercial.free_faq_q4') }}",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "{{ __('public_commercial.free_faq_a4') }}"
          }
        },
        {
          "@type": "Question",
          "name": "{{ __('public_commercial.free_faq_q5') }}",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "{{ __('public_commercial.free_faq_a5') }}"
          }
        }
      ]
    }
    </script>
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "{{ __('public_marketing.nav_home') }}",
          "item": "{{ url('/') }}"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "{{ __('public_commercial.free_kicker') }}",
          "item": "{{ url('/aplikasi-kasir-gratis') }}"
        }
      ]
    }
    </script>
@endpush

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- 1. HERO SECTION --}}
        <section class="relative bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">
            <div class="absolute top-1/4 -right-24 w-96 h-96 bg-[#007AFF]/20 rounded-full blur-[120px] pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-[#00C4D8]/15 rounded-full blur-[140px] pointer-events-none"></div>

            <div class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-8 pb-16 lg:py-16">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    <div class="lg:col-span-7 space-y-6 text-left">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                {{ __('public_commercial.free_hero_badge') }}
                            </p>
                        </div>

                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.14] text-balance">
                            {{ \App\Services\Seo\SeoMetadataService::getH1(__('public_commercial.free_title')) }}
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-2xl">
                            {{ __('public_commercial.free_hero_desc') }}
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 text-left w-full">
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/20">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">{{ __('public_commercial.free_hero_pill_1', ['outlet' => $tierLimits['free']['outlet'] ?? 1, 'product' => $tierLimits['free']['product'] ?? 10]) }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-sky-500/15 text-[#00C4D8] flex items-center justify-center shrink-0 mt-0.5 border border-sky-400/20">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">{{ __('public_commercial.free_hero_pill_2') }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center shrink-0 mt-0.5 border border-purple-400/20">
                                    <i data-lucide="scan" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">{{ __('public_commercial.free_hero_pill_3') }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0 mt-0.5 border border-amber-400/20">
                                    <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">{{ __('public_commercial.free_hero_pill_4') }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-4">
                            <a href="/register" class="inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl bg-gradient-to-r from-[#007AFF] to-[#00C4D8] text-white font-bold text-sm sm:text-base shadow-lg shadow-[#007AFF]/30 hover:brightness-110 active:scale-[0.99] transition-all">
                                <span>{{ __('public_commercial.free_cta_register') }}</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="/pricing" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm sm:text-base border border-white/15 transition-all">
                                <span>{{ __('public_commercial.free_cta_compare') }}</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right Column: Package Highlight Card --}}
                    <div class="lg:col-span-5 w-full">
                        <div class="relative rounded-2xl bg-gradient-to-b from-white/10 to-white/[0.03] border border-white/15 p-6 sm:p-8 backdrop-blur-xl shadow-2xl">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-xs font-bold uppercase tracking-wider mb-4">
                                <span>{{ __('public_commercial.free_card_badge') }}</span>
                            </div>
                            <div class="flex items-baseline gap-2 mb-2">
                                <span class="text-4xl sm:text-5xl font-black text-white">{{ __('public_commercial.free_card_price') }}</span>
                                <span class="text-slate-400 text-sm font-semibold">{{ __('public_commercial.free_card_period') }}</span>
                            </div>
                            <p class="text-slate-300 text-sm mb-6 pb-6 border-b border-white/10">
                                {{ __('public_commercial.free_card_desc') }}
                            </p>

                            <ul class="space-y-3.5 text-sm text-slate-200 mb-8">
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <span>{!! __('public_commercial.free_card_item_1', ['outlet' => '<strong>' . ($tierLimits['free']['outlet'] ?? 1) . '</strong>']) !!}</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <span>{!! __('public_commercial.free_card_item_2', ['product' => '<strong>' . ($tierLimits['free']['product'] ?? 10) . '</strong>']) !!}</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <span>{!! __('public_commercial.free_card_item_3', ['pos' => '<strong>' . ($tierLimits['free']['pos'] ?? 30) . '</strong>']) !!}</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <span>{{ __('public_commercial.free_card_item_4') }}</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <span>{{ __('public_commercial.free_card_item_5') }}</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <span>{{ __('public_commercial.free_card_item_6') }}</span>
                                </li>
                            </ul>

                            <a href="/register" class="w-full inline-flex items-center justify-center gap-2 py-3 px-5 rounded-xl bg-white text-slate-900 font-bold text-sm hover:bg-slate-100 transition-colors">
                                <span>{{ __('public_commercial.free_card_cta') }}</span>
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. MASALAH PENCATATAN MANUAL YANG DITUNTASKAN --}}
        <section class="py-16 sm:py-20 max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-12 text-left">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">{{ __('public_commercial.free_pain_kicker') }}</span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                    {{ __('public_commercial.free_pain_title') }}
                </h2>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                    {{ __('public_commercial.free_pain_subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center font-bold">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('public_commercial.free_pain_1_title') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('public_commercial.free_pain_1_desc') }}
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('public_commercial.free_pain_2_title') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('public_commercial.free_pain_2_desc') }}
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center font-bold">
                        <i data-lucide="trending-down" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('public_commercial.free_pain_3_title') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('public_commercial.free_pain_3_desc') }}
                    </p>
                </div>
            </div>
        </section>

        {{-- 3. PERBANDINGAN PAKET: APA YANG DIDAPAT DI VERSI GRATIS VS BERBAYAR --}}
        <section class="py-16 sm:py-20 bg-slate-50 dark:bg-white/[0.02] border-y border-slate-200 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mb-12 text-left">
                    <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">{{ __('public_commercial.free_comp_kicker') }}</span>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                        {{ __('public_commercial.free_comp_title') }}
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        {{ __('public_commercial.free_comp_subtitle') }}
                    </p>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-white/10 shadow-sm bg-white dark:bg-[#060B1E]">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-white/10 bg-slate-100/70 dark:bg-white/5">
                                <th class="p-4 sm:p-5 font-bold text-slate-900 dark:text-white">{{ __('public_commercial.free_comp_th_feature') }}</th>
                                <th class="p-4 sm:p-5 font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10">{{ __('public_commercial.free_comp_th_free') }}</th>
                                <th class="p-4 sm:p-5 font-bold text-slate-900 dark:text-white">{{ __('public_commercial.free_comp_th_standard', ['price' => $pricingData['standard']['monthly_price_formatted']]) }}</th>
                                <th class="p-4 sm:p-5 font-bold text-slate-900 dark:text-white">{{ __('public_commercial.free_comp_th_premium', ['price' => $pricingData['premium']['monthly_price_formatted']]) }}</th>
                                <th class="p-4 sm:p-5 font-bold text-slate-900 dark:text-white">{{ __('public_commercial.free_comp_th_prestige', ['price' => $pricingData['prestige']['monthly_price_formatted']]) }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-white/10 text-slate-700 dark:text-slate-300">
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-slate-900 dark:text-white">{{ __('public_commercial.free_comp_row_outlets') }}</td>
                                <td class="p-4 sm:p-5 bg-emerald-500/5 font-semibold text-emerald-600 dark:text-emerald-400">{{ __('public_commercial.free_comp_val_outlets_free', ['outlet' => $tierLimits['free']['outlet'] ?? 1]) }}</td>
                                <td class="p-4 sm:p-5">{{ __('public_commercial.free_comp_val_outlets_std', ['outlet' => $tierLimits['standard']['outlet'] ?? 2]) }}</td>
                                <td class="p-4 sm:p-5">{{ __('public_commercial.free_comp_val_outlets_prem', ['outlet' => $tierLimits['premium']['outlet'] ?? 5]) }}</td>
                                <td class="p-4 sm:p-5">{{ __('public_commercial.free_comp_val_outlets_prest') }}</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-slate-900 dark:text-white">{{ __('public_commercial.free_comp_row_products') }}</td>
                                <td class="p-4 sm:p-5 bg-emerald-500/5 font-semibold text-emerald-600 dark:text-emerald-400">{{ __('public_commercial.free_comp_val_prod_free', ['product' => $tierLimits['free']['product'] ?? 10]) }}</td>
                                <td class="p-4 sm:p-5">{{ ($tierLimits['standard']['product'] ?? null) ? __('public_commercial.free_comp_val_prod_free', ['product' => $tierLimits['standard']['product']]) : __('public_commercial.free_comp_val_unlimited') }}</td>
                                <td class="p-4 sm:p-5">{{ __('public_commercial.free_comp_val_unlimited') }}</td>
                                <td class="p-4 sm:p-5">{{ __('public_commercial.free_comp_val_unlimited') }}</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-slate-900 dark:text-white">{{ __('public_commercial.free_comp_row_pos') }}</td>
                                <td class="p-4 sm:p-5 bg-emerald-500/5 font-semibold text-emerald-600 dark:text-emerald-400">{{ __('public_commercial.free_comp_val_pos_free', ['pos' => $tierLimits['free']['pos'] ?? 30]) }}</td>
                                <td class="p-4 sm:p-5">{{ ($tierLimits['standard']['pos'] ?? null) ? __('public_commercial.free_comp_val_pos_format', ['count' => number_format($tierLimits['standard']['pos'], 0, ',', '.')]) : __('public_commercial.free_comp_val_unlimited') }}</td>
                                <td class="p-4 sm:p-5">{{ __('public_commercial.free_comp_val_unlimited') }}</td>
                                <td class="p-4 sm:p-5">{{ __('public_commercial.free_comp_val_unlimited') }}</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-slate-900 dark:text-white">{{ __('public_commercial.free_comp_row_receipt') }}</td>
                                <td class="p-4 sm:p-5 bg-emerald-500/5 text-emerald-600 dark:text-emerald-400 font-bold">{{ __('public_commercial.free_comp_val_yes') }}</td>
                                <td class="p-4 sm:p-5 text-emerald-600 dark:text-emerald-400 font-bold">{{ __('public_commercial.free_comp_val_yes') }}</td>
                                <td class="p-4 sm:p-5 text-emerald-600 dark:text-emerald-400 font-bold">{{ __('public_commercial.free_comp_val_yes') }}</td>
                                <td class="p-4 sm:p-5 text-emerald-600 dark:text-emerald-400 font-bold">{{ __('public_commercial.free_comp_val_yes') }}</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-slate-900 dark:text-white">{{ __('public_commercial.free_comp_row_barcode') }}</td>
                                <td class="p-4 sm:p-5 bg-emerald-500/5 text-emerald-600 dark:text-emerald-400 font-bold">{{ __('public_commercial.free_comp_val_yes') }}</td>
                                <td class="p-4 sm:p-5 text-emerald-600 dark:text-emerald-400 font-bold">{{ __('public_commercial.free_comp_val_yes') }}</td>
                                <td class="p-4 sm:p-5 text-emerald-600 dark:text-emerald-400 font-bold">{{ __('public_commercial.free_comp_val_yes') }}</td>
                                <td class="p-4 sm:p-5 text-emerald-600 dark:text-emerald-400 font-bold">{{ __('public_commercial.free_comp_val_yes') }}</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-slate-900 dark:text-white">{{ __('public_commercial.free_comp_row_roles') }}</td>
                                <td class="p-4 sm:p-5 bg-emerald-500/5 text-slate-400">{{ __('public_commercial.free_comp_val_roles_free', ['user' => $tierLimits['free']['user'] ?? 1]) }}</td>
                                <td class="p-4 sm:p-5">{{ __('public_commercial.free_comp_val_roles_std', ['user' => $tierLimits['standard']['user'] ?? 3]) }}</td>
                                <td class="p-4 sm:p-5">{{ __('public_commercial.free_comp_val_roles_prem', ['user' => $tierLimits['premium']['user'] ?? 10]) }}</td>
                                <td class="p-4 sm:p-5">{{ __('public_commercial.free_comp_val_roles_prest') }}</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-slate-900 dark:text-white">{{ __('public_commercial.free_comp_row_reports') }}</td>
                                <td class="p-4 sm:p-5 bg-emerald-500/5">{{ __('public_commercial.free_comp_val_rep_free') }}</td>
                                <td class="p-4 sm:p-5">{{ __('public_commercial.free_comp_val_rep_std') }}</td>
                                <td class="p-4 sm:p-5">{{ __('public_commercial.free_comp_val_rep_prem') }}</td>
                                <td class="p-4 sm:p-5">{{ __('public_commercial.free_comp_val_rep_prest') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- 4. CARA DAFTAR 2 MENIT --}}
        <section class="py-16 sm:py-20 max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-12 text-left">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">{{ __('public_commercial.free_steps_kicker') }}</span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                    {{ __('public_commercial.free_steps_title') }}
                </h2>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                    {{ __('public_commercial.free_steps_subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="relative p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10">
                    <span class="text-4xl font-black text-[#007AFF]/20 dark:text-[#00C4D8]/20 block mb-3">{{ __('public_commercial.free_step_1_num') }}</span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('public_commercial.free_step_1_title') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('public_commercial.free_step_1_desc') }}
                    </p>
                </div>

                <div class="relative p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10">
                    <span class="text-4xl font-black text-[#007AFF]/20 dark:text-[#00C4D8]/20 block mb-3">{{ __('public_commercial.free_step_2_num') }}</span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('public_commercial.free_step_2_title') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('public_commercial.free_step_2_desc') }}
                    </p>
                </div>

                <div class="relative p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10">
                    <span class="text-4xl font-black text-[#007AFF]/20 dark:text-[#00C4D8]/20 block mb-3">{{ __('public_commercial.free_step_3_num') }}</span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('public_commercial.free_step_3_title') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('public_commercial.free_step_3_desc') }}
                    </p>
                </div>
            </div>
        </section>

        {{-- 5. FAQ SECTION --}}
        <section class="py-16 sm:py-20 bg-slate-50 dark:bg-white/[0.02] border-t border-slate-200 dark:border-white/10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-12 text-center">
                    <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">{{ __('public_commercial.free_faq_kicker') }}</span>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        {{ __('public_commercial.free_faq_title') }}
                    </h2>
                </div>

                <div class="space-y-4">
                    <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('public_commercial.free_faq_q1') }}</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            {{ __('public_commercial.free_faq_a1') }}
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('public_commercial.free_faq_q2') }}</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            {{ __('public_commercial.free_faq_a2', ['outlet' => $tierLimits['free']['outlet'] ?? 1, 'product' => $tierLimits['free']['product'] ?? 10, 'pos' => $tierLimits['free']['pos'] ?? 30]) }}
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('public_commercial.free_faq_q3') }}</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            {{ __('public_commercial.free_faq_a3') }}
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('public_commercial.free_faq_q4') }}</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            {{ __('public_commercial.free_faq_a4') }}
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('public_commercial.free_faq_q5') }}</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            {{ __('public_commercial.free_faq_a5') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 6. FINAL CTA --}}
        <section class="py-16 sm:py-20 bg-gradient-to-br from-[#060B1E] via-[#081232] to-[#060B1E] text-white border-t border-white/10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-snug">
                    {{ __('public_commercial.free_final_title') }}
                </h2>
                <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                    {{ __('public_commercial.free_final_subtitle') }}
                </p>
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/register" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-gradient-to-r from-[#007AFF] to-[#00C4D8] text-white font-bold text-base shadow-xl shadow-[#007AFF]/25 hover:brightness-110 transition-all">
                        <span>{{ __('public_commercial.free_final_cta') }}</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="/pricing" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-base border border-white/15 transition-all">
                        <span>{{ __('public_commercial.free_final_cta_secondary') }}</span>
                    </a>
                </div>
            </div>
        </section>

    </div>
@endsection
