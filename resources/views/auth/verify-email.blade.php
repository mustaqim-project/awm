@extends('layouts.public_marketing', ['title' => __('auth.verify_email.meta_title'), 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-14rem)] flex flex-col justify-center py-12 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md mx-auto">

            <!-- Official COOCA Branding & Header -->
            <div class="text-center mb-8">
                @include('auth.partials.brand-logo', ['class' => 'mb-5'])
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ __('auth.verify_email.heading') }}
                </h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                    {{ __('auth.verify_email.subheading') }}
                </p>
            </div>

            <!-- Structured Auth Card -->
            <div class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-colors">

                <div class="p-4 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 text-center space-y-2 mb-5">
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('auth.verify_email.sent_to') }}</p>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-mono text-xs sm:text-sm font-semibold max-w-full truncate">
                        <i data-lucide="mail" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">{{ auth()->user()->email }}</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed pt-1">
                        {{ __('auth.verify_email.instructions') }}
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-5 p-4 rounded-xl bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] dark:text-[#30D158] text-xs sm:text-sm flex items-start gap-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0 mt-0.5"></i>
                        <div class="flex-1 leading-relaxed">
                            <span class="font-semibold block">{{ __('auth.verify_email.link_sent_title') }}</span>
                            <span class="text-xs sm:text-sm opacity-90">{{ session('status') === 'verification-link-sent' ? __('auth.verify_email.link_sent_desc') : session('status') }}</span>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
                    @csrf
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm sm:text-[15px] shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99] min-h-[50px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25 cursor-pointer">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>{{ __('auth.verify_email.resend_btn') }}</span>
                    </button>
                </form>

                @if (app()->environment('local', 'testing'))
                    <!-- Instant Bypass Button for Review / Testing -->
                    <div class="mt-3">
                        <a href="{{ route('verification.notice', ['bypass' => 1]) }}"
                            class="w-full min-h-[44px] py-2.5 px-4 rounded-xl bg-[#34C759]/10 hover:bg-[#34C759]/20 text-[#34C759] dark:text-[#30D158] font-semibold text-xs sm:text-sm border border-[#34C759]/30 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                            <i data-lucide="check-check" class="w-4 h-4"></i>
                            <span>{{ __('auth.verify_email.bypass_btn') }}</span>
                        </a>
                    </div>
                @endif

                <!-- Emergency Recovery / Inaccessible Email Bento Box -->
                <div class="mt-5 p-4 rounded-xl bg-[#FF9500]/10 border border-[#FF9500]/25 text-xs text-left">
                    <div class="flex items-start gap-3">
                        <i data-lucide="help-circle" class="w-5 h-5 text-[#FF9500] dark:text-[#FF9F0A] shrink-0 mt-0.5"></i>
                        <div>
                            <span class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm block">{{ __('auth.verify_email.trouble_title') }}</span>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 mb-2 leading-relaxed">
                                {{ __('auth.verify_email.trouble_desc') }}
                            </p>
                            <a href="{{ route('account-recovery.create') }}"
                                class="inline-flex items-center gap-1.5 font-bold text-[#FF9500] dark:text-[#FF9F0A] hover:underline transition-colors text-xs py-0.5">
                                <span>{{ __('auth.verify_email.request_recovery_link') }}</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-5 mt-5 border-t border-slate-200/80 dark:border-white/10 text-xs">
                    @if (auth()->user()->hasVerifiedEmail())
                        <a href="{{ route('dashboard') }}"
                            class="text-[#007AFF] dark:text-[#0A84FF] hover:underline font-semibold flex items-center gap-1 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                            <span>{{ __('auth.verify_email.proceed_dashboard') }}</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    @else
                        <span class="text-slate-500 dark:text-slate-400">{{ __('auth.verify_email.waiting_confirmation') }}</span>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-[#FF3B30] dark:text-[#FF453A] hover:underline font-semibold flex items-center gap-1.5 py-1 focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/20 rounded cursor-pointer">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span>{{ __('auth.verify_email.logout_btn') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
