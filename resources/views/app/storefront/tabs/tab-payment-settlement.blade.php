{{-- TAB 5: PEMBAYARAN, SALDO GATEWAY & SETTLEMENT DANA --}}
<div class="space-y-6">

    {{-- BENTO CARD 1: SALDO GATEWAY & PENARIKAN DANA (TRIPAY ESCROW CLEARING) --}}
    <div
        class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-sm space-y-5">
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-black/5 dark:border-white/10">
            <div class="flex items-center gap-2.5">
                <div
                    class="w-9 h-9 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight">{{ __('storefront.backoffice.tab_payments.gateway_balance_title') }}</h3>
                        <span
                            class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $isGatewayConfigured ? 'bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF9500]/10 text-[#D97706]' }}">
                            {{ $isGatewayConfigured ? __('storefront.backoffice.tab_payments.gateway_active') : __('storefront.backoffice.tab_payments.gateway_ready_config') }}
                        </span>
                    </div>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('storefront.backoffice.tab_payments.gateway_desc') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <a href="{{ route('finance.settlements.index') }}"
                    class="h-9 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12.5px] font-semibold flex items-center gap-1.5 transition active:scale-[0.98] shadow-xs">
                    <i data-lucide="arrow-down-to-dot" class="w-3.5 h-3.5"></i>
                    <span>{{ __('storefront.backoffice.tab_payments.withdraw_to_bank') }}</span>
                </a>
            </div>
        </div>

        {{-- 3 KPI METRICS --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Gross --}}
            <div
                class="p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                <span class="text-[11.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">
                    {{ __('storefront.backoffice.tab_payments.kpi_held_balance') }}
                </span>
                <div class="text-2xl font-extrabold text-black dark:text-white tabular-nums tracking-tight">
                    {{ $business->currency_symbol ?? 'Rp' }}
                    {{ number_format($unsettledData['summary']['total_gross'] ?? 0, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-black/50 dark:text-white/50">
                    {{ __('storefront.backoffice.tab_payments.kpi_held_balance_sub', ['count' => $unsettledData['summary']['count'] ?? 0]) }}
                </p>
            </div>

            {{-- Fee MDR --}}
            <div
                class="p-4.5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1">
                <span class="text-[11.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 block">
                    {{ __('storefront.backoffice.tab_payments.kpi_mdr_fee') }}
                </span>
                <div class="text-2xl font-extrabold text-[#FF3B30] tabular-nums tracking-tight">
                    {{ $business->currency_symbol ?? 'Rp' }}
                    {{ number_format($unsettledData['summary']['total_fee'] ?? 0, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-black/50 dark:text-white/50">
                    {{ __('storefront.backoffice.tab_payments.kpi_mdr_fee_sub') }}
                </p>
            </div>

            {{-- Net Payout --}}
            <div class="p-4.5 rounded-[18px] bg-[#34C759]/5 dark:bg-[#30D158]/5 border border-[#34C759]/20 space-y-1">
                <span class="text-[11.5px] font-bold uppercase tracking-wider text-[#248A3D] dark:text-[#30D158] block">
                    {{ __('storefront.backoffice.tab_payments.kpi_net_balance') }}
                </span>
                <div class="text-2xl font-extrabold text-[#248A3D] dark:text-[#30D158] tabular-nums tracking-tight">
                    {{ $business->currency_symbol ?? 'Rp' }}
                    {{ number_format($unsettledData['summary']['total_net'] ?? 0, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-black/50 dark:text-white/50">
                    {{ __('storefront.backoffice.tab_payments.kpi_net_balance_sub') }}
                </p>
            </div>
        </div>

        {{-- Edukasi Penarikan & Riwayat --}}
        <div
            class="p-4 rounded-[16px] bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-start gap-2.5">
                <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                <p class="text-[12px] text-black/70 dark:text-white/70 leading-relaxed">
                    {{ __('storefront.backoffice.tab_payments.settlement_education') }}
                </p>
            </div>
            <a href="{{ route('finance.settlements.index') }}"
                class="text-[12px] font-bold text-[#007AFF] hover:underline shrink-0 inline-flex items-center gap-1 self-start sm:self-auto">
                <span>{{ __('storefront.backoffice.tab_payments.open_settlements_module') }}</span>
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>

    {{-- BENTO CARD 2: PANDUAN DUA METODE PEMBAYARAN TOKO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Saluran 1: Otomatis Gateway --}}
        <div
            class="p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs space-y-3">
            <div class="flex items-center gap-2 text-[#007AFF]">
                <i data-lucide="zap" class="w-5 h-5"></i>
                <h4 class="text-[14px] font-bold text-black dark:text-white">{{ __('storefront.backoffice.tab_payments.channel_1_title') }}</h4>
            </div>
            <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                {!! __('storefront.backoffice.tab_payments.channel_1_desc', ['qris' => '<strong>QRIS Dinamis 24 Jam</strong>', 'va' => '<strong>Virtual Account Bank (BCA, Mandiri, BRI, BNI, Permata, BSI)</strong>']) !!}
            </p>
            <ul class="text-[11.5px] text-black/60 dark:text-white/60 space-y-1.5 list-disc list-inside">
                <li>{{ __('storefront.backoffice.tab_payments.channel_1_bullet_1') }}</li>
                <li>{{ __('storefront.backoffice.tab_payments.channel_1_bullet_2') }}</li>
                <li>{!! __('storefront.backoffice.tab_payments.channel_1_bullet_3', ['saldo' => '<strong>' . __('storefront.backoffice.tab_payments.kpi_held_balance') . '</strong>']) !!}</li>
            </ul>
        </div>

        {{-- Saluran 2: Manual Transfer --}}
        <div
            class="p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-xs space-y-3">
            <div class="flex items-center gap-2 text-[#34C759]">
                <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                <h4 class="text-[14px] font-bold text-black dark:text-white">{{ __('storefront.backoffice.tab_payments.channel_2_title') }}</h4>
            </div>
            <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                {{ __('storefront.backoffice.tab_payments.channel_2_desc') }}
            </p>
            <ul class="text-[11.5px] text-black/60 dark:text-white/60 space-y-1.5 list-disc list-inside">
                <li>{{ __('storefront.backoffice.tab_payments.channel_2_bullet_1') }}</li>
                <li>{{ __('storefront.backoffice.tab_payments.channel_2_bullet_2') }}</li>
                <li>{!! __('storefront.backoffice.tab_payments.channel_2_bullet_3', ['menu' => '<strong>' . __('storefront.backoffice.nav.orders') . '</strong>']) !!}</li>
            </ul>
        </div>
    </div>

    {{-- BENTO CARD 3: REKENING BANK & QRIS TOKO MANUAL --}}
    <div
        class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-sm space-y-5">
        <div class="flex items-center justify-between pb-4 border-b border-black/5 dark:border-white/10">
            <div class="flex items-center gap-2.5">
                <div
                    class="w-9 h-9 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white tracking-tight">{{ __('storefront.backoffice.tab_payments.manual_accounts_title') }}</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('storefront.backoffice.tab_payments.manual_accounts_desc') }}</p>
                </div>
            </div>

            <button type="button" @click="addMethodModalOpen = true"
                class="h-9 px-4 rounded-[12px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] text-xs font-bold transition flex items-center gap-1.5 cursor-pointer active:scale-[0.98]">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>{{ __('storefront.backoffice.tab_payments.add_account_btn') }}</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($paymentMethods as $method)
                <div
                    class="p-4 rounded-[18px] border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.01] flex items-start justify-between gap-3">
                    <div class="space-y-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-[14px] font-bold text-black dark:text-white truncate">{{ $method->bank_name }}</span>
                            <span
                                class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase shrink-0 {{ $method->type === 'qris' ? 'bg-[#5856D6]/10 text-[#5856D6]' : 'bg-[#007AFF]/10 text-[#007AFF]' }}">
                                {{ $method->type === 'qris' ? __('storefront.backoffice.tab_payments.modal_type_qris') : __('storefront.backoffice.tab_payments.modal_type_bank') }}
                            </span>
                        </div>
                        @if ($method->account_number)
                            <p
                                class="text-[13px] font-mono tabular-nums text-black/80 dark:text-white/80 tracking-wide font-medium">
                                {{ $method->account_number }}
                            </p>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50">{{ __('storefront.backoffice.tab_payments.account_holder_prefix') }} {{ $method->account_holder }}
                            </p>
                        @endif
                        @if ($method->qris_image_path)
                            <a href="{{ $method->qris_image_url }}" target="_blank"
                                class="text-[11px] text-[#007AFF] hover:underline font-medium inline-flex items-center gap-1 mt-1">
                                <i data-lucide="image" class="w-3 h-3"></i> {{ __('storefront.backoffice.tab_payments.view_qris_image') }}
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        {{-- TOGGLE ACTIVE --}}
                        <form action="{{ route('storefront.settings.payment_methods.toggle', $method->id) }}"
                            method="POST">
                            @csrf
                            <button type="submit" title="{{ $method->is_active ? __('storefront.backoffice.tab_payments.btn_deactivate') : __('storefront.backoffice.tab_payments.btn_activate') }}"
                                class="w-9 h-9 sm:w-8 sm:h-8 rounded-full flex items-center justify-center transition cursor-pointer {{ $method->is_active ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/25' : 'bg-black/10 text-black/40 hover:bg-black/20' }}">
                                <i data-lucide="{{ $method->is_active ? 'check' : 'power' }}" class="w-4 h-4"></i>
                            </button>
                        </form>

                        {{-- DELETE TRIGGER --}}
                        <button type="button"
                            @click="methodToDelete = {{ json_encode(['id' => $method->id, 'bank_name' => $method->bank_name, 'type' => $method->type, 'account_number' => $method->account_number]) }}; deleteModalOpen = true;"
                            title="{{ __('storefront.backoffice.tab_payments.btn_delete_account') }}"
                            class="w-9 h-9 sm:w-8 sm:h-8 rounded-full bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 text-[#FF3B30] flex items-center justify-center transition cursor-pointer">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-black/40 dark:text-white/40 space-y-1">
                    <i data-lucide="credit-card" class="w-10 h-10 mx-auto stroke-1 opacity-40 mb-2"></i>
                    <p class="text-[13px] font-medium">{{ __('storefront.backoffice.tab_payments.empty_accounts_title') }}</p>
                    <p class="text-[11.5px]">{{ __('storefront.backoffice.tab_payments.empty_accounts_desc') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
