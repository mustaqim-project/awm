@extends('layouts.public_marketing')

@section('title', __('public_contact.contact_seo_title'))
@section('description', __('public_contact.contact_seo_desc'))
@section('og_title', __('public_contact.contact_seo_title'))
@section('og_description', __('public_contact.contact_seo_desc'))
@section('canonical', route('contact'))
@section('og_type', 'website')

    @push('seo')
        <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "ContactPage",
      "name": "Kontak Dukungan COOCA",
      "url": "{{ route('contact') }}",
      "telephone": "{{ $officialWhatsapp }}"
    }
    </script>
    @endpush

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        {{-- HERO SECTION (Apple Bento Modern Dark Style without Breadcrumb) --}}
        {{-- ══════════════════════════════════════════════════════════════════════ --}}
        <section
            class="relative w-full min-w-full bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">
            {{-- Ambient Glows --}}
            <div
                class="absolute -top-32 right-1/4 w-[500px] h-[500px] bg-[#007AFF]/15 rounded-full blur-[140px] pointer-events-none -z-0">
            </div>
            <div
                class="absolute bottom-0 left-1/4 w-[450px] h-[450px] bg-[#00C4D8]/10 rounded-full blur-[130px] pointer-events-none -z-0">
            </div>

            <div
                class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-6 pb-[calc(5rem+env(safe-area-inset-bottom,0px))] sm:pt-10 sm:pb-20 lg:py-14">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center w-full">
                    {{-- Left Column: Copy & Core Actions --}}
                    <div class="lg:col-span-6 space-y-6 text-left flex flex-col items-start w-full">
                        <div class="space-y-3 w-full">
                            <div
                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/[0.06] border border-white/10 text-xs sm:text-[13px] font-semibold text-[#00C4D8] backdrop-blur-md mb-2">
                                <span class="w-2 h-2 rounded-full bg-[#00C4D8] animate-pulse"></span>
                                <span>{{ __('public_contact.contact_kicker') }}</span>
                            </div>

                            <h1
                                class="text-2.5xl xs:text-3xl sm:text-4xl md:text-5xl lg:text-[2.65rem] xl:text-[3rem] font-black tracking-tight text-white leading-[1.15] text-balance break-words text-left">
                                {{ __('public_contact.contact_title_prefix') }} <span
                                    class="bg-gradient-to-r from-[#00C4D8] via-[#60A5FA] to-[#007AFF] bg-clip-text text-transparent">{{ __('public_contact.contact_title_accent') }}</span>
                            </h1>
                        </div>

                        <p
                            class="text-sm sm:text-base lg:text-lg text-slate-300 leading-relaxed font-normal text-pretty text-left max-w-xl">
                            {{ __('public_contact.contact_subtitle') }}
                        </p>

                        {{-- Direct Primary Actions --}}
                        <div
                            class="flex flex-col sm:flex-row items-stretch sm:items-center justify-start gap-3.5 pt-2 w-full sm:w-auto">
                            <a href="https://wa.me/{{ $officialWhatsappRaw }}?text=Halo%20Tim%20Cooca%20UMKM,%20saya%20butuh%20bantuan%20seputar%20aplikasi"
                                target="_blank" rel="noopener"
                                class="inline-flex justify-center items-center gap-2.5 px-6 py-3.5 rounded-xl bg-[#34C759] hover:bg-[#2DB84D] text-white font-semibold text-sm shadow-lg shadow-emerald-500/25 active:scale-[0.98] transition-all min-h-[48px]">
                                <i data-lucide="message-circle" class="w-5 h-5 shrink-0"></i>
                                <span>{{ __('public_contact.wa_channel_button') }}</span>
                            </a>
                            <a href="#form-pesan"
                                class="inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm border border-white/15 backdrop-blur-sm transition-all min-h-[48px]">
                                <i data-lucide="mail" class="w-4 h-4 text-slate-300 shrink-0"></i>
                                <span>{{ __('public_contact.form_btn_nav') }}</span>
                            </a>
                        </div>

                        {{-- Key Trust Checklist Badges --}}
                        <div
                            class="flex flex-wrap items-center gap-x-6 gap-y-2.5 pt-4 border-t border-white/10 text-xs sm:text-sm text-slate-300 font-medium w-full">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </div>
                                <span>{{ __('public_contact.contact_badge_response') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </div>
                                <span>{{ __('public_contact.contact_badge_guide') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </div>
                                <span>{{ __('public_contact.contact_badge_privacy') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Support Operation Bento Card --}}
                    <div class="lg:col-span-6 relative w-full mt-4 lg:mt-0">
                        <div
                            class="relative rounded-2xl sm:rounded-3xl bg-gradient-to-b from-[#0E1E45]/90 to-[#0A122C]/90 p-4 sm:p-6 shadow-2xl border border-white/15 ring-1 ring-white/10 backdrop-blur-xl text-white overflow-hidden space-y-4">
                            {{-- Spotlight Decoration --}}
                            <div
                                class="absolute -top-24 -right-24 w-48 h-48 bg-[#007AFF]/25 rounded-full blur-3xl pointer-events-none">
                            </div>

                            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="relative flex h-2.5 w-2.5">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                    </span>
                                    <span class="text-xs font-bold uppercase tracking-wider text-white">{{ __('public_contact.status_support_title') }}</span>
                                </div>
                                <span
                                    class="text-[11px] text-emerald-400 font-semibold bg-emerald-500/15 border border-emerald-500/30 px-2.5 py-0.5 rounded-full">
                                    {{ __('public_contact.status_support_active') }}
                                </span>
                            </div>

                            {{-- Channel 1: WhatsApp --}}
                            <div
                                class="p-3.5 rounded-2xl bg-[#060B1E]/90 border border-white/10 flex items-center justify-between gap-3 hover:border-emerald-500/40 transition-all">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-[#34C759] text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                                        <i data-lucide="phone-call" class="w-5 h-5"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider truncate">
                                            {{ __('public_contact.channel_wa_hotline') }}</div>
                                        <div class="text-sm sm:text-base font-bold text-white font-mono truncate">
                                            {{ $officialWhatsapp }}</div>
                                    </div>
                                </div>
                                <a href="https://wa.me/{{ $officialWhatsappRaw }}?text=Halo%20Tim%20Cooca%20UMKM"
                                    target="_blank" rel="noopener"
                                    class="text-xs font-bold text-emerald-400 hover:underline flex items-center gap-1 shrink-0 px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                                    Chat <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            </div>

                            {{-- Channel 2: Email --}}
                            <div
                                class="p-3.5 rounded-2xl bg-[#060B1E]/90 border border-white/10 flex items-center justify-between gap-3 hover:border-[#007AFF]/40 transition-all">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-[#007AFF]/20 border border-[#007AFF]/30 text-[#00C4D8] flex items-center justify-center shrink-0">
                                        <i data-lucide="mail" class="w-5 h-5"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="text-[10px] font-bold text-slate-400 uppercase tracking-wider truncate">
                                            {{ __('public_contact.channel_email_official') }}</div>
                                        <a href="mailto:{{ $officialEmail }}"
                                            class="text-xs sm:text-sm font-bold text-white hover:underline truncate block">{{ $officialEmail }}</a>
                                    </div>
                                </div>
                                <a href="mailto:{{ $officialEmail }}"
                                    class="text-xs font-bold text-[#00C4D8] hover:underline shrink-0 px-2.5 py-1 rounded-lg bg-[#007AFF]/10 border border-[#007AFF]/20">{{ __('public_contact.email_channel_button') }}</a>
                            </div>

                            {{-- Channel 3: Office --}}
                            <div
                                class="p-3.5 rounded-2xl bg-[#060B1E]/90 border border-white/10 flex items-start gap-3 hover:border-white/20 transition-all">
                                <div
                                    class="w-10 h-10 rounded-xl bg-white/10 text-slate-200 flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="map-pin" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('public_contact.channel_office_ops') }}</div>
                                    <div class="text-xs sm:text-sm font-semibold text-white mt-0.5 leading-snug break-words">
                                        {{ $officeLocation }}</div>
                                    <div class="text-[11px] text-slate-400 mt-1 leading-normal">{{ $supportSla['hours'] ?? __('public_contact.channel_hours_default') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Main Content: Form Section --}}
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <section id="form-pesan" class="pt-2">
                <div
                    class="max-w-3xl mx-auto bg-white dark:bg-[#0E172F]/70 border border-slate-200/80 dark:border-white/10 p-6 sm:p-10 rounded-[24px] shadow-sm">
                    <div class="border-b border-slate-200/80 dark:border-white/10 pb-6 mb-8">
                        <div
                            class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] mb-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>{{ __('public_contact.form_kicker') }}</span>
                        </div>
                        <h2
                            class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-tight text-balance break-words">
                            {{ __('public_contact.form_heading') }}
                        </h2>
                        <p
                            class="text-sm text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed text-pretty break-words">
                            {{ __('public_contact.form_subheading') }}
                        </p>
                    </div>

                    @if (session('success_message'))
                        <div
                            class="p-4 rounded-[16px] bg-emerald-500/10 border border-emerald-500/25 text-slate-900 dark:text-white text-sm mb-8 flex items-start gap-3.5">
                            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                            <div>
                                <div class="font-bold text-emerald-600 dark:text-emerald-400">{{ __('public_contact.form_success_title') }}</div>
                                <div class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                                    {{ session('success_message') }}</div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
                                    {{ __('public_contact.form_label_name') }} <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="name" required placeholder="{{ __('public_contact.form_placeholder_name') }}"
                                    value="{{ old('name') }}"
                                    class="w-full h-12 px-4 bg-slate-50 dark:bg-[#070A14] border border-slate-200 dark:border-white/10 rounded-[14px] text-slate-900 dark:text-white text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-slate-400">
                                @error('name')
                                    <span class="text-xs text-[#FF3B30] mt-1.5 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
                                    {{ __('public_contact.form_label_email') }} <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="email" name="email" required placeholder="{{ __('public_contact.form_placeholder_email') }}"
                                    value="{{ old('email') }}"
                                    class="w-full h-12 px-4 bg-slate-50 dark:bg-[#070A14] border border-slate-200 dark:border-white/10 rounded-[14px] text-slate-900 dark:text-white text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-slate-400">
                                @error('email')
                                    <span class="text-xs text-[#FF3B30] mt-1.5 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
                                    {{ __('public_contact.form_label_phone') }}
                                </label>
                                <input type="tel" name="phone" placeholder="{{ __('public_contact.form_placeholder_phone') }}"
                                    value="{{ old('phone') }}"
                                    class="w-full h-12 px-4 bg-slate-50 dark:bg-[#070A14] border border-slate-200 dark:border-white/10 rounded-[14px] text-slate-900 dark:text-white text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-slate-400">
                                <span class="text-[12px] text-slate-500 dark:text-slate-400 mt-1 block">{{ __('public_contact.form_hint_phone') }}</span>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
                                    {{ __('public_contact.form_label_subject') }} <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="subject" required
                                    placeholder="{{ __('public_contact.form_placeholder_subject') }}" value="{{ old('subject') }}"
                                    class="w-full h-12 px-4 bg-slate-50 dark:bg-[#070A14] border border-slate-200 dark:border-white/10 rounded-[14px] text-slate-900 dark:text-white text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-slate-400">
                                @error('subject')
                                    <span class="text-xs text-[#FF3B30] mt-1.5 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-2">
                                {{ __('public_contact.form_label_message') }} <span class="text-[#FF3B30]">*</span>
                            </label>
                            <textarea name="message" rows="5" required
                                placeholder="{{ __('public_contact.form_placeholder_message') }}"
                                class="w-full p-4 bg-slate-50 dark:bg-[#070A14] border border-slate-200 dark:border-white/10 rounded-[14px] text-slate-900 dark:text-white text-[16px] focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 focus:outline-none transition-all placeholder-slate-400">{{ old('message') }}</textarea>
                            @error('message')
                                <span class="text-xs text-[#FF3B30] mt-1.5 block font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="w-full h-13 py-3.5 rounded-[16px] bg-[#007AFF] hover:bg-[#0066DF] text-white font-semibold text-sm sm:text-base flex items-center justify-center gap-2.5 shadow-lg shadow-[#007AFF]/25 active:scale-[0.99] transition-all">
                                <span>{{ __('public_contact.form_submit_button') }}</span>
                                <i data-lucide="send" class="w-4 h-4"></i>
                            </button>
                            <p class="text-center text-xs text-slate-500 dark:text-slate-400 mt-3">
                                {{ __('public_contact.form_privacy_footer') }}
                            </p>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection
