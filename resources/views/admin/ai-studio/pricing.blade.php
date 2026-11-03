@extends('layouts.admin', [
    'title' => __('admin.matriks_harga_ledger_kredit_admin_console'),
    'headerTitle' => 'Model Registry, Tarif AI Studio & Kurs Real-Time',
    'headerSubtitle' => 'Kelola katalog model OpenAI GPT Image 2.5, kurs USD/IDR, tarif token, dan pantau ledger kredit',
])

@section('content')
<div class="space-y-6 pb-28 lg:pb-10" x-data="adminAiStudioPricing()">

    <!-- Header Action Cockpit -->
    <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-lg font-bold text-black dark:text-white flex items-center gap-2">
                <span class="w-8 h-8 rounded-[10px] bg-[#FF9500]/15 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="cpu" class="w-4 h-4"></i>
                </span>
                <span>Model Registry, FinOps &amp; Kurs Platform AI Studio</span>
            </h1>
            <p class="text-xs text-black/60 dark:text-white/60">
                Pusat kendali model AI image generation, tarif token OpenAI GPT Image 2.5, kurs real-time USD/IDR, dan ledger audit saldo tenant.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <form action="{{ route('admin.ai-studio.pricing.sync-exchange-rate') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                    class="px-4 py-2 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white transition-all flex items-center gap-1.5">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span>Sinkronkan Kurs (Rp {{ number_format($usdToIdrRate, 0, ',', '.') }})</span>
                </button>
            </form>
            <button type="button" @click="openGrantModal()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[12px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-xs font-bold shadow-sm transition-all active:scale-[0.98]">
                <i data-lucide="gift" class="w-4 h-4"></i>
                <span>+ Hibahkan Kredit Tenant</span>
            </button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if (session('success'))
        <div class="p-4 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 text-xs font-semibold text-[#34C759] flex items-center gap-2">
            <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- 1. Stats Bento Grid (4 Metrics) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Total Beredar -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Kredit Beredar di Tenant</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-black dark:text-white">{{ number_format($stats['total_credits_circulating']) }}</span>
                <span class="text-xs text-black/50 dark:text-white/50">kredit (Rp {{ number_format($stats['total_credits_circulating'], 0, ',', '.') }})</span>
            </div>
        </div>

        <!-- Metric 2: Total Pemakaian Render -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Total Pemakaian (Usage)</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                    <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-black dark:text-white">{{ number_format($stats['total_debits']) }}</span>
                <span class="text-xs text-black/50 dark:text-white/50">kredit digunakan</span>
            </div>
        </div>

        <!-- Metric 3: Kurs USD/IDR Aktif -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Kurs Real-Time USD/IDR</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="dollar-sign" class="w-3.5 h-3.5"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-[#34C759]">Rp {{ number_format($usdToIdrRate, 0, ',', '.') }}</span>
                <span class="text-[10px] text-black/40 dark:text-white/40">per 1 USD</span>
            </div>
        </div>

        <!-- Metric 4: Total Refund Otomatis -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Total Refund Otomatis</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-black dark:text-white">{{ number_format($stats['total_refunds']) }}</span>
                <span class="text-xs text-amber-500">100% dipulihkan</span>
            </div>
        </div>
    </div>

    <!-- 2. AI Model Registry & Token Pricing Console -->
    <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="space-y-0.5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-black/70 dark:text-white/70 flex items-center gap-1.5">
                    <i data-lucide="layers" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Katalog Model AI Image Generation &amp; Tarif Token (Model Registry)</span>
                </h3>
                <p class="text-[11px] text-black/50 dark:text-white/50">
                    Standar FinOps: 1 Kredit = Rp 1. Formula: Kredit = ⌈(Biaya USD × Kurs × 1,10) / 0,65⌉ (Gross Margin 35%, Reserve 10%).
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 text-[10px] text-black/50 dark:text-white/50 uppercase font-mono">
                        <th class="py-2.5 px-3">Model AI &amp; Provider</th>
                        <th class="py-2.5 px-3">Exact Model ID</th>
                        <th class="py-2.5 px-3">Kemampuan</th>
                        <th class="py-2.5 px-3">Billing Mode</th>
                        <th class="py-2.5 px-3 text-right">Tarif Token (per 1M)</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-black/80 dark:text-white/90">
                    @forelse ($models as $m)
                        @php
                            $p = $m->activePricing;
                        @endphp
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-3">
                                <div class="font-bold text-black dark:text-white flex items-center gap-1.5">
                                    <span>{{ $m->model_name }}</span>
                                    @if ($m->is_default)
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-[#007AFF]/15 text-[#007AFF]">DEFAULT</span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-black/50 dark:text-white/50 capitalize font-mono">{{ $m->provider }} &bull; v{{ $m->version ?? '1.0' }}</span>
                            </td>
                            <td class="py-3 px-3 font-mono text-[11px] text-black/70 dark:text-white/70">
                                <code>{{ $m->exact_model_id }}</code>
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex flex-wrap gap-1 text-[10px]">
                                    @if ($m->capabilities['text_input'] ?? false)
                                        <span class="px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">Text In</span>
                                    @endif
                                    @if ($m->capabilities['image_input'] ?? false)
                                        <span class="px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400">Image In</span>
                                    @endif
                                    @if ($m->capabilities['image_edit'] ?? false)
                                        <span class="px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-600 dark:text-purple-400">Image Edit</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3 text-[11px] capitalize">
                                <span class="px-2 py-0.5 rounded font-mono text-[10px] {{ ($p?->billing_mode ?? 'token') === 'token' ? 'bg-[#5856D6]/15 text-[#5856D6]' : 'bg-black/5 dark:bg-white/10 text-black/70' }}">
                                    {{ $p?->billing_mode ?? 'token' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right text-[11px] font-mono">
                                @if (($p?->billing_mode ?? 'token') === 'token')
                                    <div>Text In: <span class="font-bold">${{ number_format($p?->text_input_rate ?? 5.0, 2) }}</span></div>
                                    <div>Image Out: <span class="font-bold text-[#007AFF]">${{ number_format($p?->image_output_rate ?? 30.0, 2) }}</span></div>
                                @else
                                    <div>Fixed: <span class="font-bold">${{ number_format($p?->fixed_per_image_cost_usd ?? 0.08, 4) }} / img</span></div>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if ($m->is_active)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#34C759]">Aktif</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openRateModal({{ json_encode($m) }})"
                                        class="px-2.5 py-1 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[10px] font-semibold text-black/80 dark:text-white transition-all">
                                        Tarif
                                    </button>
                                    <form action="{{ route('admin.ai-studio.pricing.models.toggle', $m->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="px-2.5 py-1 rounded-[8px] text-[10px] font-bold transition-all {{ $m->is_active ? 'bg-amber-500/10 hover:bg-amber-500/20 text-amber-600' : 'bg-[#34C759]/10 hover:bg-[#34C759]/20 text-[#34C759]' }}">
                                            {{ $m->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-xs text-black/50 dark:text-white/50">
                                Belum ada model AI terdaftar di Model Registry. Jalankan seeder: <code>php artisan db:seed --class=AiModelRegistrySeeder</code>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Top Tenant Balances & Recent Transactions (2-Col Bento) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: Top Tenant Balances (5 Cols) -->
        <div class="lg:col-span-5 rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-black/70 dark:text-white/70 flex items-center gap-1.5">
                <i data-lucide="users" class="w-4 h-4 text-[#007AFF]"></i>
                <span>{{ __('admin.saldo_kredit_tenant_teratas') }}</span>
            </h3>

            <div class="divide-y divide-black/5 dark:divide-white/5 max-h-[460px] overflow-y-auto">
                @foreach ($businesses as $biz)
                    <div class="py-2.5 flex items-center justify-between text-xs">
                        <div class="min-w-0 flex-1 pr-2">
                            <span class="font-bold text-black dark:text-white truncate block">{{ $biz->name }}</span>
                            <span class="text-[10px] text-black/40 dark:text-white/40 truncate block">{{ $biz->slug }}</span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="font-mono font-bold text-[#007AFF] text-xs">{{ number_format($biz->ai_credits_balance ?? 0) }}</span>
                            <button type="button" @click="quickGrant($el.dataset.id, $el.dataset.name)"
                                data-id="{{ $biz->id }}"
                                data-name="{{ $biz->name }}"
                                class="px-2 py-1 rounded-[6px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-[#007AFF] hover:text-white text-[10px] font-bold transition-all">
                                + Hibah
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right: Recent Ledger Transactions (7 Cols) -->
        <div class="lg:col-span-7 rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-black/70 dark:text-white/70 flex items-center gap-1.5">
                <i data-lucide="receipt" class="w-4 h-4 text-[#34C759]"></i>
                <span>Audit FinOps &amp; Transaksi Ledger AI Platform</span>
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-black/5 text-[10px] text-black/50 uppercase font-mono">
                            <th class="py-2 px-2">Waktu</th>
                            <th class="py-2 px-2">Tipe</th>
                            <th class="py-2 px-2">Keterangan</th>
                            <th class="py-2 px-2 text-right">Nominal</th>
                            <th class="py-2 px-2 text-right">Saldo</th>
                            <th class="py-2 px-2 text-right">Biaya USD / Kurs</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 text-black/80 dark:text-white/90">
                        @foreach ($recentTransactions as $tx)
                            <tr class="hover:bg-black/[0.015]">
                                <td class="py-2 px-2 whitespace-nowrap text-black/50 text-[11px]">
                                    {{ $tx->created_at->format('d/m H:i') }}
                                </td>
                                <td class="py-2 px-2 whitespace-nowrap">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $tx->amount > 0 ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-red-500/10 text-red-500' }}">
                                        {{ $tx->type }}
                                    </span>
                                </td>
                                <td class="py-2 px-2 max-w-[160px] truncate text-[11px]" title="{{ $tx->description }}">
                                    {{ $tx->description }}
                                </td>
                                <td class="py-2 px-2 text-right whitespace-nowrap font-bold {{ $tx->amount > 0 ? 'text-[#34C759]' : 'text-red-500' }}">
                                    {{ $tx->amount > 0 ? '+' : '' }}{{ number_format($tx->amount) }}
                                </td>
                                <td class="py-2 px-2 text-right whitespace-nowrap font-mono text-black/60 text-[11px]">
                                    {{ number_format($tx->balance_after) }}
                                </td>
                                <td class="py-2 px-2 text-right whitespace-nowrap font-mono text-[10px] text-black/50 dark:text-white/50">
                                    @if ($tx->api_cost_usd)
                                        <div>${{ number_format((float) $tx->api_cost_usd, 4) }}</div>
                                        <div class="text-[9px]">@ Rp {{ number_format((float) ($tx->exchange_rate ?? $usdToIdrRate), 0) }}</div>
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-2">
                {{ $recentTransactions->links() }}
            </div>
        </div>

    </div>

    <!-- MODAL: HIBAH KREDIT KE TENANT -->
    <div x-show="grantModalOpen" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display: none;">
        <div @click.away="grantModalOpen = false"
            class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="gift" class="w-4 h-4"></i>
                    </span>
                    <h3 class="text-sm font-bold text-black dark:text-white">Hibahkan Kredit AI Studio</h3>
                </div>
                <button type="button" @click="grantModalOpen = false" class="p-1 text-black/40 hover:bg-black/5 rounded-full">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('admin.ai-studio.pricing.grant-credits') }}" method="POST" class="space-y-3">
                @csrf
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">Pilih Tenant Bisnis <span class="text-red-500">*</span></label>
                    <select name="business_id" x-model="selectedBizId" required
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                        <option value="">-- Pilih Bisnis --</option>
                        @foreach ($businesses as $b)
                            <option value="{{ $b->id }}">{{ $b->name }} (Saldo: {{ number_format($b->ai_credits_balance ?? 0) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">Jumlah Kredit yang Diberikan <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" @click="grantAmount = 50"
                            class="py-1.5 rounded-[8px] border text-xs font-bold"
                            :class="grantAmount == 50 ? 'bg-[#007AFF] text-white border-[#007AFF]' : 'bg-black/5 text-black/70'">
                            50
                        </button>
                        <button type="button" @click="grantAmount = 100"
                            class="py-1.5 rounded-[8px] border text-xs font-bold"
                            :class="grantAmount == 100 ? 'bg-[#007AFF] text-white border-[#007AFF]' : 'bg-black/5 text-black/70'">
                            100
                        </button>
                        <button type="button" @click="grantAmount = 250"
                            class="py-1.5 rounded-[8px] border text-xs font-bold"
                            :class="grantAmount == 250 ? 'bg-[#007AFF] text-white border-[#007AFF]' : 'bg-black/5 text-black/70'">
                            250
                        </button>
                        <button type="button" @click="grantAmount = 500"
                            class="py-1.5 rounded-[8px] border text-xs font-bold"
                            :class="grantAmount == 500 ? 'bg-[#007AFF] text-white border-[#007AFF]' : 'bg-black/5 text-black/70'">
                            500
                        </button>
                    </div>
                    <input type="number" name="credits" x-model="grantAmount" required min="1" max="500000"
                        class="w-full mt-1 px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-bold text-black dark:text-white">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">Catatan / Alasan Hibah</label>
                    <input type="text" name="description" placeholder="Contoh: Bonus uji coba fitur AI Studio"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-black/5">
                    <button type="button" @click="grantModalOpen = false"
                        class="px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60">{{ __('common.cancel') }}</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-[10px] bg-[#007AFF] text-white text-xs font-bold shadow-sm transition-all">{{ __('admin.kirim_hibah_kredit') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: UBAH TARIF TOKEN MODEL AI -->
    <div x-show="rateModalOpen" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display: none;">
        <div @click.away="rateModalOpen = false"
            class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-[10px] bg-[#5856D6]/15 text-[#5856D6] flex items-center justify-center">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-black dark:text-white" x-text="currentModel?.model_name || 'Tarif Model'"></h3>
                        <p class="text-[10px] text-black/50 dark:text-white/50" x-text="currentModel?.exact_model_id"></p>
                    </div>
                </div>
                <button type="button" @click="rateModalOpen = false" class="p-1 text-black/40 hover:bg-black/5 rounded-full">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/ai-studio/pricing/models') }}/' + (currentModel?.id || '') + '/update-rates'" method="POST" class="space-y-3">
                @csrf
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">Text Input Rate (USD per 1M Tokens)</label>
                    <input type="number" step="0.01" name="text_input_rate" x-model="modelRates.text_input_rate" required
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">Cached Text Rate (USD per 1M Tokens)</label>
                    <input type="number" step="0.01" name="cached_text_rate" x-model="modelRates.cached_text_rate"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">Image Input Rate (USD per 1M Tokens)</label>
                    <input type="number" step="0.01" name="image_input_rate" x-model="modelRates.image_input_rate" required
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-black dark:text-white">Image Output Rate (USD per 1M Tokens)</label>
                    <input type="number" step="0.01" name="image_output_rate" x-model="modelRates.image_output_rate" required
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                </div>

                <div class="pt-2 flex items-center justify-end gap-2 border-t border-black/5">
                    <button type="button" @click="rateModalOpen = false"
                        class="px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60">{{ __('common.cancel') }}</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-[10px] bg-[#007AFF] text-white text-xs font-bold shadow-sm transition-all">Simpan Tarif</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function adminAiStudioPricing() {
    return {
        grantModalOpen: false,
        rateModalOpen: false,
        selectedBizId: '',
        grantAmount: 100,
        currentModel: null,
        modelRates: {
            text_input_rate: 5.0,
            cached_text_rate: 1.25,
            image_input_rate: 8.0,
            image_output_rate: 30.0
        },

        openGrantModal() {
            this.grantModalOpen = true;
            this.$nextTick(() => lucide.createIcons());
        },

        quickGrant(bizId, name) {
            this.selectedBizId = bizId;
            this.grantModalOpen = true;
            this.$nextTick(() => lucide.createIcons());
        },

        openRateModal(model) {
            this.currentModel = model;
            const p = model.active_pricing || {};
            this.modelRates.text_input_rate = p.text_input_rate || 5.0;
            this.modelRates.cached_text_rate = p.cached_text_rate || 1.25;
            this.modelRates.image_input_rate = p.image_input_rate || 8.0;
            this.modelRates.image_output_rate = p.image_output_rate || 30.0;
            this.rateModalOpen = true;
            this.$nextTick(() => lucide.createIcons());
        },

        init() {
            lucide.createIcons();
        }
    };
}
</script>
@endpush
@endsection
