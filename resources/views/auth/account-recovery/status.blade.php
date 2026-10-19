@extends('layouts.public_marketing', [
    'title' => __('auth.recovery.status_meta_title'),
    'noindex' => true,
])

@section('content')
    <div class="relative min-h-[calc(100vh-5rem)] flex items-center justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <!-- Ambient Apple Background Glow -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none -z-10">
            <div
                class="absolute -top-32 left-1/2 -translate-x-1/2 w-[640px] h-[340px] bg-gradient-to-tr from-[#007AFF]/15 to-[#5856D6]/10 dark:from-[#0A84FF]/10 dark:to-[#5E5CE6]/10 rounded-full blur-[100px] opacity-75">
            </div>
            <div
                class="absolute bottom-10 left-1/2 -translate-x-1/3 w-[500px] h-[300px] bg-[#34C759]/10 dark:bg-[#30D158]/5 rounded-full blur-[90px] opacity-50">
            </div>
        </div>

        <div class="w-full max-w-xl mx-auto space-y-6">
            <!-- Official COOCA Branding & Header -->
            <div class="text-center space-y-2 mb-2">
                @include('auth.partials.brand-logo', ['class' => 'mb-3'])
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ __('auth.recovery.status_heading') }}</h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">{{ __('auth.recovery.status_subheading') }}</p>
            </div>

            @if ($recovery)
                <!-- Ticket Detail Card (Bento Grouped Card) -->
                <div class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40 space-y-6 transition-all"
                    x-data="{ copied: false }">
                    <!-- Ticket Hero Bar -->
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                        <div>
                            <span
                                class="text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider block">{{ __('auth.recovery.ticket_number_label') }}</span>
                            <div class="flex items-center gap-2.5 mt-0.5">
                                <span
                                    class="text-lg sm:text-xl font-mono font-bold text-black dark:text-white tracking-wide">{{ $recovery->ticket_number }}</span>
                                <button type="button"
                                    @click="navigator.clipboard.writeText('{{ $recovery->ticket_number }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="px-2.5 py-1 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black/70 dark:text-white/70 text-[11px] font-medium inline-flex items-center gap-1 transition-all cursor-pointer"
                                    title="{{ __('auth.recovery.copy_btn') }}">
                                    <i :data-lucide="copied ? 'check' : 'copy'" class="w-3.5 h-3.5"></i>
                                    <span x-text="copied ? {{ json_encode(__('auth.recovery.copied_btn')) }} : {{ json_encode(__('auth.recovery.copy_btn')) }}"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Status Badge (Apple Pill) -->
                        <div>
                            @if ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_PENDING)
                                <span
                                    class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#FF9500]/10 border border-[#FF9500]/25 text-[#FF9500] dark:text-[#FF9F0A] text-xs font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-[#FF9500] animate-ping"></span>
                                    <span>{{ __('auth.recovery.status_pending_badge') }}</span>
                                </span>
                            @elseif ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#34C759]/10 border border-[#34C759]/25 text-[#34C759] dark:text-[#30D158] text-xs font-semibold">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-[#34C759] dark:text-[#30D158]"></i>
                                    <span>{{ __('auth.recovery.status_approved_badge') }}</span>
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs font-semibold">
                                    <i data-lucide="x-circle" class="w-4 h-4 text-[#FF3B30] dark:text-[#FF453A]"></i>
                                    <span>{{ __('auth.recovery.status_rejected_badge') }}</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Status Announcement Banner -->
                    @if ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_PENDING)
                        <div
                            class="p-4 rounded-[20px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-black dark:text-white text-xs sm:text-sm flex items-start gap-3.5">
                            <div
                                class="w-9 h-9 rounded-[14px] bg-[#FF9500]/20 flex items-center justify-center shrink-0 mt-0.5 text-[#FF9500] dark:text-[#FF9F0A]">
                                <i data-lucide="hourglass" class="w-5 h-5"></i>
                            </div>
                            <div class="space-y-1">
                                <strong class="font-bold block text-sm sm:text-base text-black dark:text-white">{{ __('auth.recovery.status_pending_title') }}</strong>
                                <p class="text-xs sm:text-sm leading-relaxed text-black/70 dark:text-white/70">
                                    {{ __('auth.recovery.status_pending_desc') }}
                                </p>
                            </div>
                        </div>
                    @elseif ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                        <div
                            class="p-4 rounded-[20px] bg-[#34C759]/10 border border-[#34C759]/25 text-black dark:text-white text-xs sm:text-sm flex items-start gap-3.5">
                            <div
                                class="w-9 h-9 rounded-[14px] bg-[#34C759]/20 flex items-center justify-center shrink-0 mt-0.5 text-[#34C759] dark:text-[#30D158]">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </div>
                            <div class="space-y-1">
                                <strong class="font-bold block text-sm sm:text-base text-black dark:text-white">{{ __('auth.recovery.status_approved_title') }}</strong>
                                <p class="text-xs sm:text-sm leading-relaxed text-black/70 dark:text-white/70">
                                    {{ __('auth.recovery.status_approved_desc') }}
                                </p>
                            </div>
                        </div>
                    @else
                        <div
                            class="p-4 rounded-[20px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-black dark:text-white text-xs sm:text-sm flex items-start gap-3.5">
                            <div
                                class="w-9 h-9 rounded-[14px] bg-[#FF3B30]/20 flex items-center justify-center shrink-0 mt-0.5 text-[#FF3B30] dark:text-[#FF453A]">
                                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                            </div>
                            <div class="space-y-1">
                                <strong class="font-bold block text-sm sm:text-base text-black dark:text-white">{{ __('auth.recovery.status_rejected_title') }}</strong>
                                <p class="text-xs sm:text-sm leading-relaxed text-black/70 dark:text-white/70">
                                    {{ __('auth.recovery.admin_notes_prefix') }}<span
                                        class="font-semibold italic text-black dark:text-white">"{{ $recovery->rejection_reason ?? __('auth.recovery.default_rejection_reason') }}"</span>
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Detail Bento Inset Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                        <div
                            class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45 block mb-1">{{ __('auth.recovery.applicant_label') }}</span>
                            <span
                                class="font-semibold text-black dark:text-white text-sm">{{ $recovery->applicant_name }}</span>
                        </div>

                        <div
                            class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45 block mb-1">{{ __('auth.recovery.business_label') }}</span>
                            <span
                                class="font-semibold text-black dark:text-white text-sm">{{ $recovery->business_name }}</span>
                        </div>

                        <div
                            class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45 block mb-1">{{ __('auth.recovery.issue_type_label') }}</span>
                            <span
                                class="font-semibold text-[#007AFF] dark:text-[#0A84FF] text-sm">{{ $recovery->getIssueTypeLabel() }}</span>
                        </div>

                        <div
                            class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45 block mb-1">{{ __('auth.recovery.submission_time_label') }}</span>
                            <span
                                class="font-semibold text-black dark:text-white text-sm">{{ $recovery->created_at->translatedFormat('d M Y, H:i') }}
                                WIB</span>
                        </div>

                        <div
                            class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45 block mb-1">{{ __('auth.recovery.new_email_replacement_label') }}</span>
                            <span class="font-mono font-semibold text-black dark:text-white text-sm">
                                {{ $recovery->getMaskedNewEmail() }}
                            </span>
                        </div>

                        <div
                            class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45 block mb-1">{{ __('auth.recovery.new_phone_replacement_label') }}</span>
                            <span class="font-mono font-semibold text-black dark:text-white text-sm">
                                {{ $recovery->getMaskedNewPhone() }}
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-2 flex flex-col sm:flex-row gap-3">
                        @if ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_APPROVED)
                            <a href="{{ route('login') }}"
                                class="flex-1 min-h-[50px] py-3.5 px-5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm text-center shadow-lg shadow-[#007AFF]/25 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                                <span>{{ __('auth.recovery.login_now_btn') }}</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        @elseif ($recovery->status === \App\Models\AccountRecoveryRequest::STATUS_REJECTED)
                            <a href="{{ route('account-recovery.create') }}"
                                class="flex-1 min-h-[50px] py-3.5 px-5 rounded-[16px] bg-[#FF3B30] hover:bg-[#E0352B] text-white font-semibold text-sm text-center shadow-lg shadow-[#FF3B30]/25 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                <span>{{ __('auth.recovery.reapply_btn') }}</span>
                            </a>
                        @else
                            <button type="button" onclick="location.reload()"
                                class="flex-1 min-h-[50px] py-3.5 px-5 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white font-semibold text-sm text-center border border-black/5 dark:border-white/10 transition-all flex items-center justify-center gap-2 active:scale-[0.98] cursor-pointer">
                                <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                                <span>{{ __('auth.recovery.refresh_status_btn') }}</span>
                            </button>
                        @endif

                        <a href="{{ route('landing') }}"
                            class="min-h-[50px] py-3.5 px-5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.1] border border-black/5 dark:border-white/10 text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white font-semibold text-sm text-center transition-all flex items-center justify-center">
                            {{ __('auth.recovery.home_btn') }}
                        </a>
                    </div>
                </div>
            @else
                <!-- Search Form Card (Apple HIG Inset Grouped) -->
                <div
                    class="glass-card bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] rounded-[28px] p-6 sm:p-8 shadow-2xl shadow-black/5 dark:shadow-black/50 transition-all">
                    @if ($errors->any())
                        <div
                            class="mb-5 p-4 rounded-[18px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs sm:text-sm flex items-center gap-2.5 animate-shake">
                            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('account-recovery.check') }}" class="space-y-5">
                        <div>
                            <label for="ticket"
                                class="block text-xs sm:text-sm font-semibold text-black/80 dark:text-white/85 uppercase tracking-wider mb-2">
                                {{ __('auth.recovery.search_ticket_label') }} <span class="text-[#FF3B30]">*</span>
                            </label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                    <i data-lucide="ticket" class="w-5 h-5"></i>
                                </div>
                                <input type="text" name="ticket" id="ticket" required
                                    placeholder="{{ __('auth.recovery.search_ticket_placeholder') }}"
                                    class="w-full pl-11 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 rounded-[16px] text-black dark:text-white font-mono placeholder:text-black/35 dark:placeholder:text-white/35 text-[16px] sm:text-sm uppercase transition-all focus:outline-none focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15">
                            </div>
                            <p class="mt-1.5 text-xs text-black/50 dark:text-white/50">{{ __('auth.recovery.search_ticket_hint') }}</p>
                        </div>

                        <button type="submit"
                            class="w-full min-h-[50px] py-3.5 px-5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-sm sm:text-base shadow-lg shadow-[#007AFF]/25 transition-all flex items-center justify-center gap-2 active:scale-[0.98] cursor-pointer">
                            <i data-lucide="search" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                            <span>{{ __('auth.recovery.search_ticket_btn') }}</span>
                        </button>
                    </form>

                    <div
                        class="mt-6 pt-5 border-t border-black/[0.06] dark:border-white/[0.08] text-center text-xs sm:text-sm text-black/60 dark:text-white/60">
                        {{ __('auth.recovery.not_applied_yet') }}
                        <a href="{{ route('account-recovery.create') }}"
                            class="font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors ml-1">
                            {{ __('auth.recovery.apply_new_link') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
