@extends('layouts.app', [
    'title' => __('ai.riwayat_ledger_kredit_ai_studio'),
    'headerTitle' => 'Ledger Kredit & Mutasi Transaksi AI Studio',
    'headerSubtitle' => 'Transparansi penuh atas setiap reservasi kredit (two-phase hold), pemakaian render, dan pengembalian dana',
])

@section('content')
<div class="space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="{
    credits: {{ (int) $creditsBalance }},
    init() {
        lucide.createIcons();
        window.addEventListener('ai-credits-updated', (e) => {
            if (e.detail && typeof e.detail.credits !== 'undefined') {
                this.credits = e.detail.credits;
            }
        });
    }
}">

    <!-- ========================================== -->
    <!-- 1. BENTO APPLE HIG MODULE HEADER          -->
    <!-- ========================================== -->
    <x-module-header
        title="Ledger Kredit & Mutasi Transaksi AI Studio"
        subtitle="Transparansi penuh atas setiap reservasi kredit (two-phase hold), pemakaian render, dan pengembalian saldo"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'AI Studio Visual', 'url' => route('ai-studio.index')],
            ['label' => 'Riwayat Kredit', 'url' => null]
        ]">

        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
            <!-- Saldo Card -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 text-xs font-semibold text-black/80 dark:text-white/90">
                <span class="w-5 h-5 rounded-full bg-[#FF9500]/15 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="coins" class="w-3 h-3"></i>
                </span>
                <span>Saldo Kredit:</span>
                <span class="font-bold text-[#007AFF] dark:text-[#0A84FF] text-sm" x-text="credits">{{ number_format($creditsBalance) }}</span>
            </div>

            <!-- Top Up Trigger -->
            <button type="button" @click="$dispatch('open-ai-topup-modal')"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-[10px] bg-[#FF9500] hover:bg-[#E08500] text-white text-xs font-semibold shadow-xs transition-all cursor-pointer">
                <i data-lucide="zap" class="w-3.5 h-3.5 fill-white"></i>
                <span>Top Up Kredit</span>
            </button>
        </div>
    </x-module-header>

    <!-- ========================================== -->
    <!-- 2. TRANSACTIONS LEDGER TABLE               -->
    <!-- ========================================== -->
    <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4 text-[#007AFF]"></i>
                <h3 class="text-xs font-bold uppercase tracking-wider text-black/70 dark:text-white/70">{{ __('ai.daftar_transaksi_ledger') }}</h3>
            </div>
            <span class="text-xs text-black/40 dark:text-white/40">Menampilkan mutasi terbaru</span>
        </div>

        @if ($transactions->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                    <i data-lucide="receipt" class="w-6 h-6"></i>
                </div>
                <h4 class="text-sm font-bold text-black dark:text-white">{{ __('ai.belum_ada_riwayat_transaksi') }}</h4>
                <p class="text-xs text-black/60 dark:text-white/60 max-w-sm mx-auto">
                    Kredit akan tercatat otomatis saat Anda melakukan top-up, memulai render gambar, atau menerima pengembalian dana.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.01] dark:bg-white/[0.02] text-black/50 dark:text-white/50 uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4 font-bold">Waktu (WIB / UTC+7)</th>
                            <th class="py-3 px-4 font-bold">Jenis Mutasi</th>
                            <th class="py-3 px-4 font-bold">Keterangan</th>
                            <th class="py-3 px-4 font-bold text-right">Nominal Kredit</th>
                            <th class="py-3 px-4 font-bold text-right">Saldo Sesudah</th>
                            <th class="py-3 px-4 font-bold text-center">Ref ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5 text-black/80 dark:text-white/90">
                        @foreach ($transactions as $tx)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                                <!-- Timestamp -->
                                <td class="py-3 px-4 whitespace-nowrap text-black/60 dark:text-white/60">
                                    {{ $tx->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                                </td>

                                <!-- Type Badge -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if ($tx->type === 'topup' || $tx->type === 'bonus')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            <i data-lucide="arrow-down-left" class="w-3 h-3"></i>
                                            <span>Top Up / Hibah</span>
                                        </span>
                                    @elseif ($tx->type === 'usage')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/15 text-[#007AFF]">
                                            <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                                            <span>Pemesanan Render</span>
                                        </span>
                                    @elseif ($tx->type === 'refund')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                            <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                            <span>Pengembalian (Refund)</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-black/5 text-black/70">
                                            <span>{{ ucfirst($tx->type) }}</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Description -->
                                <td class="py-3 px-4 max-w-xs sm:max-w-md truncate">
                                    {{ $tx->description }}
                                </td>

                                <!-- Amount (Positive or Negative) -->
                                <td class="py-3 px-4 text-right whitespace-nowrap font-bold">
                                    @if ($tx->amount > 0)
                                        <span class="text-[#34C759]">+{{ number_format($tx->amount) }}</span>
                                    @else
                                        <span class="text-red-500">{{ number_format($tx->amount) }}</span>
                                    @endif
                                </td>

                                <!-- Balance After -->
                                <td class="py-3 px-4 text-right whitespace-nowrap font-mono text-black/70 dark:text-white/70">
                                    {{ number_format($tx->balance_after) }}
                                </td>

                                <!-- Idempotency Ref -->
                                <td class="py-3 px-4 text-center whitespace-nowrap font-mono text-[10px] text-black/40 dark:text-white/40">
                                    {{ substr($tx->idempotency_key, 0, 10) }}...
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-black/5 dark:border-white/5">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Top Up Modal Component -->
    @include('app.ai-studio.partials.topup-modal')

</div>
@endsection
