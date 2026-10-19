@extends('layouts.public_marketing', ['title' => __('auth.otp.meta_title'), 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-14rem)] flex flex-col justify-center py-12 sm:py-16 px-4 sm:px-6 lg:px-8"
        x-data="coocaOtp.boxes({{ (int) ($expiresAt ?? 0) }}, {{ (int) ($resendIn ?? 0) }})">
        <div class="w-full max-w-md mx-auto">

            <!-- Official COOCA Branding & Header -->
            <div class="text-center mb-8">
                @include('auth.partials.brand-logo', ['class' => 'mb-5'])
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ __('auth.otp.heading') }}
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                    {!! __('auth.otp.subheading', ['phone' => '<span class="font-mono font-bold text-slate-900 dark:text-white px-2 py-0.5 rounded bg-slate-100 dark:bg-white/10">' . e($phone) . '</span>']) !!}
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

                @if (isset($activeRecovery) && $activeRecovery && $activeRecovery->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                    <div class="mb-5 p-3.5 rounded-xl bg-[#34C759]/10 border border-[#34C759]/25 text-slate-900 dark:text-white text-xs sm:text-sm flex items-start gap-3">
                        <i data-lucide="check-circle-2" class="w-4 h-4 mt-0.5 text-[#34C759] dark:text-[#30D158] shrink-0"></i>
                        <div>
                            <strong class="font-bold block text-[#34C759] dark:text-[#30D158]">{{ __('auth.otp.recovery_approved_title') }}</strong>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                                {!! __('auth.otp.recovery_approved_desc', ['phone' => '<span class="font-mono font-semibold">' . e($activeRecovery->new_phone) . '</span>']) !!}
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

                @if ($deliveryError && !$errors->any())
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

                <form method="POST" action="{{ route('auth.otp.verify') }}" class="space-y-5" x-data="{ submitting: false }"
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
                        <span>{{ __('auth.otp.submit_btn') }}</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <form method="POST" action="{{ route('auth.otp.resend') }}" class="mt-4 text-center">
                    @csrf
                    <button type="submit" :disabled="cooldown() > 0"
                        :class="cooldown() > 0 ? 'opacity-40 cursor-not-allowed' : ''"
                        class="min-h-[44px] px-3 py-2 text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors inline-flex items-center justify-center gap-1.5 cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="cooldown() > 0 ? 'animate-spin' : ''"></i>
                        <span x-show="cooldown() <= 0">{{ __('auth.otp.resend_btn') }}</span>
                        <span x-show="cooldown() > 0" x-text="{{ json_encode(__('auth.otp.resend_countdown', ['seconds' => '__S__'])) }}.replace('__S__', cooldown())"></span>
                    </button>
                </form>

                <!-- Status / Pengajuan Pemulihan Akses Akun -->
                @if (isset($activeRecovery) && $activeRecovery)
                    @if ($activeRecovery->status === \App\Models\AccountRecoveryRequest::STATUS_PENDING)
                        <div class="mt-5 p-4 rounded-xl bg-[#007AFF]/10 border border-[#007AFF]/20 text-xs text-left">
                            <div class="flex items-start gap-3">
                                <i data-lucide="clock" class="w-5 h-5 text-[#007AFF] dark:text-[#0A84FF] shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm block">{{ __('auth.otp.recovery_pending_title') }}</span>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 mb-2 leading-relaxed">
                                        {!! __('auth.otp.recovery_pending_desc', ['ticket' => '<strong class="text-[#007AFF] dark:text-[#0A84FF] font-mono">' . e($activeRecovery->ticket_number) . '</strong>', 'phone' => '<span class="font-mono font-semibold text-slate-900 dark:text-white">' . e($activeRecovery->new_phone) . '</span>']) !!}
                                    </p>
                                    <a href="{{ route('account-recovery.status', $activeRecovery->ticket_number) }}"
                                        class="inline-flex items-center gap-1 font-bold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors text-xs">
                                        <span>{{ __('auth.otp.check_ticket_status') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @elseif ($activeRecovery->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                        <div class="mt-5 p-4 rounded-xl bg-[#34C759]/10 border border-[#34C759]/20 text-xs text-left">
                            <div class="flex items-start gap-3">
                                <i data-lucide="check-circle" class="w-5 h-5 text-[#34C759] dark:text-[#30D158] shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm block">{{ __('auth.otp.recovery_approved_title') }}</span>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                                        {!! __('auth.otp.recovery_approved_banner', ['phone' => '<strong class="text-[#34C759] dark:text-[#30D158] font-mono">' . e($activeRecovery->new_phone) . '</strong>']) !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-5 p-4 rounded-xl bg-[#FF9500]/10 border border-[#FF9500]/20 text-xs text-left">
                            <div class="flex items-start gap-3">
                                <i data-lucide="help-circle" class="w-5 h-5 text-[#FF9500] dark:text-[#FF9F0A] shrink-0 mt-0.5"></i>
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm block">{{ __('auth.otp.lost_device_title') }}</span>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 mb-2 leading-relaxed">
                                        {!! __('auth.otp.lost_device_desc') !!}
                                    </p>
                                    <a href="{{ route('account-recovery.create') }}"
                                        class="inline-flex items-center gap-1 font-bold text-[#FF9500] dark:text-[#FF9F0A] hover:underline transition-colors text-xs">
                                        <span>{{ __('auth.otp.request_recovery_btn') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="mt-5 p-4 rounded-xl bg-[#FF9500]/10 border border-[#FF9500]/20 text-xs text-left">
                        <div class="flex items-start gap-3">
                            <i data-lucide="help-circle" class="w-5 h-5 text-[#FF9500] dark:text-[#FF9F0A] shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm block">{{ __('auth.otp.lost_device_title') }}</span>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 mb-2 leading-relaxed">
                                    {!! __('auth.otp.lost_device_desc') !!}
                                </p>
                                <a href="{{ route('account-recovery.create') }}"
                                    class="inline-flex items-center gap-1 font-bold text-[#FF9500] dark:text-[#FF9F0A] hover:underline transition-colors text-xs">
                                    <span>{{ __('auth.otp.request_recovery_btn') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="mt-5 pt-4 border-t border-slate-200/80 dark:border-white/10 text-center">
                    @csrf
                    <button type="submit"
                        class="min-h-[40px] px-3 text-xs sm:text-sm text-slate-500 dark:text-slate-400 hover:text-[#FF3B30] dark:hover:text-[#FF453A] transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/20 rounded">
                        {{ __('auth.otp.logout_btn') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/otp-widget.js') }}"></script>
@endsection
