{{-- ========================================================================= --}}
{{-- TRUST & VERIFIED BADGES: Flora Romance (Florist & Karangan Bunga) (Florist, Bunga Segar, Buket Wisuda & Papan Bunga) --}}
{{-- ========================================================================= --}}
@props([
    'type' => 'all', // 'all', 'halal', 'bpom', 'kemenkes', 'official', 'warranty'
    'compact' => false,
])

<div class="w-full">
    @if ($type === 'all')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            {{-- Badge 1 --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs"
                     style="background-color: #E11D4818; color: #E11D48;">
                    <i data-lucide="badge-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight font-heading">
                        Flora Romance (Florist & Karangan Bunga) Resmi
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        Florist, Bunga Segar, Buket Wisuda & Papan Bunga
                    </span>
                </div>
            </div>

            {{-- Badge 2 --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs"
                     style="background-color: #E11D4818; color: #E11D48;">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight font-heading">
                        100% Produk Berkualitas
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        Standar Mutu Pilihan Terbaik
                    </span>
                </div>
            </div>

            {{-- Badge 3 --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs"
                     style="background-color: #E11D4818; color: #E11D48;">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight font-heading">
                        Pembayaran Aman QRIS
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        Standar Bank Indonesia
                    </span>
                </div>
            </div>

            {{-- Badge 4 --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs"
                     style="background-color: #E11D4818; color: #E11D48;">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight font-heading">
                        Pengiriman Cepat & Rapi
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        Kemasan Aman Sampai Tujuan
                    </span>
                </div>
            </div>
        </div>
    @elseif ($type === 'official')
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold border"
             style="background-color: #E11D4815; color: #E11D48; border-color: #E11D4830;">
            <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
            <span>Flora Romance (Florist & Karangan Bunga) Resmi</span>
        </div>
    @elseif ($type === 'warranty')
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-medium border"
             style="background-color: #E11D4812; color: #E11D48; border-color: #E11D4825;">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
            <span>100% Produk Berkualitas</span>
        </div>
    @else
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[11px] font-semibold">
            <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
            <span>Terverifikasi Resmi</span>
        </div>
    @endif
</div>