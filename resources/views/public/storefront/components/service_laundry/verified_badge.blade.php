{{-- ========================================================================= --}}
{{-- TRUST & VERIFIED BADGES: Aqua Bubble (Laundry Kiloan & Dry Clean) (Laundry Kiloan, Dry Clean, Cuci Sepatu & Karpet) --}}
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
                     style="background-color: #0284C718; color: #0284C7;">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight font-heading">
                        1 Mesin 1 Pelanggan
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        Pakaian Tidak Dicampur
                    </span>
                </div>
            </div>

            {{-- Badge 2 --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs"
                     style="background-color: #0284C718; color: #0284C7;">
                    <i data-lucide="wind" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight font-heading">
                        Deterjen Ramah Serat
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        Wangi Tahan Lama & Lembut
                    </span>
                </div>
            </div>

            {{-- Badge 3 --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs"
                     style="background-color: #0284C718; color: #0284C7;">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight font-heading">
                        Garansi Tidak Luntur & Hilang
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        Penghitungan Teliti Saat Diterima
                    </span>
                </div>
            </div>

            {{-- Badge 4 --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs"
                     style="background-color: #0284C718; color: #0284C7;">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight font-heading">
                        Layanan Antar Jemput
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        Praktis Tanpa Repot Keluar Rumah
                    </span>
                </div>
            </div>
        </div>
    @elseif ($type === 'official')
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold border"
             style="background-color: #0284C715; color: #0284C7; border-color: #0284C730;">
            <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
            <span>1 Mesin 1 Pelanggan</span>
        </div>
    @elseif ($type === 'warranty')
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-medium border"
             style="background-color: #0284C712; color: #0284C7; border-color: #0284C725;">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
            <span>Deterjen Ramah Serat</span>
        </div>
    @else
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[11px] font-semibold">
            <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
            <span>Terverifikasi Resmi</span>
        </div>
    @endif
</div>