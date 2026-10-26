@extends('layouts.public_marketing', ['title' => __('auth.register_otp.meta_title'), 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-14rem)] flex flex-col justify-center py-12 sm:py-16 px-4 sm:px-6 lg:px-8"
        x-data="coocaOtp.boxes({{ (int) ($expiresAt ?? 0) }}, {{ (int) ($resendIn ?? 0) }})">
        <div class="w-full max-w-md mx-auto">

            <!-- Official COOCA Branding & Header -->
            <div class="text-center mb-8">
                @include('auth.partials.brand-logo', ['class' => 'mb-5'])
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ __('auth.register_otp.heading') }}
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                    @if (($channel ?? 'whatsapp') === 'email' && !empty($maskedEmail))
                        {!! __('auth.register_otp.subheading_email', [
                            'email' => '<span class="font-mono font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded bg-slate-100 dark:bg-white/10">' . e($maskedEmail) . '</span>',
                            'phone' => '<span class="font-mono font-semibold text-slate-700 dark:text-slate-300">' . e($phone) . '</span>'
                        ]) !!}
                    @else
                        {!! __('auth.register_otp.subheading', ['phone' => '<span class="font-mono font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded bg-slate-100 dark:bg-white/10">' . e($phone) . '</span>']) !!}
                    @endif
                </p>
            </div>

            <!-- Structured Auth Card -->
            <div class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-colors">

                @if (session('status'))
                    <div class="mb-5 p-3.5 rounded-xl bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] dark:text-[#30D158] text-xs sm:text-sm flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if (($channel ?? 'whatsapp') === 'email' && !empty($maskedEmail))
                    <div class="mb-5 p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs sm:text-sm flex items-start gap-2.5">
                        <i data-lucide="mail-warning" class="w-4 h-4 mt-0.5 text-amber-600 dark:text-amber-400 shrink-0"></i>
                        <div>
                            <strong class="font-bold block text-amber-800 dark:text-amber-300">{{ __('auth.register_otp.waba_failed_email_notice') }}</strong>
                            <p class="text-xs text-amber-700/90 dark:text-amber-400/90 mt-0.5 leading-relaxed">
                                Kode OTP telah dialihkan dan dikirimkan ke email Anda: <strong class="font-mono text-slate-900 dark:text-white">{{ $maskedEmail }}</strong>. Silakan periksa folder Kotak Masuk (Inbox) atau Spam email Anda.
                            </p>
                        </div>
                    </div>
                @endif

                @if (app()->environment('local', 'testing'))
                    <!-- Dev Testing Bypass Alert -->
                    <div class="mb-5 p-3.5 rounded-xl bg-[#007AFF]/10 border border-[#007AFF]/20 text-[#007AFF] dark:text-[#0A84FF] text-xs sm:text-sm flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                        <span><strong>{{ __('auth.otp.bypass_dev_title') }}</strong> {!! __('auth.otp.bypass_dev_desc', ['code' => '<code class="font-mono font-bold bg-[#007AFF]/15 px-1.5 py-0.5 rounded">123456</code>']) !!}</span>
                    </div>
                @endif

                @if ($deliveryError)
                    <div class="mb-5 p-3.5 rounded-xl bg-[#FF9500]/10 border border-[#FF9500]/25 text-[#FF9500] dark:text-[#FF9F0A] text-xs sm:text-sm flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                        <span>{{ $deliveryError }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs">
                        <div class="font-semibold mb-1 flex items-center gap-1.5 text-sm">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            <span>{{ __('auth.otp.failed_alert_title') }}</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.verify.submit') }}" class="space-y-5"
                    x-data="{ submitting: false }"
                    @submit="submitting = true; if ($el.querySelector('input[name=otp]')) $el.querySelector('input[name=otp]').value = otp()">
                    @csrf
                    <div>
                        <label for="otp" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-3 text-center">
                            {{ __('auth.otp.input_label') }}
                        </label>
                        <input type="hidden" name="otp" :value="otp()" required>
                        <div class="flex justify-between gap-1.5 sm:gap-2.5">
                            <template x-for="(_, i) in [0, 1, 2, 3, 4, 5]" :key="i">
                                <input :id="'otp-box-' + i" :value="parts[i]" @input="handleInput(i, $event)"
                                    @keydown="handleKeydown(i, $event)" @paste="paste($event)" type="text"
                                    inputmode="numeric" maxlength="1" autocapitalize="off" spellcheck="false"
                                    :autocomplete="i === 0 ? 'one-time-code' : 'off'"
                                    :aria-label="{{ json_encode(__('auth.otp.digit_aria', ['number' => '__N__'])) }}.replace('__N__', i + 1)"
                                    class="w-full h-12 sm:h-14 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-center text-xl sm:text-2xl font-mono font-bold text-slate-900 dark:text-white transition-all outline-none">
                            </template>
                        </div>
                        <div class="flex items-center justify-between mt-3 text-xs text-slate-500 dark:text-slate-400">
                            <span x-show="available()" class="inline-flex items-center gap-1.5">
                                <i data-lucide="timer" class="w-4 h-4 shrink-0 text-[#FF9500] dark:text-[#FF9F0A]"></i>
                                <span>{{ __('auth.otp.valid_for') }} <span class="font-mono font-bold text-[#FF9500] dark:text-[#FF9F0A]" x-text="clockLabel()"></span> {{ __('auth.otp.remaining') }}</span>
                            </span>
                            <span x-show="!available()" class="text-[#FF3B30] dark:text-[#FF453A] font-semibold">
                                {{ __('auth.otp.expired') }}
                            </span>
                        </div>
                    </div>

                    <button type="submit" :disabled="submitting" :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm sm:text-[15px] shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99] min-h-[50px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25 cursor-pointer">
                        <span>{{ __('auth.register_otp.submit_btn') }}</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <form method="POST" action="{{ route('register.verify.resend') }}" class="mt-4 text-center">
                    @csrf
                    <button type="submit" :disabled="cooldown() > 0"
                        :class="cooldown() > 0 ? 'opacity-40 cursor-not-allowed' : ''"
                        class="min-h-[44px] px-3 py-2 text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors inline-flex items-center justify-center gap-1.5 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="cooldown() > 0 ? 'animate-spin' : ''"></i>
                        <span x-show="cooldown() <= 0">{{ __('auth.otp.resend_btn') }}</span>
                        <span x-show="cooldown() > 0" x-text="{{ json_encode(__('auth.otp.resend_countdown', ['seconds' => '__S__'])) }}.replace('__S__', cooldown())"></span>
                    </button>
                </form>

                <!-- Ganti Nomor WhatsApp Form -->
                <div class="mt-5 pt-4 border-t border-slate-200/80 dark:border-white/10">
                    <form method="POST" action="{{ route('register.verify.change') }}" x-data="{ open: false }" class="text-center">
                        @csrf
                        <button type="button" @click="open = !open"
                            class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors cursor-pointer min-h-[40px] px-2 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                            <span>{{ __('auth.register_otp.change_number_toggle') }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="open" x-transition
                            class="mt-3 text-left p-4 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-2.5"
                            style="display: none;">
                            <label for="new-phone" class="block text-xs font-semibold text-slate-700 dark:text-slate-200">
                                {{ __('auth.register_otp.new_number_label') }}
                            </label>
                            <div class="flex gap-2">
                                <input type="tel" id="new-phone" name="phone" inputmode="tel" required maxlength="20"
                                    placeholder="{{ __('auth.register.phone_placeholder') }}"
                                    class="min-w-0 flex-1 px-3.5 py-2.5 bg-white dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none">
                                <button type="submit"
                                    class="shrink-0 min-h-[42px] px-4 py-2 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-semibold text-xs sm:text-sm shadow-md shadow-[#007AFF]/20 transition-all flex items-center gap-1 active:scale-[0.98] cursor-pointer">
                                    <span>{{ __('auth.register_otp.send_new_otp_btn') }}</span>
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                {{ __('auth.register_otp.change_number_notice') }}
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/otp-widget.js') }}"></script>
@endsection
