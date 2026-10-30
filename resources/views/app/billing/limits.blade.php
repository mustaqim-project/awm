@extends('layouts.app', [
    'title' => __('billing.meta_title_limits'),
    'headerTitle' => __('billing.title'),
    'headerSubtitle' => __('billing.subtitle'),
])

@section('content')
    <div class="space-y-6 pb-28 lg:pb-10" x-data="storageLimitsManager()">

        @php
            $tier = $usage['tier'] ?? 'free';
            $tierBadge = match($tier) {
                'prestige' => ['label' => __('billing.tier_prestige_label'), 'bg' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-400 border-purple-200/90 dark:border-purple-800/90', 'dot' => 'bg-purple-500'],
                'premium' => ['label' => __('billing.tier_premium_label'), 'bg' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border-indigo-200/90 dark:border-indigo-800/90', 'dot' => 'bg-indigo-500'],
                'standard' => ['label' => __('billing.tier_standard_label'), 'bg' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90', 'dot' => 'bg-emerald-500'],
                default => ['label' => __('billing.tier_free_label'), 'bg' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700', 'dot' => 'bg-slate-400'],
            };

            $providerNames = [
                'openai' => 'OpenAI',
                'gemini' => 'Google Gemini',
                'anthropic' => 'Anthropic Claude',
                'openrouter' => 'OpenRouter',
            ];
            $hasActiveAi = !empty($activeAiConfig) && !empty($activeAiConfig->api_key);
            $activeProviderLabel = $hasActiveAi ? ($providerNames[$activeAiConfig->provider] ?? ucfirst($activeAiConfig->provider)) : null;
        @endphp

        <!-- Standard 3-Row Module Header Bento Apple HIG -->
        <x-module-header
            :title="__('billing.title')"
            :subtitle="__('billing.subtitle')"
            :breadcrumbs="[
                ['label' => __('billing.breadcrumb_billing'), 'route' => 'billing.limits'],
                ['label' => __('billing.breadcrumb_limits')],
            ]"
            :badge="$tierBadge['label']"
        >
            <x-slot:actions>
                <a href="{{ route('billing.history') }}"
                    class="min-h-[44px] h-11 sm:h-10 px-3.5 sm:px-4 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] active:scale-[0.98] transition flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>{{ __('billing.action_history') }}</span>
                </a>
                @if (\App\Support\Context::hasPermission('billing.manage'))
                    <a href="{{ route('billing.checkout') }}"
                        class="min-h-[44px] h-11 sm:h-10 px-3.5 sm:px-4 rounded-[12px] text-xs font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] shadow-sm active:scale-[0.98] transition flex items-center gap-2">
                        <i data-lucide="{{ $usage['is_core'] ? 'refresh-cw' : 'sparkles' }}" class="w-4 h-4"></i>
                        <span>{{ $usage['is_core'] ? __('billing.action_renew_change') : __('billing.action_choose_plan') }}</span>
                    </a>
                @endif
            </x-slot:actions>
        </x-module-header>

        <!-- Submodule Navigation Tabs -->
        <x-module-tabs module="billing" class="mt-2 mb-2" />

        @if(!empty($usage['is_past_due']))
            <!-- Amber Bento Banner: Grace Period -->
            <div class="p-4 sm:p-5 rounded-[20px] bg-amber-500/10 border border-amber-500/30 text-amber-900 dark:text-amber-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="p-2 rounded-[12px] bg-amber-500/20 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <div class="space-y-0.5 text-xs">
                        <p class="font-extrabold text-sm text-amber-900 dark:text-amber-100">{{ __('billing.grace_period_title') }}</p>
                        <p class="text-amber-800 dark:text-amber-300">{{ __('billing.grace_period_desc') }}</p>
                    </div>
                </div>
                <a href="{{ route('billing.checkout') }}" class="min-h-[44px] px-4 py-2 rounded-[12px] text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 shadow-sm shrink-0 flex items-center gap-1.5">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>{{ __('billing.settle_bill') }}</span>
                </a>
            </div>
        @endif

        <!-- 2. 4 Command Pillars KPI Cards (Bento Metric Grid) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
            <!-- Pillar 1: Status Paket -->
            <div
                class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('billing.pillar_plan_status') }}</span>
                        <div
                            class="w-9 h-9 rounded-[12px] bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/80 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div
                        class="text-lg sm:text-xl md:text-2xl font-black tracking-tight text-slate-900 dark:text-white truncate" title="{{ $tierBadge['label'] }}">
                        {{ $tierBadge['label'] }}
                    </div>
                </div>
                <div
                    class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between gap-2 flex-wrap">
                    <span class="shrink-0">{{ __('billing.pillar_license') }}</span>
                    <span
                        class="font-bold text-emerald-600 dark:text-emerald-400 text-right shrink-0">{{ $usage['is_core'] ? __('billing.status_active') : __('billing.status_standard') }}</span>
                </div>
            </div>

            <!-- Pillar 2: Masa Aktif -->
            <div
                class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('billing.pillar_active_period') }}</span>
                        <div
                            class="w-9 h-9 rounded-[12px] bg-teal-50 dark:bg-teal-950/60 border border-teal-200/80 dark:border-teal-800/80 flex items-center justify-center text-teal-600 dark:text-teal-400">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div
                        class="text-lg sm:text-xl md:text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-white truncate tabular-nums">
                        @if (!empty($usage['ends_at']))
                            {{ $usage['ends_at'] }}
                        @else
                            {{ __('billing.forever') }}
                        @endif
                    </div>
                </div>
                <div
                    class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between gap-2 flex-wrap">
                    <span class="shrink-0">{{ __('billing.pillar_renewal') }}</span>
                    <span
                        class="font-bold text-slate-700 dark:text-slate-300 text-right shrink-0" title="{{ $usage['is_core'] ? __('billing.can_renew') : __('billing.available_upgrade') }}">{{ $usage['is_core'] ? __('billing.can_renew') : __('billing.available_upgrade') }}</span>
                </div>
            </div>

            <!-- Pillar 3: Kasir POS Bulan Ini -->
            @php
                $pos = $usage['pos_this_month'] ?? [];
                $posUsed = (int) ($pos['used'] ?? 0);
                $posLimit = $usage['is_core'] ? __('billing.unlimited') : (int) ($pos['limit'] ?? 100);
            @endphp
            <div
                class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('billing.pillar_pos_cashier') }}</span>
                        <div
                            class="w-9 h-9 rounded-[12px] bg-blue-50 dark:bg-blue-950/60 border border-blue-200/80 dark:border-blue-800/80 flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div
                        class="text-lg sm:text-xl md:text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-white truncate tabular-nums">
                        {{ number_format($posUsed, 0, ',', '.') }} <span class="text-xs font-normal text-slate-500">/
                            {{ $posLimit }}</span>
                    </div>
                </div>
                <div
                    class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between gap-2 flex-wrap">
                    <span class="shrink-0">{{ __('billing.reset_date_1') }}</span>
                    @if (!empty($usage['pos_in_emergency_buffer']))
                        <span class="font-bold text-amber-600 dark:text-amber-400 text-right shrink-0 flex items-center gap-1 font-mono">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-amber-500"></i>
                            <span>Buffer Darurat (Sisa: {{ $usage['pos_remaining_buffer'] }} Trx)</span>
                        </span>
                    @else
                        <span
                            class="font-bold text-emerald-600 dark:text-emerald-400 text-right shrink-0">{{ $usage['is_core'] ? __('billing.no_quota') : ($pos['is_reached'] ?? false ? __('billing.limit_reached') : __('billing.available')) }}</span>
                    @endif
                </div>
            </div>

            <!-- Pillar 4: AI Engine (BYOAI) & Storage -->
            <div
                class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('billing.pillar_ai_storage') }}</span>
                        <div
                            class="w-9 h-9 rounded-[12px] {{ $hasActiveAi ? 'bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/80 text-emerald-600 dark:text-emerald-400' : 'bg-amber-50 dark:bg-amber-950/60 border border-amber-200/80 dark:border-amber-800/80 text-amber-600 dark:text-amber-400' }} flex items-center justify-center">
                            <i data-lucide="bot" class="w-4 h-4"></i>
                        </div>
                    </div>
                    @if ($hasActiveAi)
                        <div
                            class="text-lg sm:text-xl md:text-2xl font-black font-mono tracking-tight text-emerald-600 dark:text-emerald-400 truncate">
                            {{ __('billing.ai_byoai_active') }}
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5 truncate flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300 truncate">{{ $activeProviderLabel }}</span>
                            <span class="text-slate-400 dark:text-slate-500 shrink-0">· Unlimited</span>
                        </div>
                    @else
                        <div
                            class="text-base sm:text-lg font-bold tracking-tight text-slate-800 dark:text-slate-200 truncate" title="{{ __('billing.ai_byoai_unconfigured') }}">
                            {{ __('billing.ai_byoai_unconfigured') }}
                        </div>
                        <a href="{{ route('cooca-ai.providers') }}"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline mt-0.5">
                            <span>{{ __('billing.ai_byoai_setup_key') }}</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    @endif
                </div>
                <div
                    class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between gap-2 flex-wrap">
                    <span class="shrink-0">{{ __('billing.cloud_storage') }}</span>
                    <span
                        class="font-bold text-slate-700 dark:text-slate-300 font-mono tabular-nums text-right shrink-0">{{ number_format($usage['storage']['used_mb'] ?? 0, 0) }}
                        MB / {{ $usage['storage']['limit_gb'] ?? 1 }} GB</span>
                </div>
            </div>
        </div>

        <!-- 3. Active Plan Showcase or Upgrade Conversion Banner -->
        @if ($usage['is_core'])
            <!-- Active Core Plan Executive Card -->
            <section aria-labelledby="active-plan-heading"
                class="rounded-[20px] p-6 sm:p-7 border border-emerald-200 dark:border-emerald-500/30 bg-gradient-to-br from-emerald-50/90 via-white to-teal-50/50 dark:from-emerald-950/40 dark:via-slate-900 dark:to-slate-950 shadow-xs relative overflow-hidden backdrop-blur-xl">
                <div class="absolute -right-16 -top-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"
                    aria-hidden="true"></div>

                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 relative z-10">
                    <div class="space-y-3 max-w-2xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="rounded-[10px] px-2.5 py-1 text-xs font-bold uppercase tracking-wider bg-emerald-100/80 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30 flex items-center gap-1.5 shadow-2xs">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"
                                    aria-hidden="true"></i>
                                <span>{{ __('billing.active_plan_notice') }}</span>
                            </span>
                            @if (!empty($usage['ends_at']))
                                <span class="text-xs text-slate-600 dark:text-slate-400 font-mono">
                                    {{ __('billing.active_until') }} <strong
                                        class="text-slate-900 dark:text-white font-semibold">{{ $usage['ends_at'] }}</strong>
                                </span>
                            @endif
                        </div>

                        <h2 id="active-plan-heading"
                            class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">
                            {!! __('billing.business_running_on', ['plan' => '<span class="text-emerald-600 dark:text-emerald-400">' . e($usage['plan_label']) . '</span>']) !!}
                        </h2>

                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            {{ __('billing.active_plan_unlimited_desc') }}
                        </p>

                        <div
                            class="flex flex-wrap items-center gap-x-5 gap-y-2 pt-1 text-xs text-slate-700 dark:text-slate-300">
                            <span class="flex items-center gap-1.5 font-medium text-emerald-700 dark:text-emerald-300">
                                <i data-lucide="check-circle-2"
                                    class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"
                                    aria-hidden="true"></i>
                                <span>{{ __('billing.feature_multi_warehouse') }}</span>
                            </span>
                            <span class="flex items-center gap-1.5 font-medium text-emerald-700 dark:text-emerald-300">
                                <i data-lucide="check-circle-2"
                                    class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"
                                    aria-hidden="true"></i>
                                <span>{{ __('billing.feature_excel_import_export') }}</span>
                            </span>
                            <span class="flex items-center gap-1.5 font-medium text-emerald-700 dark:text-emerald-300">
                                <i data-lucide="check-circle-2"
                                    class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"
                                    aria-hidden="true"></i>
                                <span>{{ __('billing.feature_unlimited_pos') }}</span>
                            </span>
                            <span class="flex items-center gap-1.5 font-medium text-emerald-700 dark:text-emerald-300">
                                <i data-lucide="check-circle-2"
                                    class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"
                                    aria-hidden="true"></i>
                                <span>{{ __('billing.feature_wa_bot_receipt') }}</span>
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto shrink-0">
                        @if (\App\Support\Context::hasPermission('billing.manage'))
                            <a href="{{ route('billing.checkout') }}"
                                class="px-5 py-2.5 rounded-[12px] text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2 focus-visible:ring-2 focus-visible:ring-emerald-500">
                                <i data-lucide="refresh-cw" class="w-4 h-4" aria-hidden="true"></i>
                                <span>{{ __('billing.action_renew_active_period') }}</span>
                            </a>
                        @endif
                        <a href="{{ route('billing.history') }}"
                            class="px-4 py-2.5 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 active:scale-[0.98] transition cursor-pointer shadow-2xs flex items-center justify-center gap-2 focus-visible:ring-2 focus-visible:ring-emerald-500">
                            <i data-lucide="receipt" class="w-4 h-4 text-slate-400" aria-hidden="true"></i>
                            <span>{{ __('billing.action_view_invoice') }}</span>
                        </a>
                    </div>
                </div>
            </section>
        @endif

        <!-- 3. Official 4-Tier Subscription Plans Showcase -->
        <section aria-labelledby="pricing-tiers-heading" class="space-y-4" x-data="{
            pricingCycle: 'monthly',
            tierPrices: @js($tierPrices),
            annualBadge: '{{ $annualDiscountBadge }}',
            formatRupiah(num) {
                return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
            },
            formatApprox(annualNum) {
                return Number(Math.round((annualNum || 0) / 12)).toLocaleString('id-ID');
            },
            approxText(annualNum) {
                const template = @js(__('billing.plan_per_year_approx', ['price' => ':price']));
                return template.replace(':price', this.formatApprox(annualNum));
            }
        }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
                <div>
                    <h2 id="pricing-tiers-heading" class="text-sm sm:text-base font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" aria-hidden="true"></i>
                        <span>{{ __('billing.pricing_tiers_heading') }}</span>
                    </h2>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ __('billing.pricing_tiers_subtitle') }}
                    </p>
                </div>

                <!-- Cycle Toggle Segmented Control -->
                <div class="inline-flex p-1 rounded-[12px] bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 self-start sm:self-auto">
                    <button type="button" @click="pricingCycle = 'monthly'"
                        :class="pricingCycle === 'monthly' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3 py-1.5 rounded-[9px] text-xs transition-all cursor-pointer">
                        {{ __('billing.billing_monthly') }}
                    </button>
                    <button type="button" @click="pricingCycle = 'annual'"
                        :class="pricingCycle === 'annual' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white font-medium'"
                        class="px-3 py-1.5 rounded-[9px] text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <span>{{ __('billing.billing_annual') }}</span>
                        <span class="px-1.5 py-0.5 text-[9px] font-bold rounded-full bg-emerald-600 text-white" x-text="annualBadge">{{ $annualDiscountBadge }}</span>
                    </button>
                </div>
            </div>

            <!-- 4 Bento Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- 1. Free Solo -->
                <div class="bg-white dark:bg-slate-900 rounded-[20px] border {{ $tier === 'free' ? 'border-slate-400 dark:border-slate-600 ring-2 ring-slate-400/20' : 'border-black/[0.06] dark:border-white/[0.08]' }} p-5 shadow-xs flex flex-col justify-between relative">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="rounded-[8px] px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                {{ __('billing.tier_solo_owner') }}
                            </span>
                            @if($tier === 'free')
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ __('billing.plan_active_badge') }}</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">{{ __('billing.plan_free') }}</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ __('billing.plan_free_desc') }}</p>
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div class="text-2xl font-black font-mono tabular-nums text-slate-900 dark:text-white">Rp 0</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ __('billing.plan_free_forever') }}</div>
                        </div>

                        <!-- Ringkasan Kapasitas Inti -->
                        <div class="grid grid-cols-3 gap-1 py-2 px-2.5 rounded-[12px] bg-slate-50 dark:bg-slate-800/50 text-[10.5px] font-mono border border-slate-200/60 dark:border-slate-700/60 text-center">
                            <div>
                                <div class="text-slate-400 text-[9px] uppercase">Storage</div>
                                <div class="font-bold text-slate-800 dark:text-slate-200">1 GB</div>
                            </div>
                            <div>
                                <div class="text-slate-400 text-[9px] uppercase">Staf</div>
                                <div class="font-bold text-slate-800 dark:text-slate-200">1 Akun</div>
                            </div>
                            <div>
                                <div class="text-slate-400 text-[9px] uppercase">Lokasi</div>
                                <div class="font-bold text-slate-800 dark:text-slate-200">1 Toko</div>
                            </div>
                        </div>

                        <ul class="space-y-1.5 pt-1 text-[11px] text-slate-600 dark:text-slate-300">
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i><span>1 Bisnis Cooca</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i><span>{{ $tierLimits['free']['product'] ?? 10 }} Produk &amp; {{ $tierLimits['free']['recipe'] ?? 3 }} Resep</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i><span>{{ $tierLimits['free']['material'] ?? 10 }} Bahan Baku</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i><span>{{ $tierLimits['free']['customer'] ?? 15 }} Pelanggan &amp; {{ $tierLimits['free']['supplier'] ?? 2 }} Pemasok</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i><span>{{ $tierLimits['free']['pos'] ?? 30 }} Transaksi Kasir POS / bln</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i><span>{{ $tierLimits['free']['invoice'] ?? 3 }} Faktur &amp; {{ $tierLimits['free']['po'] ?? 3 }} PO / bln</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i><span>{{ $tierLimits['free']['whatsapp'] ?? 10 }} Notifikasi WA / bln</span></li>
                            <li class="flex items-center gap-1.5 text-rose-500/80 dark:text-rose-400/80"><i data-lucide="lock" class="w-3.5 h-3.5 text-rose-500 shrink-0"></i><span>Fitur AI &amp; Gateway MCP Terkunci</span></li>
                            <li class="flex items-center gap-1.5 text-slate-400 line-through"><i data-lucide="x" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i><span>Transfer Stok Antar-Cabang</span></li>
                            <li class="flex items-center gap-1.5 text-slate-400 line-through"><i data-lucide="x" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i><span>Kitchen Display System (KDS)</span></li>
                        </ul>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        @if($tier === 'free')
                            <div class="w-full min-h-[44px] py-2.5 rounded-[12px] text-xs font-bold text-center bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 font-mono flex items-center justify-center">
                                {{ __('billing.plan_current_active') }}
                            </div>
                        @else
                            <div class="w-full min-h-[44px] py-2.5 rounded-[12px] text-xs font-medium text-center text-slate-400 font-mono flex items-center justify-center">
                                {{ __('billing.plan_baseline_tier') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 2. Standard Plan -->
                <div class="bg-white dark:bg-slate-900 rounded-[20px] border {{ $tier === 'standard' ? 'border-emerald-500 dark:border-emerald-400 ring-2 ring-emerald-500/20' : 'border-black/[0.06] dark:border-white/[0.08]' }} p-5 shadow-xs flex flex-col justify-between relative group hover:border-emerald-500/50 transition-all">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="rounded-[8px] px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider font-mono bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                {{ __('billing.tier_umkm_pemula') }}
                            </span>
                            @if($tier === 'standard')
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ __('billing.plan_active_badge') }}</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">{{ __('billing.plan_standard') }}</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ __('billing.plan_standard_desc') }}</p>
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div class="text-2xl font-black font-mono tabular-nums text-slate-900 dark:text-white"
                                x-text="pricingCycle === 'annual' ? formatRupiah(tierPrices.standard?.annual) : formatRupiah(tierPrices.standard?.monthly)"></div>
                            <div class="text-[10px] text-slate-500 font-mono"
                                x-text="pricingCycle === 'annual' ? approxText(tierPrices.standard?.annual) : '{{ __('billing.plan_per_month_flexible') }}'"></div>
                        </div>

                        <!-- Ringkasan Kapasitas Inti -->
                        <div class="grid grid-cols-3 gap-1 py-2 px-2.5 rounded-[12px] bg-emerald-50/70 dark:bg-emerald-950/30 text-[10.5px] font-mono border border-emerald-200/60 dark:border-emerald-800/60 text-center">
                            <div>
                                <div class="text-emerald-600/70 dark:text-emerald-400/70 text-[9px] uppercase">Storage</div>
                                <div class="font-bold text-emerald-800 dark:text-emerald-300">3 GB</div>
                            </div>
                            <div>
                                <div class="text-emerald-600/70 dark:text-emerald-400/70 text-[9px] uppercase">Staf</div>
                                <div class="font-bold text-emerald-800 dark:text-emerald-300">3 Staf</div>
                            </div>
                            <div>
                                <div class="text-emerald-600/70 dark:text-emerald-400/70 text-[9px] uppercase">Lokasi</div>
                                <div class="font-bold text-emerald-800 dark:text-emerald-300">2 Lokasi</div>
                            </div>
                        </div>

                        <ul class="space-y-1.5 pt-1 text-[11px] text-slate-600 dark:text-slate-300">
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>1 Bisnis Cooca</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span><strong>{{ $tierLimits['standard']['product'] ?? 100 }} Produk</strong> &amp; {{ $tierLimits['standard']['recipe'] ?? 20 }} Resep HPP</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>{{ $tierLimits['standard']['material'] ?? 30 }} Bahan Baku</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>{{ $tierLimits['standard']['customer'] ?? 100 }} Pelanggan &amp; {{ $tierLimits['standard']['supplier'] ?? 5 }} Pemasok</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span><strong>{{ number_format($tierLimits['standard']['pos'] ?? 1000, 0, ',', '.') }} Kasir POS / bln</strong></span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>{{ $tierLimits['standard']['invoice'] ?? 15 }} Faktur &amp; {{ $tierLimits['standard']['po'] ?? 15 }} PO / bln</span></li>
                            @if($business->hasDineInFeature())
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>{{ $tierLimits['standard']['table'] ?? 5 }} Meja Kasir POS (Dine-In)</span></li>
                            @endif
                            <li class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-300 font-semibold"><i data-lucide="sparkles" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>Akses Fitur AI &amp; Gateway MCP (1 Token)</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>Otorisasi PIN Kasir &amp; Laci Kas</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>Ekspor / Impor Massal Excel</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i><span>{{ $tierLimits['standard']['whatsapp'] ?? 50 }} Notifikasi WA / bln</span></li>
                        </ul>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        @if($tier === 'standard')
                            <div class="w-full min-h-[44px] py-2.5 rounded-[12px] text-xs font-bold text-center bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-700 font-mono flex items-center justify-center">
                                {{ __('billing.plan_current_active') }}
                            </div>
                        @else
                            <a :href="'{{ route('billing.checkout', ['tier' => 'standard']) }}&cycle=' + pricingCycle"
                                class="w-full min-h-[44px] h-11 py-2.5 rounded-[12px] text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5">
                                <span>{{ __('billing.btn_choose_standard') }}</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- 3. Premium Plan (Highlighted / Populer) -->
                <div class="bg-white dark:bg-slate-900 rounded-[20px] border-2 {{ $tier === 'premium' ? 'border-indigo-500 ring-2 ring-indigo-500/30' : 'border-indigo-500 dark:border-indigo-500' }} p-5 shadow-md flex flex-col justify-between relative group hover:border-indigo-600 transition-all">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 bg-indigo-600 text-white text-[9px] font-bold uppercase tracking-wider rounded-full shadow-xs whitespace-nowrap z-10">
                        {{ __('billing.popular') }}
                    </div>
                    <div class="space-y-3 mt-1">
                        <div class="flex items-center justify-between">
                            <span class="rounded-[8px] px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider font-mono bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800">
                                {{ __('billing.tier_scaleup_umkm') }}
                            </span>
                            @if($tier === 'premium')
                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 font-mono">{{ __('billing.plan_active_badge') }}</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">{{ __('billing.plan_premium') }}</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ __('billing.plan_premium_desc') }}</p>
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div class="text-2xl font-black font-mono tabular-nums text-indigo-600 dark:text-indigo-400"
                                x-text="pricingCycle === 'annual' ? formatRupiah(tierPrices.premium?.annual) : formatRupiah(tierPrices.premium?.monthly)"></div>
                            <div class="text-[10px] text-slate-500 font-mono"
                                x-text="pricingCycle === 'annual' ? approxText(tierPrices.premium?.annual) : '{{ __('billing.plan_per_month_flexible') }}'"></div>
                        </div>

                        <!-- Ringkasan Kapasitas Inti -->
                        <div class="grid grid-cols-3 gap-1 py-2 px-2.5 rounded-[12px] bg-indigo-50/70 dark:bg-indigo-950/30 text-[10.5px] font-mono border border-indigo-200/60 dark:border-indigo-800/60 text-center">
                            <div>
                                <div class="text-indigo-600/70 dark:text-indigo-400/70 text-[9px] uppercase">Storage</div>
                                <div class="font-bold text-indigo-800 dark:text-indigo-300">10 GB</div>
                            </div>
                            <div>
                                <div class="text-indigo-600/70 dark:text-indigo-400/70 text-[9px] uppercase">Staf</div>
                                <div class="font-bold text-indigo-800 dark:text-indigo-300">10 Staf</div>
                            </div>
                            <div>
                                <div class="text-indigo-600/70 dark:text-indigo-400/70 text-[9px] uppercase">Lokasi</div>
                                <div class="font-bold text-indigo-800 dark:text-indigo-300">5 Lokasi</div>
                            </div>
                        </div>

                        <ul class="space-y-1.5 pt-1 text-[11px] text-slate-600 dark:text-slate-300">
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-indigo-600 shrink-0"></i><span><strong>3 Bisnis</strong> ({{ __('billing.premium_three_businesses') }})</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-indigo-600 shrink-0"></i><span>{{ __('billing.premium_unlimited_catalog') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-indigo-600 shrink-0"></i><span>{{ __('billing.premium_unlimited_pos') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-indigo-600 shrink-0"></i><span>{{ __('billing.premium_unlimited_invoices') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-indigo-600 shrink-0"></i><span>{{ __('billing.premium_unlimited_tables') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-indigo-600 shrink-0"></i><span>{{ __('billing.premium_kds_transfer') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-indigo-600 shrink-0"></i><span>{{ __('billing.premium_multipricing') }}</span></li>
                            <li class="flex items-center gap-1.5 text-indigo-700 dark:text-indigo-300 font-semibold"><i data-lucide="sparkles" class="w-3.5 h-3.5 text-indigo-600 shrink-0"></i><span>{{ __('billing.premium_ai_suite') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-indigo-600 shrink-0"></i><span>{{ __('billing.premium_payroll_features') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-indigo-600 shrink-0"></i><span>{{ __('billing.premium_accounting_branch') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-indigo-600 shrink-0"></i><span>{{ __('billing.premium_wa_social') }}</span></li>
                        </ul>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        @if($tier === 'premium')
                            <div class="w-full min-h-[44px] py-2.5 rounded-[12px] text-xs font-bold text-center bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-300 dark:border-indigo-700 font-mono flex items-center justify-center">
                                {{ __('billing.plan_current_active') }}
                            </div>
                        @else
                            <a :href="'{{ route('billing.checkout', ['tier' => 'premium']) }}&cycle=' + pricingCycle"
                                class="w-full min-h-[44px] h-11 py-2.5 rounded-[12px] text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-sm shadow-indigo-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5">
                                <span>{{ __('billing.btn_choose_premium') }}</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- 4. Prestige Plan -->
                <div class="bg-white dark:bg-slate-900 rounded-[20px] border {{ $tier === 'prestige' ? 'border-purple-500 ring-2 ring-purple-500/30' : 'border-purple-200 dark:border-purple-900/60' }} p-5 shadow-xs flex flex-col justify-between relative group hover:border-purple-500 transition-all">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="rounded-[8px] px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider font-mono bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800">
                                {{ __('billing.tier_enterprise_umkm') }}
                            </span>
                            @if($tier === 'prestige')
                                <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400 font-mono">{{ __('billing.plan_active_badge') }}</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">{{ __('billing.plan_prestige') }}</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ __('billing.plan_prestige_desc') }}</p>
                        </div>
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div class="text-2xl font-black font-mono tabular-nums text-purple-600 dark:text-purple-400"
                                x-text="pricingCycle === 'annual' ? formatRupiah(tierPrices.prestige?.annual) : formatRupiah(tierPrices.prestige?.monthly)"></div>
                            <div class="text-[10px] text-slate-500 font-mono"
                                x-text="pricingCycle === 'annual' ? approxText(tierPrices.prestige?.annual) : '{{ __('billing.plan_per_month_flexible') }}'"></div>
                        </div>

                        <!-- Ringkasan Kapasitas Inti -->
                        <div class="grid grid-cols-3 gap-1 py-2 px-2.5 rounded-[12px] bg-purple-50/70 dark:bg-purple-950/30 text-[10.5px] font-mono border border-purple-200/60 dark:border-purple-800/60 text-center">
                            <div>
                                <div class="text-purple-600/70 dark:text-purple-400/70 text-[9px] uppercase">Storage</div>
                                <div class="font-bold text-purple-800 dark:text-purple-300">30 GB</div>
                            </div>
                            <div>
                                <div class="text-purple-600/70 dark:text-purple-400/70 text-[9px] uppercase">Staf</div>
                                <div class="font-bold text-purple-800 dark:text-purple-300">{{ __('billing.unlimited_free') }}</div>
                            </div>
                            <div>
                                <div class="text-purple-600/70 dark:text-purple-400/70 text-[9px] uppercase">Lokasi</div>
                                <div class="font-bold text-purple-800 dark:text-purple-300">{{ __('billing.unlimited_free') }}</div>
                            </div>
                        </div>

                        <ul class="space-y-1.5 pt-1 text-[11px] text-slate-600 dark:text-slate-300">
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span><strong>{{ __('billing.prestige_unlimited_business') }}</strong></span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>{{ __('billing.prestige_unlimited_branches') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>{{ __('billing.prestige_unlimited_staff') }}</span></li>
                            <li class="flex items-center gap-1.5 text-purple-700 dark:text-purple-300 font-semibold"><i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>{{ __('billing.prestige_tax_pph21') }}</span></li>
                            <li class="flex items-center gap-1.5 text-purple-700 dark:text-purple-300 font-semibold"><i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>{{ __('billing.prestige_payslip_wa') }}</span></li>
                            <li class="flex items-center gap-1.5 text-purple-700 dark:text-purple-300 font-semibold"><i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>{{ __('billing.prestige_ai_mcp_unlimited') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>{{ __('billing.prestige_bpjs_thr') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>{{ __('billing.prestige_spt_pajak') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>{{ __('billing.prestige_consolidation') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>{{ __('billing.prestige_wa_notifications') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>{{ __('billing.prestige_social_unlimited') }}</span></li>
                            <li class="flex items-center gap-1.5"><i data-lucide="check" class="w-3.5 h-3.5 text-purple-600 shrink-0"></i><span>{{ __('billing.prestige_priority_support') }}</span></li>
                        </ul>
                    </div>
                    <div class="mt-5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        @if($tier === 'prestige')
                            <div class="w-full min-h-[44px] py-2.5 rounded-[12px] text-xs font-bold text-center bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-400 border border-purple-300 dark:border-purple-700 font-mono flex items-center justify-center">
                                {{ __('billing.plan_current_active') }}
                            </div>
                        @else
                            <a :href="'{{ route('billing.checkout', ['tier' => 'prestige']) }}&cycle=' + pricingCycle"
                                class="w-full min-h-[44px] h-11 py-2.5 rounded-[12px] text-xs font-bold text-white bg-purple-600 hover:bg-purple-500 shadow-sm shadow-purple-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5">
                                <span>{{ __('billing.btn_choose_prestige') }}</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Callout Edukasi Fitur AI & BYOAI -->
            <div class="rounded-[18px] bg-gradient-to-r from-indigo-50/80 via-purple-50/60 to-cyan-50/80 dark:from-indigo-950/30 dark:via-purple-950/20 dark:to-cyan-950/30 border border-indigo-200/80 dark:border-indigo-800/60 p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-[12px] bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <i data-lucide="bot" class="w-5 h-5"></i>
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">{{ __('billing.byoai_callout_title') }}</h4>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300 font-mono">{{ __('billing.byoai_badge_model') }}</span>
                        </div>
                        <p class="text-[12px] text-slate-600 dark:text-slate-300 leading-relaxed max-w-3xl">
                            {!! __('billing.byoai_callout_desc') !!}
                        </p>
                    </div>
                </div>
                <div class="shrink-0 flex items-center gap-2">
                    <a href="{{ route('cooca-ai.providers') }}"
                        class="min-h-[44px] h-11 sm:h-10 px-4 py-2.5 rounded-[12px] text-xs font-bold text-slate-800 dark:text-white bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 shadow-2xs transition flex items-center gap-1.5 whitespace-nowrap">
                        <i data-lucide="settings-2" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        <span>{{ __('billing.manage_ai_providers') }}</span>
                    </a>
                </div>
            </div>

            <!-- Matriks Perbandingan Detail Seluruh Fitur & Limitasi Antar Paket (Apple HIG Table) -->
            <div class="rounded-[22px] bg-white dark:bg-slate-900 border border-black/[0.06] dark:border-white/[0.08] shadow-xs overflow-hidden" x-data="{ expandedMatrix: false }">
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-800/20">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="layers" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                            <span>{{ __('billing.matrix_table_title') }}</span>
                        </h3>
                        <p class="text-[11.5px] text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ __('billing.matrix_table_subtitle') }}
                        </p>
                    </div>
                    <button type="button" @click="expandedMatrix = !expandedMatrix"
                        class="min-h-[44px] sm:min-h-0 px-3.5 py-2 rounded-[10px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                        <span x-text="expandedMatrix ? '{{ __('billing.hide_matrix') }}' : '{{ __('billing.open_matrix_full') }}'"></span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="expandedMatrix ? 'rotate-180' : ''"></i>
                    </button>
                </div>

                <div x-show="expandedMatrix" x-collapse>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-800/70 text-[11px] font-mono uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    <th class="py-3 px-4 w-[36%] font-bold">{{ __('billing.matrix_col_feature') }}</th>
                                    <th class="py-3 px-3 text-center w-[16%]">Free (Rp 0)</th>
                                    <th class="py-3 px-3 text-center w-[16%] text-emerald-700 dark:text-emerald-400">
                                        <span>Standard</span>
                                        <span class="block text-[10px] font-normal" x-text="pricingCycle === 'annual' ? formatRupiah(tierPrices.standard?.annual) : formatRupiah(tierPrices.standard?.monthly)"></span>
                                    </th>
                                    <th class="py-3 px-3 text-center w-[16%] text-indigo-700 dark:text-indigo-400 font-bold bg-indigo-50/50 dark:bg-indigo-950/20">
                                        <span>Premium</span>
                                        <span class="block text-[10px] font-normal" x-text="pricingCycle === 'annual' ? formatRupiah(tierPrices.premium?.annual) : formatRupiah(tierPrices.premium?.monthly)"></span>
                                    </th>
                                    <th class="py-3 px-3 text-center w-[16%] text-purple-700 dark:text-purple-400">
                                        <span>Prestige</span>
                                        <span class="block text-[10px] font-normal" x-text="pricingCycle === 'annual' ? formatRupiah(tierPrices.prestige?.annual) : formatRupiah(tierPrices.prestige?.monthly)"></span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-sans text-slate-700 dark:text-slate-300 text-[12px]">

                                <!-- GRUP 1: AI & INTELEGENSI BISNIS -->
                                <tr class="bg-indigo-50/30 dark:bg-indigo-950/10 font-bold text-slate-900 dark:text-white">
                                    <td colspan="5" class="py-2.5 px-4 text-[11.5px] uppercase tracking-wider font-mono flex items-center gap-2">
                                        <i data-lucide="bot" class="w-3.5 h-3.5 text-indigo-600"></i>
                                        <span>{{ __('billing.matrix_grp_ai') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Akses Cooca AI &amp; POS Intelligence</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="lock" class="w-4 h-4 text-rose-500 inline"></i> Terkunci</td>
                                    <td class="py-2.5 px-3 text-center font-semibold text-emerald-600"><i data-lucide="check" class="w-4 h-4 inline"></i> Terbuka</td>
                                    <td class="py-2.5 px-3 text-center font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i> Full Suite</td>
                                    <td class="py-2.5 px-3 text-center font-bold text-purple-600"><i data-lucide="check" class="w-4 h-4 inline"></i> Full Suite</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Integrasi BYOAI (Gemini Gratis, OpenAI, Claude, Groq)</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400">-</td>
                                    <td class="py-2.5 px-3 text-center font-mono">Bebas Terhubung</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">Bebas Terhubung</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">Bebas Terhubung</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Sales Forecasting POS (Prediksi 14 Hari)</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-emerald-600"><i data-lucide="check" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-purple-600"><i data-lucide="check" class="w-4 h-4 inline"></i></td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Deteksi Anomali &amp; Fraud Kasir</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-emerald-600"><i data-lucide="check" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-purple-600"><i data-lucide="check" class="w-4 h-4 inline"></i></td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Menu Engineering BCG Matrix &amp; Saran Bundling</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-emerald-600"><i data-lucide="check" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-purple-600"><i data-lucide="check" class="w-4 h-4 inline"></i></td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Draft Balasan AI Grounded di Kotak Masuk Terpadu</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-emerald-600"><i data-lucide="check" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-purple-600"><i data-lucide="check" class="w-4 h-4 inline"></i></td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Gateway MCP (Model Context Protocol Universal)</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="lock" class="w-4 h-4 text-rose-500 inline"></i> Terkunci</td>
                                    <td class="py-2.5 px-3 text-center font-mono text-emerald-600">1 Token Aktif</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">Multi-Client (5 Token)</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">Dedicated Unlimited</td>
                                </tr>

                                <!-- GRUP 2: AKUN OWNER & KAPASITAS DASAR -->
                                <tr class="bg-slate-50/80 dark:bg-slate-800/40 font-bold text-slate-900 dark:text-white">
                                    <td colspan="5" class="py-2.5 px-4 text-[11.5px] uppercase tracking-wider font-mono flex items-center gap-2">
                                        <i data-lucide="building" class="w-3.5 h-3.5 text-cyan-600"></i>
                                        <span>{{ __('billing.matrix_grp_capacity') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Maksimal Entitas Bisnis / Toko</td>
                                    <td class="py-2.5 px-3 text-center font-mono">1 Bisnis</td>
                                    <td class="py-2.5 px-3 text-center font-mono">1 Bisnis</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">Hingga 3 Bisnis</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">∞ Unlimited</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Akun Pengguna Staf / Karyawan</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['free']['user'] ?? 1 }} (Solo Owner)</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['standard']['user'] ?? 3 }} Karyawan</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">{{ $tierLimits['premium']['user'] ?? 10 }} Karyawan</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">∞ Unlimited</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Kapasitas Cloud Storage Terproteksi</td>
                                    <td class="py-2.5 px-3 text-center font-mono">1 GB</td>
                                    <td class="py-2.5 px-3 text-center font-mono">3 GB</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">10 GB</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">30 GB (+ Top-up)</td>
                                </tr>

                                <!-- GRUP 3: MASTER DATA & STOK -->
                                <tr class="bg-slate-50/80 dark:bg-slate-800/40 font-bold text-slate-900 dark:text-white">
                                    <td colspan="5" class="py-2.5 px-4 text-[11.5px] uppercase tracking-wider font-mono flex items-center gap-2">
                                        <i data-lucide="package" class="w-3.5 h-3.5 text-amber-600"></i>
                                        <span>3. Master Data Operasional &amp; Bahan</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Katalog Produk &amp; SKU</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['free']['product'] ?? 10 }} Produk</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['standard']['product'] ?? 100 }} Produk</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">{{ ($tierLimits['premium']['product'] ?? null) ? $tierLimits['premium']['product'].' Produk' : '∞ Unlimited' }}</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">{{ ($tierLimits['prestige']['product'] ?? null) ? $tierLimits['prestige']['product'].' Produk' : '∞ Unlimited' }}</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Formula Resep HPP / BOM</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['free']['recipe'] ?? 3 }} Resep</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['standard']['recipe'] ?? 20 }} Resep</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">∞ Unlimited</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">∞ Unlimited</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Bahan Baku (Raw Materials)</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['free']['material'] ?? 10 }} Bahan</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['standard']['material'] ?? 30 }} Bahan</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">∞ Unlimited</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">∞ Unlimited</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Kontak Pelanggan CRM</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['free']['customer'] ?? 15 }} Kontak</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['standard']['customer'] ?? 100 }} Kontak</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">{{ number_format($tierLimits['premium']['customer'] ?? 1000, 0, ',', '.') }} Kontak</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">∞ Unlimited</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Pemasok / Vendor</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['free']['supplier'] ?? 2 }} Vendor</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['standard']['supplier'] ?? 5 }} Vendor</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">∞ Unlimited</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">∞ Unlimited</td>
                                </tr>

                                <!-- GRUP 4: TRANSAKSI BULANAN -->
                                <tr class="bg-slate-50/80 dark:bg-slate-800/40 font-bold text-slate-900 dark:text-white">
                                    <td colspan="5" class="py-2.5 px-4 text-[11.5px] uppercase tracking-wider font-mono flex items-center gap-2">
                                        <i data-lucide="shopping-cart" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>4. Transaksi Bulanan (Reset Tiap Tanggal 1)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Transaksi Kasir POS</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['free']['pos'] ?? 30 }} struk / bln</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-semibold text-emerald-600">{{ number_format($tierLimits['standard']['pos'] ?? 1000, 0, ',', '.') }} struk / bln</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">∞ Unlimited</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">∞ Unlimited</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Faktur Penjualan B2B (Invoices)</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['free']['invoice'] ?? 3 }} faktur / bln</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['standard']['invoice'] ?? 15 }} faktur / bln</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">∞ Unlimited</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">∞ Unlimited</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Purchase Order Pembelian (PO)</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['free']['po'] ?? 3 }} PO / bln</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['standard']['po'] ?? 15 }} PO / bln</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">∞ Unlimited</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">∞ Unlimited</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Struk Notifikasi WhatsApp Gateway</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['free']['whatsapp'] ?? 10 }} pesan / bln</td>
                                    <td class="py-2.5 px-3 text-center font-mono">{{ $tierLimits['standard']['whatsapp'] ?? 50 }} pesan / bln</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">{{ $tierLimits['premium']['whatsapp'] ?? 200 }} pesan / bln</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">{{ number_format($tierLimits['prestige']['whatsapp'] ?? 1000, 0, ',', '.') }} pesan / bln</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Jadwal Postingan Media Sosial</td>
                                    <td class="py-2.5 px-3 text-center font-mono">3 post / bln</td>
                                    <td class="py-2.5 px-3 text-center font-mono">10 post / bln</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">30 post / bln</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">∞ Unlimited</td>
                                </tr>

                                <!-- GRUP 5: MULTI-CABANG & RESTORAN F&B -->
                                <tr class="bg-slate-50/80 dark:bg-slate-800/40 font-bold text-slate-900 dark:text-white">
                                    <td colspan="5" class="py-2.5 px-4 text-[11.5px] uppercase tracking-wider font-mono flex items-center gap-2">
                                        <i data-lucide="store" class="w-3.5 h-3.5 text-blue-600"></i>
                                        <span>5. Multi-Cabang, Gudang &amp; Restoran F&amp;B</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Cabang Toko / Outlet Fisik</td>
                                    <td class="py-2.5 px-3 text-center font-mono">1 Outlet</td>
                                    <td class="py-2.5 px-3 text-center font-mono">1 Outlet</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">Hingga 3 Cabang</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">∞ Unlimited</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Gudang / Dapur Pusat (Central Kitchen)</td>
                                    <td class="py-2.5 px-3 text-center font-mono">1 Gudang</td>
                                    <td class="py-2.5 px-3 text-center font-mono">1 Gudang</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">Hingga 3 Gudang</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">∞ Unlimited</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Surat Jalan Transfer Stok Antar-Cabang</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 font-bold bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i> Bebas Transfer</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> Bebas Transfer</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Multi-Pricing (Beda Harga per Cabang Toko)</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i> 1 Harga</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i> 1 Harga</td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 font-bold bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i> Beda Harga</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> Beda Harga</td>
                                </tr>
                                @if($business->hasDineInFeature())
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Denah Meja Kasir POS (Dine-In)</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400">0 Meja</td>
                                    <td class="py-2.5 px-3 text-center font-mono">5 Meja</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">∞ Unlimited</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">∞ Unlimited</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Kitchen Display System (KDS Layar Dapur)</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 font-bold bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i> Aktif</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> Aktif</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Self-Order QR Meja Pelanggan</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 font-bold bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i> Aktif</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> Aktif</td>
                                </tr>
                                @endif

                                <!-- GRUP 6: HRM & PENGGAJIAN -->
                                <tr class="bg-slate-50/80 dark:bg-slate-800/40 font-bold text-slate-900 dark:text-white">
                                    <td colspan="5" class="py-2.5 px-4 text-[11.5px] uppercase tracking-wider font-mono flex items-center gap-2">
                                        <i data-lucide="users" class="w-3.5 h-3.5 text-violet-600"></i>
                                        <span>6. HRM, Presensi &amp; Penggajian Staf</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Otorisasi PIN Kasir &amp; Shift Kasir</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-emerald-600"><i data-lucide="check" class="w-4 h-4 inline"></i> PIN Kasir</td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i> Custom Roles</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> Granular RBAC</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Presensi Shift / Absensi Karyawan</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400">-</td>
                                    <td class="py-2.5 px-3 text-center">Presensi POS</td>
                                    <td class="py-2.5 px-3 text-center font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">GPS + POS</td>
                                    <td class="py-2.5 px-3 text-center font-bold text-purple-600">Multi-Cabang</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Sistem Komisi Kinerja Staf / Kasir</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 font-bold bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i> Per Struk</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> Komisi Bertingkat</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Buku Kasbon &amp; Cicilan Pinjaman Karyawan</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-slate-500">Catatan Kasar</td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 font-bold bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i> Buku Cicilan</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> Auto-Potong Gaji</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Skema Pekerja Harian Lepas (Daily Worker)</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 font-bold bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i> Upah Harian</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> Harian + Borongan</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Kalkulasi BPJS TK &amp; BPJS Kesehatan</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">Kalkulator Persentase</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> Modul Terpadu</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Kalkulasi THR Berbasis Tanggal Masuk (Join Date)</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">Rumus Manual</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> Otomatis Join Date</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Slip Gaji Digital via WhatsApp Otomatis</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-slate-500 bg-indigo-50/30 dark:bg-indigo-950/10">Rekap Sederhana</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> Auto-Kirim Slip WA</td>
                                </tr>

                                <!-- GRUP 7: PERPAJAKAN & PEMBUKUAN KEUANGAN -->
                                <tr class="bg-slate-50/80 dark:bg-slate-800/40 font-bold text-slate-900 dark:text-white">
                                    <td colspan="5" class="py-2.5 px-4 text-[11.5px] uppercase tracking-wider font-mono flex items-center gap-2">
                                        <i data-lucide="calculator" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>7. Perpajakan &amp; Akuntansi Finansial</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Pajak Restoran (PB1 10%) &amp; PPN Kasir</td>
                                    <td class="py-2.5 px-3 text-center font-mono">Tarif Flat</td>
                                    <td class="py-2.5 px-3 text-center font-mono">Tarif Flat</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">Multi-Tarif Fleksibel</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">Multi-Tarif Fleksibel</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">PPh Final UMKM 0.5% (PP 55/2022)</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-slate-500">Rekap Omzet</td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i> Batas 500 Juta</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> Rekonsiliasi SPT</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Pajak Karyawan PPh 21 TER (PP 58/2023)</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-slate-500 bg-indigo-50/30 dark:bg-indigo-950/10">Estimasi Bruto</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> TER A/B/C + Des</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Jurnal Akuntansi Otomatis &amp; Laba Rugi Cabang</td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-slate-400"><i data-lucide="x" class="w-4 h-4 inline"></i></td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 font-bold bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i> per Cabang</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> Konsolidasian</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Ekspor &amp; Impor Massal Excel/CSV</td>
                                    <td class="py-2.5 px-3 text-center text-slate-500">Ekspor Dasar</td>
                                    <td class="py-2.5 px-3 text-center text-emerald-600"><i data-lucide="check" class="w-4 h-4 inline"></i> Lengkap</td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 font-bold bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i> Lengkap</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> Lengkap</td>
                                </tr>

                                <!-- GRUP 8: JAMINAN KEAMANAN & DUKUNGAN -->
                                <tr class="bg-slate-50/80 dark:bg-slate-800/40 font-bold text-slate-900 dark:text-white">
                                    <td colspan="5" class="py-2.5 px-4 text-[11.5px] uppercase tracking-wider font-mono flex items-center gap-2">
                                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>8. Jaminan Privasi &amp; Dukungan Pelanggan</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Jaminan No Data Punishment (Data Tidak Pernah Dihapus)</td>
                                    <td class="py-2.5 px-3 text-center text-emerald-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> 100% Aman</td>
                                    <td class="py-2.5 px-3 text-center text-emerald-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> 100% Aman</td>
                                    <td class="py-2.5 px-3 text-center text-indigo-600 font-bold bg-indigo-50/30 dark:bg-indigo-950/10"><i data-lucide="check" class="w-4 h-4 inline"></i> 100% Aman</td>
                                    <td class="py-2.5 px-3 text-center text-purple-600 font-bold"><i data-lucide="check" class="w-4 h-4 inline"></i> 100% Aman</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Akses Data Historis saat Downgrade / Expired</td>
                                    <td class="py-2.5 px-3 text-center font-mono">Read-Only</td>
                                    <td class="py-2.5 px-3 text-center font-mono">Read-Only</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">Read-Only</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">Read-Only</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-4 font-medium">Jalur Bantuan Customer Support</td>
                                    <td class="py-2.5 px-3 text-center text-slate-500 font-mono">Komunitas</td>
                                    <td class="py-2.5 px-3 text-center font-mono">Email Support</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-600 bg-indigo-50/30 dark:bg-indigo-950/10">WhatsApp CS</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-purple-600">24/7 Dedicated Manager</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-50/80 dark:bg-slate-950/60 border-t border-slate-200 dark:border-slate-800">
                                <tr>
                                    <td class="py-3 px-4 font-bold text-slate-700 dark:text-slate-300">{{ __('billing.choose_business_plan') }}</td>
                                    <td class="py-3 px-3 text-center">
                                        @if($tier === 'free')
                                            <span class="px-2.5 py-1 rounded-[8px] bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-[11px] font-mono">{{ __('billing.current_active_plan') }}</span>
                                        @else
                                            <span class="text-slate-400 text-xs font-mono">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if($tier === 'standard')
                                            <span class="px-2.5 py-1 rounded-[8px] bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-semibold text-[11px] font-mono">{{ __('billing.current_active_plan') }}</span>
                                        @else
                                            <a :href="'{{ route('billing.checkout', ['tier' => 'standard']) }}&cycle=' + pricingCycle" class="min-h-[44px] px-3.5 py-2 rounded-[10px] text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-2xs inline-flex items-center gap-1">
                                                <span>{{ __('billing.tier_choose_standard') }}</span>
                                            </a>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center bg-indigo-50/40 dark:bg-indigo-950/30">
                                        @if($tier === 'premium')
                                            <span class="px-2.5 py-1 rounded-[8px] bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 font-semibold text-[11px] font-mono">{{ __('billing.current_active_plan') }}</span>
                                        @else
                                            <a :href="'{{ route('billing.checkout', ['tier' => 'premium']) }}&cycle=' + pricingCycle" class="min-h-[44px] px-3.5 py-2 rounded-[10px] text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-2xs inline-flex items-center gap-1">
                                                <span>{{ __('billing.tier_choose_premium') }}</span>
                                            </a>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center bg-purple-50/40 dark:bg-purple-950/30">
                                        @if($tier === 'prestige')
                                            <span class="px-2.5 py-1 rounded-[8px] bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-400 font-semibold text-[11px] font-mono">{{ __('billing.current_active_plan') }}</span>
                                        @else
                                            <a :href="'{{ route('billing.checkout', ['tier' => 'prestige']) }}&cycle=' + pricingCycle" class="min-h-[44px] px-3.5 py-2 rounded-[10px] text-xs font-bold text-white bg-purple-600 hover:bg-purple-500 shadow-2xs inline-flex items-center gap-1">
                                                <span>{{ __('billing.tier_choose_prestige') }}</span>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>


        <!-- 4. SECTION 1: Special Infrastructure Hub (Storage Cloud & AI Engine) -->
        <section aria-labelledby="infra-hub-heading" class="space-y-4">
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-slate-200 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="cpu" class="w-4 h-4 text-cyan-600 dark:text-cyan-400" aria-hidden="true"></i>
                    <h2 id="infra-hub-heading"
                        class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">
                        {{ __('billing.infra_cloud_ai_heading') }}
                    </h2>
                </div>
                <span class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-mono">{{ __('billing.infra_cloud_ai_sub') }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                <!-- 1. Cloud Storage Owner -->
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-xs p-5 sm:p-6 flex flex-col justify-between gap-5 relative overflow-hidden">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div
                                class="flex items-center gap-2 text-cyan-700 dark:text-cyan-300 text-xs font-black uppercase tracking-wider">
                                <div class="p-2 rounded-[10px] bg-cyan-100/80 dark:bg-cyan-500/15 text-cyan-600 dark:text-cyan-400"
                                    aria-hidden="true">
                                    <i data-lucide="hard-drive" class="w-4 h-4"></i>
                                </div>
                                <span>{{ __('billing.business_cloud_storage') }}</span>
                            </div>
                            <span
                                class="rounded-[8px] px-2 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-400 border-cyan-200/90 dark:border-cyan-800/90 font-mono">
                                {{ __('billing.protected_badge') }}
                            </span>
                        </div>

                        <div class="flex items-baseline gap-2 pt-1">
                            <span class="text-2xl sm:text-3xl font-black font-mono text-slate-900 dark:text-white tabular-nums">
                                {{ number_format($usage['storage']['used_mb'] ?? 0, 1, ',', '.') }} <span
                                    class="text-sm sm:text-base font-bold text-slate-500 dark:text-slate-400">MB</span>
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono tabular-nums">
                                / {{ number_format($usage['storage']['limit_gb'] ?? 1, 1, ',', '.') }} GB {{ __('billing.capacity_label') }}
                            </span>
                        </div>

                        <!-- Storage Progress Bar with ARIA -->
                        @php $storagePercent = min(100, max(0, (int)($usage['storage']['percentage'] ?? 0))); @endphp
                        <div class="space-y-1.5" role="progressbar" aria-valuenow="{{ $storagePercent }}"
                            aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('billing.storage_usage_percentage_aria') }}">
                            <div
                                class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-2.5 overflow-hidden p-0.5 border border-slate-200 dark:border-slate-800">
                                <div class="h-full rounded-full transition-all duration-500 {{ $storagePercent >= 90 ? 'bg-rose-500' : ($storagePercent >= 75 ? 'bg-amber-400' : 'bg-cyan-500') }}"
                                    style="width: {{ $storagePercent }}%"></div>
                            </div>
                            <div class="flex justify-between text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                                <span>{{ __('billing.storage_used_label') }}: <strong
                                        class="text-slate-800 dark:text-slate-200 font-bold tabular-nums">{{ $storagePercent }}%</strong></span>
                                <span>{{ __('billing.storage_remaining_label') }}: <strong
                                        class="text-slate-800 dark:text-slate-200 font-bold tabular-nums">{{ max(0, 100 - $storagePercent) }}%</strong></span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ __('billing.storage_safe_desc') }}
                        </p>
                    </div>

                    @if (\App\Support\Context::hasPermission('billing.manage'))
                        <div
                            class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('billing.add_permanent_quota') }}</span>
                            <a href="{{ route('billing.checkout', ['type' => 'storage']) }}"
                                class="min-h-[44px] h-11 sm:h-10 px-4 py-2 rounded-[12px] text-xs font-bold text-white bg-cyan-600 hover:bg-cyan-500 shadow-sm shadow-cyan-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5 focus-visible:ring-2 focus-visible:ring-cyan-500">
                                <i data-lucide="plus" class="w-3.5 h-3.5" aria-hidden="true"></i>
                                <span>{{ __('billing.topup_storage') }}</span>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- 2. AI Intelligence Engine (BYOAI) -->
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-xs p-5 sm:p-6 flex flex-col justify-between gap-5 relative overflow-hidden">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div
                                class="flex items-center gap-2 {{ $hasActiveAi ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }} text-xs font-black uppercase tracking-wider">
                                <div class="p-2 rounded-[10px] {{ $hasActiveAi ? 'bg-emerald-100/80 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400' : 'bg-amber-100/80 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400' }}"
                                    aria-hidden="true">
                                    <i data-lucide="bot" class="w-4 h-4"></i>
                                </div>
                                <span>{{ __('billing.ai_engine_byoai') }}</span>
                            </div>
                            @if ($hasActiveAi)
                                <span
                                    class="rounded-[8px] px-2 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90 font-mono">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                    {{ strtoupper($activeAiConfig->provider) }} {{ __('billing.ai_byoai_badge_connected') }}
                                </span>
                            @else
                                <span
                                    class="rounded-[8px] px-2 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200/90 dark:border-amber-800/90 font-mono">
                                    <i data-lucide="key-round" class="w-3 h-3"></i>
                                    {{ __('billing.ai_byoai_badge_unconfigured') }}
                                </span>
                            @endif
                        </div>

                        @if ($hasActiveAi)
                            <div class="space-y-1">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-2xl sm:text-3xl font-black font-mono text-slate-900 dark:text-white tabular-nums">
                                        {{ $activeProviderLabel }}
                                    </span>
                                    <span class="text-xs text-emerald-600 dark:text-emerald-400 font-mono font-bold">{{ __('billing.ai_byoai_active') }}</span>
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                    {{ __('billing.main_model_label') }}: <strong class="text-slate-800 dark:text-slate-200">{{ $activeAiConfig->model ?: 'Auto-selected' }}</strong>
                                </div>
                            </div>

                            <!-- BYOAI Spec Grid Apple HIG -->
                            <div class="rounded-[14px] bg-slate-50 dark:bg-slate-950/50 border border-slate-200/80 dark:border-slate-800/80 p-3.5 space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400">{{ __('billing.platform_compute_quota') }}:</span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono flex items-center gap-1">
                                        <i data-lucide="infinity" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('billing.unlimited_no_limit') }}</span>
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400">{{ __('billing.token_cost_model') }}:</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 font-mono text-[11px]">{{ __('billing.direct_to_provider') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 dark:text-slate-400">{{ __('billing.api_key_security') }}:</span>
                                    <span class="font-mono text-slate-700 dark:text-slate-300 text-[11px] flex items-center gap-1">
                                        <i data-lucide="lock" class="w-3 h-3 text-emerald-500"></i>
                                        <span>{{ __('billing.aes_encrypted') }}</span>
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="space-y-1">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-slate-200">
                                        {{ __('billing.ai_byoai_unconfigured') }}
                                    </span>
                                </div>
                                <div class="text-xs text-amber-600 dark:text-amber-400 font-mono font-medium">
                                    {{ __('billing.connect_key_to_activate') }}
                                </div>
                            </div>

                            <!-- Provider Selection Callout Apple HIG -->
                            <div class="rounded-[14px] bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/60 p-3.5 space-y-1.5 text-xs text-amber-950 dark:text-amber-200">
                                <div class="font-bold flex items-center gap-1.5">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400"></i>
                                    <span>{{ __('billing.use_preferred_provider') }}</span>
                                </div>
                                <p class="text-[11.5px] text-amber-800 dark:text-amber-300 leading-relaxed">
                                    {{ __('billing.byoai_support_desc') }}
                                </p>
                            </div>
                        @endif

                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ __('billing.ai_byoai_desc') }}
                        </p>
                    </div>

                    <div
                        class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ __('billing.byoai_self_model') }}</span>
                        <a href="{{ route('cooca-ai.providers') }}"
                            class="min-h-[44px] h-11 sm:h-10 px-4 py-2 rounded-[12px] text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-100 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5 shadow-xs focus-visible:ring-2 focus-visible:ring-slate-500">
                            <i data-lucide="settings" class="w-3.5 h-3.5" aria-hidden="true"></i>
                            <span>{{ __('billing.ai_byoai_manage') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4.5. SECTION 1B: Storage Detail & Breakdown per Bisnis -->
        @if (!empty($storageDetails))
            @php
                $sdLimitBytes = $storageDetails['limit_bytes'];
                $sdUsedBytes = $storageDetails['used_bytes'];
                $sdPct = $storageDetails['percentage'];
                $sdIsOver = $storageDetails['is_over_limit'];
            @endphp
            <section aria-labelledby="storage-detail-heading" class="space-y-4">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="hard-drive" class="w-4 h-4 text-cyan-600 dark:text-cyan-400"
                            aria-hidden="true"></i>
                        <h2 id="storage-detail-heading"
                            class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">
                            {{ __('billing.storage_detail_heading') }}
                        </h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-mono tabular-nums">
                            {{ $storageDetails['total_files_count'] }} file aktif ({{ $storageDetails['business_used_mb'] ?? $storageDetails['used_mb'] }} MB) · Kuota: {{ $storageDetails['limit_gb'] }} GB
                        </span>
                        <!-- Kelola Semua Berkas Modal Trigger -->
                        <button type="button" @click="openModal()"
                            class="min-h-[44px] sm:min-h-0 px-3 py-2 rounded-[10px] text-[11px] sm:text-[10px] font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 active:scale-[0.98] transition cursor-pointer flex items-center gap-1.5 shadow-2xs focus-visible:ring-2 focus-visible:ring-cyan-500">
                            <i data-lucide="folder" class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400" aria-hidden="true"></i>
                            <span>{{ __('billing.manage_all_files') }}</span>
                        </button>
                        <!-- Recalculate Button -->
                        @if (\App\Support\Context::hasPermission('billing.manage'))
                            <form method="POST" action="{{ route('billing.storage.recalculate') }}" class="inline">
                                @csrf
                                <button type="submit"
                                    onclick="return confirm(@js(__('billing.storage_recalculate_confirm')))"
                                    class="min-h-[44px] sm:min-h-0 px-3 py-2 rounded-[10px] text-[11px] sm:text-[10px] font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 active:scale-[0.98] transition cursor-pointer flex items-center gap-1.5 shadow-2xs focus-visible:ring-2 focus-visible:ring-cyan-500">
                                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400"
                                        aria-hidden="true"></i>
                                    <span>Recalculate</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">

                    <!-- Per-Business Breakdown -->
                    @if (!empty($storageDetails['business_breakdown']))
                        <div
                            class="bg-white dark:bg-slate-900 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-xs p-5 space-y-4">
                            <div
                                class="flex items-center gap-2 text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-violet-600 dark:text-violet-400"
                                    aria-hidden="true"></i>
                                <span>{{ __('billing.storage_usage_per_business') }}</span>
                            </div>
                            <div class="space-y-3">
                                @foreach ($storageDetails['business_breakdown'] as $biz)
                                    @php
                                        $bizPct =
                                            $sdLimitBytes > 0
                                                ? min(100, round(($biz['used_bytes'] / $sdLimitBytes) * 100, 1))
                                                : 0;
                                    @endphp
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between text-xs">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span
                                                    class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[160px]">
                                                    {{ $biz['name'] }}
                                                </span>
                                                @if (!empty($biz['is_current']))
                                                    <span class="px-1.5 py-0.2 rounded-[6px] text-[9px] font-bold bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border border-cyan-200/80 dark:border-cyan-800/80 shrink-0 font-mono">
                                                        {{ __('billing.active_business') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <span
                                                class="font-mono text-slate-500 dark:text-slate-400 text-[11px] shrink-0 ml-2 tabular-nums">
                                                {{ $biz['used_mb'] }} MB ({{ $bizPct }}%) ·
                                                {{ $biz['files_count'] }} file
                                            </span>
                                        </div>
                                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-1.5 overflow-hidden border border-slate-200 dark:border-slate-800"
                                            role="progressbar" aria-valuenow="{{ $bizPct }}" aria-valuemin="0"
                                            aria-valuemax="100">
                                            <div class="h-full rounded-full {{ !empty($biz['is_current']) ? 'bg-gradient-to-r from-cyan-500 to-teal-400' : 'bg-slate-400 dark:bg-slate-600' }} transition-all duration-500"
                                                style="width: {{ $bizPct }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Category Breakdown -->
                    @if (!empty($storageDetails['category_breakdown']))
                        <div
                            class="bg-white dark:bg-slate-900 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-xs p-5 space-y-4">
                            <div
                                class="flex items-center gap-2 text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                <i data-lucide="pie-chart" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400"
                                    aria-hidden="true"></i>
                                <span>{{ __('billing.storage_usage_per_category') }}</span>
                            </div>
                            <div class="space-y-2.5">
                                @foreach ($storageDetails['category_breakdown'] as $cat)
                                    <div class="flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 shrink-0"></span>
                                            <span
                                                class="font-medium text-slate-700 dark:text-slate-300 truncate">{{ $cat['label'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0 ml-2">
                                            <span
                                                class="font-mono text-slate-500 dark:text-slate-400 text-[11px] tabular-nums">{{ $cat['used_mb'] }}
                                                MB</span>
                                            <span
                                                class="rounded-[6px] px-1.5 py-0.5 text-[9px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-mono tabular-nums">{{ $cat['percentage'] }}%</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Largest Files (Top 10): Table-to-Card Pattern -->
                @if (!empty($storageDetails['largest_files']))
                    <div
                        class="bg-white dark:bg-slate-900 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-xs p-4 sm:p-5 space-y-3">
                        <div
                            class="flex items-center gap-2 text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            <i data-lucide="file-search" class="w-3.5 h-3.5 text-rose-500 dark:text-rose-400"
                                aria-hidden="true"></i>
                            <span>{{ __('billing.largest_files_heading') }}</span>
                        </div>

                        <!-- Desktop Table View -->
                        <div class="hidden md:block overflow-x-auto -mx-5 px-5">
                            <table class="w-full text-xs min-w-[540px]" aria-label="{{ __('billing.largest_files_heading') }}">
                                <thead>
                                    <tr
                                        class="text-[10px] uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                                        <th scope="col" class="py-2.5 text-left font-semibold">{{ __('billing.table_file_name') }}</th>
                                        <th scope="col" class="py-2.5 text-left font-semibold">{{ __('billing.table_business') }}</th>
                                        <th scope="col" class="py-2.5 text-left font-semibold">{{ __('billing.table_category') }}</th>
                                        <th scope="col" class="py-2.5 text-right font-semibold">{{ __('billing.table_size') }}</th>
                                        <th scope="col" class="py-2.5 text-right font-semibold">{{ __('billing.table_uploaded') }}</th>
                                        <th scope="col" class="py-2.5 text-center font-semibold whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('billing.table_action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                    @foreach ($storageDetails['largest_files'] as $lf)
                                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors group">
                                            <td class="py-2.5 pr-3 font-medium text-slate-800 dark:text-slate-200 truncate max-w-[180px]"
                                                title="{{ $lf['file_name'] }}">
                                                {{ $lf['file_name'] }}
                                            </td>
                                            <td
                                                class="py-2.5 pr-3 text-slate-600 dark:text-slate-400 truncate max-w-[140px]">
                                                {{ $lf['business_name'] }}</td>
                                            <td class="py-2.5 pr-3">
                                                <span
                                                    class="rounded-[6px] px-2 py-0.5 text-[9px] font-bold bg-cyan-50 dark:bg-cyan-950/40 text-cyan-700 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/60 font-mono">
                                                    {{ $lf['category_label'] }}
                                                </span>
                                            </td>
                                            <td
                                                class="py-2.5 text-right font-mono font-bold text-slate-800 dark:text-slate-200 tabular-nums">
                                                {{ $lf['formatted_size'] }}</td>
                                            <td class="py-2.5 text-right text-slate-500 dark:text-slate-400 font-mono tabular-nums">
                                                {{ $lf['uploaded_at'] }}</td>
                                            <td class="py-2.5 text-center whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                                @if (\App\Support\Context::hasPermission('billing.manage'))
                                                    <form method="POST" action="{{ route('billing.storage.files.destroy', $lf['id']) }}" class="inline-flex items-center justify-center"
                                                        onsubmit="return confirm({{ json_encode(__('billing.storage_delete_confirm_named', ['name' => $lf['file_name']])) }})">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" title="{{ __('billing.delete_file_tooltip') }}" aria-label="{{ __('billing.delete_file_tooltip') }}"
                                                            class="w-9 h-9 min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 sm:w-8 sm:h-8 rounded-[8px] flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer shrink-0">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card List View -->
                        <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/80 -mx-4">
                            @foreach ($storageDetails['largest_files'] as $lf)
                                <div class="p-3.5 space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="font-semibold text-xs text-slate-900 dark:text-white truncate"
                                            title="{{ $lf['file_name'] }}">
                                            {{ $lf['file_name'] }}
                                        </div>
                                        <span class="font-mono font-bold text-xs text-slate-900 dark:text-white shrink-0 tabular-nums">
                                            {{ $lf['formatted_size'] }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                        <span class="truncate">{{ $lf['business_name'] }}</span>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span
                                                class="rounded-[6px] px-1.5 py-0.2 text-[9px] font-bold bg-cyan-50 dark:bg-cyan-950/40 text-cyan-700 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/60 font-mono">
                                                {{ $lf['category_label'] }}
                                            </span>
                                            <span class="font-mono tabular-nums">{{ $lf['uploaded_at'] }}</span>
                                            @if (\App\Support\Context::hasPermission('billing.manage'))
                                                <form method="POST" action="{{ route('billing.storage.files.destroy', $lf['id']) }}" class="inline"
                                                    onsubmit="return confirm({{ json_encode(__('billing.storage_delete_confirm_named', ['name' => $lf['file_name']])) }})">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="{{ __('billing.delete_file_tooltip') }}" aria-label="{{ __('billing.delete_file_tooltip') }}"
                                                        class="min-h-[44px] min-w-[44px] flex items-center justify-center text-slate-400 hover:text-rose-600 transition cursor-pointer p-1">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($sdIsOver)
                    <div
                        class="flex items-start gap-3 p-4 rounded-[16px] bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-500/30 text-xs">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5"
                            aria-hidden="true"></i>
                        <div>
                            <strong class="text-rose-800 dark:text-rose-300">{{ __('billing.storage_over_limit_title') }}</strong>
                            <span class="text-rose-700 dark:text-rose-400 ml-1">{{ __('billing.storage_over_limit_desc', ['used' => $storageDetails['used_mb'], 'limit' => $storageDetails['limit_gb']]) }}@if (\App\Support\Context::hasPermission('billing.manage')) {!! __('billing.storage_exceeded_cta', ['link' => '<a href="' . route('billing.checkout', ['type' => 'storage']) . '" class="underline font-bold hover:text-rose-900 dark:hover:text-rose-200">' . __('billing.topup_storage') . '</a>']) !!}@endif</span>
                        </div>
                    </div>
                @endif
            </section>
        @endif

        <!-- 5. SECTION 2: Monthly Commercial Quotas (POS, B2B Invoices, Purchase Orders) -->
        <section aria-labelledby="monthly-quotas-heading" class="space-y-4">
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-slate-200 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar-check" class="w-4 h-4 text-purple-600 dark:text-purple-400"
                        aria-hidden="true"></i>
                    <h2 id="monthly-quotas-heading"
                        class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">
                        {{ __('billing.monthly_quotas_heading') }}
                    </h2>
                </div>
                <span class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-mono">{{ __('billing.monthly_quotas_reset_notice') }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4 lg:gap-5">
                <!-- 1. POS Orders -->
                @php
                    $pos = $usage['pos_this_month'] ?? [];
                    $posPercent = $usage['is_core'] ? 100 : min(100, max(0, (int) ($pos['percent'] ?? 0)));
                    $posReached = $pos['is_reached'] ?? false;
                @endphp
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border {{ $posReached ? 'border-rose-300 dark:border-rose-500/50 bg-rose-50/50 dark:bg-rose-950/15' : 'border-black/[0.06] dark:border-white/[0.08]' }} shadow-xs p-4 sm:p-5 space-y-3 flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('billing.pos_cashier_transactions') }}</span>
                            <div class="p-1.5 rounded-[8px] bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                                aria-hidden="true">
                                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                            </div>
                        </div>

                        <div class="mt-2 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono tabular-nums {{ $posReached ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                                {{ number_format($pos['used'] ?? 0, 0, ',', '.') }}
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                /
                                {{ $pos['limit'] ? number_format($pos['limit'], 0, ',', '.') . ' struk' : '∞ Unlimited' }}
                            </span>
                        </div>

                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-2 overflow-hidden mt-3 border border-slate-200 dark:border-slate-800"
                            role="progressbar" aria-valuenow="{{ $posPercent }}" aria-valuemin="0"
                            aria-valuemax="100" aria-label="{{ __('billing.pos_transactions') }}">
                            <div class="h-full rounded-full transition-all duration-500 {{ $posReached ? 'bg-rose-500' : ($posPercent >= 80 ? 'bg-amber-400' : 'bg-emerald-500') }}"
                                style="width: {{ $posPercent }}%"></div>
                        </div>
                    </div>

                    <div
                        class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px]">
                        <span
                            class="text-slate-500 dark:text-slate-400">{{ $usage['is_core'] ? __('billing.unlimited_pos_tx') : __('billing.limit_free_receipts') }}</span>
                        @if ($posReached)
                            <span
                                class="rounded-[6px] px-2 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-200/90 dark:border-rose-800/90">{{ __('billing.limit_reached_badge') }}</span>
                        @elseif($usage['is_core'])
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold font-mono">UNLIMITED</span>
                        @endif
                    </div>
                </div>

                <!-- 2. Invoices (B2B) -->
                @php
                    $inv = $usage['invoices_this_month'] ?? [];
                    $invPercent = $usage['is_core'] ? 100 : min(100, max(0, (int) ($inv['percent'] ?? 0)));
                    $invReached = $inv['is_reached'] ?? false;
                @endphp
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border {{ $invReached ? 'border-rose-300 dark:border-rose-500/50 bg-rose-50/50 dark:bg-rose-950/15' : 'border-black/[0.06] dark:border-white/[0.08]' }} shadow-xs p-4 sm:p-5 space-y-3 flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('billing.b2b_invoices') }}</span>
                            <div class="p-1.5 rounded-[8px] bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400"
                                aria-hidden="true">
                                <i data-lucide="receipt" class="w-4 h-4"></i>
                            </div>
                        </div>

                        <div class="mt-2 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono tabular-nums {{ $invReached ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                                {{ number_format($inv['used'] ?? 0, 0, ',', '.') }}
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                /
                                {{ $inv['limit'] ? number_format($inv['limit'], 0, ',', '.') . ' ' . __('billing.invoices_unit') : __('billing.unlimited') }}
                            </span>
                        </div>

                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-2 overflow-hidden mt-3 border border-slate-200 dark:border-slate-800"
                            role="progressbar" aria-valuenow="{{ $invPercent }}" aria-valuemin="0"
                            aria-valuemax="100" aria-label="{{ __('billing.b2b_invoices') }}">
                            <div class="h-full rounded-full transition-all duration-500 {{ $invReached ? 'bg-rose-500' : ($invPercent >= 80 ? 'bg-amber-400' : 'bg-purple-500') }}"
                                style="width: {{ $invPercent }}%"></div>
                        </div>
                    </div>

                    <div
                        class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px]">
                        <span
                            class="text-slate-500 dark:text-slate-400">{{ $usage['is_core'] ? __('billing.unlimited_digital_invoices') : __('billing.limit_free_invoices') }}</span>
                        @if ($invReached)
                            <span
                                class="rounded-[6px] px-2 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-200/90 dark:border-rose-800/90">{{ __('billing.limit_reached_badge') }}</span>
                        @elseif($usage['is_core'])
                            <span class="text-purple-600 dark:text-purple-400 font-bold font-mono">UNLIMITED</span>
                        @endif
                    </div>
                </div>

                <!-- 3. Purchase Orders (PO) -->
                @php
                    $po = $usage['po_this_month'] ?? [];
                    $poPercent = $usage['is_core'] ? 100 : min(100, max(0, (int) ($po['percent'] ?? 0)));
                    $poReached = $po['is_reached'] ?? false;
                @endphp
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border {{ $poReached ? 'border-rose-300 dark:border-rose-500/50 bg-rose-50/50 dark:bg-rose-950/15' : 'border-black/[0.06] dark:border-white/[0.08]' }} shadow-xs p-4 sm:p-5 space-y-3 flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('billing.purchase_orders') }}</span>
                            <div class="p-1.5 rounded-[8px] bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400"
                                aria-hidden="true">
                                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                            </div>
                        </div>

                        <div class="mt-2 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono tabular-nums {{ $poReached ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                                {{ number_format($po['used'] ?? 0, 0, ',', '.') }}
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                / {{ $po['limit'] ? number_format($po['limit'], 0, ',', '.') . ' PO' : __('billing.unlimited') }}
                            </span>
                        </div>

                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-2 overflow-hidden mt-3 border border-slate-200 dark:border-slate-800"
                            role="progressbar" aria-valuenow="{{ $poPercent }}" aria-valuemin="0"
                            aria-valuemax="100" aria-label="{{ __('billing.purchase_orders') }}">
                            <div class="h-full rounded-full transition-all duration-500 {{ $poReached ? 'bg-rose-500' : ($poPercent >= 80 ? 'bg-amber-400' : 'bg-cyan-500') }}"
                                style="width: {{ $poPercent }}%"></div>
                        </div>
                    </div>

                    <div
                        class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px]">
                        <span
                            class="text-slate-500 dark:text-slate-400">{{ $usage['is_core'] ? __('billing.unlimited_supplier_po') : __('billing.limit_free_po') }}</span>
                        @if ($poReached)
                            <span
                                class="rounded-[6px] px-2 py-0.5 text-[10px] font-bold border inline-flex items-center gap-1 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-200/90 dark:border-rose-800/90">{{ __('billing.limit_reached_badge') }}</span>
                        @elseif($usage['is_core'])
                            <span class="text-cyan-600 dark:text-cyan-400 font-bold font-mono">UNLIMITED</span>
                        @endif
                    </div>
                </div>

                <!-- 4. Media Sosial Scheduler (Add-on / Freemium) -->
                @php
                    $soc = $usage['social_posts_this_month'] ?? [];
                    $hasSocAddon = !empty($usage['has_social_addon']);
                    $socPercent = $hasSocAddon ? 100 : min(100, max(0, (int) ($soc['percent'] ?? 0)));
                    $socReached = $soc['is_reached'] ?? false;
                @endphp
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border {{ $socReached ? 'border-rose-300 dark:border-rose-500/50 bg-rose-50/50 dark:bg-rose-950/15' : 'border-black/[0.06] dark:border-white/[0.08]' }} shadow-xs p-4 sm:p-5 space-y-3 flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('billing.social_media') }}</span>
                            <div class="p-1.5 rounded-[8px] bg-pink-50 dark:bg-pink-500/10 text-pink-600 dark:text-pink-400"
                                aria-hidden="true">
                                <i data-lucide="share-2" class="w-4 h-4"></i>
                            </div>
                        </div>

                        <div class="mt-2 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono tabular-nums {{ $socReached ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                                {{ number_format($soc['used'] ?? 0, 0, ',', '.') }}
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                / {{ $soc['limit'] ? number_format($soc['limit'], 0, ',', '.') . ' ' . __('billing.quota_unit_posts') : __('billing.unlimited') }}
                            </span>
                        </div>

                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-2 overflow-hidden mt-3 border border-slate-200 dark:border-slate-800"
                            role="progressbar" aria-valuenow="{{ $socPercent }}" aria-valuemin="0"
                            aria-valuemax="100" aria-label="{{ __('billing.social_media') }}">
                            <div class="h-full rounded-full transition-all duration-500 {{ $socReached ? 'bg-rose-500' : ($socPercent >= 80 ? 'bg-amber-400' : 'bg-pink-500') }}"
                                style="width: {{ $socPercent }}%"></div>
                        </div>
                    </div>

                    <div
                        class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px]">
                        <span
                            class="text-slate-500 dark:text-slate-400">{{ $hasSocAddon ? __('billing.addon_active_badge') : __('billing.limit_free_content') }}</span>
                        @if ($socReached)
                            <button type="button"
                                @click="window.dispatchEvent(new CustomEvent('open-quota-modal', {
                                    detail: {
                                        title: @js(__('billing.quota_modal_social_title')),
                                        desc: @js(__('billing.quota_modal_social_desc')),
                                        used: {{ $soc['used'] ?? 0 }},
                                        limit: 3,
                                        unit: @js(__('billing.quota_unit_posts')),
                                        upgradeUrl: '{{ route('billing.checkout') }}',
                                        upgradeFee: 'Rp 89.000/bln',
                                        isAddon: true
                                    }
                                }))"
                                class="rounded-[6px] px-2 py-1 text-[10px] font-bold border inline-flex items-center gap-1 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-200/90 dark:border-rose-800/90 cursor-pointer hover:bg-rose-100 transition min-h-[44px]">
                                {{ __('billing.limit_reached_badge') }}
                            </button>
                        @elseif($hasSocAddon)
                            <span class="text-pink-600 dark:text-pink-400 font-bold font-mono">UNLIMITED</span>
                        @endif
                    </div>
                </div>

                <!-- 5. WhatsApp Gateway (Notification & Receipts) -->
                @php
                    $wa = $usage['whatsapp_this_month'] ?? [];
                    $waPercent = $usage['is_core'] ? 100 : min(100, max(0, (int) ($wa['percent'] ?? 0)));
                    $waReached = $wa['is_reached'] ?? false;
                @endphp
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border {{ $waReached ? 'border-rose-300 dark:border-rose-500/50 bg-rose-50/50 dark:bg-rose-950/15' : 'border-black/[0.06] dark:border-white/[0.08]' }} shadow-xs p-4 sm:p-5 space-y-3 flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('billing.whatsapp_gateway') }}</span>
                            <div class="p-1.5 rounded-[8px] bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                                aria-hidden="true">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                            </div>
                        </div>

                        <div class="mt-2 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono tabular-nums {{ $waReached ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">
                                {{ number_format($wa['used'] ?? 0, 0, ',', '.') }}
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                / {{ $wa['limit'] ? number_format($wa['limit'], 0, ',', '.') . ' ' . __('billing.quota_unit_messages') : __('billing.unlimited') }}
                            </span>
                        </div>

                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-2 overflow-hidden mt-3 border border-slate-200 dark:border-slate-800"
                            role="progressbar" aria-valuenow="{{ $waPercent }}" aria-valuemin="0"
                            aria-valuemax="100" aria-label="{{ __('billing.whatsapp_gateway') }}">
                            <div class="h-full rounded-full transition-all duration-500 {{ $waReached ? 'bg-rose-500' : ($waPercent >= 80 ? 'bg-amber-400' : 'bg-emerald-500') }}"
                                style="width: {{ $waPercent }}%"></div>
                        </div>
                    </div>

                    <div
                        class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px]">
                        <span
                            class="text-slate-500 dark:text-slate-400">{{ $usage['is_core'] ? __('billing.unlimited_wa_notifications') : __('billing.limit_free_wa') }}</span>
                        @if ($waReached)
                            <button type="button"
                                @click="window.dispatchEvent(new CustomEvent('open-quota-modal', {
                                    detail: {
                                        title: @js(__('billing.quota_modal_wa_title')),
                                        desc: @js(__('billing.quota_modal_wa_desc')),
                                        used: {{ $wa['used'] ?? 0 }},
                                        limit: 10,
                                        unit: @js(__('billing.quota_unit_messages')),
                                        upgradeUrl: '{{ route('billing.checkout') }}',
                                        upgradeFee: 'Rp 49.000/bln'
                                    }
                                }))"
                                class="rounded-[6px] px-2 py-1 text-[10px] font-bold border inline-flex items-center gap-1 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-200/90 dark:border-rose-800/90 cursor-pointer hover:bg-rose-100 transition min-h-[44px]">
                                {{ __('billing.limit_reached_badge') }}
                            </button>
                        @elseif($usage['is_core'])
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold font-mono">UNLIMITED</span>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. SECTION 3: Master Data & Catalog Capacity (8-card grid) -->
        <section aria-labelledby="catalog-capacity-heading" class="space-y-4">
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-slate-200 dark:border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="database" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"
                        aria-hidden="true"></i>
                    <h2 id="catalog-capacity-heading"
                        class="text-xs sm:text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">
                        {{ __('billing.master_data_capacity_heading') }}
                    </h2>
                </div>
                <span class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-mono">{{ __('billing.master_data_capacity_subtitle') }}</span>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-5">
                <!-- 1. Katalog Produk -->
                @php $prd = $usage['products'] ?? []; @endphp
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border {{ $prd['is_reached'] ?? false ? 'border-rose-300 dark:border-rose-500/50 bg-rose-50/50 dark:bg-rose-950/15' : 'border-black/[0.06] dark:border-white/[0.08]' }} shadow-xs p-4 space-y-2 flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('billing.resource_products') }}</span>
                            <i data-lucide="box" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"
                                aria-hidden="true"></i>
                        </div>
                        <div class="mt-1.5 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono tabular-nums {{ $prd['is_reached'] ?? false ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">{{ $prd['used'] ?? 0 }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">/
                                {{ $prd['limit'] ? $prd['limit'] . ' ' . __('billing.unit_items') : __('billing.unlimited') }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-200 dark:border-slate-800"
                            role="progressbar"
                            aria-valuenow="{{ $usage['is_core'] ? 100 : min(100, $prd['percent'] ?? 0) }}"
                            aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('billing.resource_products') }}">
                            <div class="h-full rounded-full transition-all duration-500 {{ $prd['is_reached'] ?? false ? 'bg-rose-500' : 'bg-emerald-500' }}"
                                style="width: {{ $usage['is_core'] ? 100 : min(100, $prd['percent'] ?? 0) }}%"></div>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 pt-1 font-mono">
                        {{ $usage['is_core'] ? __('billing.unlimited_product_catalog') : __('billing.limit_free_products') }}</div>
                </div>

                <!-- 2. Bahan Baku -->
                @if($business->isModuleEnabled('recipe_bom') || in_array($business->industry, ['fnb_resto', 'fnb_cafe', 'fnb_bakery', 'fnb_street_food', 'manufacturing']))
                @php $mat = $usage['materials'] ?? []; @endphp
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border {{ $mat['is_reached'] ?? false ? 'border-rose-300 dark:border-rose-500/50 bg-rose-50/50 dark:bg-rose-950/15' : 'border-black/[0.06] dark:border-white/[0.08]' }} shadow-xs p-4 space-y-2 flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('billing.resource_materials') }}</span>
                            <i data-lucide="layers" class="w-4 h-4 text-cyan-600 dark:text-cyan-400"
                                aria-hidden="true"></i>
                        </div>
                        <div class="mt-1.5 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono tabular-nums {{ $mat['is_reached'] ?? false ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">{{ $mat['used'] ?? 0 }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">/
                                {{ $mat['limit'] ? $mat['limit'] . ' ' . __('billing.unit_items') : __('billing.unlimited') }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-200 dark:border-slate-800"
                            role="progressbar"
                            aria-valuenow="{{ $usage['is_core'] ? 100 : min(100, $mat['percent'] ?? 0) }}"
                            aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('billing.resource_materials') }}">
                            <div class="h-full rounded-full transition-all duration-500 {{ $mat['is_reached'] ?? false ? 'bg-rose-500' : 'bg-cyan-500' }}"
                                style="width: {{ $usage['is_core'] ? 100 : min(100, $mat['percent'] ?? 0) }}%"></div>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 pt-1 font-mono">
                        {{ $usage['is_core'] ? __('billing.unlimited_raw_materials') : __('billing.limit_free_materials') }}</div>
                </div>
                @endif

                <!-- 3. Resep HPP (BOM) -->
                @if($business->isModuleEnabled('recipe_bom') || in_array($business->industry, ['fnb_resto', 'fnb_cafe', 'fnb_bakery', 'fnb_street_food', 'manufacturing']))
                @php $rcp = $usage['recipes'] ?? []; @endphp
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border {{ $rcp['is_reached'] ?? false ? 'border-rose-300 dark:border-rose-500/50 bg-rose-50/50 dark:bg-rose-950/15' : 'border-black/[0.06] dark:border-white/[0.08]' }} shadow-xs p-4 space-y-2 flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('billing.resource_recipes') }}</span>
                            <i data-lucide="chef-hat" class="w-4 h-4 text-amber-600 dark:text-amber-400"
                                aria-hidden="true"></i>
                        </div>
                        <div class="mt-1.5 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono tabular-nums {{ $rcp['is_reached'] ?? false ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">{{ $rcp['used'] ?? 0 }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">/
                                {{ $rcp['limit'] ? $rcp['limit'] . ' ' . __('billing.unit_recipes') : __('billing.unlimited') }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-200 dark:border-slate-800"
                            role="progressbar"
                            aria-valuenow="{{ $usage['is_core'] ? 100 : min(100, $rcp['percent'] ?? 0) }}"
                            aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('billing.resource_recipes') }}">
                            <div class="h-full rounded-full transition-all duration-500 {{ $rcp['is_reached'] ?? false ? 'bg-rose-500' : 'bg-amber-500' }}"
                                style="width: {{ $usage['is_core'] ? 100 : min(100, $rcp['percent'] ?? 0) }}%"></div>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 pt-1 font-mono">
                        {{ $usage['is_core'] ? __('billing.unlimited_recipes') : __('billing.limit_free_recipes') }}</div>
                </div>
                @endif

                <!-- 4. Pelanggan CRM -->
                @php $cst = $usage['customers'] ?? []; @endphp
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border {{ $cst['is_reached'] ?? false ? 'border-rose-300 dark:border-rose-500/50 bg-rose-50/50 dark:bg-rose-950/15' : 'border-black/[0.06] dark:border-white/[0.08]' }} shadow-xs p-4 space-y-2 flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('billing.resource_customers') }}</span>
                            <i data-lucide="users" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"
                                aria-hidden="true"></i>
                        </div>
                        <div class="mt-1.5 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono tabular-nums {{ $cst['is_reached'] ?? false ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">{{ $cst['used'] ?? 0 }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">/
                                {{ $cst['limit'] ? $cst['limit'] . ' ' . __('billing.unit_contacts') : __('billing.unlimited') }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-200 dark:border-slate-800"
                            role="progressbar"
                            aria-valuenow="{{ $usage['is_core'] ? 100 : min(100, $cst['percent'] ?? 0) }}"
                            aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('billing.resource_customers') }}">
                            <div class="h-full rounded-full transition-all duration-500 {{ $cst['is_reached'] ?? false ? 'bg-rose-500' : 'bg-indigo-500' }}"
                                style="width: {{ $usage['is_core'] ? 100 : min(100, $cst['percent'] ?? 0) }}%"></div>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 pt-1 font-mono">
                        {{ $usage['is_core'] ? __('billing.unlimited_contacts_db') : __('billing.limit_free_contacts') }}</div>
                </div>

                <!-- 5. Pemasok / Supplier -->
                @php $sup = $usage['suppliers'] ?? []; @endphp
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border {{ $sup['is_reached'] ?? false ? 'border-rose-300 dark:border-rose-500/50 bg-rose-50/50 dark:bg-rose-950/15' : 'border-black/[0.06] dark:border-white/[0.08]' }} shadow-xs p-4 space-y-2 flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('billing.resource_suppliers') }}</span>
                            <i data-lucide="truck" class="w-4 h-4 text-rose-600 dark:text-rose-400"
                                aria-hidden="true"></i>
                        </div>
                        <div class="mt-1.5 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono tabular-nums {{ $sup['is_reached'] ?? false ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">{{ $sup['used'] ?? 0 }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">/
                                {{ $sup['limit'] ? $sup['limit'] . ' ' . __('billing.unit_vendors') : __('billing.unlimited') }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-200 dark:border-slate-800"
                            role="progressbar"
                            aria-valuenow="{{ $usage['is_core'] ? 100 : min(100, $sup['percent'] ?? 0) }}"
                            aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('billing.resource_suppliers') }}">
                            <div class="h-full rounded-full transition-all duration-500 {{ $sup['is_reached'] ?? false ? 'bg-rose-500' : 'bg-rose-500' }}"
                                style="width: {{ $usage['is_core'] ? 100 : min(100, $sup['percent'] ?? 0) }}%"></div>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 pt-1 font-mono">
                        {{ $usage['is_core'] ? __('billing.unlimited_vendors_list') : __('billing.limit_free_vendors') }}</div>
                </div>

                <!-- 6. Outlet & Gudang -->
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-xs p-4 space-y-2 flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('billing.resource_outlets') }}</span>
                            <i data-lucide="store" class="w-4 h-4 text-teal-600 dark:text-teal-400"
                                aria-hidden="true"></i>
                        </div>
                        <div class="mt-1.5 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono tabular-nums text-slate-900 dark:text-white">{{ ($usage['outlets']['used'] ?? 0) + ($usage['warehouses']['used'] ?? 0) }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">/
                                {{ $usage['is_core'] ? __('billing.unlimited') : __('billing.one_store_one_warehouse') }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-200 dark:border-slate-800"
                            role="progressbar"
                            aria-valuenow="{{ $usage['is_core'] ? 100 : min(100, $usage['outlets']['percent'] ?? 0) }}"
                            aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('billing.resource_outlets') }}">
                            <div class="h-full rounded-full bg-teal-500 transition-all duration-500"
                                style="width: {{ $usage['is_core'] ? 100 : min(100, $usage['outlets']['percent'] ?? 0) }}%">
                            </div>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 pt-1 font-mono">
                        {{ $usage['outlets']['limit'] ? __('billing.max_branches_format', ['limit' => $usage['outlets']['limit']]) : __('billing.unlimited_branches_warehouses') }}</div>
                </div>

                <!-- 7. Pengguna / Karyawan -->
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-xs p-4 space-y-2 flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('billing.resource_staff') }}</span>
                            <i data-lucide="user-check" class="w-4 h-4 text-blue-600 dark:text-blue-400"
                                aria-hidden="true"></i>
                        </div>
                        <div class="mt-1.5 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono tabular-nums text-slate-900 dark:text-white">{{ $usage['users']['used'] ?? 0 }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">/
                                {{ $usage['users']['limit'] ? $usage['users']['limit'] . ' ' . __('billing.unit_staff') : __('billing.unlimited') }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-200 dark:border-slate-800"
                            role="progressbar"
                            aria-valuenow="{{ min(100, $usage['users']['percent'] ?? 0) }}"
                            aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('billing.resource_staff') }}">
                            <div class="h-full rounded-full bg-blue-500 transition-all duration-500"
                                style="width: {{ min(100, $usage['users']['percent'] ?? 0) }}%">
                            </div>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 pt-1 font-mono">
                        {{ $usage['users']['limit'] ? __('billing.max_staff_format', ['limit' => $usage['users']['limit']]) : __('billing.unlimited_team_staff') }}</div>
                </div>

                <!-- 8. Entitas Bisnis -->
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border border-black/[0.06] dark:border-white/[0.08] shadow-xs p-4 space-y-2 flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('billing.resource_businesses') }}</span>
                            <i data-lucide="building-2" class="w-4 h-4 text-violet-600 dark:text-violet-400"
                                aria-hidden="true"></i>
                        </div>
                        <div class="mt-1.5 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono tabular-nums text-slate-900 dark:text-white">{{ $usage['businesses']['used'] ?? 1 }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">/
                                {{ $usage['businesses']['limit'] ? $usage['businesses']['limit'] . ' ' . __('billing.unit_businesses') : __('billing.unlimited') }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-200 dark:border-slate-800"
                            role="progressbar"
                            aria-valuenow="{{ min(100, $usage['businesses']['percent'] ?? 0) }}"
                            aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('billing.resource_businesses') }}">
                            <div class="h-full rounded-full bg-violet-500 transition-all duration-500"
                                style="width: {{ min(100, $usage['businesses']['percent'] ?? 0) }}%">
                            </div>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 pt-1 font-mono">
                        {{ $usage['businesses']['limit'] ? __('billing.max_businesses_format', ['limit' => $usage['businesses']['limit']]) : __('billing.unlimited_multi_business') }}</div>
                </div>

                <!-- 9. Meja Kasir POS (Dine-In) -->
                @if($business->hasDineInFeature())
                @php $tbl = $usage['tables'] ?? []; @endphp
                <div
                    class="bg-white dark:bg-slate-900 rounded-[20px] border {{ $tbl['is_reached'] ?? false ? 'border-rose-300 dark:border-rose-500/50 bg-rose-50/50 dark:bg-rose-950/15' : 'border-black/[0.06] dark:border-white/[0.08]' }} shadow-xs p-4 space-y-2 flex flex-col justify-between transition-all">
                    <div>
                        <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                            <span class="text-xs font-bold uppercase tracking-wider">{{ __('billing.resource_tables') }}</span>
                            <i data-lucide="layout-grid" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"
                                aria-hidden="true"></i>
                        </div>
                        <div class="mt-1.5 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-black font-mono tabular-nums {{ $tbl['is_reached'] ?? false ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white' }}">{{ $tbl['used'] ?? 0 }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono">/
                                {{ $tbl['limit'] ? $tbl['limit'] . ' ' . __('billing.unit_tables') : ($usage['is_core'] ? __('billing.unlimited') : __('billing.zero_tables')) }}</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-1.5 overflow-hidden mt-2 border border-slate-200 dark:border-slate-800"
                            role="progressbar"
                            aria-valuenow="{{ min(100, $tbl['percent'] ?? 0) }}"
                            aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('billing.resource_tables') }}">
                            <div class="h-full rounded-full transition-all duration-500 {{ $tbl['is_reached'] ?? false ? 'bg-rose-500' : 'bg-emerald-500' }}"
                                style="width: {{ min(100, $tbl['percent'] ?? 0) }}%"></div>
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 pt-1 font-mono">
                        {{ $tbl['limit'] ? __('billing.max_tables_format', ['limit' => $tbl['limit']]) : ($usage['is_core'] ? __('billing.unlimited_dinein_tables') : __('billing.paid_feature_standard_premium')) }}</div>
                </div>
                @endif
            </div>
        </section>

        <!-- 7. SECTION 4: Trust, Continuity & Privacy Commitment -->
        <section aria-labelledby="commitment-heading"
            class="rounded-[20px] p-5 sm:p-6 border border-emerald-500/20 dark:border-emerald-500/30 bg-emerald-50/70 dark:bg-emerald-950/20 flex flex-col sm:flex-row items-start sm:items-center gap-4 backdrop-blur-md">
            <div class="p-3 rounded-[16px] bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 shrink-0"
                aria-hidden="true">
                <i data-lucide="heart-handshake" class="w-6 h-6"></i>
            </div>
            <div class="text-xs space-y-1 flex-1">
                <h4 id="commitment-heading" class="font-extrabold text-slate-900 dark:text-white text-sm">{{ __('billing.privacy_commitment_title') }}</h4>
                <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ __('billing.privacy_commitment_desc') }}
                </p>
            </div>
        </section>

        <!-- 9. BENTO MODAL: Storage File Manager (Kelola Semua Berkas) -->
        <template x-teleport="body">
            <div x-show="openFileManager" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-5 overflow-y-auto">
                <!-- Backdrop -->
                <div x-show="openFileManager"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="openFileManager = false"
                    aria-hidden="true"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"></div>

                <!-- Modal Content (Squircle Bento Card) -->
                <div x-show="openFileManager"
                    @click.away="openFileManager = false"
                    @keydown.escape.window="openFileManager = false"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="file-manager-title"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative z-10 w-full max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px] max-h-[90vh] bg-white dark:bg-slate-900 rounded-[24px] border border-black/[0.08] dark:border-white/[0.12] shadow-2xl flex flex-col overflow-hidden my-auto">

                <!-- Modal Header -->
                <div class="px-5 py-4 sm:px-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-[12px] bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-200/80 dark:border-cyan-800/80 flex items-center justify-center text-cyan-600 dark:text-cyan-400 shrink-0">
                            <i data-lucide="hard-drive" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 id="file-manager-title" class="text-sm sm:text-base font-black text-slate-900 dark:text-white tracking-tight">
                                {{ __('billing.storage_manager_title', ['business' => $business->name]) }}
                            </h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                {{ __('billing.storage_manager_subtitle') }}
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="openFileManager = false"
                        class="min-h-[44px] min-w-[44px] p-2 rounded-[12px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer flex items-center justify-center">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Filters & Search Bar -->
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative flex-1 w-full">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" x-model="searchQuery" @input.debounce.300ms="fetchFiles(1)"
                            placeholder="{{ __('billing.modal_search_files') }}"
                            class="w-full pl-9 pr-4 py-2 text-base sm:text-xs rounded-[12px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>
                    <div class="w-full sm:w-56">
                        <select x-model="selectedCategory" @change="fetchFiles(1)"
                            class="w-full px-3 py-2 text-base sm:text-xs rounded-[12px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option value="all">{{ __('billing.modal_all_categories') }}</option>
                            <option value="product_image">{{ __('billing.category_product_image') }}</option>
                            <option value="business_logo">{{ __('billing.category_business_logo') }}</option>
                            <option value="landing_page_image">{{ __('billing.category_landing_page_image') }}</option>
                            <option value="qris_image">{{ __('billing.category_qris_image') }}</option>
                            <option value="expense_receipt">{{ __('billing.category_expense_receipt') }}</option>
                            <option value="community_image">{{ __('billing.category_community_image') }}</option>
                            <option value="owner_avatar">{{ __('billing.category_owner_avatar') }}</option>
                            <option value="feedback_attachment">{{ __('billing.category_feedback_attachment') }}</option>
                        </select>
                    </div>
                </div>

                <!-- File List Body -->
                <div class="flex-1 overflow-y-auto p-4 sm:p-5 min-h-[260px]">
                    <!-- Loading State -->
                    <div x-show="loadingFiles" class="py-12 text-center text-xs text-slate-500">
                        <i data-lucide="loader" class="w-6 h-6 animate-spin mx-auto text-cyan-600 mb-2"></i>
                        <span>{{ __('billing.modal_loading_files') }}</span>
                    </div>

                    <!-- Empty State -->
                    <div x-show="!loadingFiles && files.length === 0" class="py-12 text-center text-xs text-slate-500">
                        <i data-lucide="file-x" class="w-8 h-8 mx-auto text-slate-400 mb-2"></i>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ __('billing.modal_no_files') }}</span>
                    </div>

                    <!-- Files Table (Desktop) -->
                    <div x-show="!loadingFiles && files.length > 0" class="hidden md:block overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-[10px] uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                                    <th class="py-2.5 text-left font-semibold">{{ __('billing.table_file_name') }}</th>
                                    <th class="py-2.5 text-left font-semibold">{{ __('billing.table_business') }}</th>
                                    <th class="py-2.5 text-left font-semibold">{{ __('billing.table_category') }}</th>
                                    <th class="py-2.5 text-right font-semibold">{{ __('billing.table_size') }}</th>
                                    <th class="py-2.5 text-right font-semibold">{{ __('billing.table_uploaded') }}</th>
                                    <th class="py-2.5 text-center font-semibold whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('billing.table_action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                <template x-for="f in files" :key="f.id">
                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors group">
                                        <td class="py-2.5 pr-3 font-medium text-slate-800 dark:text-slate-200 truncate max-w-[220px]" :title="f.file_name" x-text="f.file_name"></td>
                                        <td class="py-2.5 pr-3 text-slate-600 dark:text-slate-400 truncate max-w-[140px]" x-text="f.business_name"></td>
                                        <td class="py-2.5 pr-3">
                                            <span class="rounded-[6px] px-2 py-0.5 text-[9px] font-bold bg-cyan-50 dark:bg-cyan-950/40 text-cyan-700 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/60 font-mono" x-text="f.category_label"></span>
                                        </td>
                                        <td class="py-2.5 text-right font-mono font-bold text-slate-800 dark:text-slate-200 tabular-nums" x-text="f.formatted_size"></td>
                                        <td class="py-2.5 text-right text-slate-500 dark:text-slate-400 font-mono tabular-nums" x-text="f.uploaded_at"></td>
                                        <td class="py-2.5 text-center whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                            <div class="flex items-center justify-center">
                                                <button type="button" @click="confirmDelete(f)" title="{{ __('billing.storage_delete_title') }}" aria-label="{{ __('billing.storage_delete_title') }}"
                                                    class="w-8 h-8 rounded-[8px] flex items-center justify-center text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer shrink-0">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Files Card List (Mobile) -->
                    <div x-show="!loadingFiles && files.length > 0" class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/80">
                        <template x-for="f in files" :key="f.id">
                            <div class="py-3 space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="font-semibold text-xs text-slate-900 dark:text-white truncate" x-text="f.file_name"></div>
                                    <span class="font-mono font-bold text-xs text-slate-900 dark:text-white shrink-0 tabular-nums" x-text="f.formatted_size"></span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                                    <span class="truncate" x-text="f.business_name"></span>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-[6px] px-1.5 py-0.2 text-[9px] font-bold bg-cyan-50 dark:bg-cyan-950/40 text-cyan-700 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/60 font-mono" x-text="f.category_label"></span>
                                        <span class="font-mono tabular-nums text-[10px]" x-text="f.uploaded_at"></span>
                                    </div>
                                </div>
                                <div class="pt-1 flex justify-end">
                                    <button type="button" @click="confirmDelete(f)"
                                        class="min-h-[44px] text-[11px] font-bold text-rose-600 dark:text-rose-400 hover:underline inline-flex items-center gap-1.5 cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('billing.action_delete_file') }}</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Modal Footer with Pagination -->
                <div class="px-5 py-3 sm:px-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30 flex items-center justify-between text-xs">
                    <span class="text-slate-500 dark:text-slate-400 font-mono tabular-nums">
                        {!! __('billing.modal_showing_files', ['count' => '<strong class="text-slate-800 dark:text-slate-200" x-text="files.length"></strong>', 'total' => '<strong class="text-slate-800 dark:text-slate-200" x-text="totalFiles"></strong>']) !!}
                    </span>
                    <div class="flex items-center gap-2">
                        <button type="button" :disabled="currentPage <= 1" @click="fetchFiles(currentPage - 1)"
                            class="min-h-[40px] px-3.5 py-2 rounded-[10px] text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 disabled:opacity-40 cursor-pointer disabled:cursor-not-allowed transition">
                            {{ __('billing.modal_prev') }}
                        </button>
                        <span class="font-mono text-xs text-slate-500 tabular-nums px-1" x-text="currentPage + ' / ' + lastPage"></span>
                        <button type="button" :disabled="currentPage >= lastPage" @click="fetchFiles(currentPage + 1)"
                            class="min-h-[40px] px-3.5 py-2 rounded-[10px] text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 disabled:opacity-40 cursor-pointer disabled:cursor-not-allowed transition">
                            {{ __('billing.modal_next') }}
                        </button>
                    </div>
                </div>

                <!-- 2-Step Bento Confirmation Modal Sheet -->
                <template x-teleport="body">
                    <div x-show="confirmDeleteModal" x-cloak
                        class="fixed inset-0 z-[220] flex items-center justify-center p-4 overflow-y-auto">
                        <!-- Backdrop -->
                        <div class="fixed inset-0 bg-black/70 dark:bg-black/80 backdrop-blur-md transition-opacity"
                            x-show="confirmDeleteModal"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            @click="if (!deletingFile) confirmDeleteModal = false"
                            aria-hidden="true"></div>

                        <div @click.away="if (!deletingFile) confirmDeleteModal = false"
                            @keydown.escape.window="if (!deletingFile) confirmDeleteModal = false"
                            role="alertdialog"
                            aria-modal="true"
                            aria-labelledby="modal-confirm-delete-title"
                            x-show="confirmDeleteModal"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="relative z-10 bg-white dark:bg-slate-900 rounded-[24px] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl max-w-md w-full p-6 space-y-4 my-auto">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[12px] bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 id="modal-confirm-delete-title" class="font-bold text-sm text-slate-900 dark:text-white">{{ __('billing.modal_confirm_delete_title') }}</h3>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ __('billing.modal_confirm_delete_desc') }}</p>
                                </div>
                            </div>
                            <template x-if="targetFile">
                                <div class="p-3.5 rounded-[14px] bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 space-y-1.5 text-xs">
                                    <div class="font-semibold text-slate-900 dark:text-white truncate" x-text="targetFile.file_name"></div>
                                    <div class="flex items-center justify-between text-[11px] text-slate-500 font-mono">
                                        <span x-text="targetFile.category_label"></span>
                                        <span class="font-bold text-slate-700 dark:text-slate-300 tabular-nums" x-text="targetFile.formatted_size"></span>
                                    </div>
                                </div>
                            </template>
                            <div class="flex items-center justify-end gap-2.5 pt-2">
                                <button type="button" :disabled="deletingFile" @click="confirmDeleteModal = false; targetFile = null"
                                    class="min-h-[44px] px-4 py-2.5 rounded-[12px] text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer">
                                    {{ __('billing.modal_cancel') }}
                                </button>
                                <button type="button" :disabled="deletingFile" @click="executeDelete()"
                                    class="min-h-[44px] px-4 py-2.5 rounded-[12px] text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 transition cursor-pointer flex items-center gap-1.5 disabled:opacity-60 shadow-xs">
                                    <span x-show="deletingFile" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                    <span x-text="deletingFile ? '{{ __('billing.modal_deleting') }}' : '{{ __('billing.modal_delete_permanently') }}'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </template>

        <!-- Non-disruptive Toast Notification -->
        <div x-show="toastMessage" x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-6 right-6 z-70 px-4 py-3 rounded-[16px] bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 border border-slate-700 dark:border-slate-300 shadow-2xl flex items-center gap-2.5 text-xs font-semibold">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400 dark:text-emerald-600"></i>
            <span x-text="toastMessage"></span>
        </div>

    </div>

    <script>
    function storageLimitsManager() {
        return {
            pricingCycle: 'monthly',
            openFileManager: false,
            loadingFiles: false,
            files: [],
            searchQuery: '',
            selectedCategory: 'all',
            currentPage: 1,
            lastPage: 1,
            totalFiles: 0,
            confirmDeleteModal: false,
            targetFile: null,
            deletingFile: false,
            toastMessage: '',
            toastTimer: null,

            showToast(msg) {
                this.toastMessage = msg;
                if (this.toastTimer) clearTimeout(this.toastTimer);
                this.toastTimer = setTimeout(() => {
                    this.toastMessage = '';
                }, 3500);
            },

            openModal() {
                this.openFileManager = true;
                this.fetchFiles(1);
            },

            async fetchFiles(page = 1) {
                this.loadingFiles = true;
                this.currentPage = page;
                try {
                    const params = new URLSearchParams({
                        page: page,
                        q: this.searchQuery,
                        category: this.selectedCategory
                    });
                    const res = await fetch(`{{ route('billing.storage.files') }}?${params.toString()}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        this.files = data.data || [];
                        this.currentPage = data.current_page || 1;
                        this.lastPage = data.last_page || 1;
                        this.totalFiles = data.total || 0;
                        this.$nextTick(() => {
                            if (window.lucide) { window.lucide.createIcons(); }
                        });
                    }
                } catch (err) {
                    console.error('Gagal memuat berkas storage:', err);
                } finally {
                    this.loadingFiles = false;
                }
            },

            confirmDelete(file) {
                this.targetFile = file;
                this.confirmDeleteModal = true;
                this.$nextTick(() => {
                    if (window.lucide) { window.lucide.createIcons(); }
                });
            },

            async executeDelete() {
                if (!this.targetFile) return;
                this.deletingFile = true;
                try {
                    const res = await fetch(this.targetFile.delete_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    });
                    if (res.ok) {
                        const data = await res.json();
                        const removedId = this.targetFile.id;
                        this.files = this.files.filter(f => f.id !== removedId);
                        this.totalFiles = Math.max(0, this.totalFiles - 1);
                        this.confirmDeleteModal = false;
                        this.targetFile = null;
                        this.showToast(data.message || @js(__('billing.storage_file_deleted')));
                    } else {
                        const errData = await res.json().catch(() => ({}));
                        this.showToast(errData.message || @js(__('billing.storage_file_delete_failed')));
                    }
                } catch (err) {
                    console.error('Error saat menghapus berkas:', err);
                    this.showToast(@js(__('billing.network_error_toast')));
                } finally {
                    this.deletingFile = false;
                }
            }
        };
    }
    </script>
@endsection
