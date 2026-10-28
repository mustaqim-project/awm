@extends('layouts.app', [
    'title' => 'Riwayat Ledger & Kredit AI Studio',
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
    <!-- 1. BENTO HEADER & COCKPIT                  -->
    <!-- ========================================== -->
    <div class="relative z-10 rounded-[16px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5 transition-colors shadow-[0_1px_2px_rgba(0,0,0,0.02)]">
        <div class="space-y-1.5 max-w-2xl">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-1.5 text-xs text-black/50 dark:text-white/50 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <a href="{{ route('ai-studio.index') }}" class="hover:text-[#007AFF] transition-colors">AI Studio</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-black/80 dark:text-white/80 font-semibold">Ledger Kredit</span>
            </div>

            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-black dark:text-white flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-[10px] bg-[#FF9500]/15 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="coins" class="w-4 h-4"></i>
                </span>
                <span>Ledger &amp; Saldo Kredit AI Studio</span>
            </h1>
            <p class="text-xs sm:text-sm text-black/60 dark:text-white/60 leading-relaxed">
                Catatan mutasi kredit immutable dengan mekanisme jaminan refund 100% apabila render visual gagal diproses oleh provider.
            </p>
        </div>

        <!-- Current Balance Bento Pill & Top Up Action -->
        <div class="flex flex-wrap items-center gap-2.5">
            <div class="p-3.5 rounded-[14px] bg-[#007AFF]/10 dark:bg-[#007AFF]/20 border border-[#007AFF]/20 flex items-center gap-3">
                <span class="w-9 h-9 rounded-[10px] bg-[#007AFF] text-white flex items-center justify-center shadow-sm">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </span>
                <div>
                    <span class="text-[11px] font-semibold text-black/60 dark:text-white/60 block">Saldo Kredit Aktif</span>
                    <span class="text-xl font-black text-[#007AFF] dark:text-[#0A84FF]" x-text="credits">{{ number_format($creditsBalance) }}</span>
                    <span class="text-[10px] text-black/40 dark:text-white/40">kredit</span>
                </div>
            </div>

            <!-- Top Up Button -->
            <button type="button" @click="$dispatch('open-ai-topup-modal')"
                class="inline-flex items-center gap-1.5 px-4 py-3 rounded-[14px] bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white text-xs font-bold shadow-md shadow-orange-500/20 active:scale-[0.98] transition-all">
                <i data-lucide="zap" class="w-4 h-4 fill-white"></i>
                <span>Top Up Kredit</span>
            </button>

            <a href="{{ route('ai-studio.index') }}"
                class="px-4 py-3 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-xs font-semibold text-black/80 dark:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-all">
                &larr; Studio
            </a>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 2. TRANSACTIONS LEDGER TABLE               -->
    <!-- ========================================== -->
    <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4 text-[#007AFF]"></i>
                <h3 class="text-xs font-bold uppercase tracking-wider text-black/70 dark:text-white/70">Daftar Transaksi Ledger</h3>
            </div>
            <span class="text-xs text-black/40 dark:text-white/40">Menampilkan mutasi terbaru</span>
        </div>

        @if ($transactions->isEmpty())
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 flex items-center justify-center mx-auto">
                    <i data-lucide="receipt" class="w-6 h-6"></i>
                </div>
                <h4 class="text-sm font-bold text-black dark:text-white">Belum Ada Riwayat Transaksi</h4>
                <p class="text-xs text-black/60 dark:text-white/60 max-w-sm mx-auto">
                    Kredit akan tercatat otomatis saat Anda melakukan top-up, memulai render gambar, atau menerima pengembalian dana.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.01] dark:bg-white/[0.02] text-black/50 dark:text-white/50 uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-4 font-bold">Waktu</th>
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
                                    {{ $tx->created_at->format('d M Y, H:i') }}
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
