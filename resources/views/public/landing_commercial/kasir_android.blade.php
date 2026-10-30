@extends('layouts.public_marketing')

@php
    $pageSeo = \App\Services\Seo\SeoMetadataService::getPageSeo();
@endphp

@section('title', $pageSeo['title'] ?? __('public_commercial.android_title') . ' | COOCA')
@section('description', $pageSeo['description'] ?? __('public_commercial.android_subtitle'))
@section('og_title', $pageSeo['og_title'] ?? __('public_commercial.android_title') . ' | COOCA')
@section('og_description', $pageSeo['og_description'] ?? __('public_commercial.android_subtitle'))
@section('canonical', url('/aplikasi-kasir-android'))
@section('og_type', 'product')

@push('seo')
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "COOCA Aplikasi Kasir Android",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Android 7.0 ke atas",
      "description": "Aplikasi kasir Android ringan untuk smartphone dan tablet dengan dukungan printer Bluetooth thermal dan scanner barcode kamera HP.",
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
          "name": "{{ __('public_commercial.android_faq_q1') }}",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "{{ __('public_commercial.android_faq_a1') }}"
          }
        },
        {
          "@type": "Question",
          "name": "{{ __('public_commercial.android_faq_q2') }}",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "{{ __('public_commercial.android_faq_a2') }}"
          }
        },
        {
          "@type": "Question",
          "name": "{{ __('public_commercial.android_faq_q3') }}",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "{{ __('public_commercial.android_faq_a3') }}"
          }
        },
        {
          "@type": "Question",
          "name": "{{ __('public_commercial.android_faq_q4') }}",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "{{ __('public_commercial.android_faq_a4') }}"
          }
        },
        {
          "@type": "Question",
          "name": "{{ __('public_commercial.android_faq_q5') }}",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "{{ __('public_commercial.android_faq_a5') }}"
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
          "name": "{{ __('public_commercial.android_kicker') }}",
          "item": "{{ url('/aplikasi-kasir-android') }}"
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
                            <span class="inline-flex w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse"></span>
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                {{ __('public_commercial.android_hero_badge') }}
                            </p>
                        </div>

                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.14] text-balance">
                            {{ \App\Services\Seo\SeoMetadataService::getH1(__('public_commercial.android_title')) }}
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-2xl">
                            {{ __('public_commercial.android_hero_desc') }}
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 text-left w-full">
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/20">
                                    <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">{{ __('public_commercial.android_hero_pill_1') }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-sky-500/15 text-[#00C4D8] flex items-center justify-center shrink-0 mt-0.5 border border-sky-400/20">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">{{ __('public_commercial.android_hero_pill_2') }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center shrink-0 mt-0.5 border border-purple-400/20">
                                    <i data-lucide="scan-barcode" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">{{ __('public_commercial.android_hero_pill_3') }}</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0 mt-0.5 border border-amber-400/20">
                                    <i data-lucide="wifi-off" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">{{ __('public_commercial.android_hero_pill_4') }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-4">
                            <a href="/download/apk" class="inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl bg-gradient-to-r from-[#007AFF] to-[#00C4D8] text-white font-bold text-sm sm:text-base shadow-lg shadow-[#007AFF]/30 hover:brightness-110 active:scale-[0.99] transition-all">
                                <i data-lucide="download" class="w-5 h-5"></i>
                                <span>{{ __('public_commercial.android_cta_download') }}</span>
                            </a>
                            <a href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm sm:text-base border border-white/15 transition-all">
                                <span>{{ __('public_commercial.android_cta_register') }}</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right Column: Specs Card --}}
                    <div class="lg:col-span-5 w-full">
                        <div class="relative rounded-2xl bg-gradient-to-b from-white/10 to-white/[0.03] border border-white/15 p-6 sm:p-8 backdrop-blur-xl shadow-2xl">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/20 text-[#00C4D8] border border-sky-400/30 text-xs font-bold uppercase tracking-wider mb-4">
                                <span>{{ __('public_commercial.android_specs_badge') }}</span>
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-2">{{ __('public_commercial.android_specs_title') }}</h3>
                            <p class="text-slate-300 text-sm mb-6 pb-6 border-b border-white/10">
                                {{ __('public_commercial.android_specs_desc') }}
                            </p>

                            <div class="space-y-4 text-sm text-slate-200 mb-8">
                                <div class="flex items-start gap-3">
                                    <i data-lucide="cpu" class="w-4 h-4 text-[#00C4D8] mt-1 shrink-0"></i>
                                    <div>
                                        <div class="font-bold text-white">{{ __('public_commercial.android_specs_os') }}</div>
                                        <div class="text-slate-400 text-xs">{{ __('public_commercial.android_specs_os_val') }}</div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i data-lucide="hard-drive" class="w-4 h-4 text-[#00C4D8] mt-1 shrink-0"></i>
                                    <div>
                                        <div class="font-bold text-white">{{ __('public_commercial.android_specs_ram') }}</div>
                                        <div class="text-slate-400 text-xs">{{ __('public_commercial.android_specs_ram_val') }}</div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i data-lucide="camera" class="w-4 h-4 text-[#00C4D8] mt-1 shrink-0"></i>
                                    <div>
                                        <div class="font-bold text-white">{{ __('public_commercial.android_specs_camera') }}</div>
                                        <div class="text-slate-400 text-xs">{{ __('public_commercial.android_specs_camera_val') }}</div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i data-lucide="bluetooth" class="w-4 h-4 text-[#00C4D8] mt-1 shrink-0"></i>
                                    <div>
                                        <div class="font-bold text-white">{{ __('public_commercial.android_specs_bluetooth') }}</div>
                                        <div class="text-slate-400 text-xs">{{ __('public_commercial.android_specs_bluetooth_val') }}</div>
                                    </div>
                                </div>
                            </div>

                            <a href="/download/apk" class="w-full inline-flex items-center justify-center gap-2 py-3 px-5 rounded-xl bg-white text-slate-900 font-bold text-sm hover:bg-slate-100 transition-colors">
                                <span>{{ __('public_commercial.android_specs_cta') }}</span>
                                <i data-lucide="arrow-down" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. DUKUNGAN PRINTER BLUETOOTH THERMAL LENGKAP --}}
        <section class="py-16 sm:py-20 max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-12 text-left">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">{{ __('public_commercial.android_printer_kicker') }}</span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                    {{ __('public_commercial.android_printer_title') }}
                </h2>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                    {{ __('public_commercial.android_printer_subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center font-bold">
                        <i data-lucide="printer" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_printer_1_title') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('public_commercial.android_printer_1_desc') }}
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_printer_2_title') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('public_commercial.android_printer_2_desc') }}
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold">
                        <i data-lucide="share-2" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_printer_3_title') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('public_commercial.android_printer_3_desc') }}
                    </p>
                </div>
            </div>
        </section>

        {{-- 3. FITUR UNGGULAN APLIKASI KASIR ANDROID --}}
        <section class="py-16 sm:py-20 bg-slate-50 dark:bg-white/[0.02] border-y border-slate-200 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mb-12 text-left">
                    <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">{{ __('public_commercial.android_touch_kicker') }}</span>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                        {{ __('public_commercial.android_touch_title') }}
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        {{ __('public_commercial.android_touch_subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="p-5 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10 space-y-2">
                        <div class="w-8 h-8 rounded-lg bg-sky-500/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="zap" class="w-4 h-4"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_touch_1_title') }}</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ __('public_commercial.android_touch_1_desc') }}
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10 space-y-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold mb-3">
                            <i data-lucide="qr-code" class="w-4 h-4"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_touch_2_title') }}</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ __('public_commercial.android_touch_2_desc') }}
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10 space-y-2">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold mb-3">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_touch_3_title') }}</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ __('public_commercial.android_touch_3_desc') }}
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10 space-y-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold mb-3">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white">{{ __('public_commercial.android_touch_4_title') }}</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ __('public_commercial.android_touch_4_desc') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 4. FAQ SECTION --}}
        <section class="py-16 sm:py-20 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">{{ __('public_commercial.android_faq_kicker') }}</span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ __('public_commercial.android_faq_title') }}
                </h2>
            </div>

            <div class="space-y-4">
                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('public_commercial.android_faq_q1') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('public_commercial.android_faq_a1') }}
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('public_commercial.android_faq_q2') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('public_commercial.android_faq_a2') }}
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('public_commercial.android_faq_q3') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('public_commercial.android_faq_a3') }}
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('public_commercial.android_faq_q4') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('public_commercial.android_faq_a4') }}
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('public_commercial.android_faq_q5') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ __('public_commercial.android_faq_a5') }}
                    </p>
                </div>
            </div>
        </section>

        {{-- 5. FINAL CTA --}}
        <section class="py-16 sm:py-20 bg-gradient-to-br from-[#060B1E] via-[#081232] to-[#060B1E] text-white border-t border-white/10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-snug">
                    {{ __('public_commercial.android_final_title') }}
                </h2>
                <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                    {{ __('public_commercial.android_final_subtitle') }}
                </p>
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/download/apk" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-gradient-to-r from-[#007AFF] to-[#00C4D8] text-white font-bold text-base shadow-xl shadow-[#007AFF]/25 hover:brightness-110 transition-all">
                        <i data-lucide="download" class="w-5 h-5"></i>
                        <span>{{ __('public_commercial.android_final_cta') }}</span>
                    </a>
                    <a href="/register" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-base border border-white/15 transition-all">
                        <span>{{ __('public_commercial.android_final_cta_secondary') }}</span>
                    </a>
                </div>
            </div>
        </section>

    </div>
@endsection
