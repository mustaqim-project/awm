@extends('layouts.admin', [
    'title' => 'Matriks Harga & Ledger Kredit - Admin Console',
    'headerTitle' => 'Matriks Tarif Model AI & Finansial Ledger',
    'headerSubtitle' => 'Kelola biaya kredit per provider render, pantau peredaran saldo di seluruh tenant, dan berikan hibah kredit',
])

@section('content')
<div class="space-y-6 pb-28 lg:pb-10" x-data="adminAiStudioPricing()">

    <!-- Header Action Cockpit -->
    <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-lg font-bold text-black dark:text-white flex items-center gap-2">
                <span class="w-8 h-8 rounded-[10px] bg-[#FF9500]/15 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="coins" class="w-4 h-4"></i>
                </span>
                <span>Matriks Harga &amp; Ledger Platform AI Studio</span>
            </h1>
            <p class="text-xs text-black/60 dark:text-white/60">
                Pusat kendali ekonomi token visual platform: rasio tarif kredit, monitoring saldo merchant, dan audit log finansial.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.ai-studio.sources.index') }}"
                class="px-4 py-2 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-xs font-semibold text-black/80 dark:text-white transition-all">
                <i data-lucide="compass" class="w-3.5 h-3.5 inline mr-1 text-[#AF52DE]"></i>
                <span>Sumber Inspirasi</span>
            </a>
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
                <span class="text-xs text-black/50 dark:text-white/50">kredit</span>
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

        <!-- Metric 3: Total Top Up & Hibah -->
        <div class="rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-black/55 dark:text-white/55">Total Top Up &amp; Hibah</span>
                <span class="w-7 h-7 rounded-[8px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="arrow-down-left" class="w-3.5 h-3.5"></i>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold tracking-tight text-black dark:text-white">{{ number_format($stats['total_topups']) }}</span>
                <span class="text-xs text-[#34C759]">kredit masuk</span>
            </div>
        </div>

        <!-- Metric 4: Total Refund Proteksi -->
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

    <!-- 2. Model Pricing Matrix (Bento Cards) -->
    <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 space-y-4">
        <div class="flex items-center justify-between">
            <div class="space-y-0.5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-black/70 dark:text-white/70 flex items-center gap-1.5">
                    <i data-lucide="sliders" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Matriks Tarif Model Generative AI</span>
                </h3>
                <p class="text-[11px] text-black/50 dark:text-white/50">Skema biaya dasar dan pengali dimensi per mesin render</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($pricingMatrix as $modelKey => $matrix)
                <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-xs font-bold text-black dark:text-white uppercase font-mono">{{ $modelKey }}</span>
                            <span class="text-[10px] text-black/50 dark:text-white/50 block capitalize">Provider: {{ $matrix['provider'] ?? 'AI Engine' }}</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-[#007AFF]/15 text-[#007AFF]">
                            {{ $matrix['base_cost'] }} Kredit
                        </span>
                    </div>

                    <div class="text-[11px] space-y-1 pt-1 border-t border-black/5 dark:border-white/5 text-black/60 dark:text-white/60">
                        <div class="flex justify-between">
                            <span>Pengali Rasio 1:1:</span>
                            <span class="font-bold text-black dark:text-white">{{ $matrix['aspect_ratio_multipliers']['1:1'] ?? 1.0 }}x</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pengali Rasio 9:16:</span>
                            <span class="font-bold text-black dark:text-white">{{ $matrix['aspect_ratio_multipliers']['9:16'] ?? 1.25 }}x</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Biaya Tambahan Upscale:</span>
                            <span class="font-bold text-black dark:text-white">+{{ $matrix['upscale_cost'] ?? 5 }} Kredit</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 3. Top Tenant Balances & Recent Transactions (2-Col Bento) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: Top Tenant Balances (5 Cols) -->
        <div class="lg:col-span-5 rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-black/70 dark:text-white/70 flex items-center gap-1.5">
                <i data-lucide="users" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Saldo Kredit Tenant Teratas</span>
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
                            <button type="button" @click="quickGrant('{{ $biz->id }}', '{{ addslashes($biz->name) }}')"
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
                <span>Transaksi Ledger Platform Terbaru</span>
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-black/5 text-[10px] text-black/50 uppercase">
                            <th class="py-2 px-2">Waktu</th>
                            <th class="py-2 px-2">Tipe</th>
                            <th class="py-2 px-2">Keterangan</th>
                            <th class="py-2 px-2 text-right">Nominal</th>
                            <th class="py-2 px-2 text-right">Saldo</th>
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
                                <td class="py-2 px-2 max-w-[180px] truncate text-[11px]" title="{{ $tx->description }}">
                                    {{ $tx->description }}
                                </td>
                                <td class="py-2 px-2 text-right whitespace-nowrap font-bold {{ $tx->amount > 0 ? 'text-[#34C759]' : 'text-red-500' }}">
                                    {{ $tx->amount > 0 ? '+' : '' }}{{ number_format($tx->amount) }}
                                </td>
                                <td class="py-2 px-2 text-right whitespace-nowrap font-mono text-black/60 text-[11px]">
                                    {{ number_format($tx->balance_after) }}
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
                        class="px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-[10px] bg-[#007AFF] text-white text-xs font-bold shadow-sm transition-all">
                        Kirim Hibah Kredit
                    </button>
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
        selectedBizId: '',
        grantAmount: 100,

        openGrantModal() {
            this.grantModalOpen = true;
            this.$nextTick(() => lucide.createIcons());
        },

        quickGrant(bizId, name) {
            this.selectedBizId = bizId;
            this.grantModalOpen = true;
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
